<?php
session_start();

if (isset($_SESSION['username'])) {
    $welcomeMessage = "<p class='text-white bg-blue-800 px-4 py-2 rounded-lg'>Welcome, " . $_SESSION['username'] . "!</p>";
} else {
    $welcomeMessage = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Our Library System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Fade-in animation for content */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 1s ease-out forwards;
        }

        /* Hover scale effect */
        .hover-scale {
            transition: transform 0.3s ease;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }

        /* Smooth nav link hover */
        .nav-link {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .nav-link:hover {
            background-color: #1e40af;
            color: #fff;
            border-radius: 0.375rem;
        }

        /* Content image animation */
        .frame-img {
            transition: transform 0.5s ease, box-shadow 0.5s ease;
        }

        .frame-img:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Footer heading color */
        .footer-heading {
            color: #f97316; /* Orange color from the image */
        }

        /* Back button hover */
        .back-button {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .back-button:hover {
            background-color: #1e40af;
            transform: translateY(-2px);
        }
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

    <div class="content flex-grow py-8 px-6">
        <div class="container mx-auto">
            <div class="about-section bg-white rounded-lg shadow-lg p-6 fade-in">
                <div class="frame flex flex-col md:flex-row mb-6 border border-gray-200 p-4 rounded-lg bg-gray-50">
                    <img src="accessible-library-entrance-stockcake.jpg" alt="QR Code System" class="frame-img w-full md:w-2/5 h-auto rounded-lg object-cover">
                    <div class="text w-full md:w-3/5 p-4">
                        <h2 class="text-2xl font-semibold text-blue-900 mb-3 flex items-center">
                            <i class="fas fa-qrcode mr-2"></i> QR Code Library Access Control System
                        </h2>
                        <p class="text-gray-700 leading-relaxed">
                            This web application powers a modern and secure access control system designed specifically for the University of Eswatini (UNESWA) Library. It utilizes QR code technology to streamline entry for registered members and provides an inclusive solution for disabled members, ensuring compliance with library regulations.
                        </p>
                        <h3 class="text-xl text-blue-800 mt-4 mb-2 flex items-center">
                            <i class="fas fa-bullseye mr-2"></i> Objectives:
                        </h3>
                        <ul class="list-none space-y-2">
                            <li class="flex items-center"><i class="fas fa-check-circle text-blue-600 mr-2"></i> Enhance access control management</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-blue-600 mr-2"></i> Accessibility for Disabled Members</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-blue-600 mr-2"></i> Inclusive system for all users: students, staff, external members</li>
                            <li class="flex items-center"><i class="fas fa-check-circle text-blue-600 mr-2"></i> Compliance with UNESWA Library Regulations</li>
                        </ul>
                    </div>
                </div>

                <div class="frame flex flex-col md:flex-row-reverse mb-6 border border-gray-200 p-4 rounded-lg bg-gray-50">
                    <img src="PAGE-3-UNESWA-Library-in-the-main-Campus-in-Kwaluseni.jpg" alt="UNESWA Library" class="frame-img w-full md:w-2/5 h-auto rounded-lg object-cover">
                    <div class="text w-full md:w-3/5 p-4">
                        <h3 class="text-xl text-blue-800 mb-2 flex items-center">
                            <i class="fas fa-university mr-2"></i> The University of Eswatini Library
                        </h3>
                        <p class="text-gray-700 leading-relaxed">
                            <strong>General Description</strong><br>
                            The University of Eswatini Library consists of three decentralised units, each with its staff and stock, coordinated at Kwaluseni. The stock of each unit is available to the other Libraries through an internal loan system. The stock consists chiefly of undergraduate textbooks, journals, reference materials and special collections.
                        </p>
                        <p class="text-gray-700 leading-relaxed mt-2">
                            <strong>The Kwaluseni Library</strong><br>
                            This is the main library, serving the Faculties of Commerce, Education, Humanities, Science & Engineering, Social Sciences, the Institute of Distance Education, and the Institute of Post-Graduate Studies. The library has a sitting capacity of over 700 users at a time with separate study carrels for post-graduate students and lecturers.
                        </p>
                    </div>
                </div>

                <div class="frame flex flex-col md:flex-row mb-6 border border-gray-200 p-4 rounded-lg bg-gray-50">
                    <img src="smart-libraries-1040x555.jpg" alt="Library Services" class="frame-img w-full md:w-2/5 h-auto rounded-lg object-cover">
                    <div class="text w-full md:w-3/5 p-4">
                        <h3 class="text-xl text-blue-800 mb-2 flex items-center">
                            <i class="fas fa-concierge-bell mr-2"></i> Key Services:
                        </h3>
                        <ul class="list-none space-y-2">
                            <li class="flex items-center"><i class="fas fa-print text-blue-600 mr-2"></i> Print, Copy & Scan</li>
                            <li class="flex items-center"><i class="fas fa-desktop text-blue-600 mr-2"></i> Computer labs & Apple Hub of Creativity</li>
                            <li class="flex items-center"><i class="fas fa-book text-blue-600 mr-2"></i> Growing collection of e-resources and e-books</li>
                            <li class="flex items-center"><i class="fas fa-archive text-blue-600 mr-2"></i> Academic Reserves</li>
                            <li class="flex items-center"><i class="fas fa-exchange-alt text-blue-600 mr-2"></i> Inter-library loans</li>
                            <li class="flex items-center"><i class="fas fa-search text-blue-600 mr-2"></i> Research support</li>
                            <li class="flex items-center"><i class="fas fa-id-card text-blue-600 mr-2"></i> ISBN Subscription</li>
                            <li class="flex items-center"><i class="fas fa-users text-blue-600 mr-2"></i> External Membership (Individuals and institutions)</li>
                        </ul>
                    </div>
                </div>

                <div class="frame flex flex-col md:flex-row-reverse mb-6 border border-gray-200 p-4 rounded-lg bg-gray-50">
                    <img src="1_W6GCc2m-xKRfIH_LngzGjw.png" alt="How It Works" class="frame-img w-full md:w-2/5 h-auto rounded-lg object-cover">
                    <div class="text w-full md:w-3/5 p-4">
                        <h3 class="text-xl text-blue-800 mb-2 flex items-center">
                            <i class="fas fa-cogs mr-2"></i> How It Works:
                        </h3>
                        <ol class="list-decimal pl-5 space-y-2">
                            <li><strong>QR Code Scanning:</strong> When a member approaches the library entrance, an LCD screen prompts them to scan their unique QR code.</li>
                            <li><strong>Authentication:</strong> The member scans the QR code using the integrated ESP32 camera. The encoded details are then transmitted to a local database for real-time authentication.</li>
                            <li><strong>Access Granted/Denied:</strong>
                                <ul class="list-disc pl-5 mt-1">
                                    <li>If the member's details match a valid entry in the database, access is granted. An electromagnetic door automatically opens and closes after a short delay.</li>
                                    <li>If the QR code is not recognized, access is denied, preventing unauthorized entry.</li>
                                </ul>
                            </li>
                            <li><strong>Attendance Tracking:</strong> The system automatically records timestamps and attendance data for each authorized entry, providing valuable insights for library management accessible via the admin panel.</li>
                            <li><strong>Accessibility for Disabled Members:</strong> To ensure inclusivity, disabled members can use a touch detector sensor at the entrance. This triggers an alert on the librarian's web application, allowing them to remotely open the door.</li>
                        </ol>
                    </div>
                </div>

                <div class="frame flex flex-col md:flex-row mb-6 border border-gray-200 p-4 rounded-lg bg-gray-50">
                    <img src="istockphoto-1288429212-612x612.jpg" alt="Web Application Features" class="frame-img w-full md:w-2/5 h-auto rounded-lg object-cover">
                    <div class="text w-full md:w-3/5 p-4">
                        <h3 class="text-xl text-blue-800 mb-2 flex items-center">
                            <i class="fas fa-laptop-code mr-2"></i> Web Application Features (For Librarians/Administrators):
                        </h3>
                        <ul class="list-none space-y-2">
                            <li class="flex items-center"><i class="fas fa-user-plus text-blue-600 mr-2"></i> <strong>User Registration:</strong> Librarians can easily register new members and generate unique QR codes for them.</li>
                            <li class="flex items-center"><i class="fas fa-clipboard-list text-blue-600 mr-2"></i> <strong>Access Log Management:</strong> View and manage detailed access logs, including entry times and user information.</li>
                            <li class="flex items-center"><i class="fas fa-door-open text-blue-600 mr-2"></i> <strong>Remote Door Control:</strong> Open the door remotely for disabled members via the touch sensor alert.</li>
                        </ul>
                    </div>
                </div>

                <div class="text-center mt-6">
                    <a href="homepage.php" class="back-button bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700">Back to Homepage</a>
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