<?php
// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Current date for checking overdue status (02:11 AM SAST, May 25, 2025)
$current_date = new DateTime('2025-05-25 02:11:00');

// Step 1: Check borrowing_history for overdue books and update blacklist column
$sql_check_overdue = "SELECT BorrowingID, BookID, ID, BorrowDate, DueDate, ReturnDate, Status 
                     FROM borrowing_history 
                     WHERE Status = 'borrowed'";
$result_check = $conn->query($sql_check_overdue);

while ($row = $result_check->fetch_assoc()) {
    $due_date = new DateTime($row['DueDate']);
    $borrowing_id = $row['BorrowingID'];
    $member_id = $row['ID'];
    $book_id = $row['BookID'];
    $borrow_date = $row['BorrowDate'];
    $due_date_str = $row['DueDate'];

    // Check if the book is overdue (due date passed, not returned)
    if ($current_date > $due_date && $row['Status'] == 'borrowed') {
        // Update blacklist column to 1
        $conn->query("UPDATE borrowing_history SET blacklist = 1 WHERE BorrowingID = $borrowing_id");

        // Fetch the member's email and member type
        $member_result = $conn->query("SELECT Contact_Email, Member_type FROM members WHERE ID = $member_id");
        $member_row = $member_result->fetch_assoc();
        $contact_email = $member_row['Contact_Email'];
        $member_type = strtoupper(str_replace(' ', '_', $member_row['Member_type']));

        // Calculate overdue days
        $interval = $due_date->diff($current_date);
        $overdue_days = $interval->days;

        // Fine rates based on membership type
        $fine_rates = [
            'STAFF' => 1.00,           // E1.00 per day per item
            'STUDENT' => 0.50,         // E0.50 per day per item
            'EXTERNAL_BORROWER' => 2.00 // E2.00 per day per item
        ];
        $fine_rate = isset($fine_rates[$member_type]) ? $fine_rates[$member_type] : 0;
        $charge = $overdue_days * $fine_rate;

        // Check if the member is already in the blacklist table to avoid duplicates
        $check_blacklist = $conn->query("SELECT * FROM blacklist WHERE MemberID = $member_id AND BookID = $book_id AND BorrowDate = '$borrow_date'");
        if ($check_blacklist->num_rows == 0) {
            // Add to blacklist table with charge
            $reason = "Book not returned by due date: $due_date_str";
            $blacklisted_date = $current_date->format('Y-m-d H:i:s');
            $conn->query("INSERT INTO blacklist (MemberID, BookID, Contact_Email, BorrowDate, DueDate, Reason, BlacklistedDate, Charge) 
                          VALUES ($member_id, $book_id, '$contact_email', '$borrow_date', '$due_date_str', '$reason', '$blacklisted_date', $charge)");
        }
    }
}

// Step 2: Display blacklisted members with book details and charges
$sql_display = "SELECT bl.BlacklistID, bl.MemberID, bl.BookID, bl.Contact_Email, bl.BorrowDate, bl.DueDate, bl.Reason, bl.BlacklistedDate, bl.Charge,
                m.Name, m.Surname, m.Member_type,
                b.Title, b.ISBN
                FROM blacklist bl
                JOIN members m ON bl.MemberID = m.ID
                JOIN books b ON bl.BookID = b.ID";
$result_display = $conn->query($sql_display);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blacklisted Members</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Blacklisted Members</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Surname</th>
                <th>Member Type</th>
                <th>Email</th>
                <th>Book Title</th>
                <th>ISBN</th>
                <th>Borrow Date</th>
                <th>Due Date</th>
                <th>Overdue Days</th>
                <th>Charge (E)</th>
                <th>Blacklisted Date</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_display->fetch_assoc()): ?>
                <?php
                // Calculate overdue days for display (using current date)
                $due_date = new DateTime($row['DueDate']);
                $interval = $due_date->diff($current_date);
                $overdue_days = $interval->days;
                if ($due_date > $current_date) {
                    $overdue_days = 0; // Not overdue if due date is in the future
                }
                ?>
                <tr>
                    <td><?php echo $row['Name']; ?></td>
                    <td><?php echo $row['Surname']; ?></td>
                    <td><?php echo $row['Member_type']; ?></td>
                    <td><?php echo $row['Contact_Email']; ?></td>
                    <td><?php echo $row['Title']; ?></td>
                    <td><?php echo $row['ISBN']; ?></td>
                    <td><?php echo $row['BorrowDate']; ?></td>
                    <td><?php echo $row['DueDate']; ?></td>
                    <td><?php echo $overdue_days; ?></td>
                    <td><?php echo number_format($row['Charge'], 2); ?></td>
                    <td><?php echo $row['BlacklistedDate']; ?></td>
                    <td><?php echo $row['Reason']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php $conn->close(); ?>
</body>
</html>