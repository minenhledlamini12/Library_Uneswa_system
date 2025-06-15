<?php
// Database connection details (mysqli)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library";
$tablename = "admini";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, 3306);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Correct PHPMailer paths
require 'C:\xampp\htdocs\php_program\Library_Registration\vendor\phpmailer\phpmailer\src\Exception.php';
require 'C:\xampp\htdocs\php_program\Library_Registration\vendor\phpmailer\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\php_program\Library_Registration\vendor\phpmailer\phpmailer\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $sql = "SELECT Member_ID FROM admini WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);  // "s" indicates a string
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        $sql = "UPDATE admini SET reset_token_hash = ?, reset_token_expires_at = ? WHERE Member_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $token_hash, $expires, $user['Member_ID']); // "ssi" = string, string, integer
        $stmt->execute();
        $stmt->close();

        // Updated reset link path
        $reset_link = "http://localhost/php_program/Library_Registration/reset_password.php?token=$token";

        // PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'dminenhle81@gmail.com';
            $mail->Password   = 'kyyd xgpg rbgp crts';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('dminenhle81@gmail.com', 'Uneswa Library');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Click <a href='$reset_link'>here</a> to reset your password. This link expires in 1 hour.";
            $mail->AltBody = "Copy this link: $reset_link";

            $mail->send();
            echo "If your email is registered, you will receive a reset link.";
        } catch (Exception $e) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        echo "If your email is registered, you will receive a reset link.";
    }

    $conn->close(); // Close the database connection
}
?>