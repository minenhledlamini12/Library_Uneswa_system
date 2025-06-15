<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("connection.php");

header('Content-Type: application/json'); // JSON response

// Decrypt function matching your encryption method
function decryptData($data, $key) {
    $method = 'aes-256-cbc';
    $data = base64_decode($data);
    if ($data === false) {
        return false;
    }
    $ivSize = openssl_cipher_iv_length($method);
    if (strlen($data) < $ivSize) {
        return false;
    }
    $iv = substr($data, 0, $ivSize);
    $encrypted = substr($data, $ivSize);
    $decrypted = openssl_decrypt($encrypted, $method, $key, OPENSSL_RAW_DATA, $iv);
    return $decrypted;
}

$response = [];

if (isset($_POST['qr_code'])) {
    $qrCodeData = $_POST['qr_code'];

    // Use the same key as in QR code generation
    $encryptionKey = "Pa@47781";

    // Decrypt the QR code data
    $decryptedEmail = decryptData($qrCodeData, $encryptionKey);

    // Validate decrypted email
    if ($decryptedEmail === false || !filter_var($decryptedEmail, FILTER_VALIDATE_EMAIL)) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid or corrupted QR code data.';
        echo json_encode($response);
        exit;
    }

    try {
        $verify_sql = "SELECT * FROM members WHERE Email = ?";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("s", $decryptedEmail);

        if ($verify_stmt->execute()) {
            $result = $verify_stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Set session variables for logged-in user
                $_SESSION['loggedin'] = true;
                $_SESSION['Member_ID'] = htmlspecialchars($row['Member_ID']);
                $_SESSION['Name'] = htmlspecialchars($row['Name']);
                $_SESSION['Surname'] = htmlspecialchars($row['Surname']);
                $_SESSION['Membership_type'] = htmlspecialchars($row['Membership_type']);
                $_SESSION['CourseDepartmentAffliation'] = htmlspecialchars($row['Course/Department/Affliation'] ?? '');
                $_SESSION['Contact'] = htmlspecialchars($row['Contact']);
                $_SESSION['Email'] = htmlspecialchars($row['Email']);

                $response['status'] = 'success';
                $response['message'] = 'Authentication successful!';
                $response['data'] = [
                    'name' => $_SESSION['Name'] . " " . $_SESSION['Surname'],
                    'course' => $_SESSION['CourseDepartmentAffliation'] ?: 'N/A',
                    'membership_type' => $_SESSION['Membership_type'],
                    'contact' => $_SESSION['Contact'],
                    'email' => $_SESSION['Email'],
                ];
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Authentication failed. Invalid QR code.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Database error: ' . htmlspecialchars($conn->error);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Error: ' . htmlspecialchars($e->getMessage());
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'No QR code data received.';
}

echo json_encode($response);

$conn->close();
