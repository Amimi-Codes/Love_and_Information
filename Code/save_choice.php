<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// Database configuration
$host = "localhost";
$username = "u822894334_LoveandInfo";
$password = "Love&Info";
$database = "u822894334_AnswersDB";

try {
    // Create connection
    $conn = new mysqli($host, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get the POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate data
    if (!$data) {
        throw new Exception("Invalid JSON data received");
    }

    // Prepare SQL statement
    $sql = "INSERT INTO quiz_responses (date, IE, DN, SO, IP) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("siiii", 
        $data['date'],
        $data['IE'],
        $data['DN'],
        $data['SO'],
        $data['IP']
    );

    // Execute statement
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Close statement
    $stmt->close();

    // Success response
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    // Close connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
?>