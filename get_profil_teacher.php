<?php
// Set CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Handle preflight request
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400'); // Cache for 1 day
    exit(0);
}

// Database credentials
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

// Handling GET requests
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Retrieve GET data
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Prepare statement
        $stmt = $conn->prepare("SELECT nom, prenom, motdepasse , email, telephone, adresse, date_naissance, lieu_naissance FROM talimnet_enseignants WHERE id = ?");
        
        // Bind parameter
        $stmt->bind_param("i", $id);

        // Execute statement
        $stmt->execute();

        // Get result
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Construct response
            $response = array(
                'success' => true,
                'message' => 'Teacher details fetched successfully',
                'data' => array(
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'email' => $row['email'],
                    'telephone' => $row['telephone'],
                    'adresse' => $row['adresse'],
                    'date_naissance' => $row['date_naissance'],
                    'lieu_naissance' => $row['lieu_naissance'],
                    'motdepasse' => $row['motdepasse']

                )
            );

            echo json_encode($response);
        } else {
            $response = array(
                'success' => false,
                'message' => 'No teacher found with the given ID.'
            );
            echo json_encode($response);
        }

        // Close statement
        $stmt->close();
    } else {
        $response = array(
            'success' => false,
            'message' => 'ID parameter is missing.'
        );
        echo json_encode($response);
    }
} else {
    // Handle other methods
    $response = array(
        'success' => false,
        'message' => 'Method not allowed'
    );
    echo json_encode($response);
}

// Close connection
$conn->close();

?>
