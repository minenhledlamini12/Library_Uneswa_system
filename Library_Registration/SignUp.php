<?php
// signup_process.php

require_once 'connection.php';
session_start(); // Start session to handle messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Sanitize and validate input
        $firstName = htmlspecialchars(trim($_POST['firstName']));
        $secondName = htmlspecialchars(trim($_POST['secondName'] ?? ''));
        $lastName = htmlspecialchars(trim($_POST['lastName']));
        $role = htmlspecialchars(trim($_POST['role']));
        $contact = htmlspecialchars(trim($_POST['contact'] ?? ''));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirmPassword']);

        // Validate required fields
        if (empty($firstName) || empty($lastName) || empty($role) || empty($email) || empty($password)) {
            throw new Exception("All required fields must be filled");
        }

        // Validate password match
        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if email already exists in the admini table
        $stmt = $conn->prepare("SELECT ID FROM admini WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            throw new Exception("Email already registered");
        }
        $stmt->close();

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert into admini table
        $stmt = $conn->prepare("INSERT INTO admini (First_Name, Second_Name, Surname, Role, Contact, Email, Password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssss",
            $firstName,
            $secondName,
            $lastName,
            $role,
            $contact,
            $email,
            $hashedPassword
        );

        if ($stmt->execute()) {
            // Display success message with JavaScript redirection
            echo "<!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Registration Successful</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            height: 100vh;
                            background-color: #f4f4f4;
                        }
                        .popup {
                            background-color: white;
                            border-radius: 5px;
                            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                            padding: 20px;
                            text-align: center;
                        }
                        .button {
                            background-color: #4CAF50; /* Green */
                            border: none;
                            color: white;
                            padding: 10px 20px;
                            text-align: center;
                            text-decoration: none;
                            display: inline-block;
                            font-size: 16px;
                            margin-top: 10px;
                            cursor: pointer;
                        }
                    </style>
                </head>
                <body>
                    <div class='popup'>
                        <h2>Registration Successful!</h2>
                        <p>Your account has been created successfully.</p>
                        <button class='button' onclick='redirectToLogin()'>Okay</button>
                    </div>
                    <script>
                        function redirectToLogin() {
                            window.location.href = 'index.php';
                        }
                    </script>
                </body>
                </html>";
            exit();
        } else {
            throw new Exception("Error: " . $stmt->error);
        }
    } catch (Exception $e) {
        // Log error and store error message in session
        error_log(date('Y-m-d H:i:s') . " - " . $e->getMessage() . " - IP: " . $_SERVER['REMOTE_ADDR'] . "\n", 3, "error.log");
        
        $_SESSION['error_message'] = $e->getMessage(); // Store error in session
        
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='SignUp.php';</script>";
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    echo "<script>window.location.href='SignUp.php';</script>";
    exit();
}
?>
