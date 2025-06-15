<?php
// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Fetch member with checkin/checkout times regardless of deleted status
    $result = $conn->query("SELECT *, Checkin_Time, Checkout_Time FROM members WHERE ID = $id");
    $member = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($member);
}

$conn->close();
?>
