<?php
session_start();

$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: landingpage.php');
    exit;
}

// Redirect to login if not logged in
if (!$is_logged_in) {
    header('Location: login.php');
    exit;
}

// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search
$search_results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $query = $conn->real_escape_string($_POST['query']);
    $sql = "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_term = "%$query%";
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Search Books - UNESWA Library</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    :root {
      --primary-color: #4CAF50;
      --secondary-color: #388E3C;
      --accent-color: #a5d6a7;
      --text-color: #333;
      --bg-light: #f9fafb;
      --white: #ffffff;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: var(--bg-light);
      color: var(--text-color);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      line-height: 1.6;
    }

    .top-bar {
      background: var(--secondary-color);
      color: var(--white);
      padding: 12px 30px;
      font-size: 0.9rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }

    .top-bar a {
      color: var(--white);
      margin-left: 12px;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .top-bar a:hover {
      color: var(--accent-color);
    }

    header {
      background: var(--primary-color);
      padding: 20px 30px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 15px;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .header-left img {
      height: 50px;
      width: auto;
      object-fit: contain;
      background: var(--white);
      padding: 5px;
      border-radius: 5px;
    }

    .header-left h1 {
      font-size: 1.8rem;
      color: var(--white);
      margin: 0;
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 15px;
      font-weight: 600;
      color: var(--white);
    }

    .header-right span {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn {
      background: var(--white);
      color: var(--primary-color);
      border: none;
      border-radius: 6px;
      padding: 8px 16px;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: background-color 0.3s ease, transform 0.2s ease, color 0.3s ease;
    }

    .btn:hover {
      background: #e8f5e9;
      color: var(--secondary-color);
      transform: translateY(-2px);
    }

    nav {
      background: var(--secondary-color);
      padding: 15px 0;
      display: flex;
      justify-content: center;
      gap: 40px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    nav a {
      color: var(--white);
      font-weight: 600;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 1.1rem;
      transition: color 0.3s ease;
    }

    nav a:hover {
      color: var(--accent-color);
    }

    main {
      flex: 1 0 auto;
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .search-form {
      display: flex;
      gap: 12px;
      margin-bottom: 40px;
      max-width: 700px;
      margin-left: auto;
      margin-right: auto;
    }

    .search-input {
      flex: 1;
      padding: 12px 16px;
      font-size: 1rem;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .search-input:focus {
      outline: none;
      border-color: var(--secondary-color);
      box-shadow: 0 0 0 3px rgba(56, 142, 60, 0.1);
    }

    .search-button {
      background: var(--secondary-color);
      color: var(--white);
      border: none;
      border-radius: 8px;
      padding: 12px 24px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .search-button:hover {
      background: var(--primary-color);
      transform: translateY(-2px);
    }

    .search-results h2 {
      color: var(--primary-color);
      font-size: 1.6rem;
      margin-bottom: 20px;
    }

    .book-list {
      list-style: none;
      padding: 0;
    }

    .book-list li {
      background: var(--white);
      margin-bottom: 15px;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .book-list li:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .book-list li strong {
      font-size: 1.2rem;
      color: var(--primary-color);
      display: block;
      margin-bottom: 8px;
    }

    .book-list li span {
      font-size: 0.95rem;
      color: #4b5563;
      display: block;
      margin-bottom: 4px;
    }

    footer {
      background: var(--secondary-color);
      color: var(--white);
      padding: 50px 30px 20px;
      flex-shrink: 0;
    }

    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 30px;
      margin-bottom: 40px;
    }

    .footer-section {
      padding: 0 15px;
    }

    .footer-section h3 {
      font-size: 1.3rem;
      margin-bottom: 15px;
      position: relative;
      padding-bottom: 10px;
    }

    .footer-section h3::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 60px;
      height: 2px;
      background: rgba(255, 255, 255, 0.5);
    }

    .footer-section img {
      height: 50px;
      margin-bottom: 15px;
      background: var(--white);
      padding: 8px;
      border-radius: 6px;
    }

    .footer-section p,
    .footer-section li {
      font-size: 0.95rem;
      margin-bottom: 10px;
    }

    .footer-section ul {
      list-style: none;
    }

    .footer-section a {
      color: var(--white);
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .footer-section a:hover {
      color: var(--accent-color);
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
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      transition: all 0.3s ease;
    }

    .social-icons a:hover {
      background: rgba(255, 255, 255, 0.4);
      transform: translateY(-3px);
    }

    .footer-bottom {
      text-align: center;
      padding-top: 30px;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
      font-size: 0.9rem;
    }

    @media (max-width: 768px) {
      header, .top-bar {
        flex-direction: column;
        text-align: center;
      }

      .header-left {
        margin-bottom: 15px;
      }

      .header-right {
        flex-wrap: wrap;
        justify-content: center;
      }

      nav {
        flex-wrap: wrap;
        gap: 20px;
      }

      .search-form {
        flex-direction: column;
      }

      .search-button {
        width: 100%;
      }

      .footer-section {
        text-align: center;
      }

      .footer-section h3::after {
        left: 50%;
        transform: translateX(-50%);
      }

      .social-icons {
        justify-content: center;
      }
    }

    @media (max-width: 480px) {
      .header-left h1 {
        font-size: 1.5rem;
      }

      nav a {
        font-size: 1rem;
      }

      .search-input,
      .search-button {
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body>
  <div class="top-bar">
    <div>
      <i class="far fa-clock"></i> Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM | Current Time: 04:07 AM SAST, Tuesday, June 10, 2025
      <i class="fas fa-phone"></i> 2517 0448
    </div>
    <div>
      <a href="#"><i class="fab fa-facebook"></i></a>
      <a href="#"><i class="fab fa-twitter"></i></a>
      <a href="#"><i class="fab fa-youtube"></i></a>
    </div>
  </div>

  <header>
    <div class="header-left">
      <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo" />
      <h1>University of Eswatini Library</h1>
    </div>
    <div class="header-right">
      <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['Name'] . ' ' . $_SESSION['Surname']); ?></span>
      <a href="homepage.php" class="btn"><i class="fas fa-home"></i> Home</a>
      <a href="?action=logout" class="btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </header>

  <nav>
    <a href="homepage.php"><i class="fas fa-home"></i> Home</a>
    <a href="barrowpage.php"><i class="fas fa-book-open"></i> Borrow/Issue Book</a>
    <a href="library_regulations.php"><i class="fas fa-file-alt"></i> Library Regulations</a>
  </nav>

  <main>
    <form id="searchForm" class="search-form" method="POST" action="">
      <input type="text" id="searchQuery" name="query" class="search-input" placeholder="Search for books by title, author, or ISBN..." value="<?php echo isset($_POST['query']) ? htmlspecialchars($_POST['query']) : ''; ?>" />
      <button type="submit" class="search-button"><i class="fas fa-search"></i> Search</button>
    </form>

    <div class="search-results" id="searchResults" style="display:<?php echo !empty($search_results) ? 'block' : 'none'; ?>;">
      <h2>Search Results</h2>
      <ul class="book-list" id="bookList">
        <?php foreach ($search_results as $book): ?>
          <li>
            <strong><?php echo htmlspecialchars($book['title']); ?></strong>
            <span>Author: <?php echo htmlspecialchars($book['author']); ?></span>
            <span>ISBN: <?php echo htmlspecialchars($book['isbn']); ?></span>
            <span>Year: <?php echo htmlspecialchars($book['publicationYear']); ?></span>
            <span>Publisher: <?php echo htmlspecialchars($book['publisher']); ?></span>
            <span>Format: <?php echo htmlspecialchars($book['format']); ?></span>
            <span>Language: <?php echo htmlspecialchars($book['language']); ?></span>
            <span>Pages: <?php echo htmlspecialchars($book['pages']); ?></span>
            <span>Genre: <?php echo htmlspecialchars($book['genre']); ?></span>
            <span>Copies Available: <?php echo htmlspecialchars($book['copiesAvailable']); ?></span>
            <span>Call Number: <?php echo htmlspecialchars($book['callNum']); ?></span>
            <span>Status: <?php echo htmlspecialchars($book['status']); ?></span>
          </li>
        <?php endforeach; ?>
        <?php if (empty($search_results)): ?>
          <li>No books found matching your search.</li>
        <?php endif; ?>
      </ul>
    </div>
  </main>

  <footer>
    <div class="footer-container">
      <div class="footer-section">
        <h3>Get In Touch</h3>
        <img src="/php_program/Barrowing_system/Images/download.png" alt="University of Eswatini Library Logo">
        <p>Kwaluseni, Luyengo & Mbabane</p>
        <p><i class="fas fa-phone icon"></i> 2517 0448</p>
        <p><i class="fas fa-envelope icon"></i> <a href="mailto:library@uniswa.sz">library@uniswa.sz</a></p>
      </div>

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

    <div class="footer-bottom">
      © <?php echo date("Y"); ?> University of Eswatini Library | All Rights Reserved.
    </div>
  </footer>

  <script>
    const searchForm = document.getElementById('searchForm');
    const searchQueryInput = document.getElementById('searchQuery');
    const searchResults = document.getElementById('searchResults');
    const bookList = document.getElementById('bookList');

    searchForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const query = searchQueryInput.value.trim();
      if (!query) {
        alert('Please enter a search term.');
        return;
      }
      fetchBooks(query);
    });

    async function fetchBooks(query) {
      bookList.innerHTML = '';
      searchResults.style.display = 'none';

      try {
        const response = await fetch('search_books.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'query=' + encodeURIComponent(query)
        });

        if (!response.ok) {
          throw new Error('Network response was not ok');
        }

        const data = await response.json();

        if (data.status === 'success' && data.books && data.books.length > 0) {
          data.books.forEach(book => {
            const li = document.createElement('li');
            li.innerHTML = `
              <strong>${escapeHtml(book.title)}</strong>
              <span>Author: ${escapeHtml(book.author)}</span>
              <span>ISBN: ${escapeHtml(book.isbn)}</span>
              <span>Year: ${escapeHtml(book.year)}</span>
              <span>Publisher: ${escapeHtml(book.publisher)}</span>
              <span>Format: ${escapeHtml(book.format)}</span>
              <span>Language: ${escapeHtml(book.language)}</span>
              <span>Pages: ${escapeHtml(book.pages)}</span>
              <span>Genre: ${escapeHtml(book.genre)}</span>
              <span>Copies Available: ${escapeHtml(book.copies)}</span>
              <span>Call Number: ${escapeHtml(book.callnumber)}</span>
              <span>Status: ${escapeHtml(book.status)}</span>
            `;
            bookList.appendChild(li);
          });
          searchResults.style.display = 'block';
        } else {
          bookList.innerHTML = '<li>No books found matching your search.</li>';
          searchResults.style.display = 'block';
        }
      } catch (error) {
        bookList.innerHTML = '<li>Error fetching search results.</li>';
        searchResults.style.display = 'block';
        console.error('Fetch error:', error);
      }
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>
</body>
</html>