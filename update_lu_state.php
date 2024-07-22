<?php
// Send CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type: application/json');

// Debugging output
error_log("Debugging: Script started");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"));

// Debugging output
error_log("Debugging: JSON decoded");

// Check if parameters are set
if (isset($data->messageId) && isset($data->lu)) {
    $messageId = $data->messageId;
    $lu = $data->lu;

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

    // Update query
    $stmt = $conn->prepare("UPDATE talimnet_mail SET lu = ? WHERE id = ?");
    
    if ($stmt === false) {
        die(json_encode(["error" => "Failed to prepare statement: " . $conn->error]));
    }
    
    $stmt->bind_param("ii", $lu, $messageId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Message status updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update message status."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid parameters."]);
}

// Debugging output
error_log("Debugging: Script ended");
?>
