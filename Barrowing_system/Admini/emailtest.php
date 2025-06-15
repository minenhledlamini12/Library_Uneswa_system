<?php
// Load Composer's autoloader.
// This line is essential for loading PHPMailer if you installed it via Composer.
// Assuming your script is in a subdirectory (e.g., 'Admini')
// and 'vendor' is directly under your project root (e.g., 'Barrowing_system').
require __DIR__ . '/../vendor/autoload.php';

// Import PHPMailer classes into the global namespace
// These 'use' statements make class names shorter and easier to read.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception; // Essential for catching PHPMailer's specific errors

// Create a new PHPMailer instance.
// Passing 'true' to the constructor enables exceptions for detailed error reporting.
$mail = new PHPMailer(true);

try {
    // --- Server Settings ---
    // Enable verbose debug output.
    // Set to 0 for production to disable debug output.
    // Set to 2 for detailed client-server conversation.
    // Set to 3 for even more detailed debug, including SMTP command and response.
    $mail->SMTPDebug = 2; // Set this to 2 to see the detailed SMTP log

    // Tell PHPMailer to use SMTP.
    $mail->isSMTP();

    // Set the SMTP server to send through (Gmail's SMTP server).
    $mail->Host = 'smtp.gmail.com';

    // Enable SMTP authentication.
    $mail->SMTPAuth = true;

    // Your Gmail username (full email address).
    $mail->Username = 'dminenhle477@gmail.com'; // REPLACE with your Gmail address

    // Your Gmail App Password (NOT your regular Gmail password if you have 2FA enabled).
    // IMPORTANT: If you use 2-Step Verification on your Gmail account, you MUST generate
    // an App Password (go to Google Account -> Security -> App Passwords)
    // and use that here instead of your regular Gmail password.
    $mail->Password = 'hbzl wbju nedt lfdc'; // REPLACE with your generated App Password

    // Enable implicit TLS encryption (SSL/SMTPS). Use port 465 for this.
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

    // Set the TCP port to connect to.
    $mail->Port = 465;

    // --- Recipients ---
    // Set who the email is sent from. This should generally match your Gmail Username.
    $mail->setFrom('dminenhle477@gmail.com', 'Your Name or App Name'); // REPLACE with your Gmail address and a suitable name

    // Add a recipient.
    $mail->addAddress('dminenhle477@gmail.com'); // REPLACE with the recipient's email address
    // You can also add a name: $mail->addAddress('recipient@example.com', 'Recipient Name');

    // To add multiple recipients:
    // $mail->addAddress('another_recipient@example.com');
    // $mail->addCC('cc_address@example.com'); // Add a "Carbon Copy" recipient
    // $mail->addBCC('bcc_address@example.com'); // Add a "Blind Carbon Copy" recipient

    // --- Content ---
    // Set email format to HTML.
    $mail->isHTML(true);

    // Set the email subject.
    $mail->Subject = 'Test Email from XAMPP PHPMailer';

    // Set the HTML message body.
    $mail->Body    = 'This is a test email sent from your XAMPP server using <b>PHPMailer</b>!';

    // Set the plain text alternative body for non-HTML email clients.
    $mail->AltBody = 'This is the plain text version of the email. If you see this, your email client does not support HTML.';

    // --- Optional: Attachments ---
    // Uncomment the following lines to add an attachment
    // $mail->addAttachment('/path/to/your/file.pdf', 'Document.pdf'); // Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg'); // Optional name

    // Send the email.
    $mail->send();
    echo 'Email sent successfully!';

} catch (Exception $e) {
    // Catch PHPMailer-specific exceptions and display the error.
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    // For more detailed PHP exception information during development, you can uncomment this:
    // echo "<br>Details: " . $e->getMessage();
}
?>