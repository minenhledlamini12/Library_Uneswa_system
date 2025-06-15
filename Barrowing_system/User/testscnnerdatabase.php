<?php
session_start(); // Start the session
require_once("connection.php");
require_once __DIR__ . '/vendor/autoload.php'; // For PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Decryption Function
function decryptData($encryptedData, $key) {
    $method = 'aes-256-cbc';
    $decoded = base64_decode($encryptedData);
    $ivSize = openssl_cipher_iv_length($method);
    $iv = substr($decoded, 0, $ivSize);
    $encrypted = substr($decoded, $ivSize);
    
    $decrypted = openssl_decrypt($encrypted, $method, $key, OPENSSL_RAW_DATA, $iv);
    
    return $decrypted;
}

// Retrieve user information from session
$userId = $_SESSION['user_session'];

// Fetch user details from the database
$sqlUser = "SELECT * FROM members WHERE Member_ID = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("s", $userId);
$stmtUser->execute();
$userResult = $stmtUser->get_result();
$userData = $userResult->fetch_assoc();

if (!$userData) {
    die("Invalid user session.");
}

// Membership type and borrowing limits
$membershipType = $userData['Membership_type'];
$borrowingLimit = ($membershipType === "Student") ? 6 : (($membershipType === "Staff") ? 10 : 4);
$borrowingLimit = ($membershipType === "External") ? 4 : $borrowingLimit;
$dueDateInterval = ($membershipType === "Student" || $membershipType === "External") ? "+14 days" : "+16 weeks";

// Handle book borrowing process
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve scanned book ID (from QR code or input)
    $scannedData = trim($_POST['scanned_data']);

     // Decrypt the scanned data
     $encryptionKey = "Pa@47781"; // Secure key
     $bookId = decryptData($scannedData, $encryptionKey);

    // Check if the book exists and is available
    $sqlBook = "SELECT * FROM books WHERE ISBN = ? AND Status = 'Available' AND CopiesAvailable > 0";
    $stmtBook = $conn->prepare($sqlBook);
    $stmtBook->bind_param("s", $bookId);
    $stmtBook->execute();
    $bookResult = $stmtBook->get_result();
    $bookData = $bookResult->fetch_assoc();

    if (!$bookData) {
        echo "<p style='color:red;'>Error: Book not found or unavailable or no copies available.</p>";
        exit();
    }

    // Check user's borrowing history
    $sqlBorrowedBooks = "SELECT COUNT(*) AS borrowed_count FROM borrowing_history WHERE Member_ID = ? AND ReturnDate IS NULL";
    $stmtBorrowedBooks = $conn->prepare($sqlBorrowedBooks);
    $stmtBorrowedBooks->bind_param("s", $userId);
    $stmtBorrowedBooks->execute();
    $borrowedBooksResult = $stmtBorrowedBooks->get_result();
    $borrowedBooksCount = $borrowedBooksResult->fetch_assoc()['borrowed_count'];

    if ($borrowedBooksCount >= $borrowingLimit) {
        echo "<p style='color:red;'>Error: You have reached your borrowing limit.</p>";
        exit();
    }

    // Calculate due date
    $dueDate = date("Y-m-d", strtotime($dueDateInterval));

    // Insert into borrowing history table
    $sqlBorrowBook = "INSERT INTO borrowing_history (BookID, Member_ID, BorrowDate, DueDate, ISBN, Status) VALUES (?, ?, NOW(), ?, ?, 'Borrowed')";
    $stmtBorrowBook = $conn->prepare($sqlBorrowBook);
    $stmtBorrowBook->bind_param("sssss", $bookData['ID'], $userId, $dueDate, $bookId);

    if ($stmtBorrowBook->execute()) {
        // Update book status to 'Borrowed' and decrease available copies
        $sqlUpdateBookStatus = "UPDATE books SET Status = 'Borrowed', CopiesAvailable = CopiesAvailable - 1 WHERE ISBN = ?";
        $stmtUpdateBookStatus = $conn->prepare($sqlUpdateBookStatus);
        $stmtUpdateBookStatus->bind_param("s", $bookId);
        $stmtUpdateBookStatus->execute();

        // Send email notification
        sendEmailNotification($userData['Email'], $bookData['Title'], $dueDate);

        echo "<p style='color:green;'>Success: Book borrowed successfully. Check your email for details.</p>";
    } else {
        echo "<p style='color:red;'>Error: Unable to borrow the book.</p>";
    }
}

// Function to send email notification
function sendEmailNotification($recipientEmail, $bookTitle, $dueDate) {
    try {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dminenhle477@gmail.com'; // Replace with your email
        $mail->Password   = 'hbzl wbju nedt lfdc';   // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('dminenhle477@gmail.com', 'Library Management');
        $mail->addAddress($recipientEmail);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Library Book Borrowing Confirmation';
        $mail->Body    = "<p>Dear User,</p>
                          <p>You have successfully borrowed the book titled <strong>$bookTitle</strong>.</p>
                          <p>Your due date for returning the book is <strong>$dueDate</strong>.</p>
                          <p>Please ensure you return the book on time to avoid penalties.</p>
                          <p>Thank you for using our library services!</p>";

        // Send email
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
         return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Book</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
    <style>
        .scan-area {
            width: 320px;
            margin: 0 auto;
            position: relative;
        }
        .scan-area video {
            width: 100%;
        }
        .scan-area #scan-result {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
<h1>Scan and Borrow Book</h1>
<div class="scan-area">
    <video id="preview"></video>
    <div id="scan-result"></div>
</div>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <input type="hidden" id="scanned_data" name="scanned_data" required>
    <label for="terms">
        <input type="checkbox" id="terms" name="terms" required> I agree to the library terms and conditions
    </label>
    <input type="submit" value="Borrow Book" disabled id="borrowBtn">
</form>
<script>
    let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
    scanner.addListener('scan', function (content) {
        console.log('Scanned ISBN:', content);
        document.getElementById('scan-result').innerText = 'Result: ' + content;
        document.getElementById('scanned_data').value = content;
    });
    Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
            scanner.start(cameras[0]);
        } else {
            console.error('No cameras found.');
        }
    }).catch(function (e) {
        console.error(e);
    });

    // Enable Borrow Button only when terms are accepted
    document.getElementById('terms').addEventListener('change', function() {
        document.getElementById('borrowBtn').disabled = !this.checked;
    });
</script>
</body>
</html>
