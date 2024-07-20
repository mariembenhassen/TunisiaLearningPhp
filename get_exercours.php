<?php

header('Content-Type: application/json; charset=utf-8');
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

// Turn off error reporting to avoid unexpected output
error_reporting(0);
ini_set('display_errors', 0);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Set the charset to utf8mb4
$conn->set_charset("utf8mb4");

// Function to decrypt matiere names based on id
function decryptMatiereById($id) {
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
            return 'Langue Gram+Cong+Ortho';
        case 23:
            return 'Lecture et compréhension';
        case 24:
            return 'Production';
        case 25:
            return 'Production écrite';
        case 26:
            return 'التربية الرياضية';
        default:
            return 'Matiére';
    }
}


// Step 1: Fetch the current year's `id` from `talimnet_anneescolaire` where `en_cours=1`
$sqlCurrentYear = "SELECT id FROM talimnet_anneescolaire WHERE en_cours = 1 LIMIT 1";
$resultCurrentYear = $conn->query($sqlCurrentYear);

if ($resultCurrentYear && $resultCurrentYear->num_rows > 0) {
    $rowCurrentYear = $resultCurrentYear->fetch_assoc();
    $currentYear = $rowCurrentYear['id'];
} else {
    echo json_encode(["error" => "Error fetching current year: " . $conn->error]);
    $conn->close();
    exit();
}

// Step 2: Check and retrieve GET parameters
$idetablissement = isset($_GET['idetablissement']) ? intval($_GET['idetablissement']) : 0;
$idniveau = isset($_GET['idniveau']) ? intval($_GET['idniveau']) : 0;
$idclasse = isset($_GET['idclasse']) ? intval($_GET['idclasse']) : 0;

// Step 3: Construct the main SQL query with error handling
$sql = "SELECT c.*, e.nom AS enseignant_nom, e.prenom AS enseignant_prenom, m.matiere AS matiere_nom
        FROM talimnet_cours c
        LEFT JOIN talimnet_enseignants e ON c.idenseignant = e.id
        LEFT JOIN talimnet_matiere m ON c.idmatiere = m.id
        WHERE c.idetablissement = $idetablissement
          AND c.idniveau = $idniveau
          AND c.idclasse = $idclasse
          AND c.idannescolaire = $currentYear";

$result = $conn->query($sql);

if ($result) {
    $response = array(); // Initialize an array to hold the results

    if ($result->num_rows > 0) {
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            // Fetch relevant columns from talimnet_cours table
            $idCours = $row["id"];

            // Step 4: Fetch data from talimnet_documentscours table
            $sqlDocuments = "SELECT * FROM talimnet_documentscours WHERE idcours = $idCours";
            $resultDocuments = $conn->query($sqlDocuments);

            $documents = array(); // Initialize an array to hold document data

            if ($resultDocuments->num_rows > 0) {
                // Output data of each document row
                while ($rowDocument = $resultDocuments->fetch_assoc()) {
                    $document = array(
                        "idDocument" => $rowDocument["id"],
                        "document" => $rowDocument["document"],
                        "titre" => $rowDocument["titre"]
                    );
                    // Push each document data to the documents array
                    $documents[] = $document;
                }
            }

            // Fetch other necessary course details
            $idEnseignant = $row["idenseignant"];
            $enseignantNom = $row["enseignant_nom"];
            $enseignantPrenom = $row["enseignant_prenom"];
            $idMatiere = $row["idmatiere"];
            $matiereNom = decryptMatiereById($idMatiere); // Decrypt matiere name
            $type = $row["type"];
            $date = $row["date"];

            // Construct the course data with documents
            $courseData = array(
                "idCours" => $idCours,
                "idEnseignant" => $idEnseignant,
                "Enseignant" => "$enseignantNom $enseignantPrenom",
                "Matiere" => $matiereNom, // Include the decrypted matiere name
                "type" => $type,
                "date" => $date,
                "documents" => $documents // Attach documents array to course data
            );

            // Push each course data to the response array
            $response[] = $courseData;
        }
    } else {
        echo json_encode(["error" => "0 results from talimnet_cours with current year filter"]);
        $conn->close();
        exit();
    }

    // Encode the response array as JSON and output it
    echo json_encode($response);
} else {
    echo json_encode(["error" => "Error executing query: " . $conn->error]);
}

$conn->close();
