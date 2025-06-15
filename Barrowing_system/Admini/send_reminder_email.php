<?php
// Include PHPMailer classes
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\Exception.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Current date and time (02:31 AM SAST, May 25, 2025)
$current_date = new DateTime('2025-05-25 02:31:00');

// Calculate dates for reminders
$reminder_date_minus_2 = clone $current_date;
$reminder_date_minus_2->modify('-2 days');
$reminder_date_plus_0 = clone $current_date; // Due date
$reminder_date_plus_2 = clone $current_date;
$reminder_date_plus_2->modify('+2 days');

$reminder_date_minus_2_str = $reminder_date_minus_2->format('Y-m-d');
$reminder_date_plus_0_str = $reminder_date_plus_0->format('Y-m-d');
$reminder_date_plus_2_str = $reminder_date_plus_2->format('Y-m-d');

// Fine rates based on membership type
$fine_rates = [
    'STAFF' => 1.00,           // E1.00 per day per item
    'STUDENT' => 0.50,         // E0.50 per day per item
    'EXTERNAL_BORROWER' => 2.00 // E2.00 per day per item
];

// Query to find books due in relevant time frames
$sql = "SELECT bh.BorrowingID, bh.BookID, bh.ID, bh.BorrowDate, bh.DueDate, bh.Status, bh.ReturnDate,
        m.Contact_Email, m.Member_type, m.Name, m.Surname,
        b.Title, b.ISBN
        FROM borrowing_history bh
        JOIN members m ON bh.ID = m.ID
        JOIN books b ON bh.BookID = b.ID
        WHERE bh.DueDate IN ('$reminder_date_minus_2_str', '$reminder_date_plus_0_str', '$reminder_date_plus_2_str') 
        AND bh.Status = 'borrowed'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $member_email = $row['Contact_Email'];
        $member_name = $row['Name'] . ' ' . $row['Surname'];
        $book_title = $row['Title'];
        $isbn = $row['ISBN'];
        $due_date = $row['DueDate'];
        $member_type = strtoupper(str_replace(' ', '_', $row['Member_type']));
        $fine_rate = isset($fine_rates[$member_type]) ? $fine_rates[$member_type] : 0;

        // Calculate overdue days if past due date
        $due_date_obj = new DateTime($due_date);
        $overdue_days = 0;
        if ($current_date > $due_date_obj) {
            $interval = $due_date_obj->diff($current_date);
            $overdue_days = $interval->days;
        }
        $charge = $overdue_days * $fine_rate;

        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'dminenhle477@gmail.com';
            $mail->Password   = 'hbzl wbju nedt lfdc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Recipients
            $mail->setFrom('dminenhle477@gmail.com', 'Library System');
            $mail->addAddress($member_email, $member_name);

            // Determine email content based on reminder type
            if ($current_date->format('Y-m-d') == $reminder_date_minus_2_str) {
                $mail->Subject = 'Reminder: Return Your Library Book in 2 Days';
                $mail->Body    = "
                    <h2>Library Book Return Reminder</h2>
                    <p>Dear {$member_name},</p>
                    <p>This is a reminder that you need to return the following book in 2 days to avoid overdue charges:</p>
                    <ul>
                        <li><strong>Book Title:</strong> {$book_title}</li>
                        <li><strong>ISBN:</strong> {$isbn}</li>
                        <li><strong>Due Date:</strong> {$due_date}</li>
                    </ul>
                    <p><strong>Potential Overdue Charges:</strong> If the book is not returned by the due date, you will be charged E{$fine_rate} per day per item based on your membership type ({$row['Member_type']}).</p>
                    <p>Please plan to return the book on time.</p>
                    <p>Thank you,<br>Library System</p>
                ";
                $mail->AltBody = "Library Book Return Reminder\n\nDear {$member_name},\n\nThis is a reminder that you need to return the following book in 2 days to avoid overdue charges:\n- Book Title: {$book_title}\n- ISBN: {$isbn}\n- Due Date: {$due_date}\n\nPotential Overdue Charges: If the book is not returned by the due date, you will be charged E{$fine_rate} per day per item based on your membership type ({$row['Member_type']}).\n\nPlease plan to return the book on time.\n\nThank you,\nLibrary System";
            } elseif ($current_date->format('Y-m-d') == $reminder_date_plus_0_str) {
                $mail->Subject = 'Final Reminder: Return Your Library Book Today';
                $mail->Body    = "
                    <h2>Library Book Return Final Reminder</h2>
                    <p>Dear {$member_name},</p>
                    <p>This is your final reminder to return the following book today to avoid overdue charges:</p>
                    <ul>
                        <li><strong>Book Title:</strong> {$book_title}</li>
                        <li><strong>ISBN:</strong> {$isbn}</li>
                        <li><strong>Due Date:</strong> {$due_date}</li>
                    </ul>
                    <p><strong>Potential Overdue Charges:</strong> If the book is not returned by today, you will be charged E{$fine_rate} per day per item based on your membership type ({$row['Member_type']}).</p>
                    <p>Please return the book immediately.</p>
                    <p>Thank you,<br>Library System</p>
                ";
                $mail->AltBody = "Library Book Return Final Reminder\n\nDear {$member_name},\n\nThis is your final reminder to return the following book today to avoid overdue charges:\n- Book Title: {$book_title}\n- ISBN: {$isbn}\n- Due Date: {$due_date}\n\nPotential Overdue Charges: If the book is not returned by today, you will be charged E{$fine_rate} per day per item based on your membership type ({$row['Member_type']}).\n\nPlease return the book immediately.\n\nThank you,\nLibrary System";
            } else { // 2 days after due date
                $mail->Subject = 'Overdue Notice: Return Your Library Book';
                $mail->Body    = "
                    <h2>Library Book Overdue Notice</h2>
                    <p>Dear {$member_name},</p>
                    <p>The following book is overdue by {$overdue_days} days:</p>
                    <ul>
                        <li><strong>Book Title:</strong> {$book_title}</li>
                        <li><strong>ISBN:</strong> {$isbn}</li>
                        <li><strong>Due Date:</strong> {$due_date}</li>
                    </ul>
                    <p><strong>Overdue Charge:</strong> You have been charged E" . number_format($charge, 2) . " based on your membership type ({$row['Member_type']} at E{$fine_rate} per day).</p>
                    <p>Please return the book immediately to avoid further charges.</p>
                    <p>Thank you,<br>Library System</p>
                ";
                $mail->AltBody = "Library Book Overdue Notice\n\nDear {$member_name},\n\nThe following book is overdue by {$overdue_days} days:\n- Book Title: {$book_title}\n- ISBN: {$isbn}\n- Due Date: {$due_date}\n\nOverdue Charge: You have been charged E" . number_format($charge, 2) . " based on your membership type ({$row['Member_type']} at E{$fine_rate} per day).\n\nPlease return the book immediately to avoid further charges.\n\nThank you,\nLibrary System";
            }

            // Send the email
            $mail->send();
            echo "Reminder email sent successfully to {$member_email} for book '{$book_title}' due on {$due_date}.\n";
        } catch (Exception $e) {
            echo "Reminder email could not be sent to {$member_email}. Mailer Error: {$mail->ErrorInfo}\n";
        }
    }
} else {
    echo "No books are due for reminders today.\n";
}

$conn->close();
?>