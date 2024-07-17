<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunisialearning";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'id' parameter is set and not empty
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];

    // Sanitize the ID parameter (optional if you're using prepared statements)
    $id = $conn->real_escape_string($id);

    // Prepare SQL query to fetch child details with related data
    $sql = "SELECT e.id, e.nom, e.prenom, e.sexe, e.date_naissance, e.adresse, 
                   CAST(e.idniveau AS UNSIGNED) AS idniveau, 
                   CAST(e.idclasse AS UNSIGNED) AS idclasse, 
                   CAST(e.idetablissement AS UNSIGNED) AS idetablissement, 
                   t.nomprenom AS tuteur_nomprenom, t.email AS tuteur_email, 
                   t.telephone AS tuteur_telephone, c.classe AS classe_nom, 
                   n.niveau AS niveau_nom, et.nom AS etablissement_nom 
            FROM talimnet_eleves e 
            LEFT JOIN talimnet_tuteur_eleves te ON e.id = te.ideleve 
            LEFT JOIN talimnet_tuteur t ON te.idtuteur = t.id 
            LEFT JOIN talimnet_classes c ON e.idclasse = c.id 
            LEFT JOIN talimnet_niveaux n ON e.idniveau = n.id 
            LEFT JOIN talimnet_etablissement et ON e.idetablissement = et.id 
            WHERE e.id = '$id'";

    // Execute query
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Fetch data
        $row = $result->fetch_assoc();

        // Convert necessary fields to integers
        $row['idniveau'] = (int) $row['idniveau'];
        $row['idclasse'] = (int) $row['idclasse'];
        $row['idetablissement'] = (int) $row['idetablissement'];

        // Construct response array
        $response = array(
            'success' => true,
            'data' => array(
                'id' => $row['id'],
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'sexe' => $row['sexe'],
                'date_naissance' => $row['date_naissance'],
                'adresse' => $row['adresse'],
                'idniveau' => $row['idniveau'],
                'idclasse' => $row['idclasse'],
                'idetablissement' => $row['idetablissement'],
                'tuteur_nomprenom' => $row['tuteur_nomprenom'],
               
                'tuteur_email' => $row['tuteur_email'],
                'tuteur_telephone' => $row['tuteur_telephone'],
                'classe_nom' => $row['classe_nom'],
                'niveau_nom' => $row['niveau_nom'],
                'etablissement_nom' => $row['etablissement_nom']
                // Add more fields as needed
            )
        );
    } else {
        // No child found with the given ID
        $response = array(
            'success' => false,
            'message' => 'No child found with the given ID'
        );
    }
} else {
    // 'id' parameter is missing or empty
    $response = array(
        'success' => false,
        'message' => 'ID parameter is missing or empty'
    );
}

// Close connection
$conn->close();

// Send JSON response
echo json_encode($response);


