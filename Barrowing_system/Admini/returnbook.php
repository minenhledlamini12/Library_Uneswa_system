function returnBook($conn, $borrowingID) {
    $returnDate = date("Y-m-d H:i:s");  // Current timestamp
    $status = "returned";

    $sql = "UPDATE borrowing_history SET ReturnDate = ?, Status = ? WHERE BorrowingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $returnDate, $status, $borrowingID);

    if ($stmt->execute()) {
        echo "Book returned successfully!";
    } else {
        echo "Error returning book: " . $conn->error;
    }

    $stmt->close();
}
