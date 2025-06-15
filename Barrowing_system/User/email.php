<?php
// Include PHPMailer classes
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\Exception.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();                                      // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                 // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                             // Enable SMTP authentication
    $mail->Username   = 'dminenhle477@gmail.com';         // SMTP username (sender email)
    $mail->Password   = 'hbzl wbju nedt lfdc';            // SMTP password (replace with your actual password or App Password)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;   // Enable TLS encryption
    $mail->Port       = 465;                              // TCP port to connect to

    // Recipients
    $mail->setFrom('dminenhle477@gmail.com', 'Sender Name'); // Sender's email and name
    $mail->addAddress('dminenhle81@gmail.com', 'Recipient Name'); // Recipient's email and name

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Test Email from Localhost';
    $mail->Body    = '<p>This is a test email sent from localhost using PHPMailer.</p>';
    $mail->AltBody = 'This is a test email sent from localhost using PHPMailer.';

    // Send the email
    $mail->send();
    echo 'Message has been sent successfully.';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
