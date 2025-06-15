<?php
// scan_handle.php
set_time_limit(300); // Increased timeout, adjust as needed
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once "connection.php"; // Ensure this path is correct for your setup

// PHPMailer autoloading is often handled by Composer.
// If you're not using Composer, these paths must be exact.
// Verify these paths are correct for your XAMPP installation.
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\Exception.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

ob_start(); // Start output buffering

$response = ['success' => false, 'message' => ''];

function outputResponse($response) {
    ob_clean(); // Clear any previous output
    header('Content-Type: application/json');
    echo json_encode($response);
    ob_end_flush(); // Send buffered output and turn off buffering
    exit(); // Terminate script execution
}

function logError($message) {
    // Log errors to a file for debugging
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, __DIR__ . '/borrowing_errors.log');
}

function sanitizeInput($data) {
    global $conn; // Access the global connection variable
    if (!$conn) {
        logError("Database connection not available during sanitization.");
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    return mysqli_real_escape_string($conn, trim($data));
}

function calculateDueDate($borrowDate, $membershipType) {
    $borrowTimestamp = strtotime($borrowDate);
    switch (strtolower($membershipType)) {
        case 'student': return date('Y-m-d H:i:s', strtotime('+14 days', $borrowTimestamp));
        case 'staff': return date('Y-m-d H:i:s', strtotime('+16 weeks', $borrowTimestamp));
        case 'external member': return date('Y-m-d H:i:s', strtotime('+14 days', $borrowTimestamp));
        default: return date('Y-m-d H:i:s', strtotime('+14 days', $borrowTimestamp)); // Default to 14 days
    }
}

function getBorrowingLimit($membershipType) {
    switch (strtolower($membershipType)) {
        case 'student': return 6;
        case 'staff': return 10;
        case 'external member': return 4;
        default: return 4; // Default limit
    }
}

function sendEmailPHPMailer($to, $subject, $body) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dminenhle477@gmail.com'; // Your Gmail address
        $mail->Password = 'hbzl wbju nedt lfdc'; // Your App Password for Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SMTPS for port 465
        $mail->Port = 465;
        $mail->setFrom('dminenhle477@gmail.com', 'UNESWA Library');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body); // Plain text alternative for email clients that don't support HTML
        $mail->send();
        logError("Email sent to: $to, Subject: $subject");
        return true;
    } catch (Exception $e) {
        logError("PHPMailer Error sending to $to: " . $e->getMessage());
        return false;
    }
}

