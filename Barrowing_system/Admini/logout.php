<?php
session_start();
require_once 'connection.php';

// Only update if the session is active and username is set
if (isset($_SESSION['username'])) {
    $email = $_SESSION['username'];
    $logout_time = date('Y-m-d H:i:s');

    // Update the last_login column for this user
    $sql = "UPDATE admini SET last_login = ? WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $logout_time, $email);
    $stmt->execute();
    $stmt->close();
}

// Destroy the session and redirect to login page
session_unset();
session_destroy();
header('Location: login.php');
exit();
?>
