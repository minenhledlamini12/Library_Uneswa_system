<?php
session_start();

require_once("connection.php");

//PHPMailer autoloader
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\Exception.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Function to calculate return date
function calculateReturnDate($borrowDate, $membershipType)
{
    $borrowTimestamp = strtotime($borrowDate);

    switch (strtolower($membershipType)) {
        case 'student':
        case 'external member':
            $returnTimestamp = strtotime('+14 days', $borrowTimestamp);
            break;
        case 'staff':
            $returnTimestamp = strtotime('+12 weeks', $borrowTimestamp);
            break;
        default:
            $returnTimestamp = strtotime('+14 days', $borrowTimestamp);
            break;
    }

    return date('Y-m-d', $returnTimestamp);
}

// Function to send email using PHPMailer
function sendEmailPHPMailer($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
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
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}

// Function to check borrowing limit
function checkBorrowingLimit($memberId, $membershipType)
{
    global $conn;

    $limit = 0;
    switch (strtolower($membershipType)) {
        case 'student':
            $limit = 6;
            break;
        case 'staff':
            $limit = 10;
            break;
        case 'external member':
            $limit = 4;
            break;
    }

    $borrowedCountQuery = "SELECT COUNT(*) FROM borrowing_history WHERE Member_ID = ? AND status = 'borrowed'";
    $stmt = mysqli_prepare($conn, $borrowedCountQuery);
    mysqli_stmt_bind_param($stmt, "i", $memberId);
    mysqli_stmt_execute($stmt);
    $borrowedCountResult = mysqli_stmt_get_result($stmt);
    $borrowedCount = mysqli_fetch_array($borrowedCountResult)[0];
    mysqli_stmt_close($stmt);

    return ($borrowedCount < $limit);
}

// Function to send due date reminder emails
function sendDueDateReminders()
{
    global $conn;

    $today = date("Y-m-d");
    $twoDaysBefore = date('Y-m-d', strtotime('+2 days', strtotime($today)));
    $oneDayBefore = date('Y-m-d', strtotime('+1 day', strtotime($today)));
    $onDueDate = $today;

    $reminderDates = [$twoDaysBefore => '2 days', $oneDayBefore => '1 day', $onDueDate => 'today'];

    foreach ($reminderDates as $reminderDate => $timeFrame) {
        $sql = "SELECT borrowing_history.BorrowingID, borrowing_history.Member_ID, borrowing_history.bookID, borrowing_history.return_date, members.email, books.Title FROM borrowing_history INNER JOIN members ON borrowing_history.MemberID = members.MemberID INNER JOIN books ON borrowing_history.bookID = books.ID WHERE borrowing_history.return_date = ? AND borrowing_history.status = 'borrowed'";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $reminderDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $memberEmail = $row["email"];
                $bookTitle = $row["Title"];
                $returnDate = $row["return_date"];
                $borrowingID = $row["BorrowingID"];

                $emailBody = "Dear Member,<br><br>This is a reminder that the book '{$bookTitle}' is due back on {$returnDate}.<br><br>";
                $emailBody .= "Please return the book on time to avoid fines.<br><br>";
                $emailBody .= "Thank you for using UNESWA Library!<br>";
                if (sendEmailPHPMailer($memberEmail, "Book Due Date Reminder", $emailBody)) {
                    echo "Reminder email sent successfully to {$memberEmail} for BorrowingID {$borrowingID} ({$timeFrame} before due date).<br>";
                } else {
                    echo "Error sending reminder email to {$memberEmail} for BorrowingID {$borrowingID}.<br>";
                }
            }
        } else {
            echo "No books due {$timeFrame}.<br>";
        }
        mysqli_stmt_close($stmt);
    }
}

