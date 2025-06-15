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
$affiliation = $_SESSION['CourseDepartmentAffiliation'] ?? ''; // Corrected spelling
$department = !empty($affiliation) ? (count($parts = explode(' | ', $affiliation)) > 1 ? $parts[1] : $affiliation) : 'General';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UNESWA Library Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: #ffffff;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      color: #333;
      margin: 0;
    }
    .glass {
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid rgba(0, 0, 0, 0.1);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .fade-in {
      opacity: 0;
      animation: fadeIn 1s ease-in forwards;
    }
    @keyframes fadeIn {
      to { opacity: 1; }
    }
    .slide-in {
      opacity: 0;
      transform: translateY(20px);
      animation: slideIn 0.5s ease-out forwards;
    }
    @keyframes slideIn {
      to { opacity: 1; transform: translateY(0); }
    }
    .scale-up {
      transform: scale(0.95);
      transition: transform 0.3s ease;
    }
    .scale-up:hover {
      transform: scale(1);
    }
    .icon-hover {
      transition: transform 0.2s ease, color 0.2s ease;
    }
    .icon-hover:hover {
      transform: scale(1.2);
      color: #4CAF50;
    }
    .nav-link {
      transition: color 0.2s ease;
    }
    .nav-link:hover {
      color: #2E7D32;
    }
    .feature-card {
      opacity: 0;
      animation: slideIn 0.5s ease-out forwards;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .feature-card:nth-child(1) { animation-delay: 0.1s; }
    .feature-card:nth-child(2) { animation-delay: 0.2s; }
    .feature-card:nth-child(3) { animation-delay: 0.3s; }
    .icon-circle {
      background: #4CAF50;
      color: white;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .logout-btn {
      background-color: #d32f2f;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 0.375rem;
      font-weight: 600;
      transition: background-color 0.3s;
    }
    .logout-btn:hover {
      background-color: #b71c1c;
    }
    .welcome-banner {
      background-color: #e8f5e9;
      border-left: 4px solid #4CAF50;
      margin-bottom: 1.5rem;
      animation: fadeIn 0.8s ease-in forwards;
    }
  </style>
</head>
<body>
  <div class="top-bar bg-[#388E3C] text-white text-right py-3 px-6 text-sm fade-in">
    <span>
      <i class="far fa-clock mr-2"></i> Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM
      <i class="fas fa-phone ml-4 mr-2"></i> 2517 0448
      <a href="#" class="ml-2 icon-hover"><i class="fab fa-facebook"></i></a>
      <a href="#" class="ml-2 icon-hover"><i class="fab fa-twitter"></i></a>
      <a href="#" class="ml-2 icon-hover"><i class="fab fa-youtube"></i></a>
    </span>
  </div>

  <header class="bg-[#4CAF50] text-white py-4 px-6 flex items-center justify-between shadow-lg glass fade-in">
    <div class="header-left flex items-center">
      <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo" class="h-16 mr-4">
      <h1 class="text-2xl font-bold">University of Eswatini Library</h1>
    </div>
    <div class="header-right flex items-center">
      <div class="text-right text-lg mr-6">
        Kwaluseni Campus - Self-Service Book Borrowing
      </div>
      <a href="?action=logout" class="logout-btn">
        <i class="fas fa-sign-out-alt mr-1"></i> Logout
      </a>
    </div>
  </header>

  <nav class="bg-[#388E3C] py-4 px-6 shadow-md glass">
    <div class="flex justify-center space-x-8">
      <a href="search.php" class="text-white font-semibold text-lg nav-link slide-in"><i class="fas fa-search mr-2"></i> Search Book</a>
      <a href="barrowpage.php" class="text-white font-semibold text-lg nav-link slide-in"><i class="fas fa-book-open mr-2"></i> Borrow/Issue Book</a>
      <a href="return.php" class="text-white font-semibold text-lg nav-link slide-in"><i class="fas fa-undo mr-2"></i> Return Book</a>
      <a href="library_regulations.php" class="text-white font-semibold text-lg nav-link slide-in"><i class="fas fa-file-alt mr-2"></i> Library Regulations</a>
    </div>
  </nav>

  <main class="py-12 px-6 text-center">
    <!-- Welcome Banner -->
    <div class="welcome-banner max-w-4xl mx-auto p-4 rounded-lg mb-8">
      <h2 class="text-2xl font-bold text-[#2E7D32]">
        <i class="fas fa-user-circle mr-2"></i> Welcome, <?php echo htmlspecialchars($_SESSION['Name'] . ' ' . $_SESSION['Surname']); ?>!
      </h2>
      <p class="text-gray-700">
        <?php echo htmlspecialchars($_SESSION['Membership_type']); ?> Member | 
        <?php echo htmlspecialchars($department); ?>
      </p>
    </div>
    
    <section class="portal-description max-w-4xl mx-auto bg-white rounded-xl p-8 shadow-xl glass slide-in">
      <h2 class="text-3xl font-bold text-[#2E7D32] mb-6">Book Borrowing Portal</h2>
      <p class="text-lg text-gray-700 leading-relaxed mb-6">
        Welcome to the Member Book Borrowing Portal, a user-friendly system designed to enhance your reading experience by allowing you to efficiently 
        borrow books through a self-service kiosk. 
      </p>
      <img class="system-image w-full max-w-2xl mx-auto rounded-lg shadow-lg mb-6 scale-up" src="/php_program/Barrowing_system/Images/barrowing.jpg" alt="Smart Borrowing System">
      
      <h2 class="text-3xl font-bold text-[#2E7D32] mb-6">Key Features</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
        <div class="feature-card bg-white rounded-lg p-6 border border-[#4CAF50]/20">
          <div class="flex items-start">
            <div class="icon-circle mr-4">
              <i class="fas fa-search text-xl"></i>
            </div>
            <div class="text-left">
              <h3 class="text-lg font-semibold text-[#2E7D32]">Quick Search</h3>
              <p class="text-gray-700">Our advanced search feature enables you to easily locate books within the library's collection, ensuring you find exactly what you're looking for in no time.</p>
            </div>
          </div>
        </div>
        <div class="feature-card bg-white rounded-lg p-6 border border-[#4CAF50]/20">
          <div class="flex items-start">
            <div class="icon-circle mr-4">
              <i class="fas fa-book-open text-xl"></i>
            </div>
            <div class="text-left">
              <h3 class="text-lg font-semibold text-[#2E7D32]">Effortless Borrowing</h3>
              <p class="text-gray-700">Borrowing books is a breeze!</p>
            </div>
          </div>
        </div>
        <div class="feature-card bg-white rounded-lg p-6 border border-[#4CAF50]/20">
          <div class="flex items-start">
            <div class="icon-circle mr-4">
              <i class="fas fa-chart-line text-xl"></i>
            </div>
            <div class="text-left">
              <h3 class="text-lg font-semibold text-[#2E7D32]">Real-time Tracking</h3>
              <p class="text-gray-700">Stay informed about your borrowing activities with real-time tracking and keep track of due dates to ensure timely returns.</p>
            </div>
          </div>
        </div>
      </div>
      <p class="text-lg text-gray-700 leading-relaxed mt-6">
        Join us in revolutionizing the way you borrow books and enjoy a seamless, efficient, and enjoyable reading experience!
      </p>
    </section>
  </main>

  <footer class="bg-[#388E3C] text-white py-12 px-6">
    <div class="footer-container max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
      <div class="footer-section get-in-touch">
        <h3 class="text-xl font-semibold mb-4">Get In Touch</h3>
        <img src="/php_program/Barrowing_system/Images/download.png" alt="University of Eswatini Library Logo" class="h-12 mb-4">
        <p class="text-gray-200">Kwaluseni, Luyengo & Mbabane</p>
        <p class="text-gray-200"><i class="fas fa-phone mr-2"></i> 2517 0448</p>
        <p class="text-gray-200"><i class="fas fa-envelope mr-2"></i> <a href="mailto:library@uniswa.sz" class="hover:text-[#4CAF50] transition">library@uniswa.sz</a></p>
      </div>
      <div class="footer-section quick-links">
        <h3 class="text-xl font-semibold mb-4">Quick Links</h3>
        <ul class="text-gray-200">
          <li class="mb-2"><a href="#" class="hover:text-[#4CAF50] transition">Eswatini National Bibliography</a></li>
          <li class="mb-2"><a href="#" class="hover:text-[#4CAF50] transition">UNESWA IR</a></li>
          <li class="mb-2"><a href="#" class="hover:text-[#4CAF50] transition">Notices</a></li>
          <li class="mb-2"><a href="#" class="hover:text-[#4CAF50] transition">Past Exam Papers</a></li>
          <li class="mb-2"><a href="#" class="hover:text-[#4CAF50] transition">UNESWA</a></li>
        </ul>
      </div>
      <div class="footer-section popular-databases">
        <h3 class="text-xl font-semibold mb-4">Popular Databases</h3>
        <ul class="text-gray-200">
          <li class="mb-2"><a href="#" class="hover:text-[#4CAF50] transition">Science Direct</a></li>
          <li class="mb-2"><a href="#" class="hover:text-[#4CAF50] transition">Ebscohost</a></li>
          <li class="mb-2"><a href="#" class="hover:text-[#4CAF50] transition">ERIC</a></li>
          <li class="mb-2"><a href="#" class="hover:text-[#4CAF50] transition">Taylor & Francis</a></li>
          <li class="mb-2"><a href="#" class="hover:text-[#4CAF50] transition">Sabinet</a></li>
        </ul>
      </div>
      <div class="footer-section follow-us">
        <h3 class="text-xl font-semibold mb-4">Follow Us</h3>
        <div class="flex space-x-4">
          <a href="#" class="text-2xl icon-hover"><i class="fab fa-twitter"></i></a>
          <a href="#" class="text-2xl icon-hover"><i class="fab fa-facebook"></i></a>
          <a href="#" class="text-2xl icon-hover"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
    </div>
    <div class="footer-bottom text-center pt-8 border-t border-gray-700 mt-8 text-gray-200">
      © <?php echo date("Y"); ?> University of Eswatini Library | All Rights Reserved.
    </div>
  </footer>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>