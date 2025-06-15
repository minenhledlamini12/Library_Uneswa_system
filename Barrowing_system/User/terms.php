<?php
session_start();

// Check if the user has agreed to the terms
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agree'])) {
    $_SESSION['agreed_to_terms'] = true; // Store agreement in session
    header('Location: trail2barrow.php'); // Redirect to borrowing page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Terms and Conditions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: auto; }
        .terms { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; overflow-y: scroll; height: 300px; }
        button { padding: 10px 20px; background-color: #007BFF; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Library Terms and Conditions</h1>
        <div class="terms">
            <p><strong>Welcome to the Library Borrowing Portal.</strong></p>
            <p>By borrowing a book, you agree to the following terms:</p>
            <ul>
                <li>You are responsible for returning the book by the due date.</li>
                <li>Late returns may incur penalties as per library policy.</li>
                <li>Damaged or lost books must be replaced or compensated for.</li>
                <li>The borrowing period depends on your membership type (Student, Staff, or Faculty).</li>
            </ul>
            <p>Please read these terms carefully before proceeding.</p>
        </div>

        <form method="post">
            <button type="submit" name="agree">I Agree</button>
        </form>
    </div>
</body>
</html>
