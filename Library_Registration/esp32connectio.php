<?php
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "library_db"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
}

// Decode image from POST request
$imageData = $_POST['image'];
$imageData = base64_decode($imageData);

// Save image temporarily for QR code scanning
$tempImagePath = 'temp.jpg';
file_put_contents($tempImagePath, $imageData);

// Use a command line tool like 'zbarimg' to read QR codes
$output = shell_exec("zbarimg --quiet --raw $tempImagePath");

// Check if output is not empty and query database
if (!empty($output)) {
   $outputTrimmed = trim($output);
   $sql = "SELECT * FROM users WHERE qr_code_data='$outputTrimmed'";
   $result = $conn->query($sql);

   if ($result->num_rows > 0) {
       echo "access_granted";
   } else {
       echo "access_denied";
   }
} else {
   echo "access_denied";
}

$conn->close();

// Optionally delete the temporary image file
unlink($tempImagePath);
?>
