<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Footer</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    /* Footer Styles */
    footer {
      background-color: #4CAF50; /* Green - Matching Header */
      color: white;
      padding: 20px 0;
      font-size: 0.9em;
    }

    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
    }

    .footer-section {
      margin-bottom: 20px;
    }

    .footer-section h3 {
      margin-top: 0;
      margin-bottom: 10px;
    }

    .footer-section ul {
      list-style: none;
      padding: 0;
    }

    .footer-section li {
      margin-bottom: 5px;
    }

    .footer-section a {
      color: white;
      text-decoration: none;
    }

    .footer-section a:hover {
      text-decoration: underline;
    }

    .footer-bottom {
      text-align: center;
      padding-top: 10px;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
      font-size: 0.8em;
    }

    /* "Get In Touch" Section */
    .get-in-touch img {
      height: 50px; /* Adjust as needed */
      margin-bottom: 10px;
    }

    .get-in-touch p {
      margin-bottom: 5px;
    }

    /* "Follow Us" Section */
    .follow-us a {
      display: inline-block;
      margin-right: 10px;
      font-size: 1.2em;
      color: white;
    }
  </style>
</head>
<body>

  <footer>
    <div class="footer-container">

      <div class="footer-section get-in-touch">
        <h3>Get In Touch</h3>
        <img src="/php_program/Barrowing_system/Images/download.png" alt="University of Eswatini Library Logo">
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
      &copy; <?php echo date("Y"); ?> University of Eswatini Library | All Rights Reserved.
    </div>
  </footer>

</body>
</html>