// Function to calculate fine
function calculateFine($memberId, $borrowingID)
{
    global $conn;

    $borrowQuery = "SELECT barrowdate, return_date, Member_ID FROM borrowing_history WHERE BorrowingID = ?";
    $stmt = mysqli_prepare($conn, $borrowQuery);
    mysqli_stmt_bind_param($stmt, "i", $borrowingID);
    mysqli_stmt_execute($stmt);
    $borrowResult = mysqli_stmt_get_result($stmt);

    if ($borrowResult && mysqli_num_rows($borrowResult) > 0) {
        $borrowData = mysqli_fetch_assoc($borrowResult);
        $borrowDate = $borrowData['barrowdate'];
        $returnDate = $borrowData['return_date'];
        $memberId = $borrowData['Member_ID'];
        mysqli_stmt_close($stmt);

        $memberQuery = "SELECT Membership_type FROM members WHERE MemberID = ?";
        $stmt = mysqli_prepare($conn, $memberQuery);
        mysqli_stmt_bind_param($stmt, "i", $memberId);
        mysqli_stmt_execute($stmt);
        $memberResult = mysqli_stmt_get_result($stmt);

        if ($memberResult && mysqli_num_rows($memberResult) > 0) {
            $memberData = mysqli_fetch_assoc($memberResult);
            $membershipType = strtolower($memberData['Membership_type']);
            mysqli_stmt_close($stmt);

            $today = date("Y-m-d");
            $dueDateTimestamp = strtotime($returnDate);
            $todayTimestamp = strtotime($today);

            if ($todayTimestamp > $dueDateTimestamp) {
                $overdueDays = round(($todayTimestamp - $dueDateTimestamp) / (60 * 60 * 24));
            } else {
                return 0;
            }

            $fine = 0;
            switch ($membershipType) {
                case 'student':
                    if ($overdueDays <= 14) {
                        $fine = 0.50 * $overdueDays;
                    } else {
                        $fine = (0.50 * 14) + (1.00 * ($overdueDays - 14));
                    }
                    break;
                case 'staff':
                    if ($overdueDays <= 14) {
                        $fine = 1.00 * $overdueDays;
                    } else {
                        $fine = (1.00 * 14) + (2.00 * ($overdueDays - 14));
                    }
                    break;
                case 'external member':
                    $fine = 2.00 * $overdueDays;
                    break;
            }
            return $fine;
        } else {
            mysqli_stmt_close($stmt);
            return "Error: Could not retrieve membership type.";
        }
    } else {
        mysqli_stmt_close($stmt);
        return "Error: Could not retrieve borrowing details.";
    }
}
// Handle initial QR code verification
if (isset($_POST['qr_code'])) {
    $qrCodeData = $_POST['qr_code'];

    // Verify the QR code data (e.g., check against database)
    $query = "SELECT ID, ISBN, Title, Author FROM books WHERE ISBN = ?";  // Assuming the QR code contains the book's ISBN
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $qrCodeData);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $book = mysqli_fetch_assoc($result);
        // Prepare the response
        $response = array('status' => 'success', 'book' => $book);
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid QR Code');
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
// Process the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_borrow'])) {
    $isbn = $_POST['isbn'];
    $bookId = $_POST['book_id'];

    // Get the Member_ID from session
    $memberSessionId = $_SESSION['user']['Member_ID'];

    // Look up the actual ID (primary key) from members table to satisfy the foreign key constraint
    $getMemberIdQuery = "SELECT ID FROM members WHERE Member_ID = ?";
    $stmt = mysqli_prepare($conn, $getMemberIdQuery);
    mysqli_stmt_bind_param($stmt, "i", $memberSessionId);
    mysqli_stmt_execute($stmt);
    $memberIdResult = mysqli_stmt_get_result($stmt);

    if ($memberIdResult && mysqli_num_rows($memberIdResult) > 0) {
        $memberData = mysqli_fetch_assoc($memberIdResult);
        $memberId = $memberData['ID']; // This is the ID we need for the foreign key
    } else {
        $_SESSION['message'] = "Error: Could not retrieve member ID.";
        header("Location: homepage.php");
        exit();
    }
    mysqli_stmt_close($stmt);

    $membershipType = $_SESSION['user']['Membership_type'];

    if (!checkBorrowingLimit($memberId, $membershipType)) {
        $_SESSION['message'] = "You have reached your borrowing limit.";
        header("Location: homepage.php");
        exit();
    }

    $borrowDate = date("Y-m-d");
    $dueDate = calculateReturnDate($borrowDate, $membershipType);

    // Get member email for confirmation
    $memberQuery = "SELECT Email FROM members WHERE ID = ?";
    $stmt = mysqli_prepare($conn, $memberQuery);
    mysqli_stmt_bind_param($stmt, "i", $memberId);
    mysqli_stmt_execute($stmt);
    $memberResult = mysqli_stmt_get_result($stmt);

    if ($memberResult && mysqli_num_rows($memberResult) > 0) {
        $memberData = mysqli_fetch_assoc($memberResult);
        $memberEmail = $memberData['Email']; // Using 'Email' to match the column name
    } else {
        $response = array('status' => 'error', 'message' => 'Error: Could not retrieve member email.');
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    mysqli_stmt_close($stmt);
     // Check if terms_agree is set
    if (!isset($_POST['terms_agree'])) {
       $response = array('status' => 'error', 'message' => 'Please agree to the terms and conditions..');
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Insert into borrowing_history with ReturnDate as NULL
    $insertQuery = "INSERT INTO borrowing_history (ISBN, BookID, Member_ID, BorrowDate, ReturnDate, DueDate, Status) 
                    VALUES (?, ?, ?, ?, NULL, ?, 'borrowed')";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "siiss", $isbn, $bookId, $memberId, $borrowDate, $dueDate);

    if (mysqli_stmt_execute($stmt)) {
        $borrowingID = mysqli_insert_id($conn);
        $_SESSION['message'] = "Book borrowed successfully! Due date: " . $dueDate;

        // Get book details for email
        $bookDetailsQuery = "SELECT Title, Author FROM books WHERE ID = ?";
        $stmt2 = mysqli_prepare($conn, $bookDetailsQuery);
        mysqli_stmt_bind_param($stmt2, "i", $bookId);
        mysqli_stmt_execute($stmt2);
        $bookDetailsResult = mysqli_stmt_get_result($stmt2);

        $emailBody = "Dear Member,<br><br>You have successfully borrowed a book from UNESWA Library.<br><br>";
        $emailBody .= "<b>Book Details:</b><br>";
        $emailBody .= "ISBN: " . $isbn . "<br>";

        if ($bookDetailsResult && mysqli_num_rows($bookDetailsResult) > 0) {
            $bookDetails = mysqli_fetch_assoc($bookDetailsResult);
            $emailBody .= "Title: " . $bookDetails['Title'] . "<br>";
            $emailBody .= "Author: " . $bookDetails['Author'] . "<br>";
        }
        $emailBody .= "Borrow Date: " . $borrowDate . "<br>";
        $emailBody .= "Due Date: " . $dueDate . "<br><br>";
        $emailBody .= "Thank you for using UNESWA Library!<br>";

        if (sendEmailPHPMailer($memberEmail, "Book Borrowing Confirmation", $emailBody)) {
             $response = array('status' => 'success', 'message' => 'Borrowed book Successfully!. An email is sent.');
        } else {
             $response = array('status' => 'success', 'message' => 'Borrowed book Successfully!. Email not sent.');
        }
        mysqli_stmt_close($stmt2);
    } else {
          $response = array('status' => 'error', 'message' => 'Error Borrowing book.');
    }
    mysqli_stmt_close($stmt);

    // Update book availability
    $updateQuery = "UPDATE books SET CopiesAvailable = CopiesAvailable - 1 WHERE ID = ?";
    $stmt3 = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt3, "i", $bookId);

    if (!mysqli_stmt_execute($stmt3)) {
         $response = array('status' => 'error', 'message' => 'Error in updating book.');
    }

    mysqli_stmt_close($stmt3);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}else{
    $response = array('status' => 'error', 'message' => 'Invalid Request');
      header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
