<?php
session_start();

// Include PHPMailer autoloader
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}

if (isset($_GET['qr_code'])) {
    $qrCodeData = $_GET['qr_code'];

    // Basic sanitation (adjust as needed)
    $bookID = mysqli_real_escape_string($conn, $qrCodeData);  // Assuming QR code contains BookID
    $userID = $_SESSION['user']['id'];  // Get User ID from session
    $memberType = $_SESSION['user']['Membership_type'];
    $memberID = $_SESSION['user']['Member_ID'];

    // Get User Email
    $emailSql = "SELECT Email FROM Members WHERE Member_ID = '$memberID'";
    $emailResult = $conn->query($emailSql);
    if ($emailResult && $emailResult->num_rows > 0) {
        $emailRow = $emailResult->fetch_assoc();
        $userEmail = $emailRow['Email'];
    } else {
        $userEmail = null; // Handle if email not found
    }

    // Retrieve book details and due date
    $sql = "SELECT bh.BorrowID, bh.DueDate FROM BorrowingHistory bh WHERE bh.BookID = '$bookID' AND bh.UserID = '$userID' AND bh.Returned = FALSE";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $borrowID = $row['BorrowID'];  // Get BorrowID
        $dueDate = $row['DueDate'];
        $today = date("Y-m-d");
        $overdue = ($today > $dueDate);

        if ($overdue) {
            echo json_encode(['success' => false, 'message' => 'Book return date overdue. Please go to circulation for sorting this issue.']);
        } else {
            // Update BorrowingHistory
            $updateSql = "UPDATE BorrowingHistory SET ReturnDate = CURDATE(), Returned = TRUE WHERE BorrowID = '$borrowID'";
            if ($conn->query($updateSql) === TRUE) {

                // Increment borrowing limit based on membership type
                $newLimit = getBorrowingLimit($memberType) + 1;  //Add one to the barrowing limits
                $updateLimitSql = "UPDATE Members SET Borrow_Limit = $newLimit WHERE Member_ID = '$memberID'";

                if ($conn->query($updateLimitSql) === TRUE) {
                     // Send Email Notification
                    if ($userEmail) {
                        $bookTitleSql = "SELECT Title FROM Books WHERE BookID = '$bookID'";
                        $bookTitleResult = $conn->query($bookTitleSql);
                        if ($bookTitleResult && $bookTitleResult->num_rows > 0) {
                            $bookTitleRow = $bookTitleResult->fetch_assoc();
                            $bookTitle = $bookTitleRow['Title'];

                            $mail = new PHPMailer(true);  // Enable exceptions

                            try {
                                // Server settings
                                $mail->SMTPDebug = 0;  // Disable debugging
                                $mail->isSMTP();                                            // Send using SMTP
                                $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server to send through
                                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                                $mail->Username   = 'your_email@gmail.com';                     // SMTP username
                                $mail->Password   = 'your_password';                               // SMTP password
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                                $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

                                // Recipients
                                $mail->setFrom('your_email@gmail.com', 'Library System');
                                $mail->addAddress($userEmail);  // Add a recipient

                                // Content
                                $mail->isHTML(true);                                  // Set email format to HTML
                                $mail->Subject = 'Book Returned Successfully';
                                $mail->Body    = "You have successfully returned the book '$bookTitle'. Thank you for using our library.";
                                $mail->AltBody = "You have successfully returned the book '$bookTitle'. Thank you for using our library.";

                                $mail->send();
                                echo json_encode(['success' => true, 'message' => 'Book returned successfully. Email notification sent.']);
                            } catch (Exception $e) {
                                echo json_encode(['success' => true, 'message' => "Book returned successfully! But email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Book returned successfully! Email sending failed as Book title is missing.']);
                        }
                    } else {
                        echo json_encode(['success' => true, 'message' => 'Book returned successfully! User email not found.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Book returned, but failed to increment borrowing limit. ' . $conn->error]);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Book not found in borrowing history, or already returned.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }

    $conn->close();

    function getBorrowingLimit($membershipType) {
        switch (strtolower($membershipType)) {
            case 'student': return 6;
            case 'staff': return 10;
            case 'external member': return 4;
            default: return 4;
        }
    }
    ?>
