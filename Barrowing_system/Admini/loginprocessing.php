<?php
session_start();
require_once 'connection.php'; // Make sure this connects to your DB and defines $conn




if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare and execute query
    $sql = "SELECT * FROM admini WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verify password
        if (password_verify($password, $row['Password'])) {
            // Set session and redirect
            $_SESSION['username'] = $row['Email'];
            header("Location: homepage.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
    $stmt->close();
}
?>


