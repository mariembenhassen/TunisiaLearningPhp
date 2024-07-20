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

// Get the idsource parameter from the GET request
if (isset($_GET['idsource'])) {
    $idsource = intval($_GET['idsource']); // Sanitize the input to prevent SQL injection

    // Fetch the original message
    $stmt = $conn->prepare("SELECT id, idetablissement, idutilisateur, dateheure, lu, expediteur, mail, idadministrateur, vers_qui, destinataire, idannescolaire FROM talimnet_mail WHERE id = ?");
    if ($stmt === false) {
        die(json_encode(["error" => "Failed to prepare the SQL statement: " . $conn->error]));
    }

    $stmt->bind_param("i", $idsource);
    $stmt->execute();
    $originalMessageResult = $stmt->get_result();
    $originalMessage = $originalMessageResult->fetch_assoc();
    $stmt->close();

    // Fetch the replies excluding the original message
    $stmt = $conn->prepare("SELECT id, idetablissement, idutilisateur, dateheure, lu, expediteur, mail, idadministrateur, vers_qui, destinataire, idannescolaire FROM talimnet_mail WHERE idsource = ? AND id != ? ORDER BY dateheure ASC");
    if ($stmt === false) {
        die(json_encode(["error" => "Failed to prepare the SQL statement: " . $conn->error]));
    }

    $stmt->bind_param("ii", $idsource, $idsource);
    $stmt->execute();
    $repliesResult = $stmt->get_result();
    $replies = [];
    while ($row = $repliesResult->fetch_assoc()) {
        $replies[] = $row;
    }
    $stmt->close();

    // Output the result in JSON format
    echo json_encode([
        'originalMessage' => $originalMessage,
        'replies' => $replies
    ]);

} else {
    echo json_encode(['message' => 'No idsource parameter provided.']);
}

// Close the connection
$conn->close();
?>
