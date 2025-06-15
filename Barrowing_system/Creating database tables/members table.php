<?php
require_once("connection.php");


}

// SQL query to create the 'members' table
$sql = "CREATE TABLE IF NOT EXISTS members (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Member_ID VARCHAR(255) NOT NULL,
    Name VARCHAR(255) NOT NULL,
    Surname VARCHAR(255) NOT NULL,
    Course_Department_Affiliation VARCHAR(255),
    Membership_type VARCHAR(50),
    Contact VARCHAR(50),
    Email VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Joined_Date DATE,
    QR_code VARCHAR(255),
    Status TINYINT(1),
    Checkin_Time DATETIME,
    Checkout_Time DATETIME
)";

// Execute the query and check for errors
if ($conn->query($sql) === TRUE) {
    echo "Table 'members' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

// Close the connection
$conn->close();
?>
