


<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;








// Consistent encryption function with first system
function encryptData($data, $key) {
    $method = 'aes-256-cbc';
    $ivSize = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivSize);
    $encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}

require_once("connection.php");
require_once __DIR__ . '/vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $Member_ID = trim($_POST['ID']);
    $Name = trim($_POST['Name']);
    $Surname = trim($_POST['Surname']);
    $CourseDepartmentAffliation = trim($_POST['Course/Department/Affliation']);
    $Membership_type = $_POST['Membership_Type'];
    $Contact = trim($_POST['Contact']);
    $Email = trim($_POST['Email']);
    $Password = $_POST['Password'];
    $Joined_Date = $_POST['Joined_Date'];

    // Validation
    $errors = [];
    if (empty($Member_ID)) $errors[] = "Member ID is required";
    if (empty($Email) || !filter_var($Email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address";

    if (empty($errors)) {
        // Hash password
        $Password_Hashed = password_hash($Password, PASSWORD_DEFAULT);

        // Insert member data
        $sql = "INSERT INTO members (Member_ID, Name, Surname, `Course/Department/Affliation`, 
                Membership_type, Contact, Email, Password, Joined_Date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $Member_ID, $Name, $Surname, $CourseDepartmentAffliation, 
                         $Membership_type, $Contact, $Email, $Password_Hashed, $Joined_Date);

        if ($stmt->execute()) {
            // QR Code Generation
            $encryptionKey = "Pa@47781"; // Match first system's key
            $encryptedEmail = encryptData($Email, $encryptionKey);
            
            // Create qrcodes directory if missing
            $qr_dir = __DIR__ . '/qrcodes/';
            if (!is_dir($qr_dir)) {
                mkdir($qr_dir, 0777, true);
            }

            // Generate filename using hashed email
            $filename = 'email_' . md5(strtolower(trim($Email))) . '.png';
            $qr_file_server = $qr_dir . $filename;
            $qr_file_web = 'qrcodes/' . $filename;

            try {
                // Generate QR with encrypted data
                $qrCode = QrCode::create($encryptedEmail);
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $result->saveToFile($qr_file_server);

                // Verify file creation
                if (!file_exists($qr_file_server)) {
                    throw new Exception("QR code file creation failed");
                }

                // Update database with QR path
                $update_sql = "UPDATE members SET Qr_code = ? WHERE Member_ID = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ss", $qr_file_web, $Member_ID);

                if ($update_stmt->execute()) {
                    // Success response with modern styling
                    echo '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Registration Success</title>
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                        <script src="https://cdn.tailwindcss.com"></script>
                    </head>
                    <body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
                        <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full p-8">
                            <div class="text-center mb-6">
                                <h1 class="text-3xl font-bold text-green-600 mb-2">
                                    <i class="fas fa-check-circle mr-2"></i>Registration Complete!
                                </h1>
                                <p class="text-gray-600">Member details stored successfully</p>
                            </div>

                            <div class="space-y-4 mb-8">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="font-semibold"><i class="fas fa-envelope mr-2"></i>Email:</p>
                                    <p class="text-gray-700">'.htmlspecialchars($Email).'</p>
                                </div>
                                
                                <div class="border-t pt-4 text-center">
                                    <p class="font-medium mb-4"><i class="fas fa-qrcode mr-2"></i>Membership QR Code</p>
                                    <img src="'.$qr_file_web.'" alt="QR Code" class="mx-auto w-48 h-48 border rounded-lg">
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row justify-center gap-4">
                                <a href="'.$qr_file_web.'" 
                                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg flex items-center justify-center"
                                   download="LibraryQR_'.$Member_ID.'.png">
                                    <i class="fas fa-download mr-2"></i>Download QR
                                </a>
                                
                                <a href="homepage.php" 
                                   class="bg-gray-800 hover:bg-gray-900 text-white px-6 py-3 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-home mr-2"></i>Return Home
                                </a>
                            </div>
                        </div>
                    </body>
                    </html>';
                } else {
                    throw new Exception("QR path update failed: ".$conn->error);
                }
            } catch (Exception $e) {
                die("<div class='p-4 bg-red-100 text-red-700 rounded'>Error: ".htmlspecialchars($e->getMessage())."</div>");
            }
        } else {
            die("<div class='p-4 bg-red-100 text-red-700 rounded'>Registration failed: ".htmlspecialchars($conn->error)."</div>");
        }
    } else {
        // Display validation errors
        echo "<div class='p-4 bg-red-100 text-red-700 rounded'><ul>";
        foreach ($errors as $error) {
            echo "<li>".htmlspecialchars($error)."</li>";
        }
        echo "</ul></div>";
    }
}

$conn->close();
?>
