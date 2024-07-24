<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400'); 
    exit(0);
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunisialearning";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(array("status" => "error", "message" => "Connection failed: " . $conn->connect_error)));
}

function generateUniqueFilename($path, $filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $basename = pathinfo($filename, PATHINFO_FILENAME);
    $counter = 1;

    while (file_exists($path . $basename . '.' . $ext)) {
        $basename = pathinfo($filename, PATHINFO_FILENAME) . '_' . $counter;
        $counter++;
    }

    return $basename . '.' . $ext;
}

if (isset($_POST['idniveau'], $_POST['idclasse'], $_POST['idmatiere'], $_POST['type'], $_POST['observation'], $_POST['idetablissement'], $_POST['idutilisateur']) && isset($_FILES['document'])) {
    $idniveau = $_POST['idniveau'];
    $idclasse = $_POST['idclasse'];
    $idmatiere = $_POST['idmatiere'];
    $type = $_POST['type'];
    $observation = $_POST['observation'];
    $idetablissement = $_POST['idetablissement'];
    $idutilisateur = $_POST['idutilisateur'];
    $idprofil = 4;
    $etat = 1;

    // Fetch the current academic year ID
    $currentYearQuery = "SELECT id FROM talimnet_anneescolaire WHERE en_cours = 1";
    $currentYearResult = $conn->query($currentYearQuery);
    if ($currentYearResult->num_rows > 0) {
        $currentYearRow = $currentYearResult->fetch_assoc();
        $idannescolaire = $currentYearRow['id'];
    } else {
        die(json_encode(array("status" => "error", "message" => "Current academic year not found")));
    }

    // Insert the course/exercise details into the talimnet_cours table
    $stmt = $conn->prepare("INSERT INTO talimnet_cours (idniveau, idclasse, idmatiere, type, date, etat, observation, idetablissement, idannescolaire, idutilisateur, idprofil) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissisiii", $idniveau, $idclasse, $idmatiere, $type, $etat, $observation, $idetablissement, $idannescolaire, $idutilisateur, $idprofil);

    if ($stmt->execute()) {
        $idcours = $stmt->insert_id; // Get the last inserted ID (idcours)

        // Directory where the file will be uploaded
        $uploadDir = 'Document_coursexercice/' . $idcours . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $document = $_FILES['document'];
        $filename = generateUniqueFilename($uploadDir, basename($document['name']));
        $uploadFile = $uploadDir . $filename;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($document['tmp_name'], $uploadFile)) {
            // Insert the document information into the talimnet_documentscours table
            $stmt = $conn->prepare("INSERT INTO talimnet_documentscours (idcours, document, titre) VALUES (?, ?, ?)");
            $titre = pathinfo($filename, PATHINFO_FILENAME);
            $stmt->bind_param("iss", $idcours, $uploadFile, $titre);

            if ($stmt->execute()) {
                echo json_encode(array("status" => "success", "message" => "Course and document uploaded successfully"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error inserting document into the database: " . $stmt->error));
            }
        } else {
            echo json_encode(array("status" => "error", "message" => "Failed to upload file"));
        }
    } else {
        echo json_encode(array("status" => "error", "message" => "Error inserting course into the database: " . $stmt->error));
    }

    $stmt->close();
} else {
    echo json_encode(array("status" => "error", "message" => "Missing required parameters or file"));
}

$conn->close();
?>
