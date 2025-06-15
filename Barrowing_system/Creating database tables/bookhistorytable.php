<?php
require_once("connection.php");

$sql = "CREATE TABLE book_history (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    BookID INT, /* Foreign key referencing the books table */
    ISBN VARCHAR(255),
    Title VARCHAR(255),
    Author VARCHAR(255),
    PublicationYear INT,
    Publisher VARCHAR(255),
    Format VARCHAR(255),
    Language VARCHAR(255),
    Pages INT,
    Genre VARCHAR(255),
    CopiesAvailable INT,
    Status VARCHAR(255),
    CallNumber VARCHAR(255),
    AddedDate DATETIME,
    UpdatedDate DATETIME,
    QrCode VARCHAR(255),
    ChangeDate DATETIME,  /* Timestamp of the change */
    ChangedBy VARCHAR(255), /* User that performed the change */
    ChangeType VARCHAR(50), /* e.g., 'UPDATE', 'DELETE' */
    FOREIGN KEY (BookID) REFERENCES books(ID)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table book_history created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
