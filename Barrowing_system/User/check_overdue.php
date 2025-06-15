<?php
set_time_limit(300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "connection.php";

require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\Exception.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

ob_start();

function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, __DIR__ . '/overdue_errors.log');
}

function sendEmailPHPMailer($to, $subject, $body) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dminenhle477@gmail.com';
        $mail->Password = 'hbzl wbju nedt lfdc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->setFrom('dminenhle477@gmail.com', 'UNESWA Library');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        $mail->send();
        logError("Email sent to: $to, Subject: $subject");
        return true;
    } catch (Exception $e) {
        logError("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}

function getChargeRate($membershipType) {
    switch (strtolower($membershipType)) {
        case 'student': return 0.50;
        case 'staff': return 1.00;
        case 'external member': return 2.00;
        default: return 2.00;
    }
}

try {
    if (!isset($conn) || !$conn) {
        logError("Database connection failed.");
        exit("Database connection failed.");
    }

    // Add blacklist column to borrowing_history if not exists
    $alterQuery = "ALTER TABLE borrowing_history ADD COLUMN IF NOT EXISTS blacklist DATETIME DEFAULT NULL";
    if (!mysqli_query($conn, $alterQuery)) {
        logError("DB Error (alter table): " . mysqli_error($conn));
    }

    // Get current date for comparison
    $currentDate = date('Y-m-d');
    $currentDateTime = date('Y-m-d H:i:s');

    // Query borrowed books that are not returned
    $query = "SELECT bh.*, m.Email, m.Name, m.Surname, m.Membership_type, b.Title, b.Author 
              FROM borrowing_history bh
              JOIN members m ON bh.ID = m.ID
              JOIN books b ON bh.BookID = b.ID
              WHERE bh.Status = 'borrowed' AND bh.Returned = 0";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        logError("DB Error (fetch borrowed): " . mysqli_error($conn));
        exit("Database error: " . mysqli_error($conn));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $dueDate = $row['DueDate'];
        $dueDateOnly = date('Y-m-d', strtotime($dueDate));
        $borrowingId = $row['BorrowingID'];
        $memberId = $row['ID'];
        $email = $row['Email'];
        $name = $row['Name'];
        $surname = $row['Surname'];
        $membershipType = $row['Membership_type'];
        $bookTitle = $row['Title'];
        $bookAuthor = $row['Author'];
        $isbn = $row['ISBN'];
        $chargeRate = getChargeRate($membershipType);

        // Calculate days until due date
        $daysUntilDue = (strtotime($dueDateOnly) - strtotime($currentDate)) / (60 * 60 * 24);

        // Send reminder emails 2 days and 1 day before due date
        if ($daysUntilDue == 2 || $daysUntilDue == 1) {
            $emailBody = "Dear {$name} {$surname},<br><br>";
            $emailBody .= "This is a reminder that the following book is due in {$daysUntilDue} day(s):<br>";
            $emailBody .= "Title: {$bookTitle}<br>";
            $emailBody .= "Author: {$bookAuthor}<br>";
            $emailBody .= "ISBN: {$isbn}<br>";
            $emailBody .= "Due Date: {$dueDateOnly}<br><br>";
            $emailBody .= "Please return the book by the due date to avoid charges. Overdue charges are E{$chargeRate} per day per item.<br>";
            $emailBody .= "Thank you,<br>UNESWA Library";

            sendEmailPHPMailer($email, "Reminder: Book Due in {$daysUntilDue} Day(s)", $emailBody);
        }

        // Check if book is overdue (on or after due date)
        if (strtotime($currentDate) >= strtotime($dueDateOnly)) {
            // Update status to blacklisted and set blacklist timestamp
            $updateQuery = "UPDATE borrowing_history 
                            SET Status = 'blacklisted', blacklist = '$currentDateTime'
                            WHERE BorrowingID = '$borrowingId' AND Status = 'borrowed' AND Returned = 0";
            if (!mysqli_query($conn, $updateQuery)) {
                logError("DB Error (update blacklist): " . mysqli_error($conn));
            }

            // Send overdue email on due date and next 2 days
            $daysOverdue = (strtotime($currentDate) - strtotime($dueDateOnly)) / (60 * 60 * 24);
            if ($daysOverdue >= 0 && $daysOverdue <= 2) {
                $totalCharge = $chargeRate * ($daysOverdue + 1); // Charge starts on due date
                $emailBody = "Dear {$name} {$surname},<br><br>";
                $emailBody .= "The following book is overdue:<br>";
                $emailBody .= "Title: {$bookTitle}<br>";
                $emailBody .= "Author: {$bookAuthor}<br>";
                $emailBody .= "ISBN: {$isbn}<br>";
                $emailBody .= "Due Date: {$dueDateOnly}<br>";
                $emailBody .= "Days Overdue: " . ($daysOverdue + 1) . "<br><br>";
                $emailBody .= "You are being charged E{$chargeRate} per day per item. Current charge: E{$totalCharge}.<br>";
                $emailBody .= "Please return the book immediately to avoid further charges.<br>";
                $emailBody .= "Thank you,<br>UNESWA Library";

                sendEmailPHPMailer($email, "Overdue Book Notification", $emailBody);
            }
        }
    }

    ob_clean();
    echo "Overdue checks and reminders processed successfully.";
    ob_end_flush();

} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    ob_clean();
    echo "Error: " . $e->getMessage();
    ob_end_flush();
}
?>