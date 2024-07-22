<?php
// Start output buffering
ob_start();

// Set headers for JSON response
header("Access-Control-Allow-Origin: *"); // Allows requests from any origin
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allows GET, POST, and OPTIONS methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allows specific headers
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Error reporting for development (disable in production)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

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
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['idSource'])) {
    $idSource = (int)$input['idSource'];

    // Prepare the SQL query with ordering by id in ascending order
    $stmt = $conn->prepare("SELECT mail, idetablissement, idutilisateur, id, expediteur FROM talimnet_mail WHERE idsource = ? ORDER BY id ASC");
    if ($stmt === false) {
        die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
    }

    $stmt->bind_param("i", $idSource);

    // Execute the query
    $stmt->execute();

    // Fetch all the results
    $result = $stmt->get_result();
    $mails = $result->fetch_all(MYSQLI_ASSOC);

    // Process each mail record
    $results = array();
    foreach ($mails as $mail) {
       
            $tutorSql = "SELECT nomprenom FROM talimnet_tuteur WHERE id = ?";
            $stmt2 = $conn->prepare($tutorSql);
            if ($stmt2 === false) {
                die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
            }
            $stmt2->bind_param("i", $mail['idutilisateur']);
            $stmt2->execute();
            $tutorResult = $stmt2->get_result();
            if ($tutorResult->num_rows > 0) {
                $tutor = $tutorResult->fetch_assoc();
                $mail['nomprenom'] = $tutor['nomprenom'];
            } else {
                $mail['nomprenom'] = 'Unknown';
            }
            $stmt2->close();
       

        // Decode HTML entities and remove unwanted HTML tags and characters
        $mail['mail'] = html_entity_decode($mail['mail'], ENT_QUOTES, 'UTF-8');
        $mail['mail'] = strip_tags($mail['mail']); // Remove all HTML tags
        $mail['mail'] = str_replace("\n", ' ', $mail['mail']); // Replace newlines with a space
        $mail['mail'] = preg_replace('/\s+/', ' ', $mail['mail']); // Replace multiple spaces with a single space
        $mail['mail'] = trim($mail['mail']); // Trim leading and trailing spaces

        $results[] = $mail;
    }

    // Output the results as JSON
    echo json_encode($results);
} else {
    echo json_encode(['error' => 'No idSource provided']);
}

// Close the connection
$stmt->close();
$conn->close();

// End output buffering and flush output
ob_end_flush();
?>
