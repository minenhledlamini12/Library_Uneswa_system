<?php
session_start();

if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after display
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>UNESWA Library</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #333;
            background-color: #f0fdf4;
            /* Light Green */
        }

        header {
            background-color: #4CAF50;
            /* Green */
            color: white;
            padding: 10px 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .top-bar {
            background-color: #388E3C;
            /* Darker Green */
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 5px;
            align-items: center;
        }

        .header-info {
            font-size: smaller;
        }

        .social-icons {
            display: flex;
        }

        .social-icons a {
            color: white;
            margin-left: 10px;
        }

        .bottom-bar {
            display: flex;
            align-items: center;
            width: 100%;
        }

        .logo {
            max-height: 60px;
            margin-right: 20px;
        }

        .site-title {
            font-size: 1.5em;
            white-space: nowrap;
        }

        .content {
            padding: 20px;
            background-size: cover;
            background-repeat: no-repeat;
            text-align: center;
            flex-grow: 1;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 10px;
            margin: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .content-container {
            /* New styles for the container */
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            justify-content: space-between;
            /* or space-around, space-evenly */
        }

        .text-side {
            flex: 1 1 50%;
            /* Adjust as needed */
            padding: 20px;
            text-align: left;
            /* Align text to the left */
        }

        .image-side {
            flex: 1 1 40%;
            /* Adjust as needed */
            text-align: center;
        }

        .content-image {
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* Responsive adjustments (optional) */
        @media (max-width: 768px) {
            .content-container {
                flex-direction: column;
                /* Stack vertically on small screens */
            }

            .text-side {
                text-align: center;
            }
        }

        footer {
            background-color: #808080;
            /* Grey */
            color: white;
            padding: 20px;
            text-align: center;
            width: 100%;
            margin-top: auto;
        }

        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            /* Distribute space evenly */
            max-width: 1200px;
            /* Adjust as needed */
            margin: 0 auto;
        }

        .footer-section {
            margin: 10px 20px;
            flex: 1 0 200px;
            /* Adjust min-width as needed */
            text-align: left;
        }

        .footer-section h3 {
            margin-bottom: 10px;
        }

        .footer-section a {
            color: white;
            text-decoration: none;
            display: block;
            /* Make links stack vertically */
            margin: 5px 0;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            /* Stack contact info vertically */
        }

        .footer-logo {
            max-height: 40px;
            /* Adjusted logo height in the footer */
            margin-bottom: 10px;
        }

        .social-icons {
            display: flex;
        }

        .social-icons a {
            color: white;
            text-decoration: none;
            margin-right: 10px;
            /* Space between icons */
            font-size: 1.2em;
            /* Adjust icon size as needed */
        }

        .footer-bottom {
            text-align: center;
            margin-top: 20px;
            /* Space between content and bottom text */
        }

        .footer-bottom p {
            margin: 5px 0;
            /* Space between bottom text lines */
        }

        .follow {
            display: flex;
            flex-direction: column;
        }

        .follow>div {
            margin-top: 10px;
        }

        /* Responsive adjustments (optional) */
        @media (max-width: 768px) {
            .footer-section {
                flex: 1 0 150px;
                /* Adjust min-width for smaller screens */
                text-align: center;
                /* Center text on smaller screens */
            }

            .footer-content {
                justify-content: center;
                /* Center sections on smaller screens */
            }
        }

        .qr-prompt {
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .welcome-message {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #004A89;
            /* UNESWA Blue */
        }

        .scan-button {
            background-color: #4CAF50;
            /* Green */
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.2em;
            margin-top: 20px;
            /* Add some space above the button */
        }

        .scan-button:hover {
            background-color: #388E3C;
            /* Darker Green */
        }

        /* Style for the video element */
        #qr-video {
            width: 300px;
            max-width: 100%;
            margin-bottom: 20px;
            border: 1px solid #ccc;
        }

        /* Style for the navigation links */
        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            background-color: #388E3C;
            /* Darker Green */
            color: white;
            padding: 10px;
        }

        .nav-links li {
            margin: 0 15px;
        }

        .nav-links a {
            text-decoration: none;
            color: white;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #66BB6A;
            /* Lighter Green */
        }
    </style>
</head>

<body>

    <header>
        <div class="top-bar">
            <div class="header-info">
                <span class="time" style="margin-right: 20px;">
                    <i class="far fa-clock"></i> Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM. Sun: 03:00
                    PM - 10:00 PM
                </span>
                <span class="contact">
                    <i class="fas fa-phone"></i> 2517 0448
                </span>
            </div>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <div class="bottom-bar">
            <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Logo" class="logo"> <span class="site-title">UNESWA Library</span>
        </div>
    </header>

   

    <div class="content">
       
            <p class="qr-prompt">Please scan your QR code to enter:</p>
        <!-- You might want to embed a QR code scanner here.  This typically requires JavaScript and a library. -->
        <!-- Placeholder for QR code scanner -->
        <div>
            <img src="/php_program/Barrowing_system/Images/staticqr.jpg" alt="QR Code Example" style="max-width:200px;">
            <p style="font-size: smaller;">(This is a QR code is on your Member card.)
            </p>
        </div>
        <!-- Add the button here -->
        <button class="scan-button" onclick="location.href='login_scanner.php'">Scan QR Code</button>

    </div>

    <footer>
        <div class="footer-content">

            <div class="footer-section get-in-touch">
                <h3>Get In Touch</h3>
                <img src="/php_program/Barrowing_system/Images/download.png" alt="University of Eswatini Library Logo" class="footer-logo">
                <p>Kwaluseni, Luyengo & Mbabane</p>
                <p><i class="fas fa-phone"></i> 2517 0448</p>
                <p><i class="fas fa-envelope"></i> <a href="mailto:library@uniswa.sz">library@uniswa.sz</a></p>
            </div>

            <div class="footer-section quick-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Eswatini National Bibliography</a></li>
                    <li><a href="#">UNESWA IR</a></li>
                    <li><a href="#">Notices</a></li>
                    <li><a href="#">Past Exam Papers</a></li>
                    <li><a href="#">UNESWA</a></li>
                </ul>
            </div>

            <div class="footer-section popular-databases">
                <h3>Popular Databases</h3>
                <ul>
                    <li><a href="#">Science Direct</a></li>
                    <li><a href="#">Ebscohost</a></li>
                    <li><a href="#">ERIC</a></li>
                    <li><a href="#">Taylor & Francis</a></li>
                    <li><a href="#">Sabinet</a></li>
                </ul>
            </div>

            <div class="footer-section follow-us">
                <h3>Follow Us</h3>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>

        </div>

        <div class="footer-bottom">
            &copy;
            <?php echo date("Y"); ?> University of Eswatini Library | All Rights Reserved.
        </div>
    </footer>

</body>

</html>
