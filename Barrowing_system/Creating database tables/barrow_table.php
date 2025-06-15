<?php
require_once("connection.php");

$sql = "CREATE TABLE borrowing_history (
    BorrowingID INT AUTO_INCREMENT PRIMARY KEY,
    BookID INT,              /* Foreign key referencing the books table */
    MemberID INT,            /* Foreign key referencing the members table */
    BorrowDate DATETIME,      /* Date and time the book was borrowed */
    ReturnDate DATETIME,      /* Date and time the book was returned (NULL if not returned) */
    DueDate DATETIME,         /* Date the book is due for return */
    Status VARCHAR(50),      /* e.g., 'borrowed', 'returned', 'overdue' */
    FOREIGN KEY (BookID) REFERENCES books(ID),
    FOREIGN KEY (MemberID) REFERENCES members(ID) /* Assuming your members table has Member_ID */
)";

if ($conn->query($sql) === TRUE) {
    echo "Table borrowing_history created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
