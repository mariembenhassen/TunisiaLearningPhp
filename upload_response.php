<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];

        $uploadDir = 'Document_coursexercice/';
        $destination = $uploadDir . basename($fileName);

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($fileTmpPath, $destination)) {
            $idcours = $_POST['idcours'];
            $idetablissement = $_POST['idetablissement'];
            $ideleve = $_POST['ideleve'];
            $etat = 1; // Assuming 1 means uploaded and processed

            // Modify the SQL query to use NOW() for dateheure
            $sql = "INSERT INTO talimnet_docreponse (idcours, idetablissement, document, ideleve, dateheure, etat)
                    VALUES (?, ?, ?, ?, NOW(), ?)";

            $stmt = $conn->prepare($sql);

            // Correct bind_param to match the number of placeholders
            $stmt->bind_param("iisis", $idcours, $idetablissement, $destination, $ideleve, $etat);

            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'File uploaded and record inserted successfully',
                    'filePath' => $destination
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to insert record into database'
                ]);
            }

            $stmt->close();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to move the uploaded file'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No file uploaded or there was an upload error'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?>
