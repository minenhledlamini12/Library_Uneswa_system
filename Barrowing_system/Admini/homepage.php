<?php
session_start();
require_once 'connection.php';

// Redirect to login if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch user's name and surname from the database
$email = $_SESSION['username'];
$sql = "SELECT First_name, Surname FROM admini WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESWA Library - Librarian Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #218838;
            --accent-color: #f4a261;
            --background-color: #f5f7fa;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --footer-color: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Top Bar */
        .top-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            margin-left: 0.5rem;
            transition: all 0.3s ease;
            padding: 0.25rem;
            border-radius: 4px;
        }

        .top-bar a:hover {
            color: var(--accent-color);
            transform: scale(1.1);
        }

        /* Header Main */
        .header-main {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .header-main .logo-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 1rem;
            backdrop-filter: blur(10px);
        }

        .header-main img {
            height: 60px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .header-main .title-container {
            text-align: center;
            flex-grow: 1;
        }

        .header-main h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }

        .header-main .subtitle {
            font-style: italic;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-user span {
            font-size: 1.1rem;
            font-weight: 500;
        }

        .logout-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 10px;
            padding: 8px 24px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }

        /* Main Layout */
        .main-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* Sidebar */
        .sidebar {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 2rem;
            height: fit-content;
        }

        .sidebar h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--text-color);
            text-decoration: none;
            padding: 0.875rem 1rem;
            margin: 0.25rem 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar a:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateX(8px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .sidebar a i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Content Area */
        .content {
            padding: 0;
        }

        /* Library Image Card */
        .library-image-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .library-image-content {
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
        }

        .library-image-content i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .library-image-content h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .library-image-content p {
            opacity: 0.9;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .welcome-image {
            width: 100%;
            max-width: 600px;
            height: auto;
            border-radius: 12px;
            margin: 0;
			align center
            display: block;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .welcome-image:hover {
            transform: scale(1.02);
        }

        /* Feature Cards */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .feature-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .feature-card.blue .feature-icon {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .feature-card.green .feature-icon {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .feature-card.purple .feature-icon {
            background: rgba(147, 51, 234, 0.1);
            color: #9333ea;
        }

        .feature-card.red .feature-icon {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .feature-card.indigo .feature-icon {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .feature-card.teal .feature-icon {
            background: rgba(20, 184, 166, 0.1);
            color: #14b8a6;
        }

        .feature-card h3 {
            font-size: 1.3rem;
            color: var(--text-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .feature-card p {
            color: #64748b;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Footer */
        footer {
 fruta: #28a745;
            color: white;
            padding: 3rem 2rem;
            margin-top: 4rem;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-section h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .footer-logo-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .footer-logo {
            background: white;
            padding: 0.5rem;
            border-radius: 8px;
        }

        .footer-logo i {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .footer-university-name {
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .footer-section p,
        .footer-section a {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.75rem;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: block;
        }

        .footer-section a:hover {
            color: white;
            transform: translateX(5px);
        }

        .footer-social {
            display: flex;
            gap: 1rem;
        }

        .footer-social a {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.75rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            margin-bottom: 0;
        }

        .footer-social a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 2rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .header-main {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem;
            }

            .header-main h1 {
                font-size: 2rem;
            }

            .header-user {
                flex-direction: column;
                gap: 0.5rem;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .top-bar {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .main-container {
                padding: 1rem;
            }

            .welcome-image {
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .footer-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .footer-social {
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-left">
            <i class="fas fa-clock"></i>
            <span>Mon-Fri: 08:30 AM - 11:00 PM | Sat: 10:00 AM - 05:00 PM | Sun: 03:00 PM - 10:00 PM</span>
        </div>
        <div class="top-bar-right">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-phone"></i>
                <span>2517 0448</span>
            </div>
            <div>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="header-main">
        <div class="logo-container">
            <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo">
        </div>
        <div class="title-container">
            <h1>University of Eswatini Library</h1>
            <div class="subtitle">Kwaluseni Campus - Self-Service Book Borrowing</div>
        </div>
        <div class="header-user">
            <h2><?php echo htmlspecialchars($user['First_name'] . ' ' . $user['Surname']); ?></h2>
            <form action="logout.php" method="post" style="display: inline;">
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <h3>Navigation</h3>
            <a href="form.php"><i class="fas fa-user-plus"></i> Registration</a>
            <a href="manage.php"><i class="fas fa-book-open"></i> Manage Books</a>
            <a href="borrowing_history.php"><i class="fas fa-exchange-alt"></i> Borrowing History Management</a>
            <a href="return_book.php"><i class="fas fa-undo"></i> Return Book</a>
            <a href="blacklisted_members.php"><i class="fas fa-user-slash"></i> Blacklist</a>
            <a href="manage_students.php"><i class="fas fa-users"></i> Manage Members</a>
        </nav>

        <!-- Content Area -->
        <div class="content">
            <!-- Library Image Card -->
            <div class="library-image-card">
                <div class="library-image-content">
                    <div>
                       
                        <h3> Library Management System</h3>
                       
                        <img src="/php_program/Barrowing_system/Images/efficiency.png" alt="Library Efficiency" class="welcome-image">
                    </div>
                </div>
            </div>

            <!-- Feature Grid -->
            <div class="feature-grid">
                <div class="feature-card blue">
                    <div class="feature-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3>Registration</h3>
                    <p>Effortlessly register new members with our intuitive Registration feature.</p>
                </div>
                <div class="feature-card green">
                    <div class="feature-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Book Management</h3>
                    <p>Add books, update records, and maintain an accessible catalog with ease.</p>
                </div>
                <div class="feature-card purple">
                    <div class="feature-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3>Transaction Insights</h3>
                    <p>Monitor borrowing patterns with comprehensive book and borrow history views.</p>
                </div>
                <div class="feature-card red">
                    <div class="feature-icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <h3>Member Oversight</h3>
                    <p>Efficiently manage blacklisted members to ensure a responsible borrowing environment.</p>
                </div>
                <div class="feature-card indigo">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Reporting Tools</h3>
                    <p>Generate detailed transaction reports to track library usage and trends.</p>
                </div>
                <div class="feature-card teal">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Member Management</h3>
                    <p>Manage your library members easily and efficiently.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <!-- Get In Touch -->
            <div class="footer-section">
                <h3>Get In Touch</h3>
                <img src="/php_program/Barrowing_system/Images/download.png" alt="University of Eswatini Library Logo">
                <div class="footer-logo-section">      
                </div>
                <p>Kwaluseni, Luyengo & Mbabane</p>
                <p><i class="fas fa-phone"></i> 2517 0448</p>
                <p><i class="fas fa-envelope"></i> library@uniswa.sz</p>
            </div>

            <!-- Quick Links -->
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="#">Eswatini National Bibliography</a>
                <a href="#">UNESWA IR</a>
                <a href="#">Notices</a>
                <a href="#">Past Exam Papers</a>
                <a href="#">UNESWA</a>
            </div>

            <!-- Popular Databases -->
            <div class="footer-section">
                <h3>Popular Databases</h3>
                <a href="#">Science Direct</a>
                <a href="#">Ebscohost</a>
                <a href="#">ERIC</a>
                <a href="#">Taylor & Francis</a>
                <a href="#">Sabinet</a>
            </div>

            <!-- Follow Us -->
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            © <?php echo date('Y'); ?> University of Eswatini Library | All Rights Reserved
        </div>
    </footer>
</body>
</html>