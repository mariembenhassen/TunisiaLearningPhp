<?php
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunisialearning";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data from JSON body
$data = json_decode(file_get_contents('php://input'), true);

// Extract parameters
$idenseignant = isset($data['idenseignant']) ? $data['idenseignant'] : '';
$idniveau = isset($data['idniveau']) ? $data['idniveau'] : '';
$idmatiere = isset($data['idmatiere']) ? $data['idmatiere'] : '';
$idclasse = isset($data['idclasse']) ? $data['idclasse'] : '';
$observation = isset($data['observation']) ? $data['observation'] : '';
$idetablissement = isset($data['idetablissement']) ? $data['idetablissement'] : '';
$date = isset($data['date']) ? $data['date'] : '';
$heure = isset($data['heure']) ? $data['heure'] : '';

// Validate parameters
if (empty($idenseignant) || empty($idniveau) || empty($idmatiere) || empty($idclasse) || empty($observation) || empty($idetablissement) || empty($date) || empty($heure)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

// Prepare the SQL statement
$stmt = $conn->prepare("
    INSERT INTO talimnet_rattrapage 
    (idenseignant, idniveau, idclasse, idmatiere, date, heure, etat, idutilisateur, observation, idprofil, idetablissement) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Define the values
$etat = 2;
$idutilisateur = $idenseignant;
$idprofil = 4;

// Bind parameters
$stmt->bind_param('iiiissiisii', 
    $idenseignant,  // integer
    $idniveau,      // integer
    $idclasse,      // integer
    $idmatiere,     // integer
    $date,          // string
    $heure,         // string
    $etat,          // integer
    $idutilisateur, // integer
    $observation,   // string
    $idprofil,      // integer
    $idetablissement // integer
);

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Record inserted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to insert record: ' . $stmt->error]);
}

// Close the connection
$stmt->close();
$conn->close();
?>
