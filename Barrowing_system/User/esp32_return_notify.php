<?php
session_start();
require_once "connection.php";

// PHPMailer includes
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\Exception.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

function outputResponse($response) {
    ob_clean();
    echo json_encode($response);
    ob_end_flush();
    exit();
}

if (!isset($_GET['book_id'])) {
    outputResponse(['success' => false, 'message' => 'Missing book_id']);
}

$bookId = intval($_GET['book_id']);
$conn = $GLOBALS['conn'];

// Find the latest borrowed record for this book
$sql = "SELECT * FROM borrowing_history WHERE BookID='$bookId' AND Status='borrowed' ORDER BY BorrowingID DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    outputResponse(['success' => false, 'message' => 'No borrowed record found']);
}

$row = mysqli_fetch_assoc($result);
$borrowingId = $row['BorrowingID'];
$isbn = $row['ISBN'];
$borrowDate = $row['BorrowDate'];

// Update borrowing_history
$update = "UPDATE borrowing_history SET Status='successful', ReturnDate=NOW(), Returned=1 WHERE BorrowingID='$borrowingId'";
if (!mysqli_query($conn, $update)) {
    outputResponse(['success' => false, 'message' => 'Failed to update borrowing history.']);
}

// Get user email and name from session
$userEmail = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : '';
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Library User';

// Get book title
$bookTitle = '';
$bookQuery = mysqli_query($conn, "SELECT * FROM books WHERE ID='$bookId' LIMIT 1");
if ($bookQuery && mysqli_num_rows($bookQuery) > 0) {
    $book = mysqli_fetch_assoc($bookQuery);
    $bookTitle = $book['Title'];
}

// Send confirmation email if user email is available
if ($userEmail) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dminenhle477@gmail.com'; // Your sender email
        $mail->Password   = 'hbzl wbju nedt lfdc';    // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('dminenhle477@gmail.com', 'Library System');
        $mail->addAddress($userEmail, $userName);
        $mail->isHTML(true);
        $mail->Subject = 'Book Return Confirmation';
        $mail->Body    = "
            <p>Dear {$userName},</p>
            <p>Your return of the book <strong>{$bookTitle}</strong> (ISBN: {$isbn}) has been successfully processed.</p>
            <p>Borrowed on: {$borrowDate}<br>
            Returned on: ".date('Y-m-d H:i:s')."</p>
            <p>Thank you for using our library!</p>
        ";
        $mail->AltBody = "Dear {$userName},\nYour return of the book '{$bookTitle}' (ISBN: {$isbn}) has been successfully processed.\nBorrowed on: {$borrowDate}\nReturned on: ".date('Y-m-d H:i:s')."\nThank you for using our library!";

        $mail->send();
        outputResponse(['success' => true, 'message' => 'Return recorded and email sent.']);
    } catch (Exception $e) {
        outputResponse(['success' => true, 'message' => 'Return recorded, but email could not be sent. Error: ' . $mail->ErrorInfo]);
    }
} else {
    outputResponse(['success' => true, 'message' => 'Return recorded, but user email not found in session.']);
}
?>
