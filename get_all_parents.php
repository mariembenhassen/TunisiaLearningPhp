<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunisialearning";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$idetablissement = isset($_GET['idetablissement']) ? intval($_GET['idetablissement']) : 0;
$idetablissement = $conn->real_escape_string($idetablissement);

$sql = "SELECT t.nomprenom, t.id 
        FROM talimnet_tuteur t
        JOIN talimnet_eleves e ON t.ideleve = e.id
        WHERE e.idetablissement = ?
        ORDER BY t.nomprenom ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idetablissement);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Query failed: " . $conn->error);
}

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
