<?php
session_start();
require 'connection.php'; // Database connection file

// Calculate return date
function calculateReturnDate($memberType) {
    $borrowDate = new DateTime();
    $returnDate = clone $borrowDate;

    switch($memberType) {
        case 'student':
            $returnDate->modify('+14 days');
            break;
        case 'staff':
            $returnDate->modify('+12 weeks');
            break;
        case 'faculty':
            $returnDate->modify('+14 days');
            break;
        default:
            $returnDate->modify('+14 days');
    }
    
    return $returnDate->format('Y-m-d');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['borrow'])) {
        // Process borrowing
        if (!isset($_POST['terms'])) {
            $error = "You must agree to the terms and conditions";
        } else {
            $bookId = $_POST['book_id'];
            $memberType = $_POST['member_type'];
            
            $borrowDate = date('Y-m-d');
            $returnDate = calculateReturnDate($memberType);
            
            $stmt = $conn->prepare("INSERT INTO borrow_records 
                (book_id, borrow_date, return_date, member_type) 
                VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $bookId, $borrowDate, $returnDate, $memberType);
            
            if ($stmt->execute()) {
                $success = "Book borrowed successfully! Return by: $returnDate";
            } else {
                $error = "Error borrowing book: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Borrowing Portal</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .qr-image { width: 200px; margin: 20px; }
        .book-details { display: <?= isset($_POST['scan']) ? 'block' : 'none' ?>; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1>Welcome to Library Borrowing Portal</h1>
            <img src="qr-scan.png" alt="Scan QR Code" class="qr-image">
            <form method="post">
                <button type="submit" name="scan">Scan Book QR Code</button>
            </form>
        </div>

        <!-- Book Details Section -->
        <?php if (isset($_POST['scan']) || isset($_POST['borrow'])): ?>
        <div class="book-details">
            <h2>Book Details</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <p class="success"><?= $success ?></p>
            <?php else: ?>
                <form method="post">
                    <input type="hidden" name="book_id" value="123"> <!-- Replace with actual book ID from QR scan -->
                    
                    <p>Book Title: Example Book Title</p>
                    <p>Borrow Date: <?= date('Y-m-d') ?></p>
                    
                    <label for="member_type">Member Type:</label>
                    <select name="member_type" id="member_type" required>
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                        <option value="faculty">Faculty</option>
                    </select>
                    
                    <div class="terms">
                        <input type="checkbox" name="terms" id="terms" required>
                        <label for="terms">I agree to the library terms and conditions</label>
                    </div>
                    
                    <button type="submit" name="borrow">Borrow Book</button>
                </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
