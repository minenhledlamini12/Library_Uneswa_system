<?php
session_start();

require_once("connection.php");

$esp32_ip = "192.168.27.69"; // ESP32 IP address
$encryptionKey = "Pa@47781";

function decryptData($encryptedData, $key) {
    $encryptedData = base64_decode($encryptedData);

    if (strlen($encryptedData) < 16) {
        throw new Exception("Encrypted data is too short to contain IV and ciphertext.");
    }

    $method = 'aes-256-cbc';
    $ivSize = openssl_cipher_iv_length($method);

    $iv = substr($encryptedData, 0, $ivSize);
    $encrypted = substr($encryptedData, $ivSize);
    $decrypted = openssl_decrypt($encrypted, $method, $key, OPENSSL_RAW_DATA, $iv);

    return $decrypted;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['qr_code'])) {
        $qrCodeData = $_POST['qr_code'];

        try {
            $decryptedData = decryptData($qrCodeData, $encryptionKey);

            // Verify user in the database
            $verify_sql = "SELECT * FROM members WHERE Member_ID = ?";
            $verify_stmt = $conn->prepare($verify_sql);
            $verify_stmt->bind_param("i", $decryptedData);

            if ($verify_stmt->execute()) {
                $result = $verify_stmt->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $_SESSION['Email'] = htmlspecialchars($row['Email']);
                    $_SESSION['Name'] = htmlspecialchars($row['Name']);
                    $_SESSION['authenticated'] = true;

                    $Member_ID = $row['Member_ID'];
                    $status = $row['Status'];

                    if ($status == 1) { // Member is inside the library
                        // Update member's status to 0 (leaving) and record checkout time
                        $checkoutTime = date("Y-m-d H:i:s");
                        $update_sql = "UPDATE members SET Status = 0, Checkout_Time = ? WHERE Member_ID = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("si", $checkoutTime, $Member_ID);

                        if ($update_stmt->execute()) {
                            // Send signal to ESP32 to open the door for exit
                            $esp32_url = "http://$esp32_ip/open_door";
                            $response = @file_get_contents($esp32_url);

                            if ($response !== false) {
                                echo "Goodbye " . htmlspecialchars($row['Name']) . "! You have checked out successfully. Door opened for exit.";
                            } else {
                                echo "Goodbye " . htmlspecialchars($row['Name']) . "! You have checked out successfully. However, failed to open the door for exit.";
                            }
                        } else {
                            echo "Error updating checkout status: " . htmlspecialchars($conn->error);
                        }
                        $update_stmt->close();
                    } else { // Member is outside the library
                        // Update member's status to 1 (entering) and record checkin time
                        $checkinTime = date("Y-m-d H:i:s");
                        $update_sql = "UPDATE members SET Status = 1, Checkin_Time = ? WHERE Member_ID = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("si", $checkinTime, $Member_ID);

                        if ($update_stmt->execute()) {
                            // Send signal to ESP32 to open the door for entry
                            $esp32_url = "http://$esp32_ip/open_door";
                            $response = @file_get_contents($esp32_url);

                            if ($response !== false) {
                                echo "Welcome " . htmlspecialchars($row['Name']) . "! You have checked in successfully. Door opened for entry.";
                            } else {
                                echo "Welcome " . htmlspecialchars($row['Name']) . "! You have checked in successfully. However, failed to open the door for entry.";
                            }
                        } else {
                            echo "Error updating checkin status: " . htmlspecialchars($conn->error);
                        }
                        $update_stmt->close();
                    }
                } else {
                    echo "Authentication failed. Invalid QR code.";
                }
            } else {
                echo "Database error: " . htmlspecialchars($conn->error);
            }
        } catch (Exception $e) {
            echo "Decryption error: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "Invalid request method.";
    }
} else {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Library Login</title>
        <script src="https://unpkg.com/html5-qrcode"></script>
    </head>
    <body>
        <h1>Library Login</h1>
        <div id="qr-reader" style="width:500px"></div>
        <div id="qr-reader-results"></div>
        <script>
            function onScanSuccess(decodedText, decodedResult) {
                console.log(`Code scanned = ${decodedText}`, decodedResult);
                // Post the QR code data to the server
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'qr_code=' + encodeURIComponent(decodedText),
                })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('qr-reader-results').innerHTML = data;
                    html5QrcodeScanner.clear(); // Stop scanning after successful scan
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('qr-reader-results').innerHTML = 'Error: ' + error;
                });
            }

            function onScanFailure(error) {
              // handle scan failure, usually better to ignore and keep scanning.
              // for example:
              console.warn(`Code scan error = ${error}`);
            }

            let html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader",
                { fps: 10, qrbox: {width: 250, height: 250} },
                /* verbose= */ false);
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        </script>
    </body>
    </html>
    <?php
}

$conn->close();
?>
