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
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$iduser = null;
$idetablissement = null;

// Check if iduser and idetablissement are provided via POST or GET
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $iduser = $_POST['iduser'];
    $idetablissement = $_POST['idetablissement'];
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $iduser = $_GET['iduser'];
    $idetablissement = $_GET['idetablissement'];
}

// Validate parameters
if ($iduser === null || $idetablissement === null) {
    die(json_encode(array('error' => 'iduser and/or idetablissement not provided')));
}

// Query to fetch messages
$sql = "SELECT * FROM talimnet_mail WHERE vers_qui = 5 AND destinataire = $iduser AND idetablissement = $idetablissement";
$result = $conn->query($sql);

if ($result !== false && $result->num_rows > 0) {
    // Output data of each row with specific fields
    $messages = array();
    while($row = $result->fetch_assoc()) {
        $senderName = '';
        if ($row['idadministrateur'] == 0) {
            // Fetch teacher's name
            $teacherSql = "SELECT nom, prenom FROM talimnet_enseignants WHERE id = " . $row['idutilisateur'];
            $teacherResult = $conn->query($teacherSql);
            if ($teacherResult !== false && $teacherResult->num_rows > 0) {
                $teacherRow = $teacherResult->fetch_assoc();
                $senderName = $teacherRow['nom'] . ' ' . $teacherRow['prenom'];
            } else {
                $senderName = 'Unknown Teacher';
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
