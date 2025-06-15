<?php

require_once("connection.php");

// SQL to create table
$sql = "
    CREATE TABLE IF NOT EXISTS email_reminders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        borrowing_id INT NOT NULL,
        member_id VARCHAR(50) NOT NULL,
        book_id INT NOT NULL,
        isbn VARCHAR(20) NOT NULL,
        reminder_date DATE NOT NULL,
        reminder_type VARCHAR(20) NOT NULL,
        sent TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
";

// Execute query to create table
if ($conn->query($sql) === TRUE) {
    echo "Table created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// SQL to create indexes
$sqlIndex1 = "CREATE INDEX idx_reminder_date ON email_reminders (reminder_date, sent);";
$sqlIndex2 = "CREATE INDEX idx_borrowing_id ON email_reminders (borrowing_id);";

// Execute queries to create indexes
if ($conn->query($sqlIndex1) === TRUE) {
    echo "Index idx_reminder_date created successfully\n";
} else {
    echo "Error creating index idx_reminder_date: " . $conn->error . "\n";
}

if ($conn->query($sqlIndex2) === TRUE) {
    echo "Index idx_borrowing_id created successfully\n";
} else {
    echo "Error creating index idx_borrowing_id: " . $conn->error . "\n";
}

// Close connection
$conn->close();

?>


