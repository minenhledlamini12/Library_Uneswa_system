<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header("Location: homepage.php");
    exit();

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESWA Library - Login</title>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%); /* Soft cyan gradient */
        }
        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        .login-container {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            animation: fadeIn 1s ease-out;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h2 {
            font-size: 2.25rem; /* Increased from 1.75rem */
            color: #007bff;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        h2 i {
            margin-right: 10px;
        }
        .university-logo {
            max-width: 180px;
            margin-bottom: 20px;
            animation: pulse 2s infinite alternate;
        }
        @keyframes pulse {
            from { transform: scale(1); }
            to { transform: scale(1.05); }
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }
        .form-group:focus-within label {
            color: #007bff;
        }
        .input-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon {
            position: absolute;
            left: 15px;
            color: #9ca3af;
            transition: color 0.3s ease;
        }
        .form-group:focus-within .input-icon {
            color: #007bff;
        }
        .form-group input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: #f9fafb;
        }
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
            background-color: #fff;
        }
        .login-btn {
            background: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            width: 100%;
            margin-bottom: 15px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .login-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        .login-btn i {
            transition: transform 0.3s ease;
        }
        .login-btn:hover i {
            transform: translateX(5px);
        }
        .forgot-password a, .signup-text a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .forgot-password a:hover, .signup-text a:hover {
            color: #0056b3;
            transform: translateY(-1px);
        }
        .forgot-password i, .signup-text i {
            margin-right: 6px;
        }
        /* Hover scale effect */
        .hover-scale {
            transition: transform 0.3s ease;
        }
        .hover-scale:hover {
            transform: scale(1.05);
        }
        /* Social icon hover effect */
        .social-icon {
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .social-icon:hover {
            color: #60a5fa;
            transform: translateY(-3px);
        }
        /* Footer link hover */
        .footer-link {
            transition: color 0.3s ease;
        }
        .footer-link:hover {
            color: #60a5fa;
        }
        /* Footer heading color */
        .footer-heading {
            color: #f97316; /* Orange color */
        }
        /* Animation for form elements */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-group:nth-child(1) {
            animation: slideUp 0.4s 0.2s both;
        }
        .form-group:nth-child(2) {
            animation: slideUp 0.4s 0.4s both;
        }
        .login-btn {
            animation: slideUp 0.4s 0.6s both;
        }
        .forgot-password {
            animation: slideUp 0.4s 0.7s both;
        }
        .signup-text {
            animation: slideUp 0.4s 0.8s both;
        }
    </style>
</head>
<body>
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
                <div class="social-icons flex space-x-4">
                    <a href="#" class="social-icon"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>
            <div class="flex items-center">
                <img src="download.png" alt="UNESWA Logo" class="h-16 mr-4 hover-scale">
                <span class="text-2xl font-bold site-title">UNESWA Library</span>
            </div>
        </div>
    </header>

    <div class="content">
        <div class="login-container">
            <img src="download.png" alt="UNESWA Library Logo" class="university-logo">
            <h2><i class="fas fa-sign-in-alt"></i> Login to UNESWA Library</h2>
            
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-container">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-container">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">
                    Login <i class="fas fa-sign-in-alt"></i>
                </button>
            </form>
            
            <p class="forgot-password">
                <a href="forgot_password.php">
                    <i class="fas fa-question-circle"></i> Forgot Password?
                </a>
            </p>
            
            <p class="signup-text">
                <a href="signupform.php">
                    <i class="fas fa-user-plus"></i> Not registered? Sign up here
                </a>
            </p>
        </div>
    </div>

    <footer class="bg-gray-800 text-white py-6 px-6">
        <div class="container mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="footer-section">
                    <img src="download.png" alt="UNESWA Footer Logo" class="h-12 mb-4 hover-scale">
                    <h3 class="text-lg font-semibold mb-3 footer-heading flex items-center">
                        <i class="fas fa-envelope mr-2"></i> Get In Touch
                    </h3>
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
                        <li><a href="#" class="footer-link">Science Direct</a></li>
                        <li><a href="#" class="footer-link">Ebscohost</a></li>
                        <li><a href="#" class="footer-link">ERIC</a></li>
                        <li><a href="#" class="footer-link">Taylor & Francis</a></li>
                        <li><a href="#" class="footer-link">Sabinet</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3 class="text-lg font-semibold mb-3 footer-heading flex items-center">
                        <i class="fas fa-users mr-2"></i> Follow Us
                    </h3>
                    <ul class="space-y-1 text-sm">
                        <li class="flex items-center">
                            <i class="fab fa-twitter mr-2 social-icon"></i>
                            <a href="#" class="footer-link">Twitter</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fab fa-facebook mr-2 social-icon"></i>
                            <a href="#" class="footer-link">Facebook</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fab fa-instagram mr-2 social-icon"></i>
                            <a href="#" class="footer-link">Instagram</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom mt-6 text-center border-t border-gray-700 pt-4">
                <p class="text-sm">© 2025 UNESWA Library. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>