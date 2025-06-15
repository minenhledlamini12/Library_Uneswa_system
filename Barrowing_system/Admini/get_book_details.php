<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("connection.php");

header('Content-Type: application/json');

$response = [];

if (isset($_POST['bookISBN'])) {
    $bookISBN = trim($_POST['bookISBN']);

    try {
        $sql = "SELECT Title, Author FROM books WHERE ISBN = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $bookISBN);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response['status'] = 'success';
            $response['book'] = $row;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Book not found.';
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'ISBN not provided.';
}

echo json_encode($response);
$conn->close();
?>
