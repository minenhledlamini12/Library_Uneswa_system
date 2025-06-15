<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle logout request
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: landingpage.php');
    exit;
}

// Parse department from CourseDepartmentAffiliation (temporary workaround)
$affiliation = $_SESSION['CourseDepartmentAffiliation'] ?? '';
$department = !empty($affiliation) ? (count($parts = explode(' | ', $affiliation)) > 1 ? $parts[1] : $affiliation) : 'General';

// --- PHP Logic for Book Details ---
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("connection.php"); // Your DB connection

// Get book_id and return_date from GET parameters
// Using filter_input for safer retrieval of GET parameters
$book_id = filter_input(INPUT_GET, 'book_id', FILTER_SANITIZE_NUMBER_INT);
$return_date = filter_input(INPUT_GET, 'return_date', FILTER_SANITIZE_STRING);

// Initialize book_details with default string values to prevent warnings
$book_details = ['Title' => 'Unknown', 'Author' => 'Unknown', 'ISBN' => 'Unknown'];
$error_message = '';

if ($book_id) {
    // Use prepared statements for fetching book details for security
    $book_query = "SELECT Title, Author, ISBN FROM books WHERE ID = ?";
    $book_stmt = $conn->prepare($book_query);

    if ($book_stmt) {
        $book_stmt->bind_param("i", $book_id); // 'i' for integer
        $book_stmt->execute();
        $book_result = $book_stmt->get_result();

        if ($book_result && $book_result->num_rows > 0) {
            $fetched_details = $book_result->fetch_assoc();
            // Assign fetched values, using null coalescing for robustness
            $book_details['Title'] = $fetched_details['Title'] ?? 'N/A';
            $book_details['Author'] = $fetched_details['Author'] ?? 'N/A';
            $book_details['ISBN'] = $fetched_details['ISBN'] ?? 'N/A';
        } else {
            $error_message = "No book found with ID: " . htmlspecialchars($book_id) . ".";
        }
        $book_stmt->close();
    } else {
        $error_message = "Failed to prepare book details query: " . htmlspecialchars($conn->error);
    }
} else {
    $error_message = "Book ID not provided in the URL.";
}

// Format return date and time
try {
    // If return_date is not provided or invalid, use current time
    if (empty($return_date)) {
        $dateTime = new DateTime();
    } else {
        $dateTime = new DateTime($return_date);
    }
    $formatted_date = $dateTime->format("F j, Y");
    $formatted_time = $dateTime->format("h:i A");
} catch (Exception $e) {
    $formatted_date = "Unknown Date";
    $formatted_time = "Unknown Time";
    $error_message .= " Invalid return date format: " . htmlspecialchars($e->getMessage());
}

