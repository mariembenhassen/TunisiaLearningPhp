<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunisialearning";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Decode JSON data from POST body
$data = json_decode(file_get_contents("php://input"), true);

// Check if required fields are present
if (!isset($data['idUser']) || !isset($data['selectedTeacherId']) || !isset($data['message'])) {
    die(json_encode(["error" => "Error: Missing required parameters."]));
}

// Parameters from JSON data
$idUser = $data['idUser'];
$selectedTeacherId = $data['selectedTeacherId'];
$message = $data['message'];

// Fetch idetablissement from talimnet_enseignants based on selectedTeacherId
$sqlEtablissement = "SELECT idetablissement FROM talimnet_enseignants WHERE id = ?";
$stmtEtablissement = $conn->prepare($sqlEtablissement);
if (!$stmtEtablissement) {
    die(json_encode(["error" => "Error preparing statement: " . $conn->error]));
}
$stmtEtablissement->bind_param("i", $selectedTeacherId);
$stmtEtablissement->execute();
$resultEtablissement = $stmtEtablissement->get_result();

if ($resultEtablissement->num_rows > 0) {
    $rowEtablissement = $resultEtablissement->fetch_assoc();
    $idetablissement = $rowEtablissement['idetablissement'];
} else {
    die(json_encode(["error" => "Error: No establishment found for the selected teacher."]));
}

// Fetch idannescolaire where en_cours=1
$sqlAnneeScolaire = "SELECT id FROM talimnet_anneescolaire WHERE en_cours=1";
$resultAnneeScolaire = $conn->query($sqlAnneeScolaire);
if ($resultAnneeScolaire->num_rows > 0) {
    $row = $resultAnneeScolaire->fetch_assoc();
    $idannescolaire = $row['id'];
} else {
    die(json_encode(["error" => "Error: No current school year found."]));
}

// Prepare and execute the SQL insert query
$sqlInsert = "INSERT INTO talimnet_mail (idetablissement, idutilisateur, dateheure, lu, expediteur, mail, idadministrateur, vers_qui, destinataire, idannescolaire) VALUES (?, ?, NOW(), 1, 2, ?, 0, 4, ?, ?)";
$stmtInsert = $conn->prepare($sqlInsert);
if (!$stmtInsert) {
    die(json_encode(["error" => "Error preparing insert statement: " . $conn->error]));
}
$stmtInsert->bind_param("iissi", $idetablissement, $idUser, $message, $selectedTeacherId, $idannescolaire);

if ($stmtInsert->execute()) {
    // Get the ID of the newly inserted row
    $newId = $conn->insert_id;
    
    // Update the idsource field for the new row with the same ID
    $sqlUpdate = "UPDATE talimnet_mail SET idsource = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ii", $newId, $newId);
    
    if ($stmtUpdate->execute()) {
        echo json_encode([
            "success" => "Message sent successfully.",
            "message" => $message,
            "idsource" => $newId  // Return the ID source
        ]);
    } else {
        echo json_encode(["error" => "Error updating idsource: " . $stmtUpdate->error]);
    }
} else {
    echo json_encode(["error" => "Error: " . $stmtInsert->error]);
}

$stmtEtablissement->close();
$stmtInsert->close();
$stmtUpdate->close();
$conn->close();
?>
