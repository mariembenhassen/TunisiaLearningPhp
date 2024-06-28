
<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunisialearning";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $parentId = $_POST['id'];
    $parentId = mysqli_real_escape_string($conn, $parentId);

    $query = "
        SELECT e.*
        FROM talimnet_eleves e
        JOIN talimnet_tuteur t ON e.tuteur = t.nomprenom
        WHERE t.id = '$parentId'
    ";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $children = array();

        while ($row = $result->fetch_assoc()) {
            $children[] = $row;
        }

        $response = array(
            'success' => true,
            'data' => $children
        );

        echo json_encode($response);
    } else {
        $response = array(
            'success' => false,
            'message' => 'No children found for this parent'
        );

        echo json_encode($response);
    }
} else {
    $response = array(
        'success' => false,
        'message' => 'Method not allowed'
    );

    echo json_encode($response);
}

$conn->close();

?>