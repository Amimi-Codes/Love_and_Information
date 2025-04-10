// save_choice.php
<!-- Saving Flow:
JavaScript -> save_choice.php -> Database
            <- success/error  <- 
-->
<?php
// These headers tell browsers it's okay to access this file from your website
header('Content-Type: application/json');  // We'll send back JSON data
header('Access-Control-Allow-Origin: *');  // Allows any website to access this
header('Access-Control-Allow-Methods: POST');  // Allows POST requests
header('Access-Control-Allow-Headers: Content-Type');

// Database connection settings
$host = 'localhost';
$db_username = 'u822894334_LoveandInfo';
$db_password = 'Love&Info@QUEENS2025';
$db_name = 'u822894334_AnswersDB';
 
// Create connection to MySQL database
$conn = new mysqli($host, $db_username, $db_password, $db_name);

// Check if connection failed
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get the POST data sent from JavaScript
$data = json_decode(file_get_contents('php://input'), true);
// Prevent SQL injection by escaping special characters
$choice = $conn->real_escape_string($data['choice']);

// SQL query to insert the new choice
$sql = "INSERT INTO choices (choice_text) VALUES ('$choice')";

// Execute query and send response
if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Error saving choice: ' . $conn->error]);
}

// Close database connection
$conn->close();
?>