<?php
header('Content-Type: application/json');

// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$blacklist_id = $input['blacklist_id'] ?? null;

if (!$blacklist_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid blacklist ID']);
    exit();
}

// Remove from blacklist
$stmt = $conn->prepare("DELETE FROM blacklist WHERE BlacklistID = ?");
$stmt->bind_param("i", $blacklist_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Member removed from blacklist successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove member from blacklist']);
}

$stmt->close();
$conn->close();
?>
