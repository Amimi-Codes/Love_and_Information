// get_choices.php
<!-- Saving Flow:
JavaScript -> get_choices.php -> Database
            <- all choices    <- 
-->
<?php
// Allow JSON responses and cross-origin requests
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection settings
$host = 'localhost';
$db_username = 'u822894334_LoveandInfo';
$db_password = 'Love&Info';
$db_name = 'u822894334_AnswersDB';

// Create connection to MySQL database
$conn = new mysqli($host, $db_username, $db_password, $db_name);

// Check if connection failed
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// SQL query to get all choices, ordered by newest first
$sql = "SELECT * FROM choices ORDER BY timestamp DESC";
$result = $conn->query($sql); // Run SQL command

// Process query results
$choices = []; // Create empty array to store choices
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Add each choice with its timestamp
        $choices[] = $row['timestamp'] . ': ' . $row['choice_text'];
    }
}

// Send back the array of choices as JSON
echo json_encode(['choices' => $choices]);

// Close database connection
$conn->close();
?>