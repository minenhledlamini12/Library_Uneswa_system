<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__FILE__) . '/phpqrcode-2010100721_1.1.4/phpqrcode/qrinput.php';
require_once("connection.php");
require __DIR__ . '/vendor/autoload.php';

// Function to generate QR code and send email
function generateAndSendQRCode($ID, $Email, $Name,$Password) {
    global $conn;

    // Generate QR code
	
	// Generate QR code (JSON data)
$qr_data_array = [
    'ID' => $ID,
    'Name' => $Name,
    'Password'=>$Password,
];
$qr_data = json_encode($qr_data_array); // Encode to JSON

$qr_file = 'qrcodes/' . $ID . '.png'; // Path to save QR code
QRcode::png($qr_data, $qr_file, 'L', 4, 2); // Generate QR code
	
    

    // Store QR code path in the database
    $update_sql = "UPDATE members SET Qr_code = ? WHERE ID = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $qr_file, $ID);
    $update_stmt->execute();
    $update_stmt->close();

    // Send email with QR code
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = 'your_smtp_host';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@example.com';
        $mail->Password   = 'your_email_password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('your_email@example.com', 'UNESWA Library');
        $mail->addAddress($Email, $Name);

        // Attachments
        $mail->addAttachment($qr_file, 'UNESWA_Library_QR_Code.png');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'UNESWA Library QR Code';
        $mail->Body    = 'Dear ' . htmlspecialchars($Name) . ',<br><br>Here is your QR code for the UNESWA Library. Please keep it safe for easy access to the library.<br><br>Sincerely,<br>The UNESWA Library Team';
        $mail->AltBody = 'Dear ' . $Name . ', Here is your QR code for the UNESWA Library. Please keep it safe for easy access.';

        $mail->send();
        echo "QR code generated and sent to $Email successfully.";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Fetch members who don't have a QR code yet
$sql = "SELECT ID, Email, Name FROM members WHERE Qr_code IS NULL OR Qr_code = ''";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
		
		 generateAndSendQRCode($row['ID'], $row['Email'], $row['Name'], $row['Password']); 

        // Remove the incorrect call:
    }
} else {
    echo "No members found without QR codes.";
}

$conn->close();
?>
