
<?php
require_once("connection.php");
require_once __DIR__ . '/vendor/autoload.php';

// Function to decrypt QR code data
function decryptData($encryptedData, $key) {
    $encryptedData = base64_decode($encryptedData);
    $method = 'aes-256-cbc';
    $ivSize = openssl_cipher_iv_length($method);
    
    // Extract IV from the beginning of the encrypted data
    $iv = substr($encryptedData, 0, $ivSize);
    $ciphertext = substr($encryptedData, $ivSize);
    
    // Decrypt
    $decrypted = openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    
    return $decrypted;
}

// Set content type to JSON
header('Content-Type: application/json');

// Check if POST data exists
if (!isset($_POST['scanned_data'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No scan data received'
    ]);
    exit();
}

// Get the scanned data
$scannedData = $_POST['scanned_data'];

try {
    // Encryption key - must match the one used to generate QR code
    $encryptionKey = "Pa@47781";
    
    // Decrypt the ISBN from the scanned QR code
    $isbn = decryptData($scannedData, $encryptionKey);
    
    // Validate the decryption was successful
    if (!$isbn) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid QR code'
        ]);
        exit();
    }
    
    // Query the database for the book
    $stmt = $conn->prepare("SELECT * FROM books WHERE ISBN = ?");
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        
        // Check if the book is available
        if ($book['Status'] !== 'Available') {
            echo json_encode([
                'success' => false,
                'message' => 'This book is currently unavailable for borrowing'
            ]);
            exit();
        }
        
        // Return book details
        echo json_encode([
            'success' => true,
            'book' => $book
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Book not found in database'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error processing QR code: ' . $e->getMessage()
    ]);
}
