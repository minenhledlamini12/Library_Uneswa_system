<?php
// Connect to database
require_once("connection.php");

// Function to send email using PHPMailer
function sendEmailPHPMailer($to, $subject, $body) {
    // Similar implementation as in scan_handle.php
}

// Check for books due in 2 days
$twoDaysQuery = "SELECT * FROM borrowing_history WHERE DueDate = DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND Status = 'borrowed'";
$twoDaysResult = mysqli_query($conn, $twoDaysQuery);
if ($twoDaysResult && mysqli_num_rows($twoDaysResult) > 0) {
    while ($row = mysqli_fetch_assoc($twoDaysResult)) {
        $member_ID = $row['Member_ID'];
        $memberQuery = "SELECT Email FROM members WHERE Member_ID = '$member_ID'";
        $memberResult = mysqli_query($conn, $memberQuery);
        if ($memberResult && mysqli_num_rows($memberResult) > 0) {
            $memberData = mysqli_fetch_assoc($memberResult);
            $memberEmail = $memberData['Email'];
            $emailBody = "Reminder: Book due in 2 days.";
            sendEmailPHPMailer($memberEmail, "Book Due Soon", $emailBody);
        }
    }
}

// Check for books due in 1 day
$oneDayQuery = "SELECT * FROM borrowing_history WHERE DueDate = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND Status = 'borrowed'";
$oneDayResult = mysqli_query($conn, $oneDayQuery);
if ($oneDayResult && mysqli_num_rows($oneDayResult) > 0) {
    while ($row = mysqli_fetch_assoc($oneDayResult)) {
        $member_ID = $row['Member_ID'];
        $memberQuery = "SELECT Email FROM members WHERE Member_ID = '$member_ID'";
        $memberResult = mysqli_query($conn, $memberQuery);
        if ($memberResult && mysqli_num_rows($memberResult) > 0) {
            $memberData = mysqli_fetch_assoc($memberResult);
            $memberEmail = $memberData['Email'];
            $emailBody = "Reminder: Book due tomorrow.";
            sendEmailPHPMailer($memberEmail, "Book Due Tomorrow", $emailBody);
        }
    }
}

// Check for books due today
$dueTodayQuery = "SELECT * FROM borrowing_history WHERE DueDate = CURDATE() AND Status = 'borrowed'";
$dueTodayResult = mysqli_query($conn, $dueTodayQuery);
if ($dueTodayResult && mysqli_num_rows($dueTodayResult) > 0) {
    while ($row = mysqli_fetch_assoc($dueTodayResult)) {
        $member_ID = $row['Member_ID'];
        $memberQuery = "SELECT Email FROM members WHERE Member_ID = '$member_ID'";
        $memberResult = mysqli_query($conn, $memberQuery);
        if ($memberResult && mysqli_num_rows($memberResult) > 0) {
            $memberData = mysqli_fetch_assoc($memberResult);
            $memberEmail = $memberData['Email'];
            $emailBody = "Reminder: Book is due today.";
            sendEmailPHPMailer($memberEmail, "Book Due Today", $emailBody);
        }
    }
}

// Check for overdue books and charge fines
$overdueQuery = "SELECT * FROM borrowing_history WHERE DueDate < CURDATE() AND Status = 'borrowed'";
$overdueResult = mysqli_query($conn, $overdueQuery);
if ($overdueResult && mysqli_num_rows($overdueResult) > 0) {
    while ($row = mysqli_fetch_assoc($overdueResult)) {
        $member_ID = $row['Member_ID'];
        $memberQuery = "SELECT Membership_type FROM members WHERE Member_ID = '$member_ID'";
        $memberResult = mysqli_query($conn, $memberQuery);
        if ($memberResult && mysqli_num_rows($memberResult) > 0) {
            $memberData = mysqli_fetch_assoc($memberResult);
            $membershipType = $memberData['Membership_type'];
        }

        $daysOverdue = (strtotime(CURDATE()) - strtotime($row['DueDate'])) / (60 * 60 * 24);

        switch (strtolower($membershipType)) {
            case 'student':
                if ($daysOverdue <= 14) {
                    $fine = $daysOverdue * 0.50;
                } else {
                    $fine = (14 * 0.50) + (($daysOverdue - 14) * 1.00);
                }
                break;
            case 'staff':
                if ($daysOverdue <= 14) {
                    $fine = $daysOverdue * 1.00;
                } else {
                    $fine = (14 * 1.00) + (($daysOverdue - 14) * 2.00);
                }
                break;
            case 'external member':
                $fine = $daysOverdue * 2.00;
                break;
            default:
                $fine = $daysOverdue * 2.00; // Default fine for unknown membership type
                break;
        }

        // Update fine in borrowing_history table
        // Assuming you have a `Fine` column in the `borrowing_history` table
        $updateFineQuery = "UPDATE borrowing_history SET Fine = '$fine' WHERE BorrowingID = '$row[BorrowingID]'";
        mysqli_query($conn, $updateFineQuery);

        // Send email about fine
        $emailBody = "You have been charged a fine of $fine for the overdue book.";
        sendEmailPHPMailer($memberEmail, "Overdue Book Fine", $emailBody);
    }
}
?>
