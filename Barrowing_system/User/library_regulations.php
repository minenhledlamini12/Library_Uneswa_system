<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UNESWA Library Regulations</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    /* Base Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Arial', sans-serif;
      line-height: 1.6;
      min-height: 100vh;
      background: linear-gradient(to bottom, #e6f7e9, #e6f0f5);
    }
    
    .container {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }
    
    a {
      text-decoration: none;
    }
    
    ul {
      list-style: none;
    }
    
    /* Top Bar */
    .top-bar {
      background-color: #003366;
      color: white;
      padding: 8px 15px;
      font-size: 0.875rem;
    }
    
    .top-bar-content {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      align-items: center;
    }
    
    @media (min-width: 768px) {
      .top-bar-content {
        flex-direction: row;
      }
    }
    
    .top-bar-left {
      display: flex;
      align-items: center;
      margin-bottom: 8px;
    }
    
    @media (min-width: 768px) {
      .top-bar-left {
        margin-bottom: 0;
      }
    }
    
    .top-bar-right {
      display: flex;
      align-items: center;
    }
    
    .top-bar-right a {
      color: white;
      margin-left: 16px;
      transition: color 0.2s;
    }
    
    .top-bar-right a:hover {
      color: #8eeea8;
    }
    
    .icon {
      margin-right: 8px;
    }
    
    /* Header Main */
    .header-main {
      background-color: #4CAF50;
      color: white;
      padding: 16px 0;
    }
    
    .header-content {
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      align-items: center;
    }
    
    @media (min-width: 768px) {
      .header-content {
        flex-direction: row;
      }
    }
    
    .header-logo {
      height: 48px;
      width: 48px;
      margin-bottom: 12px;
      background-color: white;
      border-radius: 50%;
      padding: 4px;
    }
    
    @media (min-width: 768px) {
      .header-logo {
        margin-bottom: 0;
      }
    }
    
    .header-title {
      font-size: 1.5rem;
      font-weight: bold;
      text-align: center;
      margin-bottom: 8px;
    }
    
    @media (min-width: 768px) {
      .header-title {
        font-size: 1.875rem;
        text-align: left;
        margin-bottom: 0;
      }
    }
    
    .header-subtitle {
      font-style: italic;
      text-align: center;
    }
    
    @media (min-width: 768px) {
      .header-subtitle {
        text-align: right;
      }
    }
    
    /* Back Button */
    .back-button {
      display: inline-flex;
      align-items: center;
      padding: 8px 16px;
      background-color: #4CAF50;
      color: white;
      border-radius: 4px;
      margin-top: 24px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: background-color 0.2s;
    }
    
    .back-button:hover {
      background-color: #3e8e41;
    }
    
    .back-button i {
      margin-right: 8px;
    }
    
    /* Main Content */
    .main-content {
      padding: 32px 0;
    }
    
    .regulations-card {
      max-width: 1024px;
      margin: 0 auto;
      background-color: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
      background: linear-gradient(to right, #4CAF50, #66bb6a);
      color: white;
      padding: 24px;
      text-align: center;
    }
    
    .card-header-icon {
      font-size: 3rem;
      margin-bottom: 8px;
    }
    
    .card-header-title {
      font-size: 1.5rem;
      font-weight: bold;
    }
    
    @media (min-width: 768px) {
      .card-header-title {
        font-size: 1.875rem;
      }
    }
    
    .card-body {
      padding: 24px;
    }
    
    @media (min-width: 768px) {
      .card-body {
        padding: 32px;
      }
    }
    
    /* Sections */
    .section {
      margin-bottom: 32px;
    }
    
    .section-header {
      display: flex;
      align-items: center;
      margin-bottom: 16px;
      padding-bottom: 8px;
      border-bottom: 1px solid #e2e8f0;
    }
    
    .section-icon {
      color: #4CAF50;
      margin-right: 8px;
      font-size: 1.5rem;
    }
    
    .section-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #4CAF50;
    }
    
    .section-list {
      padding-left: 16px;
    }
    
    .list-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 8px;
    }
    
    .list-bullet {
      color: #4CAF50;
      margin-right: 8px;
    }
    
    /* Borrowing Categories */
    .categories-box {
      background-color: #e8f5e9;
      padding: 16px;
      border-radius: 8px;
      margin-top: 8px;
    }
    
    .category-list {
      font-size: 0.875rem;
    }
    
    .category-item {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin-bottom: 4px;
    }
    
    .category-label {
      font-weight: 500;
    }
    
    /* Important Notice */
    .important-notice {
      background-color: #ffebee;
      border-left: 4px solid #ef5350;
      padding: 16px;
      border-radius: 0 8px 8px 0;
      margin-bottom: 16px;
    }
    
    .important-notice p {
      color: #c62828;
      font-weight: 500;
    }
    
    /* Table */
    .table-container {
      overflow-x: auto;
    }
    
    table {
      width: 100%;
      background-color: white;
      border-collapse: collapse;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
    }
    
    thead {
      background-color: #e8f5e9;
    }
    
    th, td {
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid #e2e8f0;
    }
    
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    
    /* Footer */
    .footer {
      background-color: #4CAF50;
      color: white;
      padding: 32px 0;
      margin-top: 48px;
    }
    
    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }
    
    .footer-content {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-around;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .footer-section {
      width: 100%;
      margin-bottom: 24px;
    }
    
    @media (min-width: 768px) {
      .footer-section {
        width: 25%;
        margin-bottom: 0;
      }
    }
    
    .footer-title {
      font-size: 1.125rem;
      font-weight: 600;
      margin-bottom: 12px;
    }
    
    .footer-logo {
      height: 48px;
      width: 48px;
      margin-bottom: 12px;
    }
    
    .footer-text {
      margin-bottom: 4px;
    }
    
    .footer-link {
      color: white;
      transition: text-decoration 0.2s;
    }
    
    .footer-link:hover {
      text-decoration: underline;
    }
    
    .footer-social {
      display: flex;
      gap: 16px;
    }
    
    .social-icon {
      font-size: 1.5rem;
      color: white;
      transition: color 0.2s;
    }
    
    .social-icon:hover {
      color: #e8f5e9;
    }
    
    .footer-bottom {
      text-align: center;
      padding-top: 24px;
      margin-top: 24px;
      border-top: 1px solid rgba(255, 255, 255, 0.4);
      font-size: 0.875rem;
    }
  </style>
</head>
<body>
  <!-- Top Bar -->
  <div class="top-bar">
    <div class="container">
      <div class="top-bar-content">
        <div class="top-bar-left">
          <i class="fas fa-clock icon"></i>
          <span>Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM</span>
        </div>
        <div class="top-bar-right">
          <i class="fas fa-phone icon"></i>
          <span>2517 0448</span>
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
    </div>
  </div>

  <!-- Header Main -->
  <div class="header-main">
    <div class="container">
      <div class="header-content">
        <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo" class="header-logo">
        <h1 class="header-title">University of Eswatini Library</h1>
        <span class="header-subtitle">Kwaluseni Campus - Self-Service Book Borrowing</span>
      </div>
    </div>
  </div>

  <!-- Back Button -->
  <div class="container">
    <a href="barrowpage.php" class="back-button">
      <i class="fas fa-arrow-left"></i>
      Back to Borrowing System
    </a>
  </div>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      <div class="regulations-card">
        <div class="card-header">
          <i class="fas fa-book card-header-icon"></i>
          <h2 class="card-header-title">UNESWA Library - Self-Service Borrowing System Regulations</h2>
        </div>
        
        <div class="card-body">
          <!-- Membership Section -->
          <section class="section">
            <div class="section-header">
              <i class="fas fa-users section-icon"></i>
              <h3 class="section-title">Membership</h3>
            </div>
            <ul class="section-list">
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>All registered students and staff are automatically library members.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>External members may be admitted upon approval by the Librarian and payment of the appropriate fee.</span>
              </li>
            </ul>
          </section>

          <!-- Use of the Library Section -->
          <section class="section">
            <div class="section-header">
              <i class="fas fa-gavel section-icon"></i>
              <h3 class="section-title">Use of the Library</h3>
            </div>
            <ul class="section-list">
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Valid University ID must be produced when requested.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Talking and noise are strictly prohibited, except in designated areas.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Smoking and eating are not permitted.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Bags, briefcases, raincoats, and umbrellas should be left at the foyer at the owner's risk.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Library materials must not be marked, defaced, or mutilated.</span>
              </li>
            </ul>
          </section>

          <!-- Borrowing Procedures Section -->
          <section class="section">
            <div class="section-header">
              <i class="fas fa-book section-icon"></i>
              <h3 class="section-title">Borrowing Procedures</h3>
            </div>
            <ul class="section-list">
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>A University/Library Identity Card is required to borrow books.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <div>
                  <p>The number of items to be borrowed and the loan period are categorized as follows:</p>
                  <div class="categories-box">
                    <ul class="category-list">
                      <li class="category-item">
                        <span class="category-label">Full-time students:</span>
                        <span>6 items for 14 days</span>
                      </li>
                      <li class="category-item">
                        <span class="category-label">Part-time students:</span>
                        <span>4 items for 14 days</span>
                      </li>
                      <li class="category-item">
                        <span class="category-label">Post-Graduate students:</span>
                        <span>6 items for 30 days</span>
                      </li>
                      <li class="category-item">
                        <span class="category-label">Full-time Academic Staff:</span>
                        <span>10 items for 1 Semester</span>
                      </li>
                      <li class="category-item">
                        <span class="category-label">Part-time Academic Staff:</span>
                        <span>4 items for 30 days</span>
                      </li>
                      <li class="category-item">
                        <span class="category-label">Non-Academic Staff:</span>
                        <span>10 items for 14 days</span>
                      </li>
                      <li class="category-item">
                        <span class="category-label">External members:</span>
                        <span>4 items for 14 days</span>
                      </li>
                      <li class="category-item">
                        <span class="category-label">Institutional members:</span>
                        <span>25 items for 30 days</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Books may be renewed once if not requested by others.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Journals, reference, and special collection materials cannot be removed without the Librarian's permission.</span>
              </li>
            </ul>
          </section>

          <!-- Offenses and Penalties Section -->
          <section class="section">
            <div class="section-header">
              <i class="fas fa-exclamation-triangle section-icon"></i>
              <h3 class="section-title">Offenses and Penalties</h3>
            </div>
            <div class="important-notice">
              <p>Important: All library users are responsible for adhering to these regulations. Failure to comply may result in penalties and loss of library privileges.</p>
            </div>
            <ul class="section-list">
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Fines are imposed for overdue materials.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Borrowers are responsible for returning materials on time.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Failure to return items after two reminders may result in the item being considered lost, and replacement costs will apply.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Fines will be charged for failure to return recalled material within 3 days.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Loss of borrowed material requires payment of the full replacement cost plus a processing charge.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Unlawful acquisition of Library material will result in a fine and potential loss of Library privileges or disciplinary action.</span>
              </li>
              <li class="list-item">
                <span class="list-bullet">•</span>
                <span>Disfigurement and mutilation of Library material will result in replacement costs and potential additional penalties.</span>
              </li>
            </ul>
          </section>

          <!-- Overdue Charges Section -->
          <section class="section">
            <div class="section-header">
              <i class="fas fa-exclamation-triangle section-icon"></i>
              <h3 class="section-title">Overdue Charges</h3>
            </div>
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>User Category</th>
                    <th>Charge</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Staff</td>
                    <td>E1.00 per day per item for the first 14 days, then E2.00 per item per day</td>
                  </tr>
                  <tr>
                    <td>Students</td>
                    <td>E0.50 per day per item for the first 14 days, then E1.00 per item per day</td>
                  </tr>
                  <tr>
                    <td>External Borrowers</td>
                    <td>E2.00 per day per item</td>
                  </tr>
                  <tr>
                    <td>Reserved Books</td>
                    <td>E1.00 per hour</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-content">
        <!-- Get In Touch Section -->
        <div class="footer-section">
          <h3 class="footer-title">Get In Touch</h3>
          <img src="/php_program/Barrowing_system/Images/download.png" alt="University of Eswatini Library Logo" class="footer-logo">
          <p class="footer-text">Kwaluseni, Luyengo & Mbabane</p>
          <p class="footer-text"><i class="fas fa-phone"></i> 2517 0448</p>
          <p class="footer-text">
            <i class="fas fa-envelope"></i>
            <a href="mailto:library@uniswa.sz" class="footer-link">library@uniswa.sz</a>
          </p>
        </div>

        <!-- Quick Links Section -->
        <div class="footer-section">
          <h3 class="footer-title">Quick Links</h3>
          <ul>
            <li class="footer-text"><a href="#" class="footer-link">Eswatini National Bibliography</a></li>
            <li class="footer-text"><a href="#" class="footer-link">UNESWA IR</a></li>
            <li class="footer-text"><a href="#" class="footer-link">Notices</a></li>
            <li class="footer-text"><a href="#" class="footer-link">Past Exam Papers</a></li>
            <li class="footer-text"><a href="#" class="footer-link">UNESWA</a></li>
          </ul>
        </div>

        <!-- Popular Databases Section -->
        <div class="footer-section">
          <h3 class="footer-title">Popular Databases</h3>
          <ul>
            <li class="footer-text"><a href="#" class="footer-link">Science Direct</a></li>
            <li class="footer-text"><a href="#" class="footer-link">Ebscohost</a></li>
            <li class="footer-text"><a href="#" class="footer-link">ERIC</a></li>
            <li class="footer-text"><a href="#" class="footer-link">Taylor & Francis</a></li>
            <li class="footer-text"><a href="#" class="footer-link">Sabinet</a></li>
          </ul>
        </div>

        <!-- Follow Us Section -->
        <div class="footer-section">
          <h3 class="footer-title">Follow Us</h3>
          <div class="footer-social">
            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
          </div>
        </div>
      </div>

      <!-- Footer Bottom -->
      <div class="footer-bottom">
        &copy; <?php echo date("Y"); ?> University of Eswatini Library | All Rights Reserved.
      </div>
    </div>
  </footer>
</body>
</html>