// --- Main logic for handling POST requests ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        logError("Received POST Data: " . print_r($_POST, true)); // Log all incoming POST data
        logError("Current Session Data at start of scan_handle.php: " . print_r($_SESSION, true)); // DEBUG: Log session data

        if (!isset($conn) || !$conn) {
            logError("Database connection failed. `\$conn` is not set or null.");
            $response['message'] = "Server error: Database unavailable.";
            outputResponse($response);
        }

        // --- Retrieve and sanitize input data ---
        $isbn = isset($_POST['isbn']) ? sanitizeInput($_POST['isbn']) : '';
        $bookId = isset($_POST['book_id']) ? sanitizeInput($_POST['book_id']) : '';
        
        // This is the Member_ID string (e.g., '202004294') received from frontend/session
        $sessionMemberID = isset($_POST['member_ID']) ? sanitizeInput($_POST['member_ID']) : (isset($_SESSION['Member_ID']) ? sanitizeInput($_SESSION['Member_ID']) : ''); 
        $membershipType = isset($_POST['membershipType']) ? sanitizeInput($_POST['membershipType']) : (isset($_SESSION['Membership_type']) ? sanitizeInput($_SESSION['Membership_type']) : '');
        
        // Validate required fields
        $missingFields = [];
        if (empty($isbn)) $missingFields[] = 'ISBN';
        if (empty($bookId)) $missingFields[] = 'Book ID';
        if (empty($sessionMemberID)) $missingFields[] = 'Session Member ID'; // Checking for the string ID
        if (empty($membershipType)) $missingFields[] = 'Membership Type';

        if (!empty($missingFields)) {
            $response['message'] = "Missing required information: " . implode(', ', $missingFields) . ".";
            logError("Missing required POST/SESSION fields: " . implode(', ', $missingFields));
            outputResponse($response);
        }

        logError("Processing borrow request for Session Member ID: '$sessionMemberID', Membership Type: '$membershipType', Book ID: '$bookId', ISBN: '$isbn'");

        // --- Fetch the PRIMARY KEY 'ID' from the 'members' table using the 'Member_ID' string ---
        // This fetched ID is what needs to go into borrowing_history.ID
        $memberQuery = "SELECT ID, Email, Name, Surname FROM members WHERE Member_ID = '$sessionMemberID'";
        logError("Executing member lookup query (to get Primary Key ID): " . $memberQuery);
        $memberResult = mysqli_query($conn, $memberQuery);

        if (!$memberResult) {
            logError("DB Error (member lookup query): " . mysqli_error($conn));
            $response['message'] = "Database error checking member details. Please try again.";
            outputResponse($response);
        }

        if (mysqli_num_rows($memberResult) === 0) {
            logError("Member not found in 'members' table for Member_ID: '$sessionMemberID'. This ID needs to exist in members.Member_ID.");
            $response['message'] = "Member not found in the system. Please ensure your login details are correct and your member ID exists.";
            outputResponse($response);
        }
        $memberData = mysqli_fetch_assoc($memberResult);
        $primaryMemberPKID = $memberData['ID']; // This is the integer primary key (e.g., 89, 172)
                                                // This is the value that must be inserted into borrowing_history.ID

        logError("Found Primary Member PK ID: '$primaryMemberPKID' for Session Member ID: '$sessionMemberID'"); // DEBUG: Log the fetched primary ID


        // --- Check borrowing limits ---
        $maxBooks = getBorrowingLimit($membershipType);
        // Using the fetched $primaryMemberPKID for the borrowing_history.ID column
        $currentBorrowedQuery = "SELECT COUNT(*) as count FROM borrowing_history WHERE ID = '$primaryMemberPKID' AND Status = 'borrowed'";
        logError("Executing borrowing limit query: " . $currentBorrowedQuery);
        $currentBorrowedResult = mysqli_query($conn, $currentBorrowedQuery);

        if (!$currentBorrowedResult) {
            logError("DB Error (borrowed count query - borrowing_history table): " . mysqli_error($conn));
            $response['message'] = "Database error checking borrowed books. Please try again.";
            outputResponse($response);
        }

        $currentBorrowedData = mysqli_fetch_assoc($currentBorrowedResult);
        if ($currentBorrowedData['count'] >= $maxBooks) {
            $response['message'] = "You have reached your maximum borrow limit of $maxBooks books.";
            outputResponse($response);
        }

        // --- Get book details and check availability ---
        $bookQuery = "SELECT CopiesAvailable, Title, Author FROM books WHERE ID = '$bookId' AND ISBN = '$isbn'";
        $bookResult = mysqli_query($conn, $bookQuery);
        if (!$bookResult || mysqli_num_rows($bookResult) == 0) {
            logError("DB Error (book info query) or Book not found: " . mysqli_error($conn));
            $response['message'] = "Book with provided ID and ISBN not found or data mismatch. Please try scanning again.";
            outputResponse($response);
        }

        $bookData = mysqli_fetch_assoc($bookResult);
        if ($bookData['CopiesAvailable'] <= 0) {
            $response['message'] = "This book is currently out of stock.";
            outputResponse($response);
        }

        $borrowDate = date("Y-m-d H:i:s");
        $dueDate = calculateDueDate($borrowDate, $membershipType);

        // --- Record borrowing transaction ---
        // Using the fetched $primaryMemberPKID for the borrowing_history.ID column
        $insertQuery = "INSERT INTO borrowing_history (BookID, ID, BorrowDate, DueDate, Status, ISBN)
                        VALUES ('$bookId', '$primaryMemberPKID', '$borrowDate', '$dueDate', 'borrowed', '$isbn')";

        logError("Insert Borrow Query: " . $insertQuery); // Line 192 is likely here
        if (!mysqli_query($conn, $insertQuery)) {
            $error = mysqli_error($conn);
            logError("DB Error (insert into borrowing_history): " . $error);
            $response['message'] = "Error recording borrow transaction: " . $error;
            outputResponse($response);
        }

        // --- Update book copies available ---
        $updateQuery = "UPDATE books SET CopiesAvailable = CopiesAvailable - 1 WHERE ID = '$bookId' AND ISBN = '$isbn'";
        if (!mysqli_query($conn, $updateQuery)) {
            logError("DB Error (update book copies): " . mysqli_error($conn));
            $response['message'] .= " Warning: Failed to update book copies. Book might still show as available.";
        }

        $response['success'] = true;
        $response['message'] = "Book borrowed successfully! Due date: " . date('Y-m-d', strtotime($dueDate)) . ".";

        // --- Send email confirmation ---
        if ($memberData && isset($memberData['Email'])) {
            $emailBody = "Dear {$memberData['Name']} {$memberData['Surname']},<br><br>";
            $emailBody .= "You have successfully borrowed a book from UNESWA Library:<br><br>";
            $emailBody .= "<strong>Book Title:</strong> {$bookData['Title']}<br>";
            $emailBody .= "<strong>Author:</strong> {$bookData['Author']}<br>";
            $emailBody .= "<strong>ISBN:</strong> {$isbn}<br>";
            $emailBody .= "<strong>Borrow Date:</strong> " . date('Y-m-d H:i:s', strtotime($borrowDate)) . "<br>";
            $emailBody .= "<strong>Due Date:</strong> " . date('Y-m-d H:i:s', strtotime($dueDate)) . "<br><br>";
            $emailBody .= "Please return the book by the due date to avoid any penalties.<br><br>";
            $emailBody .= "Thank you,<br>UNESWA Library Team";

            if (sendEmailPHPMailer($memberData['Email'], "UNESWA Library: Book Borrowing Confirmation", $emailBody)) {
                $response['message'] .= " Email confirmation sent to {$memberData['Email']}.";
            } else {
                $response['message'] .= " Email confirmation failed. Please check your email settings or spam folder.";
            }
        } else {
            logError("Could not send email: Member data or email missing for primary PK ID: '$primaryMemberPKID' (Session Member ID: '$sessionMemberID').");
            $response['message'] .= " Could not send email confirmation (email address not found).";
        }

        outputResponse($response);

    } catch (Exception $e) {
        logError("Caught Exception in scan_handle.php: " . $e->getMessage() . " on line " . $e->getLine());
        $response['message'] = "A server error occurred during the borrowing process. Please contact support. (Error: " . $e->getMessage() . ")";
        outputResponse($response);
    }
} else {
    // If request method is not POST
    $response['message'] = "Invalid request method.";
    outputResponse($response);
}
?>