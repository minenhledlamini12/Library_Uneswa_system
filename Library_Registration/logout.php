<?php
session_start();
require_once 'connection.php'; // Make sure this connects to your DB and defines $conn

if (isset($_SESSION['username'])) {
    $email = $_SESSION['username'];
    $timestamp = date('Y-m-d H:i:s');
    // Update last_login in admini table
    $sql = "UPDATE admini SET Last_Login = ? WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $timestamp, $email);
    $stmt->execute();
    $stmt->close();
}

session_unset();
session_destroy();
header("Location: index.php");
exit();
?>
