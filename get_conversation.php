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

    // Prepare the SQL query with ordering by id in ascending order
    $stmt = $conn->prepare("SELECT mail, idetablissement, idutilisateur, id, expediteur, vers_qui, dateheure FROM talimnet_mail WHERE idsource = ? ORDER BY id ASC");
    $stmt->bind_param("i", $idSource);

    // Execute the query
    $stmt->execute();

    // Fetch all the results
    $result = $stmt->get_result();
    $mails = $result->fetch_all(MYSQLI_ASSOC);

    // Process each mail record
    $results = array();
    $mailWithIdSource = null;

    foreach ($mails as $mail) {
        if ($mail['vers_qui'] == 5) {
            // Fetch nom and prenom from talimnet_enseignants
            $stmt2 = $conn->prepare("SELECT nom, prenom FROM talimnet_enseignants WHERE id = ?");
            $stmt2->bind_param("i", $mail['idutilisateur']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $teacher = $result2->fetch_assoc();
            $mail['nom'] = $teacher['nom'];
            $mail['prenom'] = $teacher['prenom'];
            $stmt2->close();
        } else {
            // Set nom to 'Moi'
            $mail['nom'] = 'Moi';
            $mail['prenom'] = '';
        }
        
        // Replace newline characters with a space
        $mail['mail'] = str_replace("\n", ' ', $mail['mail']);
        $mail['mail'] = preg_replace('/\s+/', ' ', $mail['mail']); // Replace multiple spaces with a single space
        $mail['mail'] = trim($mail['mail']); // Trim leading and trailing spaces

        if ($mail['id'] == $idSource) {
            $mailWithIdSource = $mail;
        } else {
            $results[] = $mail;
        }
    }

    // Add the entry with idSource at the beginning
    if ($mailWithIdSource) {
        array_unshift($results, $mailWithIdSource); // Place the entry with idSource at the start
    }

    // Output the results as JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['error' => 'No idSource provided']);
}

// Close the connection
$stmt->close();
$conn->close();
?>
