<?php

header('Content-Type: application/json');
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

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

        // Query to fetch teacher details by ID
        $query = "SELECT * FROM talimnet_enseignants WHERE id = $id";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Fetch additional details (etablissement and anneescolaire)
            $idEtablissement = $row['idetablissement'];
            $queryEtablissement = "SELECT nom FROM talimnet_etablissement WHERE id = $idEtablissement";
            $resultEtablissement = $conn->query($queryEtablissement);
            $etablissement = $resultEtablissement->fetch_assoc()['nom'];

            $queryAnneeScolaire = "SELECT MAX(anneescolaire) AS anneescolaire FROM talimnet_anneescolaire WHERE en_cours = 1";
            $resultAnneeScolaire = $conn->query($queryAnneeScolaire);
            $anneeScolaire = $resultAnneeScolaire->fetch_assoc()['anneescolaire'];

            // Construct response
            $response = array(
                'success' => true,
                'message' => 'Teacher details fetched successfully',
                'data' => array(
                    'id' => $row['id'],
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'date_naissance' => $row['date_naissance'],
                    'lieu_naissance' => $row['lieu_naissance'],
                    'adresse' => $row['adresse'],
                    'telephone' => $row['telephone'],
                    'sexe' => $row['sexe'],
                    'email' => $row['email'],
                    'idetablissement' => $row['idetablissement'],
                    'etablissement' => $etablissement,
                    'annee_scolaire_en_cours' => $anneeScolaire
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
