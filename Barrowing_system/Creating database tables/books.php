<?php

require_once("connection.php");



}

// SQL query to create the 'books' table
$sql = "CREATE TABLE IF NOT EXISTS books (
    ID INT PRIMARY KEY,
    ISBN VARCHAR(20),
    Title VARCHAR(255) NOT NULL,
    Author VARCHAR(255),
    PublicationYear INT,
    Publisher VARCHAR(255),
    Format VARCHAR(50),
    Language VARCHAR(50),
    Pages INT,
    Genre VARCHAR(100),
    CopiesAvailable INT,
    Status VARCHAR(50),
    CallNumber VARCHAR(50),
    AddedDate DATETIME,
    UpdatedDate DATETIME,
    QrCode VARCHAR(255)
)";

// Execute the query and check for errors
if ($conn->query($sql) === TRUE) {
    echo "Table 'books' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

// Close the connection
$conn->close();
?>
