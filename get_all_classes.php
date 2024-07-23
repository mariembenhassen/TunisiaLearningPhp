<?php
header('Content-Type: application/json');
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

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
$idniveau = isset($data['idniveau']) ? $data['idniveau'] : '';
$idetablissement = isset($data['idetablissement']) ? $data['idetablissement'] : '';

// Validate parameters
if (empty($idniveau) || empty($idetablissement)) {
    echo json_encode(['status' => 'error', 'message' => 'idniveau and idetablissement are required.']);
    exit;
}

// Prepare the SQL statement
$stmt = $conn->prepare("
    SELECT id, classe 
    FROM talimnet_classes 
    WHERE idniveau = ? AND idetablissement = ?
");

// Bind the parameters
$stmt->bind_param('ii', $idniveau, $idetablissement);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch all rows
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Return the result in JSON format
echo json_encode(['status' => 'success', 'data' => $rows]);

// Close the connection
$stmt->close();
$conn->close();
?>
