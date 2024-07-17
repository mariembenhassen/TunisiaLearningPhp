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
    die("Connection failed: " . $conn->connect_error);
}

// Decode JSON data from POST body
$data = json_decode(file_get_contents("php://input"), true);

// Check if required fields are present
if (!isset($data['idUser']) || !isset($data['selectedTeacherId']) || !isset($data['message'])) {
    die("Error: Missing required parameters.");
}

// Parameters from JSON data
$idUser = $data['idUser'];
$selectedTeacherId = $data['selectedTeacherId'];
$message = $data['message'];

// Fetch idannescolaire where en_cours=1
$sqlAnneeScolaire = "SELECT id FROM talimnet_anneescolaire WHERE en_cours=1";
$resultAnneeScolaire = $conn->query($sqlAnneeScolaire);
if ($resultAnneeScolaire->num_rows > 0) {
    $row = $resultAnneeScolaire->fetch_assoc();
    $idannescolaire = $row['id'];
} else {
    // Handle error if no result found
    die("Error: No current school year found.");
}

// Prepare and execute the SQL insert query
$sqlInsert = "INSERT INTO talimnet_mail (idetablissement, idutilisateur, dateheure, lu, expediteur, mail, idadministrateur, vers_qui, destinataire, idannescolaire, idsource) VALUES (1, $idUser, NOW(), 1, 2, '$message', 0, 4, $selectedTeacherId, $idannescolaire, LAST_INSERT_ID())";

if ($conn->query($sqlInsert) === TRUE) {
    echo "Message sent successfully.";
} else {
    echo "Error: " . $sqlInsert . "<br>" . $conn->error;
}

$conn->close();
?>
