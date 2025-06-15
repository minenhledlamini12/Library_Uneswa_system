<?php
session_start();

// --- Welcome Message with Name and Surname ---
$welcomeMessage = "";
if (isset($_SESSION['username'])) {
    // Database config
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "library";

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        $welcomeMessage = "<p class='text-white bg-red-800 px-4 py-2 rounded-lg'>Database connection failed!</p>";
    } else {
        $email = $conn->real_escape_string($_SESSION['username']);
        $sql = "SELECT Name, Surname FROM members WHERE Email = '$email' LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $name = htmlspecialchars($row['Name']);
            $surname = htmlspecialchars($row['Surname']);
            $welcomeMessage = "<p class='text-white bg-blue-800 px-4 py-2 rounded-lg'>Welcome, $name $surname!</p>";
        } else {
            $welcomeMessage = "<p class='text-white bg-blue-800 px-4 py-2 rounded-lg'>Welcome, " . htmlspecialchars($_SESSION['username']) . "!</p>";
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remote Door Control</title>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
        }
        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            max-width: 500px;
            width: 100%;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
            font-size: 1.5rem;
            text-align: center;
            padding: 20px;
            border-bottom: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-header i {
            margin-right: 10px;
        }
        .card-body {
            padding: 30px;
            text-align: center;
        }
        .disabled-img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .disabled-img:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            background-color: #6c757d;
            color: white;
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            margin: 10px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-flex;
            align-items: center;
        }
        .btn-custom:hover {
            background-color: #5a6268;
            transform: scale(1.05);
        }
        .btn-custom i {
            margin-right: 8px;
        }
        .btn-led {
            background-color: #007bff;
        }
        .btn-led:hover {
            background-color: #0056b3;
        }
        .btn-led.blinking {
            animation: blink-animation 1s steps(2, start) infinite;
        }
        @keyframes blink-animation {
            to {
                opacity: 0;
            }
        }
        .alert {
            margin-top: 20px;
            border-radius: 8px;
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeInSection {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeInSection 1s ease-out forwards;
        }
        .hover-scale {
            transition: transform 0.3s ease;
        }
        .hover-scale:hover {
            transform: scale(1.05);
        }
        .nav-link {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .nav-link:hover {
            background-color: #1e40af;
            color: #fff;
            border-radius: 0.375rem;
        }
        .social-icon {
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .social-icon:hover {
            color: #60a5fa;
            transform: translateY(-3px);
        }
        .back-button {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .back-button:hover {
            background-color: #1e40af;
            transform: translateY(-2px);
        }
        .footer-link {
            transition: color 0.3s ease;
        }
        .footer-link:hover {
            color: #60a5fa;
        }
        .footer-heading {
            color: #f97316;
        }
        .form-select {
            max-width: 200px;
            margin: 0 auto;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            display: block;
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
            <?php echo $welcomeMessage; ?>
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
    <div class="content">
        <div class="card fade-in">
            <div class="card-header">
                 Remote Door Control
            </div>
            <div class="card-body">
                <img src="disabled.jpg" alt="Disabled Person" class="disabled-img">
                <!-- Door Selector -->
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="door_select" class="form-label">Select Door:</label>
                        <select name="door_select" id="door_select" class="form-select">
                            <option value="entry" <?php if(isset($_POST['door_select']) && $_POST['door_select']=='entry') echo 'selected'; ?>>Entry</option>
                            <option value="exit" <?php if(isset($_POST['door_select']) && $_POST['door_select']=='exit') echo 'selected'; ?>>Exit</option>
                        </select>
                    </div>
                    <!-- Door Control Button -->
                    <button type="submit" name="open_door" class="btn btn-custom">
                        <i class="fas fa-sign-in-alt"></i> Open Door
                    </button>
                    <!-- LED Control Button -->
                    <button id="led-button" class="btn btn-custom btn-led" onclick="toggleLed(event)">
                        <i class="fas fa-lightbulb"></i> Toggle LED
                    </button>
                </form>

                <!-- Status Message -->
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['open_door'])) {
                    $entry_ip = "192.168.15.69";
                    $exit_ip  = "192.168.15.149";
                    $door = isset($_POST['door_select']) ? $_POST['door_select'] : 'entry';
                    $esp32_ip = ($door == "exit") ? $exit_ip : $entry_ip;
                    $url = "http://$esp32_ip/open_door_and_turn_off_led";
                    $response = @file_get_contents($url);

                    if ($response !== false) {
                        echo "<div class='alert alert-success' role='alert'>
                                LED turned off and door opened successfully on <b>" . ucfirst($door) . "</b>!
                              </div>";
                    } else {
                        echo "<div class='alert alert-danger' role='alert'>
                                Failed to open door. Please try again.
                              </div>";
                    }
                }
                ?>

                <!-- Back Button -->
                <div class="text-center mt-6">
                    <a href="homepage.php" class="back-button bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">
                        <i class="fas fa-home mr-2"></i> Back to Homepage
                    </a>
                </div>
            </div>
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
    <script>
        let isLedOn = false;
        function getSelectedIp() {
            const entry_ip = "192.168.15.69";
            const exit_ip  = "192.168.15.149";
            const select = document.getElementById("door_select");
            return (select && select.value === "exit") ? exit_ip : entry_ip;
        }
        function toggleLed(event) {
            event.preventDefault();
            const ledButton = document.getElementById("led-button");
            const esp32_ip = getSelectedIp();
            fetch(`http://${esp32_ip}/toggle_led`)
                .then(response => response.text())
                .then(data => {
                    isLedOn = !isLedOn;
                    ledButton.classList.toggle("blinking", isLedOn);
                })
                .catch(error => {
                    alert("Failed to toggle LED. Please check your connection.");
                });
        }
    </script>
</body>
</html>
