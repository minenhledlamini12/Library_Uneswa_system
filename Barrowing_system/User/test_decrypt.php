<?php
// Turn off error display for production
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Decryption Function
function decryptData($data, $key) {
    $method = 'aes-256-cbc';
    $ivSize = openssl_cipher_iv_length($method);
    
    echo "Input data: " . $data . "<br>";
    echo "Key: " . $key . "<br>";
    
    // Make sure the data is properly base64 encoded
    $decoded = base64_decode($data, true);
    if ($decoded === false) {
        echo "Failed to decode base64 data<br>";
        return false;
    }
    
    echo "Base64 decoded length: " . strlen($decoded) . "<br>";
    
    // Check if decoded data is long enough to contain IV
    if (strlen($decoded) <= $ivSize) {
        echo "Decoded data too short to contain IV<br>";
        return false;
    }
    
    $iv = substr($decoded, 0, $ivSize);
    $encrypted = substr($decoded, $ivSize);
    
    echo "IV length: " . strlen($iv) . "<br>";
    echo "Encrypted data length: " . strlen($encrypted) . "<br>";
    
    $decrypted = openssl_decrypt($encrypted, $method, $key, OPENSSL_RAW_DATA, $iv);
    if ($decrypted === false) {
        echo "Failed to decrypt data with openssl_decrypt<br>";
        echo "OpenSSL error: " . openssl_error_string() . "<br>";
        return false;
    }
    
    echo "Decrypted result: " . $decrypted . "<br>";
    return $decrypted;
}

// Test with a sample QR code
if (isset($_GET['qr'])) {
    $qrCode = $_GET['qr'];
    $encryptionKey = "Pa@47781"; // Secure key
    
    echo "<h2>Testing Decryption</h2>";
    $result = decryptData($qrCode, $encryptionKey);
    
    if ($result !== false) {
        echo "<h3>Success!</h3>";
        echo "Decrypted ISBN: " . $result;
    } else {
        echo "<h3>Decryption Failed</h3>";
    }
} else {
    echo "<h2>QR Code Decryption Test</h2>";
    echo "<form>";
    echo "Enter QR Code data: <input type='text' name='qr' size='50'><br>";
    echo "<input type='submit' value='Test Decryption'>";
    echo "</form>";
}
?>