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

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if required parameters are provided via GET
if (isset($_GET['idetablissement']) && isset($_GET['idniveau']) && isset($_GET['idclasse'])) {
    $idetablissement = $_GET['idetablissement'];
    $idniveau = $_GET['idniveau'];
    $idclasse = $_GET['idclasse'];

    // Sanitize input (optional, if using prepared statements)
    $idetablissement = $conn->real_escape_string($idetablissement);
    $idniveau = $conn->real_escape_string($idniveau);
    $idclasse = $conn->real_escape_string($idclasse);

    // Prepare SQL query to fetch data
    $sql = "SELECT e.id, e.idetablissement, e.idniveau, e.idclasse, e.pdf, e.idanneescolaire
            FROM talimnet_emploi_eleve e
            WHERE e.idetablissement = '$idetablissement' 
              AND e.idniveau = '$idniveau' 
              AND e.idclasse = '$idclasse'
               AND e.idanneescolaire = (
                  SELECT MAX(idanneescolaire) 
                  FROM talimnet_emploi_eleve 
                  WHERE idetablissement = '$idetablissement' 
                    AND idniveau = '$idniveau' 
                    AND idclasse = '$idclasse'
              )
              
              
              ";

    // Execute query
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Fetch data into an array
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        // Construct response array
        $response = array(
            'success' => true,
            'data' => $data
        );
    } else {
        // No records found with the given criteria
        $response = array(
            'success' => false,
            'message' => 'No records found with the given criteria'
        );
    }
} else {
    // Parameters are missing
    $response = array(
        'success' => false,
        'message' => 'Required parameters are missing'
    );
}

// Close connection
$conn->close();

// Send JSON response
echo json_encode($response);
?>