$conn->close();
// --- End PHP Logic for Book Details ---
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Success | UNESWA Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f9f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Top Bar */
        .top-bar {
            background-color: #003366;
            color: white;
            padding: 8px 0;
            font-size: 0.85em;
        }

        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            transition: color 0.2s;
        }

        .top-bar a:hover {
            color: #8eeea8;
        }

        .icon {
            margin-right: 8px;
        }

        /* Header */
        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-left img {
            height: 60px;
            margin-right: 15px;
            border-radius: 50%;
            background-color: white;
            padding: 5px;
        }

        .header-left h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .header-right {
            font-style: italic;
            font-size: 1.1rem;
        }

        /* Navigation */
        nav {
            background-color: #388E3C;
            padding: 12px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-content {
            display: flex;
            justify-content: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            margin: 0 5px;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        nav a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        nav a.active {
            background-color: rgba(255, 255, 255, 0.3);
        }

        /* Main Content */
        main {
            padding: 40px 0;
            min-height: calc(100vh - 400px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .success-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(to right, #4CAF50, #8BC34A);
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background-color: #e8f5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: #4CAF50;
            font-size: 3.5rem;
            box-shadow: 0 10px 20px rgba(76, 175, 80, 0.2);
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            60% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-title {
            color: #2e7d32;
            font-size: 2.2rem;
            margin-bottom: 15px;
            animation: fadeInUp 0.6s ease-out;
        }

        .success-message {
            color: #555;
            font-size: 1.2rem;
            margin-bottom: 30px;
            animation: fadeInUp 0.7s ease-out;
        }

        .success-message strong {
            color: #2e7d32;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #4CAF50;
            opacity: 0.8;
            animation: confetti 5s ease-in-out infinite;
        }

        @keyframes confetti {
            0% {
                transform: translateY(-100px) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(600px) rotate(360deg);
                opacity: 0;
            }
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .btn:hover {
            background-color: #2e7d32;
            transform: translateY(-3px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .btn i {
            margin-right: 8px;
        }

        .book-details {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: left;
            animation: fadeInUp 0.7s ease-out;
        }

        .detail-row {
            display: flex;
            margin-bottom: 8px;
        }

        .detail-label {
            font-weight: 600;
            width: 120px;
            color: #555;
        }

        .detail-value {
            flex: 1;
        }

        .error-message {
            color: #d32f2f;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        /* Footer */
        footer {
            background-color: #4CAF50;
            color: white;
            padding: 40px 0 20px;
        }

        .footer-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .footer-section {
            flex: 1;
            min-width: 200px;
            margin-bottom: 20px;
            padding: 0 15px;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: rgba(255, 255, 255, 0.5);
        }

        .footer-section img {
            height: 50px;
            margin-bottom: 15px;
            background-color: white;
            padding: 5px;
            border-radius: 5px;
        }

        .footer-section p, .footer-section li {
            margin-bottom: 8px;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section a {
            color: white;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-section a:hover {
            color: #e8f5e9;
            text-decoration: underline;
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            background-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content, .top-bar-content {
                flex-direction: column;
                text-align: center;
            }

            .header-left {
                margin-bottom: 10px;
            }

            .top-bar-left, .top-bar-right {
                width: 100%;
                justify-content: center;
                margin-bottom: 5px;
            }

            .nav-content {
                flex-wrap: wrap;
            }

            nav a {
                margin-bottom: 5px;
            }

            .success-card {
                padding: 30px 20px;
            }

            .success-icon {
                width: 100px;
                height: 100px;
                font-size: 3rem;
            }

            .success-title {
                font-size: 1.8rem;
            }

            .detail-row {
                flex-direction: column;
            }

            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }

            .footer-section {
                flex: 0 0 100%;
                text-align: center;
            }

            .footer-section h3::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .social-icons {
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <i class="far fa-clock icon"></i> Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM
                </div>
                <div class="top-bar-right">
                    <i class="fas fa-phone icon"></i> 2517 0448
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo">
                    <h1>University of Eswatini Library</h1>
                </div>
                <div class="header-right">
                    <div class="header-right-text">
                        Kwaluseni Campus - Self-Service Book Borrowing
                    </div>
                    <a href="?action=logout" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav>
        <div class="container">
            <div class="nav-content">
                <a href="homepage.php"><i class="fas fa-home icon"></i> Home</a>
                <a href="search.php"><i class="fas fa-search icon"></i> Search Book</a>
                <a href="barrowpage.php"><i class="fas fa-book-open icon"></i> Borrow/Issue Book</a>
                <a href="return.php"><i class="fas fa-undo icon"></i> Return Book</a>
                <a href="library_regulations.php"><i class="fas fa-file-alt icon"></i> Library Regulations</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- Confetti Animation Elements -->
            <?php for ($i = 0; $i < 20; $i++) : ?>
                <div class="confetti" style="
                    left: <?php echo rand(5, 95); ?>%;
                    top: -<?php echo rand(10, 100); ?>px;
                    width: <?php echo rand(5, 15); ?>px;
                    height: <?php echo rand(5, 15); ?>px;
                    background-color: <?php echo ['#4CAF50', '#8BC34A', '#CDDC39', '#FFC107', '#2196F3'][rand(0, 4)]; ?>;
                    animation-delay: <?php echo rand(0, 30) / 10; ?>s;
                    animation-duration: <?php echo rand(30, 60) / 10; ?>s;
                "></div>
            <?php endfor; ?>

            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>

                <h1 class="success-title">Book Returned Successfully!</h1>

                <p class="success-message">Thank you for returning the book to the UNESWA Library. <strong>Please place the book in the return bin. A confirmation email has been sent.</strong></p>

                <?php if ($error_message): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>

                <div class="book-details">
                    <div class="detail-row">
                        <div class="detail-label">Book Title:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($book_details['Title']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Author:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($book_details['Author']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">ISBN:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($book_details['ISBN']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Return Date:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($formatted_date); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Return Time:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($formatted_time); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div class="detail-value" style="color: #4CAF50; font-weight: 600;">Returned Successfully</div>
                    </div>
                </div>

                <div style="margin-top: 25px;">
                    <a href="homepage.php" class="btn">
                        <i class="fas fa-home"></i> Return to Home
                    </a>
                    <a href="return.php" class="btn">
                        <i class="fas fa-undo"></i> Return Another Book
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-container">
                <!-- Get In Touch Section -->
                <div class="footer-section">
                    <h3>Get In Touch</h3>
                    <img src="/php_program/Barrowing_system/Images/download.png" alt="University of Eswatini Library Logo">
                    <p>Kwaluseni, Luyengo & Mbabane</p>
                    <p><i class="fas fa-phone icon"></i> 2517 0448</p>
                    <p><i class="fas fa-envelope icon"></i> <a href="mailto:library@uniswa.sz">library@uniswa.sz</a></p>
                </div>

                <!-- Quick Links Section -->
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#">Eswatini National Bibliography</a></li>
                        <li><a href="#">UNESWA IR</a></li>
                        <li><a href="#">Notices</a></li>
                        <li><a href="#">Past Exam Papers</a></li>
                        <li><a href="#">UNESWA</a></li>
                    </ul>
                </div>

                <!-- Popular Databases Section -->
                <div class="footer-section">
                    <h3>Popular Databases</h3>
                    <ul>
                        <li><a href="#">Science Direct</a></li>
                        <li><a href="#">Ebscohost</a></li>
                        <li><a href="#">ERIC</a></li>
                        <li><a href="#">Taylor & Francis</a></li>
                        <li><a href="#">Sabinet</a></li>
                    </ul>
                </div>

                <!-- Follow Us Section -->
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <p>Stay connected with us on social media for updates and announcements</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                © <?php echo date("Y"); ?> University of Eswatini Library | All Rights Reserved.
            </div>
        </div>
    </footer>

    <script>
        // Add a small animation effect when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Simulate a loading delay for better effect
            setTimeout(function() {
                document.querySelector('.success-icon').style.visibility = 'visible';
            }, 300);
        });
    </script>
</body>
</html>
