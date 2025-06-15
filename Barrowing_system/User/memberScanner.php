<?php
session_start();

require_once("connection.php"); // Include your database connection

// Encryption key (DO NOT HARDCODE IN PRODUCTION! Use environment variables or a secure method)
$encryptionKey = "YourSuperSecretKey"; // Replace with a strong, securely stored key

/**
 * Decrypt data using AES-256-CBC with a given key and initialization vector (IV).
 *
 * @param string $encryptedData The encrypted data to decrypt.
 * @param string $encryptionKey The encryption key.
 *
 * @return string|false The decrypted data, or false on failure.
 */
function decryptData(string $encryptedData, string $encryptionKey): string|false
{
    $cipher = "aes-256-cbc";
    $options = 0;
    $iv = substr(md5($encryptionKey), 0, 16);  //THIS IS INSECURE, USE A PROPER IV GENERATION
	try {
        $decryptedData = openssl_decrypt(
            base64_decode($encryptedData),
            $cipher,
            $encryptionKey,
            $options,
            $iv
        );
    } catch (Exception $e) {
        error_log("Decryption failed: " . $e->getMessage()); // Log the error
        return false;  // Return false if decryption fails
    }


    return $decryptedData;
}



if (isset($_POST['qrData'])) {
    $qrData = $_POST['qrData'];

    // 1. Decrypt the QR code data
    $decryptedData = decryptData($qrData, $encryptionKey);


    // 2.  Authenticate against the database
    $sql = "SELECT * FROM members WHERE email = ?"; //  Assuming memberID is stored in decrypted data and you have a 'members' table
    $stmt = $conn->prepare($sql);

    // Assuming the decrypted data contains the member ID, adjust accordingly
    $email = $decryptedData;  //Or extract from json or whatever format
    $stmt->bind_param("s", $email); // "s" for string, adjust if it's an integer

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();

        // Authentication successful
        $_SESSION['memberID'] = $member['memberID']; // Store member ID in session
        $_SESSION['name'] = $member['name']; //Store member name


        // Set a success message
        $_SESSION['message'] = "<div class='success'>Authentication Successful! Please verify your details.</div>";

        // Redirect to verification page
        header("Location: verify.php"); // Create verify.php to display member details and a verification button
        exit();


    } else {
        // Authentication failed
        $_SESSION['message'] = "<div class='error'>Access Denied.  Please register first.</div>";
        header("Location: index.php"); // Redirect to landing page/registration page
        exit();
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>UNESWA Library - QR Scanner</title>
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
            align-items: center;
            /* Center horizontally */
            justify-content: center;
            /* Center vertically */
            text-align: center;
            /* Ensure text is centered */
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

        /* Styles for video and canvas */
        #qr-video,
        #qr-canvas {
            width: 300px;
            max-width: 100%;
            margin-bottom: 20px;
            border: 1px solid #ccc;
        }

        /* Scan button style */
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
        }

        .scan-button:hover {
            background-color: #388E3C;
            /* Darker Green */
        }

        /* Message styles */
        .success {
            color: green;
            margin-top: 10px;
        }

        .error {
            color: red;
            margin-top: 10px;
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-section {
            margin: 10px 20px;
            flex: 1 0 200px;
            text-align: left;
        }

        .footer-section h3 {
            margin-bottom: 10px;
        }

        .footer-section a {
            color: white;
            text-decoration: none;
            display: block;
            margin: 5px 0;
        }

        .social-icons {
            display: flex;
        }

        .social-icons a {
            color: white;
            text-decoration: none;
            margin-right: 10px;
            font-size: 1.2em;
        }
    </style>
</head>

<body>

    <header>
        <div class="top-bar">
            <div class="header-info">
                <span class="time">
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
            <img src="download.png" alt="UNESWA Logo" class="logo">
            <span class="site-title">UNESWA Library</span>
        </div>
    </header>

    <div class="content">
        <h1>QR Code Scanner</h1>
        <video id="qr-video"></video>
        <canvas id="qr-canvas" style="display:none;"></canvas>
        <p id="scan-result"></p>

        <form id="qr-form" method="post" style="display:none;">
            <input type="hidden" id="qr-data" name="qrData">
        </form>

    </div>
    <footer>
        <div class="footer-content">

            <div class="footer-section get-in-touch">
                <h3>Get In Touch</h3>
                <img src="download.png" alt="University of Eswatini Library Logo">
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

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
        const video = document.getElementById("qr-video");
        const canvas = document.getElementById("qr-canvas");
        const canvasContext = canvas.getContext("2d");
        const resultPara = document.getElementById("scan-result");
        const qrForm = document.getElementById("qr-form");
        const qrDataInput = document.getElementById("qr-data");

        function startScanner() {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                .then(function (stream) {
                    video.srcObject = stream;
                    video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
                    video.play();
                    requestAnimationFrame(tick);
                })
                .catch(function (err) {
                    resultPara.innerText = "Error accessing camera: " + err;
                });
        }

        function tick() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.height = video.videoHeight;
                canvas.width = video.videoWidth;
                canvasContext.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });

                if (code) {
                    // QR code found
                    resultPara.innerText = "QR code detected. Processing...";
                    qrDataInput.value = code.data;
                    qrForm.submit(); // Submit the form to the PHP script
                    return; // Stop scanning after successful detection
                }
            }
            requestAnimationFrame(tick);
        }

        // Start the scanner when the page loads
        startScanner();
    </script>

</body>

</html>
