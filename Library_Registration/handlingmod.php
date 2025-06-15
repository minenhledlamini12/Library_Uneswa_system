<?php
require_once 'phpqrcode/qrlib.php';

// Database connection details (REPLACE WITH YOUR CREDENTIALS)
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "qr_code_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {  // Check if it's a POST request
    // ... (Your existing code to retrieve form data) ...

    // SQL (Using Prepared Statements - Important for security!)
    $sql = "INSERT INTO members (Student_ID, Student_name, Surname, `Course/Department/Affliation`, Membership_Type, Contact, Email, Password, Joined_Date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $Student_ID, $Student_name, $Surname, $CourseDepartmentAffliation, $Membership_Type, $Contact, $Email, $Password, $Joined_Date); // Corrected bind_param

    if ($stmt->execute()) {
        $member_id = $conn->insert_id;

        // Generate QR code
        $qr_data = $member_id;
        $qr_file = 'qrcodes/' . $member_id . '.png';
        QRcode::png($qr_data, $qr_file, 'L', 4, 2);

        // Update database with QR code path
        $update_sql = "UPDATE members SET Qr_code = ? WHERE Student_Counter = ?"; // Assuming Student_Counter is your primary key
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $qr_file, $member_id);

        if ($update_stmt->execute()) {
          echo "<div style='text-align:center; color: white; background-color: green; padding: 10px;'>";
          echo "Registration successful! Your QR code has been generated.";
          echo "<img src='" . $qr_file . "' alt='QR Code'>";
          echo "</div>";

        } else {
            echo "Error updating QR code path: " . $conn->error;
        }
        $update_stmt->close();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close(); // Close the statement
}

$conn->close();
?>