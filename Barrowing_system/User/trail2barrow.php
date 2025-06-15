<?php
session_start();
require 'connection.php'; // Include database connection file

// Redirect to terms page if user hasn't agreed
if (!isset($_SESSION['agreed_to_terms']) || $_SESSION['agreed_to_terms'] !== true) {
    header('Location: terms.php');
    exit();
}

// Calculate return date based on member type
function calculateReturnDate($memberType) {
    $borrowDate = new DateTime();
    $returnDate = clone $borrowDate;

    switch ($memberType) {
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['borrow'])) {
        // Validate form data
        if (!isset($_POST['terms'])) {
            $error = "You must agree to the terms and conditions.";
        } else {
            // Retrieve form data
            $bookId = $_POST['book_id'];
            $memberId = $_POST['member_id'];
            $memberType = $_POST['member_type'];

            // Fetch book details from the database
            $stmt = $conn->prepare("SELECT Title, ISBN, CopiesAvailable FROM book WHERE ID = ?");
            $stmt->bind_param("i", $bookId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $book = $result->fetch_assoc();
                if ($book['CopiesAvailable'] > 0) {
                    // Calculate borrow and return dates
                    $borrowDate = date('Y-m-d');
                    $returnDate = calculateReturnDate($memberType);

                    // Insert transaction into borrowing_history table
                    $status = "Borrowed";
                    $stmt2 = $conn->prepare("INSERT INTO borrowing_history 
                        (BookID, BookTitle, ISBN, MemberID, BorrowDate, ReturnDate, Status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt2->bind_param(
                        "issssss",
                        $bookId,
                        $book['Title'],
                        $book['ISBN'],
                        $memberId,
                        $borrowDate,
                        $returnDate,
                        $status
                    );

                    if ($stmt2->execute()) {
                        // Update book availability in the book table
                        $stmt3 = $conn->prepare("UPDATE book SET CopiesAvailable = CopiesAvailable - 1 WHERE ID = ?");
                        $stmt3->bind_param("i", $bookId);
                        if ($stmt3->execute()) {
                            // Success message
                            unset($_SESSION['agreed_to_terms']); // Clear agreement for next transaction
                            $success = "Book borrowed successfully! Return by: " . date('Y-m-d', strtotime($returnDate));
                        } else {
                            // Error updating book availability
                            $error = "Error updating book availability: " . $conn->error;
                        }
                    } else {
                        // Error inserting into borrowing_history table
                        $error = "Error borrowing book: " . $conn->error;
                    }
                } else {
                    // No copies available
                    $error = "No copies available for this book.";
                }
            } else {
                // Book not found in database
                $error = "Book not found.";
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
    <title>Borrowing Portal</title>
</head>
<body>
    <h1>Borrow a Book</h1>

    <?php if (isset($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">
        <!-- Replace with actual scanned book ID -->
        <input type="hidden" name="book_id" value="1"> 

        <!-- Replace with actual member ID -->
        <input type="hidden" name="member_id" value="12345"> 

        <label for="member_type">Member Type:</label>
        <select name="member_type" id="member_type" required>
            <option value="student">Student</option>
            <option value="staff">Staff</option>
            <option value="faculty">Faculty</option>
        </select>

        <div>
            <input type="checkbox" name="terms" id="terms" required>
            <label for="terms">I agree to the library terms and conditions.</label>
        </div>

        <button type="submit" name="borrow">Borrow Book</button>
    </form>
</body>
</html>
