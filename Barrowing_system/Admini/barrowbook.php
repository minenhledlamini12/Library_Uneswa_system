function borrowBook($conn, $bookID, $memberID, $dueDate) {
    $borrowDate = date("Y-m-d H:i:s"); // Current timestamp
    $status = "borrowed";

    $sql = "INSERT INTO borrowing_history (BookID, MemberID, BorrowDate, DueDate, Status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $bookID, $memberID, $borrowDate, $dueDate, $status);

    if ($stmt->execute()) {
        echo "Book borrowed successfully!";
    } else {
        echo "Error borrowing book: " . $conn->error;
    }

    $stmt->close();
}
