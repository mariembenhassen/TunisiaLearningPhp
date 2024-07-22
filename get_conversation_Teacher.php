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
header("Content-Type: application/json"); // Set content type to JSON

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['idSource'])) {
    $idSource = (int)$input['idSource'];

    // Prepare the SQL query with ordering by id in ascending order
    $stmt = $conn->prepare("SELECT mail, idetablissement, idutilisateur, id, expediteur FROM talimnet_mail WHERE idsource = ? ORDER BY id ASC");
    $stmt->bind_param("i", $idSource);

    // Execute the query
    $stmt->execute();

    // Fetch all the results
    $result = $stmt->get_result();
    $mails = $result->fetch_all(MYSQLI_ASSOC);

    // Process each mail record
    $results = array();
    foreach ($mails as $mail) {
        // Fetch the `nomprenom` from the appropriate table based on `idutilisateur`
        if ($mail['id'] == $idSource) {
            $tutorSql = "SELECT nomprenom FROM talimnet_tuteur WHERE id = ?";
            $stmt2 = $conn->prepare($tutorSql);
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
        } else {
            $mail['nomprenom'] = 'Moi';
        }

        // Decode HTML entities and remove unwanted HTML tags, characters, and newlines
        $mail['mail'] = html_entity_decode($mail['mail'], ENT_QUOTES, 'UTF-8');
        $mail['mail'] = preg_replace('/<[^>]*>/', '', $mail['mail']); // Remove all HTML tags
        $mail['mail'] = preg_replace('/&lt;br \/&gt;|&lt;b&gt;|&lt;\/b&gt;/i', '', $mail['mail']); // Remove specific unwanted substrings
        $mail['mail'] = str_replace("\n", ' ', $mail['mail']); // Replace newlines with a space
        $mail['mail'] = preg_replace('/\s+/', ' ', $mail['mail']); // Replace multiple spaces with a single space
        $mail['mail'] = preg_replace('/\.\.\.\.\.\./', '', $mail['mail']); // Remove specific unwanted substring

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
?>
