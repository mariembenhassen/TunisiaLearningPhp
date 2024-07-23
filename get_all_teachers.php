<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

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

// Prepare and execute SQL statement with ORDER BY clause
$sql = "SELECT id, nom, prenom FROM talimnet_enseignants ORDER BY nom ASC";
$result = $conn->query($sql);

$teachers = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fullName = $row["nom"] . " " . $row["prenom"];
        $teacher = array(
            'id' => $row["id"],
            'fullName' => $fullName
        );
        $teachers[] = $teacher;
    }
} else {
    echo json_encode(array("message" => "No results found"));
    $conn->close();
    exit;
}

$conn->close();

echo json_encode($teachers);
?>
