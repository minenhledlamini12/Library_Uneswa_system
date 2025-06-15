<?php
// Ensure session is started at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("connection.php"); // Your DB connection

// Include PHPMailer classes
// Adjust these paths if your vendor directory is located differently relative to return_handle.php
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\Exception.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\php_program\Barrowing_system\User\vendor\phpmailer\phpmailer\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set header for JSON response
header('Content-Type: application/json');

// Decrypt function as in your code
function decryptData($data, $key) {
    $method = 'aes-256-cbc';
    $ivSize = openssl_cipher_iv_length($method);

    $data = base64_decode($data);
    if ($data === false) return false;

    $iv = substr($data, 0, $ivSize);
    $encrypted = substr($data, $ivSize);

    $decrypted = openssl_decrypt($encrypted, $method, $key, OPENSSL_RAW_DATA, $iv);
    return $decrypted;
}

$response = ['success' => false, 'message' => 'An unknown error occurred.']; // Initialize response array

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['qr_code'])) {

    // 1. Get the Member_ID (like '202020211') from the session
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['Member_ID'])) {
        $response['message'] = 'User not logged in or session expired. Please log in again.';
        echo json_encode($response);
        exit;
    }
    $sessionMemberIdentifier = $_SESSION['Member_ID']; // This is the 'Member_ID' column value from the session

    // --- NEW STEP: Retrieve the actual primary key 'ID' and Email from the members table ---
    $actualMemberPK_ID = null; // This will hold the 'ID' column value from the members table (e.g., 172, 171)
    $userEmail = '';
    $memberName = $_SESSION['Name'] ?? 'Valued Member';    // Use session for initial name
    $memberSurname = $_SESSION['Surname'] ?? ''; // Use session for initial surname

    $memberDetailsSql = "SELECT ID, Email, Name, Surname FROM members WHERE Member_ID = ?";
    $memberDetailsStmt = $conn->prepare($memberDetailsSql);

    if ($memberDetailsStmt) {
        $memberDetailsStmt->bind_param("s", $sessionMemberIdentifier); // 's' because Member_ID looks like a string
        $memberDetailsStmt->execute();
        $memberDetailsResult = $memberDetailsStmt->get_result();
        if ($memberDetailsResult && $memberDetailsResult->num_rows > 0) {
            $memberRow = $memberDetailsResult->fetch_assoc();
            $actualMemberPK_ID = $memberRow['ID']; // This is the actual primary key 'ID' we need for borrowing_history
            $userEmail = $memberRow['Email'];
            // Optionally update session names if they were less accurate or for robustness
            $memberName = $memberRow['Name'];
            $memberSurname = $memberRow['Surname'];
        }
        $memberDetailsStmt->close();
    }

    if ($actualMemberPK_ID === null) {
        $response['message'] = 'Could not find complete member details for the logged-in user. Please contact support.';
        echo json_encode($response);
        exit;
    }
    // --- END NEW STEP ---


    // 2. Decrypt QR code to get ISBN
    $encryptionKey = "Pa@47781"; // Use your actual key
    $qrCodeData = $_POST['qr_code'];
    $isbn = decryptData($qrCodeData, $encryptionKey);

    if ($isbn === false || empty($isbn)) {
        $response['message'] = 'Failed to decrypt QR code or QR data is empty.';
        echo json_encode($response);
        exit;
    }

    // 3. Find BookID and Book Title from ISBN using prepared statement
    $bookID = null;
    $bookTitle = '';
    $bookSql = "SELECT ID, Title FROM books WHERE ISBN = ?";
    $bookStmt = $conn->prepare($bookSql);
    if ($bookStmt) {
        $bookStmt->bind_param("s", $isbn);
        $bookStmt->execute();
        $bookResult = $bookStmt->get_result();
        if ($bookResult && $bookResult->num_rows > 0) {
            $bookData = $bookResult->fetch_assoc();
            $bookID = $bookData['ID'];
            $bookTitle = $bookData['Title'];
        }
        $bookStmt->close();
    }

    if ($bookID === null) {
        $response['message'] = 'Book not found for the given ISBN.';
        echo json_encode($response);
        exit;
    }

    // 4. Update borrowing_history for this book and user using the ACTUAL primary key 'ID'
    $now = date("Y-m-d H:i:s");
    $updateSql = "
        UPDATE borrowing_history
        SET Status = 'returned',
            ReturnDate = ?,
            Returned = 1
        WHERE BookID = ?
          AND ID = ? -- Use the actual primary key 'ID' of the member from the members table
          AND Status = 'borrowed'
          AND Returned = 0
        LIMIT 1
    ";

    $updateStmt = $conn->prepare($updateSql);
    if ($updateStmt) {
        // 'sii' -> string for $now, integer for $bookID, integer for $actualMemberPK_ID
        $updateStmt->bind_param("sii", $now, $bookID, $actualMemberPK_ID);

        if ($updateStmt->execute()) {
            if ($updateStmt->affected_rows > 0) {
                // Optional: Update CopiesAvailable in books table using prepared statement
                $updateCopiesSql = "UPDATE books SET CopiesAvailable = CopiesAvailable + 1 WHERE ID = ?";
                $updateCopiesStmt = $conn->prepare($updateCopiesSql);
                if ($updateCopiesStmt) {
                    $updateCopiesStmt->bind_param("i", $bookID);
                    $updateCopiesStmt->execute();
                    $updateCopiesStmt->close();
                }

                $response['success'] = true;
                $response['message'] = 'Book "' . htmlspecialchars($bookTitle) . '" successfully returned by ' . htmlspecialchars($memberName . ' ' . $memberSurname) . '!';
                $response['book_id'] = $bookID;
                $response['return_date'] = $now;
                $response['returned_by_member_id'] = $actualMemberPK_ID; // Return the actual PK ID

                // --- Send Email Confirmation ---
                if (!empty($userEmail)) {
                    $mail = new PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'dminenhle477@gmail.com'; // Your sender email
                        $mail->Password   = 'hbzl wbju nedt lfdc';   // Your App Password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port       = 465;

                        // Recipients
                        $mail->setFrom('dminenhle477@gmail.com', 'Library System'); // Sender's email and name
                        $mail->addAddress($userEmail, $memberName . ' ' . $memberSurname); // Recipient's email and name

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Book Return Confirmation - ' . htmlspecialchars($bookTitle);
                        $mail->Body    = '
                            <p>Dear ' . htmlspecialchars($memberName . ' ' . $memberSurname) . ',</p>
                            <p>This is to confirm that you have successfully returned the following book to the library:</p>
                            <p><strong>Book Title:</strong> ' . htmlspecialchars($bookTitle) . '</p>
                            <p><strong>ISBN:</strong> ' . htmlspecialchars($isbn) . '</p>
                            <p><strong>Return Date:</strong> ' . htmlspecialchars($now) . '</p>
                            <p>Thank you for using our library services.</p>
                            <p>Sincerely,</p>
                            <p>The Library Team</p>
                        ';
                        $mail->AltBody = 'Dear ' . htmlspecialchars($memberName . ' ' . $memberSurname) . ',
                            This is to confirm that you have successfully returned the following book to the library:
                            Book Title: ' . htmlspecialchars($bookTitle) . '
                            ISBN: ' . htmlspecialchars($isbn) . '
                            Return Date: ' . htmlspecialchars($now) . '
                            Thank you for using our library services.
                            Sincerely,
                            The Library Team';

                        $mail->send();
                        $response['email_status'] = 'sent';
                        $response['email_message'] = 'Confirmation email sent successfully.';
                    } catch (Exception $e) {
                        $response['email_status'] = 'failed';
                        $response['email_message'] = "Confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                        // Log the error for debugging, but don't prevent the return success response
                        error_log("PHPMailer Error: " . $e->getMessage());
                    }
                } else {
                    $response['email_status'] = 'skipped';
                    $response['email_message'] = 'Email not sent: User email address not found.';
                }
                // --- End Send Email Confirmation ---

            } else {
                $response['message'] = 'No matching borrowed record found for this book and user, or it was already returned.';
                $response['book_id'] = $bookID;
            }
        } else {
            $response['message'] = 'Failed to update return status: ' . $updateStmt->error;
            $response['book_id'] = $bookID;
        }
        $updateStmt->close();
    } else {
        $response['message'] = 'Failed to prepare update statement: ' . $conn->error;
        $response['book_id'] = $bookID;
    }

    $conn->close();
    echo json_encode($response);
    exit;

} else {
    // If not a POST request or qr_code is missing
    $response['message'] = 'Invalid request method or missing QR code data.';
    echo json_encode($response);
    exit;
}
?>