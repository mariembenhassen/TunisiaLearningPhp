<?php
header('Content-Type: application/json; charset=utf-8');

// Allow CORS and handle preflight requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
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
$conn->set_charset("utf8mb4"); // Ensure UTF-8 encoding

// Check connection
if ($conn->connect_error) {
    die(json_encode(array('error' => 'Connection failed: ' . $conn->connect_error)));
}

// Initialize variables
$iduser = isset($_REQUEST['iduser']) ? $_REQUEST['iduser'] : null;
$idetablissement = isset($_REQUEST['idetablissement']) ? $_REQUEST['idetablissement'] : null;

// Validate parameters
if ($iduser === null || $idetablissement === null) {
    die(json_encode(array('error' => 'iduser and/or idetablissement not provided')));
}

// Fetch the current school year
$currentYearQuery = "SELECT id FROM talimnet_anneescolaire WHERE en_cours = 1";
$currentYearResult = $conn->query($currentYearQuery);

if ($currentYearResult->num_rows > 0) {
    $currentYearRow = $currentYearResult->fetch_assoc();
    $currentYear = $currentYearRow['id'];
} else {
    die(json_encode(array('error' => 'Current school year not found.')));
}

// Query to fetch messages, including the current school year
$sql = "SELECT * FROM talimnet_mail 
        WHERE vers_qui = 4 
        AND destinataire = $iduser 
        AND idetablissement = $idetablissement
        AND idannescolaire = $currentYear";
$result = $conn->query($sql);

if ($result !== false && $result->num_rows > 0) {
    // Output data of each row with specific fields
    $messages = array();
    while ($row = $result->fetch_assoc()) {
        $senderName = '';
        if ($row['idadministrateur'] == 0) {
            // Fetch tutor's name
            $tutorSql = "SELECT nomprenom FROM talimnet_tuteur WHERE id = " . $row['idutilisateur'];
            $tutorResult = $conn->query($tutorSql);
            if ($tutorResult !== false && $tutorResult->num_rows > 0) {
                $tutorRow = $tutorResult->fetch_assoc();
                $senderName = $tutorRow['nomprenom'];
            } else {
                $senderName = 'Unknown Tutor';
            }
        } else {
            $senderName = 'Admin';
        }
        $messages[] = array(
            'id' => $row['id'],
            'mail' => $row['mail'],
            'dateheure' => $row['dateheure'],
            'lu' => $row['lu'],
            'idadministrateur' => $row['idadministrateur'],
            'idsender' => $row['idutilisateur'],
            'sender_name' => $senderName,
            'idannescolaire' => $row['idannescolaire'],
            'idsource' => $row['idsource']
        );
    }
    echo json_encode($messages);
} else {
    echo json_encode(array()); // Return empty array if no messages found
}

$conn->close();
?>
