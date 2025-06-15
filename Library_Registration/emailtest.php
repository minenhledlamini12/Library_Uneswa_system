<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Load Composer's autoloader

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 0; // Disable debugging output
    $mail->isSMTP(); // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com'; // Specify main SMTP server
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'dminenhle477@gmail.com'; // Your Gmail address
    $mail->Password = 'hbzl wbju nedt lfdc'; // Your Gmail password or app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587; // TCP port for TLS

    // Recipients
    $mail->setFrom('your_gmail_address@gmail.com', 'Your Name'); // Sender's email and name
    $mail->addAddress('202004294@student.uneswa.ac.sz', 'Student Name'); // Recipient's email and name

    // Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = 'Test Email'; // Email subject
    $mail->Body = '<b>This is a test email sent using PHPMailer.</b>'; // HTML body content
    $mail->AltBody = 'This is a test email sent using PHPMailer.'; // Plain text body content

    // Send the email
    if ($mail->send()) {
        echo 'Email has been sent successfully!';
    }
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
