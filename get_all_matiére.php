<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunisialearning";
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

// Function to map IDs to Arabic names
function decryptMatiereById($id, $originalMatiere) {
    switch ($id) {
        case 1:
            return 'نشر وإعلام';
        case 2:
            return 'إنتاج كتابي';
        case 3:
            return 'تواصل شفوي';
        case 4:
            return 'خط';
        case 5:
            return 'قواعد لغة';
        case 6:
            return 'قراءة وفهم';
        case 7:
            return 'الإيقاظ العلمي';
        case 8:
            return 'التربية التكنولوجية';
        case 9:
            return 'الرياضيات';
        case 10:
            return 'التاريخ';
        case 11:
            return 'التربية الموسيقية';
        case 12:
            return 'التربية المدنية';
        case 13:
            return 'التربية الإسلامية';
        case 14:
            return 'التربية البدنية';
        case 15:
            return 'التربية التشكيلية';
        case 16:
            return 'الجغرافيا';
        case 17:
            return 'Anglais';
        case 18:
            return 'écriture + dictée';
        case 19:
            return 'Expression orale';
        case 20:
            return 'Langue';
        case 21:
            return 'Langue + dictée + écriture';
        case 22:
            return 'Langue (Gram+Cong+Ortho)';
        case 23:
            return 'Lecture et compréhension';
        case 24:
            return 'Production';
        case 25:
            return 'Production écrite';
        case 26:
            return 'التربية الرياضية';
        case 27:
            return 'Français';
        default:
            return $originalMatiere;  // Return the original matiere if ID doesn't match
    }
}

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the database
$sql = "SELECT id, matiere FROM talimnet_matiere";
$result = $conn->query($sql);

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Fetch all rows into an associative array
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    
    // Map IDs to Arabic names
    foreach ($rows as &$row) {
        // Use the original matiere from the database if the ID doesn't match
        $row['matiere'] = decryptMatiereById($row['id'], $row['matiere']);
    }
    
    // Return the result in JSON format
    echo json_encode(['status' => 'success', 'data' => $rows]);
} else {
    // No rows found
    echo json_encode(['status' => 'success', 'data' => []]);
}

// Close the connection
$conn->close();
?>
