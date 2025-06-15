<?php
session_start();
require_once 'connection.php'; // Make sure this file defines $conn

// Prepare welcome message
$welcomeMsg = '';
if (isset($_SESSION['username'])) {
    $email = $_SESSION['username'];
    $sql = "SELECT First_name, Surname FROM admini WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($first_name, $surname);
    if ($stmt->fetch()) {
        $welcomeMsg = "Welcome, " . htmlspecialchars($first_name) . " " . htmlspecialchars($surname) . "!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESWA Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 1s ease-out forwards; }
        .hover-scale { transition: transform 0.3s ease; }
        .hover-scale:hover { transform: scale(1.05); }
        .nav-link { transition: background-color 0.3s ease, color 0.3s ease; }
        .nav-link:hover { background-color: #1e40af; color: #fff; border-radius: 0.375rem; }
        .social-icon { transition: color 0.3s ease, transform 0.3s ease; }
        .social-icon:hover { color: #60a5fa; transform: translateY(-3px); }
        .content-image { transition: transform 0.5s ease, box-shadow 0.5s ease; }
        .content-image:hover { transform: scale(1.02); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); }
        .footer-link { transition: color 0.3s ease; }
        .footer-link:hover { color: #60a5fa; }
        .footer-heading { color: #f97316; }
        .logout-btn { transition: background-color 0.3s; }
        .logout-btn:hover { background-color: #b91c1c; }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 text-gray-800">
    <header class="bg-blue-900 text-white py-4 px-6 shadow-lg">
        <div class="container mx-auto">
            <div class="flex justify-between items-center mb-4">
                <div class="header-info text-sm flex items-center space-x-4">
                    <span class="flex items-center">
                        <i class="far fa-clock mr-2"></i>
                        Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-phone mr-2"></i>
                        2517 0448
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="social-icons flex space-x-4">
                        <a href="#" class="social-icon"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                    <?php if (isset($_SESSION['username'])): ?>
                        <form action="logout.php" method="post" class="ml-4">
                            <button type="submit" class="logout-btn bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex items-center">
                <img src="download.png" alt="UNESWA Logo" class="h-16 mr-4 hover-scale">
                <span class="text-2xl font-bold site-title">UNESWA Library</span>
            </div>
        </div>
    </header>

    <nav class="bg-gray-800 text-white py-3 px-6 shadow-md">
        <ul class="flex justify-center space-x-6">
            <li><a href="about.php" class="nav-link px-4 py-2">About</a></li>
            <li><a href="form.php" class="nav-link px-4 py-2">Registration</a></li>
            <li><a href="control.php" class="nav-link px-4 py-2">Control</a></li>
            <li><a href="report.php" class="nav-link px-4 py-2">Report</a></li>
        </ul>
    </nav>

    <?php if ($welcomeMsg): ?>
        <div class="container mx-auto mt-4">
            <p class="text-white bg-blue-800 px-4 py-2 rounded-lg inline-block">
                <?php echo $welcomeMsg; ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="content flex-grow py-8 px-6">
        <div class="container mx-auto bg-white rounded-lg shadow-lg p-8 fade-in">
            <div class="flex flex-wrap items-center justify-between">
                <div class="text-side w-full md:w-1/2 p-6">
                    <h2 class="text-3xl font-semibold text-blue-900 mb-4 flex items-center">
                        <i class="fas fa-book-open mr-2"></i> Welcome to UNESWA Library
                    </h2>
                    <p class="text-gray-700 leading-relaxed">
                        Welcome to the University of Eswatini Library's modern access control system. Using QR code technology and ESP32 microcontrollers, we've streamlined entry to the Kwaluseni Library, enhancing security and efficiency for all users. This system ensures a seamless experience while maintaining the order and flow within our workspace.
                    </p>
                </div>
                <div class="image-side w-full md:w-2/5 p-6">
                    <img src="building-entrance.jpg" alt="Library Access Control" class="content-image rounded-lg w-full">
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-gray-800 text-white py-6 px-6">
        <div class="container mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="footer-section">
                    <h3 class="text-lg font-semibold mb-3 footer-heading flex items-center">
                        <i class="fas fa-envelope mr-2"></i> Get In Touch
                    </h3>
                    <img src="download.png" alt="UNESWA Footer Logo" class="h-12 mb-4 hover-scale">
                    <ul class="space-y-1 text-sm">
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <a href="#" class="footer-link">Kwaluseni, Luyengo & Mbabane</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            <a href="#" class="footer-link">2517 0448</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            <a href="mailto:library@uniswa.sz" class="footer-link">library@uniswa.sz</a>
                        </li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3 class="text-lg font-semibold mb-3 footer-heading flex items-center">
                        <i class="fas fa-link mr-2"></i> Quick Links
                    </h3>
                    <ul class="space-y-1 text-sm">
                        <li><a href="#" class="footer-link">Eswatini National Bibliography</a></li>
                        <li><a href="#" class="footer-link">UNESWA IR</a></li>
                        <li><a href="#" class="footer-link">Notices</a></li>
                        <li><a href="#" class="footer-link">Past Exam Papers</a></li>
                        <li><a href="#" class="footer-link">UNESWA</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3 class="text-lg font-semibold mb-3 footer-heading flex items-center">
                        <i class="fas fa-database mr-2"></i> Popular Databases
                    </h3>
                    <ul class="space-y-1 text-sm">
