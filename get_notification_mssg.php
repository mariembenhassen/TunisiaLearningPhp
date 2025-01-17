<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
    exit(0);
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunisialearning";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$iduser = isset($_GET['iduser']) ? intval($_GET['iduser']) : 0;

$currentYearQuery = "SELECT id FROM talimnet_anneescolaire WHERE en_cours = 1";
$currentYearResult = $conn->query($currentYearQuery);

if ($currentYearResult === false || $currentYearResult->num_rows == 0) {
    die(json_encode(['error' => 'Current academic year not found']));
}

$currentYearRow = $currentYearResult->fetch_assoc();
$currentYearId = $currentYearRow['id'];

$sql = "SELECT COUNT(*) AS count FROM talimnet_mail WHERE destinataire = ? AND lu = 0 AND idannescolaire = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die(json_encode(["error" => "SQL prepare error: " . $conn->error]));
}

$stmt->bind_param('ii', $iduser, $currentYearId);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
$count = $row['count'];

echo json_encode(['count' => $count]);

$conn->close();

?>
