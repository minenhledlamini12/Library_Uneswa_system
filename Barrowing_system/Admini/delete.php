<?php
require_once("connection.php");
session_start(); // Start session to track undo actions

// Generate a unique session ID for this deletion if not already set
if (!isset($_SESSION['delete_session_id'])) {
    $_SESSION['delete_session_id'] = uniqid('delete_', true);
}
$sessionID = $_SESSION['delete_session_id'];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Check if this is an undo action
    if (isset($_GET['undo']) && $_GET['undo'] == 'true') {
        // Check if the book ID already exists in the books table
        $sql = "SELECT ID FROM books WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // A book with this ID already exists in the books table
            $message = "Cannot restore book: A book with ID $id already exists.";
            $message_color = "red";
            $undo = false;
        } else {
            // Undo the deletion: Move book back from deleted_books to books
            $sql = "INSERT INTO books 
                    SELECT ID, ISBN, Title, Author, PublicationYear, Publisher, Format, Language, Pages, Genre, CopiesAvailable, Status, CallNumber, AddedDate, UpdatedDate, QrCode 
                    FROM deleted_books 
                    WHERE ID = ? AND SessionID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $id, $sessionID);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // Remove from deleted_books
                $sql = "DELETE FROM deleted_books WHERE ID = ? AND SessionID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $id, $sessionID);
                $stmt->execute();

                // Log the undo action in book_history
                $changeDate = date('Y-m-d H:i:s');
                $changedBy = "Admin"; // Replace with actual user if available
                $changeType = "Restored";

                // Fetch the book details to log them
                $sql = "SELECT * FROM books WHERE ID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $book = $result->fetch_assoc();

                // Insert into book_history
                $sql = "INSERT INTO book_history (BookID, ChangeDate, ChangedBy, ChangeType, ISBN, Title, Author, PublicationYear, Publisher, Format, Language, Pages, Genre, CopiesAvailable, Status, CallNumber) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssssissssisiss", 
                    $id,
                    $changeDate,
                    $changedBy,
                    $changeType,
                    $book['ISBN'],
                    $book['Title'],
                    $book['Author'],
                    $book['PublicationYear'],
                    $book['Publisher'],
                    $book['Format'],
                    $book['Language'],
                    $book['Pages'],
                    $book['Genre'],
                    $book['CopiesAvailable'],
                    $book['Status'],
                    $book['CallNumber']
                );
                $stmt->execute();

                $message = "Book restored successfully!";
                $message_color = "green";
                $undo = false;
            } else {
                $message = "Error restoring book or no matching record found.";
                $message_color = "red";
                $undo = false;
                error_log("Restore Error: No matching record for ID $id", 3, "error.log");
            }
        }
        $stmt->close();
    } else {
        // Fetch the book details from books before deleting
        $sql = "SELECT * FROM books WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $book = $result->fetch_assoc();

            // Log the deletion in book_history
            $changeDate = date('Y-m-d H:i:s');
            $changedBy = "Admin"; // Replace with actual user if available
            $changeType = "Deleted";

            // Insert into book_history
            $sql = "INSERT INTO book_history (BookID, ChangeDate, ChangedBy, ChangeType, ISBN, Title, Author, PublicationYear, Publisher, Format, Language, Pages, Genre, CopiesAvailable, Status, CallNumber) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssssissssisiss", 
                $id,
                $changeDate,
                $changedBy,
                $changeType,
                $book['ISBN'],
                $book['Title'],
                $book['Author'],
                $book['PublicationYear'],
                $book['Publisher'],
                $book['Format'],
                $book['Language'],
                $book['Pages'],
                $book['Genre'],
                $book['CopiesAvailable'],
                $book['Status'],
                $book['CallNumber']
            );
            $stmt->execute();

            // Perform soft delete: Move book to deleted_books
            $sql = "INSERT INTO deleted_books (ID, ISBN, Title, Author, PublicationYear, Publisher, Format, Language, Pages, Genre, CopiesAvailable, Status, CallNumber, AddedDate, UpdatedDate, QrCode, DeletedAt, SessionID) 
                    SELECT ID, ISBN, Title, Author, PublicationYear, Publisher, Format, Language, Pages, Genre, CopiesAvailable, Status, CallNumber, AddedDate, UpdatedDate, QrCode, NOW(), ? 
                    FROM books 
                    WHERE ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $sessionID, $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // Now delete from books
                $sql = "DELETE FROM books WHERE ID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $message = "Book deleted successfully! <a href='delete.php?id=$id&undo=true' id='undo-link' class='undo-link'>Undo</a>";
                    $message_color = "green";
                    $undo = true;
                } else {
                    $message = "Error deleting book from books table.";
                    $message_color = "red";
                    $undo = false;
                    error_log("Delete Error: " . $stmt->error, 3, "error.log");
                }
            } else {
                $message = "Error moving book to deleted_books.";
                $message_color = "red";
                $undo = false;
                error_log("Soft Delete Error: " . $stmt->error, 3, "error.log");
            }
        } else {
            $message = "Book not found.";
            $message_color = "red";
            $undo = false;
        }
        $stmt->close();
    }
    $conn->close();
} else {
    $message = "Book ID not provided.";
    $message_color = "red";
    $undo = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Book - UNESWA Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #218838;
            --accent-color: #f4a261;
            --background-color: #f5f7fa;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --footer-color: #808080;
            --border-radius: 12px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            opacity: 0;
            animation: fadeInBody 1s ease-out forwards;
        }

        @keyframes fadeInBody {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .top-bar {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideInUp 0.8s ease-out;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
            transition: var(--transition);
        }

        .top-bar a:hover {
            color: var(--accent-color);
            transform: scale(1.2);
        }

        .header-main {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: center;
            animation: slideInUp 1s ease-out 0.2s;
        }

        .header-main img {
            height: 60px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .header-main h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0;
        }

        .header-main span {
            font-style: italic;
            opacity: 0.9;
        }

        .main-container {
            flex: 1;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
            animation: slideInUp 1s ease-out 0.4s forwards;
            opacity: 0;
            text-align: center;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: center;
        }

        .message {
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            padding: 15px;
            border-radius: var(--border-radius);
        }

        .message.green {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--primary-color);
        }

        .message.red {
            background-color: rgba(217, 83, 79, 0.1);
            color: #d9534f;
        }

        .undo-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: bold;
            padding: 0.5rem 1rem;
            border: 2px solid var(--accent-color);
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: inline-block;
        }

        .undo-link:hover {
            background-color: var(--accent-color);
            color: white;
            text-decoration: none;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            transition: var(--transition);
            animation: slideInUp 0.8s ease-out 0.6s forwards;
            opacity: 0;
        }

        .back-button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        footer {
            background-color: var(--footer-color);
            color: white;
            padding: 3rem 2rem;
            margin-top: auto;
            animation: slideInUp 1s ease-out 0.6s;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
        }

        .footer-section {
            padding: 1rem;
            opacity: 0;
            animation: slideInUp 0.8s ease-out forwards;
        }

        .footer-section:nth-child(1) { animation-delay: 0.7s; }
        .footer-section:nth-child(2) { animation-delay: 0.8s; }
        .footer-section:nth-child(3) { animation-delay: 0.9s; }
        .footer-section:nth-child(4) { animation-delay: 1.0s; }

        .footer-section h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }

        .footer-section p,
        .footer-section a {
            color: #dfe6e9;
            margin-bottom: 0.5rem;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-section a:hover {
            color: var(--accent-color);
            text-decoration: underline;
            transform: translateX(5px);
        }

        .footer-section img {
            height: 50px;
            margin-bottom: 1rem;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 2rem;
            animation: slideInUp 1s ease-out 1.2s forwards;
            opacity: 0;
        }

        @media (max-width: 1024px) {
            .main-container {
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .header-main {
                flex-direction: column;
                text-align: center;
            }

            .header-main img {
                margin-bottom: 1rem;
            }

            .card h2 {
                font-size: 1.5rem;
            }

            .message {
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .top-bar {
                flex-direction: column;
                gap: 0.5rem;
            }

            .header-main h1 {
                font-size: 1.8rem;
            }

            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <span>🕒 Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM</span>
        <div>
            📞 2517 0448
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
    </div>

    <div class="header-main">
        <img src="/php_program/Borrowing_system/Images/download.png" alt="UNESWA Library Logo">
        <div>
            <h1>University of Eswatini Library</h1>
            <span>Kwaluseni Campus - Self-Service Book Borrowing</span>
        </div>
    </div>

    <div class="main-container">
        <div class="card">
            <h2><i class="fas fa-trash-alt"></i> Delete Book</h2>
            <p class="message <?php echo htmlspecialchars($message_color); ?>">
                <?php echo $message; ?>
            </p>
            <a href="manage.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to Manage Books</a>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section get-in-touch">
                <h3>Get In Touch</h3>
                <img src="/php_program/Borrowing_system/Images/download.png" alt="University of Eswatini Library Logo">
                <p>Kwaluseni, Luyengo & Mbabane</p>
                <p><i class="fas fa-phone"></i> 2517 0448</p>
                <p><i class="fas fa-envelope"></i> <a href="mailto:library@uniswa.sz">library@uniswa.sz</a></p>
            </div>

            <div class="footer-section quick-links">
                <h3>Quick Links</h3>
                <p><a href="#">Eswatini National Bibliography</a></p>
                <p><a href="#">UNESWA IR</a></p>
                <p><a href="#">Notices</a></p>
                <p><a href="#">Past Exam Papers</a></p>
                <p><a href="#">UNESWA</a></p>
            </div>

            <div class="footer-section popular-databases">
                <h3>Popular Databases</h3>
                <p><a href="#">Science Direct</a></p>
                <p><a href="#">Ebscohost</a></p>
                <p><a href="#">ERIC</a></p>
                <p><a href="#">Taylor & Francis</a></p>
                <p><a href="#">Sabinet</a></p>
            </div>

            <div class="footer-section follow-us">
                <h3>Follow Us</h3>
                <p><a href="#"><i class="fab fa-twitter"></i> Twitter</a></p>
                <p><a href="#"><i class="fab fa-facebook"></i> Facebook</a></p>
                <p><a href="#"><i class="fab fa-instagram"></i> Instagram</a></p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© 2025 UNESWA Library. All rights reserved.</p>
        </div>
    </footer>

    <?php if (isset($undo) && $undo) { ?>
    <script>
        // Auto-hide undo link after 30 seconds with countdown
        let timeLeft = 30;
        const undoLink = document.getElementById('undo-link');
        if (undoLink) {
            undoLink.textContent = `Undo (${timeLeft}s)`;
            const timer = setInterval(() => {
                timeLeft--;
                undoLink.textContent = `Undo (${timeLeft}s)`;
                if (timeLeft <= 0) {
                    undoLink.style.display = 'none';
                    clearInterval(timer);
                }
            }, 1000);
        }
    </script>
    <?php } ?>
</body>
</html>