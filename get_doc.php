<?php
// Database connection settings
$servername = "localhost"; // Change this if necessary
$username = "root";        // Change this if necessary
$password = "";            // Change this if necessary
$dbname = "tunisialearning"; // Your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Get parameters from the front-end (e.g., via POST or GET)
$idetablissement = isset($_GET['idetablissement']) ? intval($_GET['idetablissement']) : 0;
$idniveau = isset($_GET['idniveau']) ? intval($_GET['idniveau']) : 0;
$idclasse = isset($_GET['idclasse']) ? intval($_GET['idclasse']) : 0;

// Get the current year (annee scolaire)
$currentYearQuery = "SELECT id FROM talimnet_anneescolaire WHERE en_cours = 1";
$currentYearResult = $conn->query($currentYearQuery);

if ($currentYearResult->num_rows > 0) {
    $currentYearRow = $currentYearResult->fetch_assoc();
    $currentYear = $currentYearRow['id'];
} else {
    die("Current school year not found.");
}

// Prepare SQL query to fetch documents based on the criteria
$sql = "
    SELECT document
    FROM talimnet_document
    WHERE idetablissement = ?
      AND niveau = ?
      AND classe = ?
      AND idanneescolaire = ?
      AND pour_qui IN (2, 3)
    ORDER BY id ASC
";

// Prepare the statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters
$stmt->bind_param("iiii", $idetablissement, $idniveau, $idclasse, $currentYear);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch all rows
$documents = [];
while ($row = $result->fetch_assoc()) {
    $documents[] = $row;
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Output the result as JSON
header('Content-Type: application/json');
echo json_encode($documents);
?>
