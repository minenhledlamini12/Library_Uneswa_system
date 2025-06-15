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
    <title>Book Return Scanner | UNESWA Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
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

        #qr-video {
            width: 80%;
            max-width: 600px;
            border: 2px solid #ccc;
            border-radius: 8px;
            margin: 0 auto 20px;
            display: block;
        }

        #loading {
            display: none;
            margin: 20px auto;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            color: #333;
            font-weight: bold;
        }

        .error {
            color: #dc3545;
            font-weight: bold;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 5px;
            margin: 10px 0;
        }

        .success {
            color: #28a745;
            font-weight: bold;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 5px;
            margin: 10px 0;
        }

        #result {
            margin: 20px auto;
            max-width: 600px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin: 10px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: #007bff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        #status-message {
            margin-top: 10px;
            font-style: italic;
            color: #666;
            text-align: center;
        }

        #scan-again {
            display: none;
        }

        #debug-info {
            margin: 20px auto;
            max-width: 600px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: left;
            font-family: monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
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

            #qr-video {
                width: 100%;
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
                <a href="return.php" class="active"><i class="fas fa-undo icon"></i> Return Book</a>
                <a href="library_regulations.php"><i class="fas fa-file-alt icon"></i> Library Regulations</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="container">
            <h1 class="page-title">Book Return Scanner</h1>
            <p class="welcome-message">
                Welcome, <?php echo htmlspecialchars($_SESSION['Name'] . ' ' . $_SESSION['Surname']); ?>! | 
                <?php echo htmlspecialchars($_SESSION['Membership_type']); ?> Member | 
                <?php echo htmlspecialchars($department); ?>
            </p>
            <video id="qr-video" playsinline></video>
            <div id="status-message">Waiting for camera access...</div>
            <div id="loading"><div class="spinner"></div>Processing...</div>
            <div id="result"></div>
            <button id="scan-again" onclick="resetScanner()">Scan Another Book</button>
            <div id="debug-info"></div>
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
        const video = document.getElementById('qr-video');
        const resultElement = document.getElementById('result');
        const loadingElement = document.getElementById('loading');
        const statusMessage = document.getElementById('status-message');
        const scanAgainButton = document.getElementById('scan-again');
        const debugInfo = document.getElementById('debug-info');
        let stream = null;

        // Log debug messages
        function logDebug(message) {
            console.log(message);
            debugInfo.innerHTML += `[${new Date().toISOString()}] ${message}<br>`;
            debugInfo.scrollTop = debugInfo.scrollHeight;
        }

        // Initialize camera
        async function initCamera() {
            try {
                statusMessage.textContent = 'Accessing camera...';
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                });
                video.srcObject = stream;
                video.play();
                statusMessage.textContent = 'Camera active. Scanning for QR code...';
                scan();
            } catch (err) {
                logDebug(`Camera access failed: ${err.message}`);
                statusMessage.textContent = 'Camera access failed. Please check permissions.';
                resultElement.innerHTML = `<p class="error">Error accessing camera: ${err.message}</p>`;
                scanAgainButton.style.display = 'block';
            }
        }

        // Scan QR code
        function scan() {
            if (video.readyState !== video.HAVE_ENOUGH_DATA) {
                requestAnimationFrame(scan);
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'dontInvert'
            });

            if (code) {
                logDebug(`QR code detected: ${code.data}`);
                statusMessage.textContent = 'QR code detected! Verifying...';
                stopCamera();
                verifyBookQrCode(code.data);
            } else {
                requestAnimationFrame(scan);
            }
        }

        // Stop camera
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }

        // Verify QR code
        async function verifyBookQrCode(qrCodeData) {
            loadingElement.style.display = 'block';
            resultElement.innerHTML = '';

            const timeout = setTimeout(() => {
                loadingElement.style.display = 'none';
                statusMessage.textContent = 'Verification timed out.';
                resultElement.innerHTML = '<p class="error">Request timed out. Server might be busy.</p>';
                scanAgainButton.style.display = 'block';
            }, 30000);

            try {
                logDebug(`Sending verification request for QR code: ${qrCodeData}`);
                const response = await fetch('/php_program/Barrowing_system/User/return_handle.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `qr_code=${encodeURIComponent(qrCodeData)}`
                });
                clearTimeout(timeout);

                if (!response.ok) {
                    throw new Error(`Server error: ${response.status} ${response.statusText}`);
                }

                const text = await response.text();
                logDebug(`Raw response from return_handle.php: ${text}`);

                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error(`Invalid JSON response: ${text}`);
                }

                loadingElement.style.display = 'none';
                statusMessage.textContent = 'Verification complete.';

                if (data.success) {
                    resultElement.innerHTML = `<p class="success">${data.message} (Book ID: ${data.book_id})</p>`;
                    setTimeout(() => {
                        window.location.href = `return_success.php?book_id=${encodeURIComponent(data.book_id)}&return_date=${encodeURIComponent(data.return_date)}`;
                    }, 3000); // Increased delay to 3 seconds
                } else {
                    resultElement.innerHTML = `<p class="error">${data.message} ${data.book_id ? '(Book ID: ' + data.book_id + ')' : ''}</p>`;
                    scanAgainButton.style.display = 'block';
                }
            } catch (error) {
                clearTimeout(timeout);
                loadingElement.style.display = 'none';
                statusMessage.textContent = 'Error during verification.';
                resultElement.innerHTML = `<p class="error">Error verifying QR code: ${error.message}</p>`;
                scanAgainButton.style.display = 'block';
                logDebug(`Verification error: ${error.message}`);
            }
        }

        // Reset scanner
        function resetScanner() {
            stopCamera();
            resultElement.innerHTML = '';
            scanAgainButton.style.display = 'none';
            statusMessage.textContent = 'Initializing camera...';
            initCamera();
        }

        // Start scanning on load
        window.addEventListener('load', initCamera);
    </script>
</body>
</html>