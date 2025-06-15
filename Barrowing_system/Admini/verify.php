<?php
session_start();

// Redirect if not authenticated
if (!isset($_SESSION['memberID']) || !isset($_SESSION['name'])) {
    $_SESSION['message'] = "<div class='error'>Please scan your QR code first.</div>";
    header("Location: qr_scanner.php"); // Redirect to the scanner page
    exit();
}

$memberID = $_SESSION['memberID'];
$name = $_SESSION['name'];
$surname = $_SESSION['surname'];  //Retrieve the surname
$CourseDepartmentAffiliation = $_SESSION['CourseDepartmentAffiliation'];   //Retrieve the department
$MembershipType = $_SESSION['MembershipType']; //Retrive the membership
$Contact = $_SESSION['Contact'];   //Retrieve Contact
$Email = $_SESSION['Email'];   //Retrieve email


?>

<!DOCTYPE html>
<html>

<head>
    <title>Verify Your Details</title>
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

        .verification-message {
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .details-table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        .details-table th,
        .details-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .details-table th {
            background-color: #f2f2f2;
        }

        .verify-button {
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

        .verify-button:hover {
            background-color: #388E3C;
            /* Darker Green */
        }

        /* Style for message display */
        .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        <h1>Verify Your Details</h1>

        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='message " . (strpos($_SESSION['message'], 'Access Denied') !== false ? 'error' : 'success') . "'>" . $_SESSION['message'] . "</div>";
            unset($_SESSION['message']); // Clear the message after displaying it
        }
        ?>

        <p class="verification-message">Please verify that the following details are correct:</p>

        <table class="details-table">
            <tr>
                <th>Member ID</th>
                <td>
                    <?php echo htmlspecialchars($memberID); ?>
                </td>
            </tr>
            <tr>
                <th>Name</th>
                <td>
                    <?php echo htmlspecialchars($name); ?>
                </td>
            </tr>
			 <tr>
                <th>Surname</th>
                <td>
                    <?php echo htmlspecialchars($surname); ?>
                </td>
            </tr>
			 <tr>
                <th>Course/Department/Affiliation</th>
                <td>
                    <?php echo htmlspecialchars($CourseDepartmentAffiliation); ?>
                </td>
            </tr>
			 <tr>
                <th>Membership Type</th>
                <td>
                    <?php echo htmlspecialchars($MembershipType); ?>
                </td>
            </tr>
			 <tr>
                <th>Contact</th>
                <td>
                    <?php echo htmlspecialchars($Contact); ?>
                </td>
            </tr>
			 <tr>
                <th>Email</th>
                <td>
                    <?php echo htmlspecialchars($Email); ?>
                </td>
            </tr>
            <!-- Add more details here as needed -->
        </table>

        <form action="index.php" method="post">
            <button type="submit" class="verify-button">Verify and Continue</button>
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

</body>

</html>
