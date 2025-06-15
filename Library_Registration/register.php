<?php
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Function to encrypt data using AES
function encryptData($data, $key) {
    $method = 'aes-256-cbc';
    $ivSize = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivSize);
    $encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
    // Prepend IV to ciphertext and base64 encode
    $encryptedData = base64_encode($iv . $encrypted);
    return $encryptedData;
}

require_once("connection.php");
require_once __DIR__ . '/vendor/autoload.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $Member_ID = trim($_POST['Member_ID']);
    $Name = trim($_POST['Name']);
    $Surname = trim($_POST['Surname']);
    $CourseDepartmentAffliation = trim($_POST['Course_Department_Affliation']);
    $Membership_type = $_POST['Membership_Type'];
    $Contact = trim($_POST['Contact']);
    $Email = trim($_POST['Email']);
    $Password = $_POST['Password'];
    $Joined_Date = $_POST['Joined_Date'];

    // Basic Validation
    $id_error = "";
    $email_error = "";

    if (empty($Member_ID)) {
        $id_error = "Member ID is required.";
    }
    if (empty($Email) || !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email address.";
    }

    // If no validation errors, proceed with registration
    if (empty($id_error) && empty($email_error)) {
        // Hash the password
        $Password_Hashed = password_hash($Password, PASSWORD_DEFAULT);

        // Insert member into database
        $sql = "INSERT INTO members (Member_ID, Name, Surname, `Course/Department/Affliation`, Membership_type, Contact, Email, Password, Joined_Date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $Member_ID, $Name, $Surname, $CourseDepartmentAffliation, $Membership_type, $Contact, $Email, $Password_Hashed, $Joined_Date);

        if ($stmt->execute()) {
            // --- QR Code Generation ---
            // Ensure qrcodes directory exists
            $qr_dir = __DIR__ . '/qrcodes/';
            if (!is_dir($qr_dir)) {
                mkdir($qr_dir, 0777, true);
            }

            // Use a safe filename for the QR code image
            $filename = 'email_' . md5(strtolower(trim($Email))) . '.png';
            $qr_file_server = $qr_dir . $filename; // Absolute path for saving on disk
            $qr_file_web = 'qrcodes/' . $filename; // Relative path for browser (HTML src)

            // Encrypt the email for QR code data
            $encryptionKey = "Pa@47781";
            $encryptedEmail = encryptData($Email, $encryptionKey);

            // Generate QR code with encrypted email
            try {
                $qrCode = QrCode::create($encryptedEmail);
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $result->saveToFile($qr_file_server);

                // Double-check file exists
                if (!file_exists($qr_file_server)) {
                    throw new Exception("QR code image was not created: $qr_file_server");
                }

                // Store QR code web path in the database
                $update_sql = "UPDATE members SET Qr_code = ? WHERE Member_ID = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ss", $qr_file_web, $Member_ID);

                if ($update_stmt->execute()) {
                    // --- Success HTML Output ---
                    ?>
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Registration Successful</title>
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
                        <script src="https://cdn.tailwindcss.com"></script>
                    </head>
                    <body class="flex items-center justify-center min-h-screen bg-gray-100 text-gray-800">
                        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl mx-4">
                            <div class="flex items-center justify-center mb-6">
                                <img src="download.png" alt="Library Logo" class="h-12 mr-2">
                                <h1 class="text-2xl font-semibold text-blue-900 flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>Registration Successful!
                                </h1>
                            </div>
                            <div class="mb-4 text-green-700 text-sm text-center">
                                Member registered successfully. Download the QR code below.
                            </div>
                            <p class="text-gray-700 text-center mb-4">
                                <i class="fas fa-id-card mr-2"></i>Member Email: <?php echo htmlspecialchars($Email); ?>
                            </p>
                            <div class="qr-code-container text-center mb-6">
                                <p class="text-gray-700 mb-2">
                                    <i class="fas fa-qrcode mr-2"></i>QR Code for <?php echo htmlspecialchars($Name); ?>:
                                </p>
                                <img src="<?php echo htmlspecialchars($qr_file_web); ?>" alt="QR Code" class="mx-auto max-w-[200px] border border-gray-300 rounded-md">
                            </div>
                            <p class="text-gray-700 text-center mb-6">
                                Print the QR code on the member's card for library system access.
                            </p>
                            <div class="flex justify-center space-x-4">
                                <a href="<?php echo htmlspecialchars($qr_file_web); ?>" class="bg-blue-900 text-white py-3 px-6 rounded-md flex items-center" download="UNESWA_Library_QR_Code.png">
                                    <i class="fas fa-download mr-2"></i>Download QR Code
                                </a>
                                <a href="index.php" class="bg-blue-900 text-white py-3 px-6 rounded-md flex items-center">
                                    <i class="fas fa-arrow-left mr-2"></i>Back to Home
                                </a>
                            </div>
                        </div>
                    </body>
                    </html>
                    <?php
                    exit();
                } else {
                    echo "<div>Error updating QR code path: " . htmlspecialchars($conn->error) . "</div>";
                }
            } catch (Exception $e) {
                echo "<div>Error generating QR code: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } else {
            echo "<div>Error inserting member: " . htmlspecialchars($conn->error) . "</div>";
        }
    } else {
        // Show validation errors
        echo "<div>$id_error $email_error</div>";
    }
}
?>
