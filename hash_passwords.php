<?php
/*$servername = "localhost";
$username = "root";
$password = "PHW#84#jeor";
$dbname = "School";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch users and hash their passwords
$sql = "SELECT id, password FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $plain_password = $row['password'];
        $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

        // Update the password in the database
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $hashed_password, $id);
        $stmt->execute();
    }
    echo "Passwords updated successfully.";
} else {
    echo "No users found.";
}

$conn->close();
*/?>
