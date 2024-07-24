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

// Debug: Log connection success
error_log("Database connection successful");

// Get parameters from the request
$idniveau = isset($_GET['idniveau']) ? intval($_GET['idniveau']) : 0;
$idclasse = isset($_GET['idclasse']) ? intval($_GET['idclasse']) : 0;
$idetablissement = isset($_GET['idetablissement']) ? intval($_GET['idetablissement']) : 0;

// Debug: Log input parameters
error_log("Parameters - idniveau: $idniveau, idclasse: $idclasse, idetablissement: $idetablissement");

// Calculate the date 7 days ago
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

// Debug: Log the calculated date
error_log("Date 7 days ago: $sevenDaysAgo");

// Prepare and execute the SQL query to check for new rows
$sql = "SELECT type, date FROM talimnet_cours WHERE idniveau = ? AND idclasse = ? AND idetablissement = ? AND date >= ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die(json_encode(["error" => "SQL prepare error: " . $conn->error]));
}

// Debug: Log SQL statement preparation success
error_log("SQL statement prepared successfully");

// Bind parameters and execute statement
$stmt->bind_param('iiss', $idniveau, $idclasse, $idetablissement, $sevenDaysAgo);
$executeResult = $stmt->execute();

if ($executeResult === false) {
    die(json_encode(["error" => "SQL execute error: " . $stmt->error]));
}

// Debug: Log SQL statement execution success
error_log("SQL statement executed successfully");

// Get the result
$result = $stmt->get_result();

// Check if there are any new rows
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'type' => $row['type'],
        'date' => $row['date']
    ];
}

// Prepare the response
if (count($notifications) > 0) {
    $message = "Vous avez de nouveaux cours ou exercices ajoutés récemment.";
    echo json_encode([
        'message' => $message,
        'notifications' => $notifications
    ]);
} else {
    echo json_encode([
        'message' => "Aucun nouveau cours ou exercice ajouté récemment.",
        'notifications' => []
    ]);
}

// Close the database connection
$conn->close();
?>
