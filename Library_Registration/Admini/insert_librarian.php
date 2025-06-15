<?php

require_once("C:/xampp/htdocs/php_program/Library_Registration/connection.php");

function librarian($username, $password, $email, $fullName, $role) {
    global $conn;

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO librarian (username, password_hash, email, full_name, role) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssss", $username, $hashedPassword, $email, $fullName, $role);

        if ($stmt->execute()) {
            return true; // Success
        } else {
            return "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        return "Error: " . $conn->error;
    }
}

// Example usage (you can modify these values):
$username = "20192020";
$password = "SaneleMotsa@12345";
$email = "dminenhle477@gmail.com"; // Removed brackets from email
$fullName = "Sanele Motsa";
$role = "librarian";

$result = librarian($username, $password, $email, $fullName, $role); // Corrected function name

if ($result === true) {
    echo "Librarian admin added successfully.";
} else {
    echo $result;
}

$conn->close();
?>