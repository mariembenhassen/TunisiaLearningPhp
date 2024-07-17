<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

// Assuming your database connection and query
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunisialearning";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, nom, prenom FROM talimnet_enseignants";
$result = $conn->query($sql);

$teachers = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $fullName = $row["nom"] . " " . $row["prenom"];
        $teacher = array(
            'id' => $row["id"],
            'fullName' => $fullName
        );
        $teachers[] = $teacher;
    }
} else {
    echo json_encode(array("message" => "No results found"));
}

$conn->close();

echo json_encode($teachers);
?>
