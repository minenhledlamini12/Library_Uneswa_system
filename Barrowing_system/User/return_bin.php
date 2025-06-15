<?php
// return_bin.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Library Book Return Bin</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 2rem;
      background-color: #f9f9f9;
      color: #333;
      text-align: center;
    }
    h1 {
      color: #005a9c;
    }
    .instruction {
      margin: 1.5rem 0;
      font-size: 1.2rem;
    }
    button {
      background-color: #0078d7;
      color: white;
      border: none;
      padding: 1rem 2rem;
      font-size: 1.1rem;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #005a9c;
    }
  </style>
</head>
<body>
  <h1>Return Your Book</h1>
  <p class="instruction">
    Please scan the QR code on your book to confirm your return.<br />
    After scanning, drop the book into the return bin.
  </p>
  <button id="scanBtn">Scan Book</button>

  <script>
    document.getElementById('scanBtn').addEventListener('click', () => {
      // Redirect to the book scanning page
      window.location.href = 'returnbin_scanner.php';
    });
  </script>
</body>
</html>
