<?php
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunisialearning";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data from JSON body
$data = json_decode(file_get_contents('php://input'), true);

// Extract idetablissement parameter
$idetablissement = isset($data['idetablissement']) ? $data['idetablissement'] : '';

// Validate parameter
if (empty($idetablissement)) {
    echo json_encode(['status' => 'error', 'message' => 'idetablissement is required.']);
    exit;
}

// Prepare the SQL statement
$stmt = $conn->prepare("
    SELECT id, niveau 
    FROM talimnet_niveaux 
    WHERE idetablissement = ?
");

// Bind the parameter
$stmt->bind_param('i', $idetablissement);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch all rows
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Transform the 'niveau' field
foreach ($rows as &$row) {
    // Clean and format the niveau field
    $cleaned_niveau = cleanNiveau($row['niveau']);
    
    // Set the cleaned value
    $row['niveau'] = $cleaned_niveau;
}

// Return the result in JSON format
echo json_encode(['status' => 'success', 'data' => $rows]);

// Close the connection
$stmt->close();
$conn->close();

/**
 * Clean and format the niveau field.
 *
 * @param string $text The text containing the niveau value.
 * @return string The cleaned and formatted niveau value.
 */
function cleanNiveau($text) {
    // Decode special characters
    $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
    
    // Use regular expression to clean the text
    // Remove all characters from the second place up to (but not including) the 'è'
    $text = preg_replace('/^(.)[^\x00-\x7F]+.*(è)/', '$1$2', $text);

    // Trim any extra whitespace
    $text = trim($text);

    // Add suffix based on the first character
    $first_char = substr($text, 0, 1);
    if ($first_char === '1') {
        return '1ère année';
    } else {
        return $first_char . 'ème année';
    }
}
?>
