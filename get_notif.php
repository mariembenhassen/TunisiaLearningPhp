<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Handle preflight request
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400'); // Cache for 1 day
    exit(0);
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

// Get the iduser from the request (e.g., from a GET or POST request)
$iduser = isset($_GET['iduser']) ? intval($_GET['iduser']) : 0;

// Prepare and execute the SQL query to get the latest unseen message
$sql = "SELECT id FROM talimnet_mail WHERE destinataire = ? AND lu <> 0 ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die(json_encode(["error" => "SQL prepare error: " . $conn->error]));
}

$stmt->bind_param('i', $iduser);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'message' => "Vous avez reçu un nouveau message, veuillez le vérifier."
    ]);
} else {
    echo json_encode([
        'message' => "Aucun nouveau message."
    ]);
}

// Close the database connection
$conn->close();
?>