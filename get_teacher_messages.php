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

// Fetch the current academic year ID
$currentYearQuery = "SELECT id FROM talimnet_anneescolaire WHERE en_cours = 1";
$currentYearResult = $conn->query($currentYearQuery);

if ($currentYearResult === false || $currentYearResult->num_rows == 0) {
    die(json_encode(array('error' => 'Current academic year not found')));
}

$currentYearRow = $currentYearResult->fetch_assoc();
$currentYearId = $currentYearRow['id'];

// Fetch unique idsource for messages where iduser is either destinataire or idutilisateur
$sql = "SELECT DISTINCT idsource 
        FROM talimnet_mail 
        WHERE (idutilisateur = $iduser OR destinataire = $iduser) 
        AND idetablissement = $idetablissement
        AND idannescolaire = $currentYearId";
$result = $conn->query($sql);

$idsources = array();
if ($result !== false && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $idsources[] = $row['idsource'];
    }
}

$messages = array();

// Fetch the latest message for each idsource
foreach ($idsources as $idsource) {
    $latestMessageSql = "SELECT * FROM talimnet_mail 
                         WHERE idsource = $idsource 
                         ORDER BY dateheure DESC 
                         LIMIT 1";
    $latestMessageResult = $conn->query($latestMessageSql);

    if ($latestMessageResult !== false && $latestMessageResult->num_rows > 0) {
        $latestMessageRow = $latestMessageResult->fetch_assoc();

        $senderName = '';

        if ($latestMessageRow['idutilisateur'] == $iduser) {
            $senderName = 'Moi'; // Set sender name to 'Moi' if the user is the sender
        } else {
            // Determine the sender's name if not the current user
            if ($latestMessageRow['idadministrateur'] == 0) {
                // Fetch teacher's name if idadministrateur is 0
                $teacherSql = "SELECT nom, prenom FROM talimnet_enseignants WHERE id = " . $latestMessageRow['idutilisateur'];
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
        }

        // Sanitize message content
        $sanitizedMessage = strip_tags($latestMessageRow['mail'], '<p><a>'); // Allow some tags if needed

        $messages[] = array(
            'id' => $latestMessageRow['id'],
            'mail' => $sanitizedMessage,
            'dateheure' => $latestMessageRow['dateheure'],
            'lu' => $latestMessageRow['lu'],
            'idadministrateur' => $latestMessageRow['idadministrateur'],
            'idsender' => $latestMessageRow['idutilisateur'],
            'sender_name' => $senderName,
            'idannescolaire' => $latestMessageRow['idannescolaire'],
            'idsource' => $latestMessageRow['idsource']
        );
    }
}

// Order messages by dateheure in descending order
usort($messages, function($a, $b) {
    return strtotime($b['dateheure']) - strtotime($a['dateheure']);
});

echo json_encode($messages);

$conn->close();
?>
