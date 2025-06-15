<?php
require_once "connection.php";
session_start();

ob_start();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['isbn'], $_POST['book_id'])) {
    $isbn = sanitizeInput($_POST['isbn']);
    $bookId = sanitizeInput($_POST['book_id']);

    // Get the latest borrowing record for this book
    $findSql = "SELECT * FROM borrowing_history WHERE BookID='$bookId' ORDER BY BorrowingID DESC LIMIT 1";
    $findResult = mysqli_query($conn, $findSql);

    if ($findResult && mysqli_num_rows($findResult) > 0) {
        $row = mysqli_fetch_assoc($findResult);
        $borrowingId = $row['BorrowingID'];
        $status = $row['Status'];

        if ($status === 'returned') {
            // Trigger ESP32 to open the bin (HTTP POST)
            $esp32_ip = "192.168.215.149"; // Your ESP32's IP address
            $esp32_url = "http://$esp32_ip/open_bin";
            $postData = http_build_query(['book_id' => $bookId]);
            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-type: application/x-www-form-urlencoded",
                    'content' => $postData,
                    'timeout' => 5 // seconds
                ]
            ];
            $context  = stream_context_create($opts);
            $result = @file_get_contents($esp32_url, false, $context);

            if ($result === FALSE) {
                $response = ['success' => false, 'message' => 'Could not communicate with return bin.'];
            } else {
                $response = ['success' => true, 'message' => 'Bin opening. Please drop your book in the bin.'];
            }
        } elseif ($status === 'borrowed') {
            $response = ['success' => false, 'message' => 'Book is still borrowed. Please return it at the kiosk first.'];
        } elseif ($status === 'successful') {
            $response = ['success' => false, 'message' => 'Book return process is already complete.'];
        } else {
            $response = ['success' => false, 'message' => 'Book is not in a valid state for return.'];
        }
    } else {
        $response = ['success' => false, 'message' => 'No borrowing record found for this book.'];
    }
} else {
    $response = ['success' => false, 'message' => 'Invalid request.'];
}

ob_clean();
echo json_encode($response);
ob_end_flush();
?>
