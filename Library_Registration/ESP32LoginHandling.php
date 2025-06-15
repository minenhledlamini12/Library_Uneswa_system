<?php
// Database credentials (REPLACE WITH YOUR ACTUAL CREDENTIALS)
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database_name";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $qrCodeData = $_POST['qr_code'];

    // Parse the QR code data (it's in query string format)
    parse_str($qrCodeData, $qrDataArray);

    $student_id = $qrDataArray['student_id'];
    // ... get other data from $qrDataArray

    // 1. Check if the user exists in the database
    $sql = "SELECT * FROM members WHERE Student_ID = ?"; // Modify as per your database
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // 2. Further verification (optional):
        // You can check if other details from the QR code (name, email, etc.)
        // match the data in the database for added security.

         // If verified, grant access (trigger door lock - this part depends on how your ESP32 is set up)
         // ... (Code to trigger the ESP32 to open the door lock) ...
         echo "Access Granted";

    } else {
        echo "Access Denied";
    }

    $stmt->close();
}

$conn->close();
?>