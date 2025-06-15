<?php

// Assuming you have a database connection established and stored in $conn

function memberIdExists($conn, $memberId) {
    // Sanitize the input (important to prevent SQL injection)
    $memberId = intval($memberId); // Ensure it's an integer

    // Prepare the SQL query
    $sql = "SELECT ID FROM members WHERE ID = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        // Bind the parameter
        mysqli_stmt_bind_param($stmt, "i", $memberId); // "i" indicates integer

        // Execute the query
        mysqli_stmt_execute($stmt);

        // Get the result
        mysqli_stmt_store_result($stmt); //needed to use num_rows
        $num_rows = mysqli_stmt_num_rows($stmt);

        // Close the statement
        mysqli_stmt_close($stmt);

        // Return true if the member ID exists, false otherwise
        return $num_rows > 0;
    } else {
        // Handle the error if the prepared statement fails
        return false; // Or throw an exception, log the error, etc.
    }
}

// Example usage (replace with your actual database connection and member ID)
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_dbname";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$memberIdToCheck = 123; // Replace with the member ID you want to check

if (memberIdExists($conn, $memberIdToCheck)) {
    echo "Member ID " . $memberIdToCheck . " exists.";
} else {
    echo "Member ID " . $memberIdToCheck . " does not exist.";
}

$conn->close();

?>