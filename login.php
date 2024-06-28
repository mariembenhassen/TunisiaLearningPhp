<?php

header('Content-Type: application/json');
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
//Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

//database credentials
$servername ="localhost"; 
$username ="root"; 
$password =""; 
$dbname = "tunisialearning"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve POST data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Sanitize input to prevent SQL injection
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);

    // Query to check in talimnet_enseignants table
    $query_enseignants = "SELECT * FROM talimnet_enseignants WHERE email = '$email' AND motdepasse = '$password'";
    $result_enseignants = $conn->query($query_enseignants);

    error_log("Checking teacher table");

    if ($result_enseignants->num_rows > 0) {
        // User found in teachers table (talimnet_enseignants)
        $row = $result_enseignants->fetch_assoc();

        // Example response data for teacher
        $response = array(
            'success' => true,
            'message' => 'Login successful',
            'role' => 'teacher',
            'data' => array(
                'id' => $row['id'],
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'date_naissance' => $row['date_naissance'],
                'motdepasse'=> $row['motdepasse'],
                'telephone '=> $row['telephone'],
                'adresse'=> $row['adresse'],
                'lieu_naissance'=> $row['lieu_naissance'],

                // Add other relevant fields you want to send to Flutter
            )
        );

        echo json_encode($response);
    } else {
        error_log("Teacher not found, checking parent table");
        // Query to check in talimnet_tuteur table
        $query_tuteur = "SELECT * FROM talimnet_tuteur WHERE email = '$email' AND motdepasse = '$password'";
        $result_tuteur = $conn->query($query_tuteur);

        if ($result_tuteur->num_rows > 0) {
            // User found in tutors table (talimnet_tuteur)
            $row = $result_tuteur->fetch_assoc();

            // Example response data for tutor
            $response = array(
                'success' => true,
                'message' => 'Login successful',
                'role' => 'parent',
                'data' => array(
                    'id' => $row['id'],
                    'nomprenom' => $row['nomprenom'],
                    
                    // Add other relevant fields you want to send to Flutter
                )
            );

            echo json_encode($response);
        } else {
            error_log("Parent not found, invalid credentials");
            // User not found in either table or credentials are incorrect
            $response = array(
                'success' => false,
                'message' => 'Invalid email or password'
            );
            echo json_encode($response);
        }
    }
} else {
    // Handle non-POST requests (if any)
    $response = array(
        'success' => false,
        'message' => 'Method not allowed'
    );
    echo json_encode($response);
}

// Close connection
$conn->close();

?>
