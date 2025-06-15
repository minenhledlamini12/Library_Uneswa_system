<?php
// Validate the email input
if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address.");
}

$email = $_POST["email"];

// Generate a secure token and hash it
$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);

// Set the token expiry time (30 minutes from now)
$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

// Include the database connection
require_once("connection.php");

// Prepare the SQL query to update the admini table
$sql = "UPDATE admini
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die("SQL error: " . $mysqli->error);
}

$stmt->bind_param("sss", $token_hash, $expiry, $email);
$stmt->execute();

// Check if the email exists and the update was successful
if ($stmt->affected_rows > 0) {
    // Include the mailer configuration
    $mail = require __DIR__ . "/mailer.php";

    // Set up the email
    $mail->setFrom("noreply@example.com", "No Reply");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset";
    $mail->Body = <<<END
    Click <a href="http://example.com/reset-password.php?token=$token">here</a> 
    to reset your password.
    END;

    // Attempt to send the email
    try {
        $mail->send();
        echo "Message sent, please check your inbox.";
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        echo "Message could not be sent. Please try again later.";
    }
} else {
    echo "Email not found.";
}
?>
