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

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['idSource'])) {
    $idSource = (int)$input['idSource'];

    // Prepare the SQL query with ordering
    $stmt = $conn->prepare("SELECT mail, idetablissement, idutilisateur, id, expediteur FROM talimnet_mail WHERE idsource = ? ORDER BY dateheure DESC");
    $stmt->bind_param("i", $idSource);

    // Execute the query
    $stmt->execute();

    // Fetch all the results
    $result = $stmt->get_result();
    $mails = $result->fetch_all(MYSQLI_ASSOC);

    // Process each mail record
    $results = array();
    foreach ($mails as $mail) {
        if ($mail['id'] == $idSource) {
            // If expediteur = 1, fetch nom and prenom from talimnet_enseignants
            $stmt2 = $conn->prepare("SELECT nom, prenom FROM talimnet_enseignants WHERE id = ?");
            $stmt2->bind_param("i", $mail['idutilisateur']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $teacher = $result2->fetch_assoc();
            $mail['nom'] = $teacher['nom'];
            $mail['prenom'] = $teacher['prenom'];
            $stmt2->close();
        } else {
            // Otherwise, set nom to 'Moi'
            $mail['nom'] = 'Moi';
            $mail['prenom'] = '';
        }
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



