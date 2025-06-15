<?php
require_once("connection.php");

require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\Exception.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT Member_ID FROM admini WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        $stmt = $pdo->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?");
        $stmt->execute([$token_hash, $expires, $user['id']]);

        $reset_link = "http://localhost/php_program/Barrowing_system/User/reset_password.php?token=$token";

        // PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'dminenhle477@gmail.com';
            $mail->Password   = 'hbzl wbju nedt lfdc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('dminenhle477@gmail.com', 'Uneswa Library ');
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
}
?>
