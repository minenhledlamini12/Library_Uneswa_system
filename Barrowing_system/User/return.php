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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Book | UNESWA Library</title>
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
            display: flex;
            align-items: center;
        }

        .header-right-text {
            font-style: italic;
            font-size: 1.1rem;
            margin-right: 20px;
        }

        .logout-btn {
            background-color: #d32f2f;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #b71c1c;
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
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: #2e7d32;
            font-size: 2.2rem;
            font-weight: 600;
        }

        .welcome-message {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #555;
        }

        .return-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 700px;
            margin: 0 auto;
            text-align: center;
        }

        .card-header {
            margin-bottom: 25px;
        }

        .card-header h2 {
            color: #2e7d32;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .card-header p {
            color: #666;
        }

        .return-steps {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }

        .step {
            flex: 1;
            min-width: 180px;
            max-width: 200px;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .step-icon {
            font-size: 2rem;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .step-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #2e7d32;
        }

        .step-description {
            font-size: 0.9rem;
            color: #666;
        }

        .qr-section {
            margin: 30px 0;
        }

        .qr-image {
            max-width: 300px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .qr-caption {
            font-size: 0.9rem;
            color: #666;
            font-style: italic;
        }

        .scan-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            display: inline-flex;
            align-items: center;
        }

        .scan-button:hover {
            background-color: #2e7d32;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .scan-button i {
            margin-right: 10px;
        }

        .note-box {
            background-color: #e8f5e9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin: 30px auto;
            max-width: 600px;
            text-align: left;
        }

        .note-box h3 {
            color: #2e7d32;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .note-box p {
            color: #555;
            font-size: 0.95rem;
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

            .return-steps {
                flex-direction: column;
                align-items: center;
            }

            .step {
                max-width: 100%;
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
                <a href="library_regulations.php"><i class="fas fa-file-alt icon"></i> Library Regulations</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="container">
            <h1 class="page-title">Return Book</h1>
            
            <p class="welcome-message">
                Welcome, <?php echo htmlspecialchars($_SESSION['Name'] . ' ' . $_SESSION['Surname']); ?>! | 
                <?php echo htmlspecialchars($_SESSION['Membership_type']); ?> Member | 
                <?php echo htmlspecialchars($department); ?>
            </p>
            
            <div class="return-card">
                <div class="card-header">
                    <h2>Return a Book</h2>
                    <p>Please follow the steps below to return your book</p>
                </div>
                
                <div class="return-steps">
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="step-title">Step 1</h3>
                        <p class="step-description">Locate the QR code on the back of the book</p>
                    </div>
                    
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <h3 class="step-title">Step 2</h3>
                        <p class="step-description">Scan the QR code using our system</p>
                    </div>
                    
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="step-title">Step 3</h3>
                        <p class="step-description">Confirm the return and place the book in the return bin</p>
                    </div>
                </div>
                
                <div class="qr-section">
                    <img src="/php_program/Barrowing_system/Images/return.jpg" alt="QR Code Example" class="qr-image">
                    <p class="qr-caption">Scan the QR code on the back cover of the book</p>
                </div>
                
                <button class="scan-button" onclick="window.location.href='return_book_scanner.php'">
                    <i class="fas fa-qrcode"></i> Scan QR Code to Return
                </button>
                
                <div class="note-box">
                    <h3><i class="fas fa-info-circle"></i> Important Note</h3>
                    <p>Please ensure that the book is in good condition when returning. Any damages should be reported to the library staff. Late returns may incur fines as per library regulations.</p>
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
</body>
</html>