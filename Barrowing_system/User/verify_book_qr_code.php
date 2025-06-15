<?php
require_once("connection.php");

// Encryption Function
function decryptData($data, $key) {
    $method = 'aes-256-cbc';
    $ivSize = openssl_cipher_iv_length($method);
    $decoded = base64_decode($data);
    if ($decoded === false) {
        return false;
    }
    $iv = substr($decoded, 0, $ivSize);
    $encrypted = substr($decoded, $ivSize);
    $decrypted = openssl_decrypt($encrypted, $method, $key, OPENSSL_RAW_DATA, $iv);
    return $decrypted;
}

// Function to sanitize user input
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Start output buffering
ob_start();

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['qr_code'])) {
    $qrCode = sanitizeInput($_POST['qr_code']);
    $encryptionKey = "Pa@47781";

    $isbn = decryptData($qrCode, $encryptionKey);

    if ($isbn === false) {
        $response = ['status' => 'error', 'message' => 'QR code decryption failed.'];
    } else {
        $query = "SELECT * FROM books WHERE ISBN = '$isbn'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $book = mysqli_fetch_assoc($result);
            if ($book['CopiesAvailable'] > 0) {
                $response = ['status' => 'success', 'message' => 'Book found', 'book' => $book];
            } else {
                $response = ['status' => 'error', 'message' => 'No copies available to borrow'];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Book not found'];
        }
    }
}

// Clear any unwanted output and send JSON
ob_clean();
echo json_encode($response);
ob_end_flush();
?>