<?php
require_once("connection.php");
}

// SQL query to create the 'admini' table
$sql = "CREATE TABLE IF NOT EXISTS administrators (
    ID INT PRIMARY KEY,
    First_name VARCHAR(255) NOT NULL,
    Second_Name VARCHAR(255),
    Surname VARCHAR(255) NOT NULL,
    Role VARCHAR(255),
    Telephone VARCHAR(255),
    Email VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    `Last Login` DATETIME,
    CreatedAt DATETIME,
    UpdatedAt DATETIME
)";

// Execute the query and check for errors
if ($conn->query($sql) === TRUE) {
    echo "Table 'administrators' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

// Close the connection
$conn->close();
?>
