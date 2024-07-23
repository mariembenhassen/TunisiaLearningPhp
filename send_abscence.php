<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
$idenseignant = isset($data['idenseignant']) ? (int)$data['idenseignant'] : 0;
$observation = isset($data['observation']) ? $data['observation'] : '';
$idetablissement = isset($data['idetablissement']) ? (int)$data['idetablissement'] : 0;
$date = isset($data['date']) ? $data['date'] : '';

// Validate parameters
if (empty($idenseignant) || empty($observation) || empty($idetablissement) || empty($date)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

// Prepare the SQL statement
$stmt = $conn->prepare("
    INSERT INTO talimnet_absence
    (idenseignant, date, etat, idutilisateur, observation, idprofil, idetablissement) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

// Define the values
$etat = 2;
$idutilisateur = $idenseignant;
$idprofil = 4;

// Bind parameters
$stmt->bind_param('isiisii', 
    $idenseignant,  // integer
    $date,          // string
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
