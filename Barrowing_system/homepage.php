 <!DOCTYPE html>
 <html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UNESWA Library - Self-Service</title>
  <style>
  body {
  font-family: sans-serif;
  margin: 0;
  padding: 0;
  background-color: #d4edda; /* Light green background */
  }

  /* Top Bar */
  .top-bar {
  background-color: #004085; /* Dark blue background */
  color: white;
  padding: 5px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8em;
  }

  .top-bar-left {
  display: flex;
  align-items: center;
  }

  .top-bar-right a {
  color: white;
  text-decoration: none;
  margin-left: 10px;
  }

  header {
  background-color: #28a745; /* Darker green header */
  color: white;
  padding: 20px;
  text-align: center;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  header img {
  height: 80px; /* Adjust logo size as needed */
  margin-right: 20px;
  }

  header h1 {
  margin: 0;
  }

  nav {
  background-color: #f8f9fa;
  padding: 10px;
  text-align: center;
  }

  nav a {
  display: inline-block;
  padding: 10px 20px;
  text-decoration: none;
  color: #28a745;
  border-radius: 5px;
  transition: background-color 0.3s;
  }

  nav a:hover {
  background-color: #e2ffe9;
  }

  .container {
  max-width: 960px;
  margin: 20px auto;
  padding: 20px;
  background-color: white;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }

  .book-search {
  margin-bottom: 20px;
  }

  .book-search input[type="text"] {
  width: 70%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  }

  .book-search button {
  padding: 10px 20px;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
  }

  .book-search button:hover {
  background-color: #1e7e34;
  }

  .book-list {
  list-style: none;
  padding: 0;
  }

  .book-list li {
  padding: 15px;
  border-bottom: 1px solid #eee;
  }

  .book-list li:last-child {
  border-bottom: none;
  }

  .book-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  }

  .book-details {
  flex-grow: 1;
  }

  .borrow-button {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
  }

  .borrow-button:hover {
  background-color: #0056b3;
  }

  footer {
  text-align: center;
  padding: 20px;
  background-color: #28a745;
  color: white;
  }
  </style>
 </head>
 <body>

  <div class="top-bar">
  <div class="top-bar-left">
  <span><i class="far fa-clock"></i> Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM</span>
  </div>
  <div class="top-bar-right">
  <span><i class="fas fa-phone"></i> 2517 0448</span>
  <a href="#"><i class="fab fa-facebook"></i></a>
  <a href="#"><i class="fab fa-twitter"></i></a>
  <a href="#"><i class="fab fa-youtube"></i></a>
  </div>
  </div>

  <header>
  <img src="https://via.placeholder.com/80" alt="UNESWA Library Logo"> <!-- Replace with the actual logo URL -->
  <h1>University of Eswatini Library</h1>
  <p>Kwaluseni Campus - Self-Service Book Borrowing</p>
  </header>

  <nav>
  <a href="#">OPAC</a>
  <a href="#">E-RESOURCES</a>
  <a href="#">UNESWA IR</a>
  <a href="#">SERVICES & COLLECTIONS</a>
  <a href="#">NOTICES</a>
  <a href="#">STAFF</a>
  <a href="#">CONTACT</a>
  </nav>

  <div class="container">
  <div class="book-search">
  <input type="text" id="search-input" placeholder="Search for books...">
  <button onclick="searchBooks()">Search</button>
  </div>

  <ul class="book-list" id="book-list">
  <!-- Book list items will be added here dynamically -->
  </ul>
  </div>

  <footer>
  <p>&copy; 2025 University of Eswatini Library</p>
  </footer>

  <script>
  function searchBooks() {
  const searchTerm = document.getElementById('search-input').value.toLowerCase();
  const bookList = document.getElementById('book-list');
  bookList.innerHTML = ''; // Clear previous results

  // Replace this with actual data from your backend
  const books = [
  { title: "Pride and Prejudice", author: "Jane Austen", id: "12345" },
  { title: "To Kill a Mockingbird", author: "Harper Lee", id: "67890" },
  { title: "1984", author: "George Orwell", id: "24680" },
  { title: "The Hitchhiker's Guide to the Galaxy", author: "Douglas Adams", id: "13579" }
  ];

  const searchResults = books.filter(book =>
  book.title.toLowerCase().includes(searchTerm) || book.author.toLowerCase().includes(searchTerm)
  );

  if (searchResults.length === 0) {
  bookList.innerHTML = '<li>No books found.</li>';
  return;
  }

  searchResults.forEach(book => {
  const listItem = document.createElement('li');
  listItem.className = 'book-item';
  listItem.innerHTML = `
  <div class="book-details">
  <h3>${book.title}</h3>
  <p>By ${book.author}</p>
  </div>
  <button class="borrow-button" onclick="borrowBook('${book.id}')">Borrow</button>
  `;
  bookList.appendChild(listItem);
  });
  }

  function borrowBook(bookId) {
  alert(`Borrowing book with ID: ${bookId}`);
  // In a real system, this would send a request to the backend to record the borrowing.
  }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

 </body>
 </html>
