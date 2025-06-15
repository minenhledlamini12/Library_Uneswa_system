<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__FILE__). '/phpqrcode-2010100721_1.1.4/phpqrcode/qrinput.php';
require_once("connection.php");
require __DIR__. '/vendor/autoload.php';

// Function to generate QR code, update the database, and optionally send an email
function generateAndSendQRCode($ID, $Email, $Name, $Password_Hashed, $send_email = true) {
    global $conn;

    // Generate QR code (JSON data - Consider removing password for security)
    $qr_data_array = [
        'id' => $ID,
        'name' => $Name,
        // 'password' => $Password_Hashed,  // Remove or comment out for better security
    ];
    $qr_data = json_encode($qr_data_array);

    $qr_file = 'qrcodes/'. $ID. '.png';
    QRcode::png($qr_data, $qr_file, 'L', 4, 2);

    // Store QR code path in the database (with error handling)
    $update_sql = "UPDATE members SET Qr_code =? WHERE ID =?";
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt === false) {
        die("Error preparing update statement: ". $conn->error);
    }
    $update_stmt->bind_param("si", $qr_file, $ID);
    if (!$update_stmt->execute()) {
        echo "Error updating QR code path for ID $ID: ". $update_stmt->error. "<br>";
    }
    $update_stmt->close();

    if ($send_email) {
        // Send email with QR code (with more robust error handling)
        $mail = new PHPMailer(true);
        try {
            //... (Your existing email configuration)...

            $mail->send();
            echo "QR code generated and sent to $Email successfully.<br>";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo} for $Email<br>";
        }
    }
}

// Initialize variables for error messages
$id_error = $email_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //... (Your existing form processing and validation logic)...

    // If no validation errors, proceed with registration
    if (empty($id_error) && empty($email_error)) {
        $Password_Hashed = password_hash($Password, PASSWORD_DEFAULT);

        // SQL to insert data into the members table (Use prepared statements!)
        $sql = "INSERT INTO members (ID, Name, Surname, `Course/Department/Affliation`, Membership_type, Contact, Email, Password, Joined_Date) 
                VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: ". $conn->error);
        }
        $stmt->bind_param("issssssss", $ID, $Name, $Surname, $CourseDepartmentAffliation, $Membership_type, $Contact, $Email, $Password_Hashed, $Joined_Date);

        if ($stmt->execute()) {
            // Generate QR code and send email (email sending is enabled by default)
            generateAndSendQRCode($ID, $Email, $Name, $Password_Hashed);

            //... (Your existing success page HTML)...

        } else {
            echo "Error: ". $stmt->error;
        }

        $stmt->close();
    } else {
        //... (Your existing validation error display logic)...
    }
}

// Generate QR codes for existing members who don't have one (no email sending)
$sql = "SELECT ID, Email, Name, Password FROM members WHERE Qr_code IS NULL OR Qr_code = ''";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Generate QR code but don't send an email
            generateAndSendQRCode($row['ID'], $row['Email'], $row['Name'], $row['Password'], false);
        }
    } else {
        echo "No members found without QR codes.<br>";
    }
} else {
    echo "Error querying members: ". $conn->error. "<br>";
}

$conn->close();?>