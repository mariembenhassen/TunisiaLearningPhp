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
if (!isset($data['idUser']) || !isset($data['selectedParentId']) || !isset($data['message']) || !isset($data['idsource'])) {
    die(json_encode(["error" => "Error: Missing required parameters."]));
}

// Parameters from JSON data
$idUser = $data['idUser'];
$selectedParentId = $data['selectedParentId'];
$message = $data['message'];
$idsource = $data['idsource'];

// Fetch idetablissement for the selected parent
$sqlEtablissement = "SELECT idetablissement FROM talimnet_tuteur_eleves WHERE idtuteur = ?";
$stmtEtablissement = $conn->prepare($sqlEtablissement);
$stmtEtablissement->bind_param("i", $selectedParentId);
$stmtEtablissement->execute();
$resultEtablissement = $stmtEtablissement->get_result();

if ($resultEtablissement->num_rows > 0) {
    $row = $resultEtablissement->fetch_assoc();
    $idetablissement = $row['idetablissement'];
} else {
    die(json_encode(["error" => "Error: No establishment found for the selected parent."]));
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
$sqlInsert = "INSERT INTO talimnet_mail (idetablissement, idutilisateur, dateheure, lu, expediteur, mail, idadministrateur, vers_qui, destinataire, idannescolaire, idsource) VALUES (?, ?, NOW(), 1, 1, ?, 0, 5, ?, ?, ?)";
$stmt = $conn->prepare($sqlInsert);
$stmt->bind_param("iissii", $idetablissement, $idUser, $message, $selectedParentId, $idannescolaire, $idsource);

if ($stmt->execute()) {
    $insertedId = $conn->insert_id; 
    echo json_encode([
        "success" => "Message sent successfully.",
        "message" => $message,  
        "idsource" => $idsource, 
        "insertedId" => $insertedId 
    ]);
} else {
    echo json_encode(["error" => "Error: " . $stmt->error]);
}

$conn->close();
?>
