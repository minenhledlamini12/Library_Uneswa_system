<?php
session_start();
require_once("connection.php");

// PHPMailer includes
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in via QR session
if (!isset($_SESSION['user'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

// Check if POST data exists
if (!isset($_POST['book_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No book data received'
    ]);
    exit();
}

// Get member ID from session
$member_id = $_SESSION['user']['member_id'];
$book_id = $_POST['book_id'];

// Start a transaction
$conn->begin_transaction();

try {
    // Get member details and membership type
    $stmt = $conn->prepare("SELECT m.*, mt.type_name, mt.max_books 
                           FROM members m 
                           JOIN membership_types mt ON m.membership_type_id = mt.type_id 
                           WHERE m.member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Member not found');
    }
    
    $member = $result->fetch_assoc();
    $membership_type = $member['type_name'];
    $max_books = $member['max_books'];
    
    // Check how many books the member currently has borrowed
    $stmt = $conn->prepare("SELECT COUNT(*) as current_borrows 
                           FROM borrowing_history 
                           WHERE member_id = ? AND return_date IS NULL");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $current_borrows = $row['current_borrows'];
    
    // Check if member has reached the borrowing limit
    if ($current_borrows >= $max_books) {
        throw new Exception("You have reached your borrowing limit of {$max_books} books");
    }
    
    // Get book details
    $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Book not found');
    }
    
    $book = $result->fetch_assoc();
    
    // Check if book is available
    if ($book['Status'] !== 'Available') {
        throw new Exception('Book is not available for borrowing');
    }
    
    // Calculate due date based on membership type
    $borrow_date = date('Y-m-d');
    $due_date = null;
    
    switch (strtolower($membership_type)) {
        case 'student':
            // 14 days for students
            $due_date = date('Y-m-d', strtotime($borrow_date . ' + 14 days'));
            break;
        case 'staff':
            // 16 weeks (112 days) for staff
            $due_date = date('Y-m-d', strtotime($borrow_date . ' + 112 days'));
            break;
        case 'external':
            // 14 days for external members
            $due_date = date('Y-m-d', strtotime($borrow_date . ' + 14 days'));
            break;
        default:
            // Default to 7 days if membership type is unknown
            $due_date = date('Y-m-d', strtotime($borrow_date . ' + 7 days'));
    }
    
    // Insert into borrowing_history table
    $stmt = $conn->prepare("INSERT INTO borrowing_history 
                          (book_id, member_id, borrow_date, due_date) 
                          VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $book_id, $member_id, $borrow_date, $due_date);
    $stmt->execute();
    
    // Update book status
    $status = 'Borrowed';
    $stmt = $conn->prepare("UPDATE books SET Status = ?, CopiesAvailable = CopiesAvailable - 1 WHERE book_id = ?");
    $stmt->bind_param("si", $status, $book_id);
    $stmt->execute();
    
    // Commit the transaction
    $conn->commit();
    
    // Send email notification
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dminenhle477@gmail.com';
        $mail->Password = 'hbzl wbju nedt lfdc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Recipients
        $mail->setFrom('dminenhle477@gmail.com', 'Library System');
        $mail->addAddress($member['email'], $member['first_name'] . ' ' . $member['last_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Book Borrowed Successfully';
        
        $emailBody = "
        <h2>Book Borrowed Successfully</h2>
        <p>Dear {$member['first_name']} {$member['last_name']},</p>
        <p>You have successfully borrowed the following book from our library:</p>
        <table border='1' cellpadding='5' style='border-collapse: collapse;'>
            <tr>
                <th>Title</th>
                <td>{$book['Title']}</td>
            </tr>
            <tr>
                <th>Author</th>
                <td>{$book['Author']}</td>
            </tr>
            <tr>
                <th>ISBN</th>
                <td>{$book['ISBN']}</td>
            </tr>
            <tr>
                <th>Borrow Date</th>
                <td>{$borrow_date}</td>
            </tr>
            <tr>
                <th>Due Date</th>
                <td>{$due_date}</td>
            </tr>
        </table>
        <p>Please ensure to return the book by the due date to avoid late fees.</p>
        <p>Thank you for using our library services!</p>
        <p>Best regards,<br>Library Management</p>
        ";
        
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody);
        
        $mail->send();
        $email_sent = true;
    } catch (Exception $e) {
        $email_sent = false;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Book borrowed successfully! ' . ($email_sent ? 'Check your email for details.' : 'Email notification failed.'),
        'due_date' => $due_date
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
