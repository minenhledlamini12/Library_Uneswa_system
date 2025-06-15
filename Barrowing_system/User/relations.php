<?php

require_once "connection.php";

// Drop existing foreign key constraints if they exist
$drop_bookid = "ALTER TABLE borrowing_history DROP FOREIGN KEY IF EXISTS FK_BookID;";
$drop_memberid = "ALTER TABLE borrowing_history DROP FOREIGN KEY IF EXISTS FK_MemberID;";

if ($conn->query($drop_bookid) === TRUE) {
    echo "Dropped existing foreign key FK_BookID<br>";
} else {
    echo "Error dropping foreign key FK_BookID: " . $conn->error . "<br>";
}

if ($conn->query($drop_memberid) === TRUE) {
    echo "Dropped existing foreign key FK_MemberID<br>";
} else {
    echo "Error dropping foreign key FK_MemberID: " . $conn->error . "<br>";
}


// SQL to add foreign key constraint for BookID
$sql_bookid = "ALTER TABLE borrowing_history
               ADD CONSTRAINT FK_BookID
               FOREIGN KEY (BookID)
               REFERENCES books(ID)
               ON DELETE CASCADE
               ON UPDATE CASCADE;";

// SQL to add foreign key constraint for Member_ID
$sql_memberid = "ALTER TABLE borrowing_history
                 ADD CONSTRAINT FK_MemberID
                 FOREIGN KEY (Member_ID)
                 REFERENCES members(ID)
                 ON DELETE CASCADE
                 ON UPDATE CASCADE;";

// Execute BookID foreign key creation
if ($conn->query($sql_bookid) === TRUE) {
    echo "Foreign key for BookID created successfully<br>";
} else {
    echo "Error creating foreign key for BookID: " . $conn->error . "<br>";
}

// Execute Member_ID foreign key creation
if ($conn->query($sql_memberid) === TRUE) {
    echo "Foreign key for Member_ID created successfully<br>";
} else {
    echo "Error creating foreign key for Member_ID: " . $conn->error . "<br>";
}

$conn->close();

?>
