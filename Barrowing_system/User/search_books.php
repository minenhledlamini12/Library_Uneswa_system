<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// Database connection
$host = 'localhost';
$user = 'root'; // Change if needed
$pass = '';     // Change if needed
$db   = 'library';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Get and sanitize query
$query = isset($_POST['query']) ? trim($_POST['query']) : '';
if ($query === '') {
    echo json_encode(['status' => 'error', 'message' => 'No search query provided']);
    exit;
}

// Prepare SQL statement (search title, author, or ISBN)
$stmt = $conn->prepare(
    "SELECT ID, ISBN, Title, Author, PublicationYear, Publisher, Format, Language, Pages, Genre, CopiesAvailable, Status, CallNumber
     FROM books
     WHERE Title LIKE CONCAT('%', ?, '%')
        OR Author LIKE CONCAT('%', ?, '%')
        OR ISBN LIKE CONCAT('%', ?, '%')
     LIMIT 25"
);
$stmt->bind_param('sss', $query, $query, $query);
$stmt->execute();
$result = $stmt->get_result();

$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = [
        'id' => $row['ID'],
        'isbn' => $row['ISBN'],
        'title' => $row['Title'],
        'author' => $row['Author'],
        'year' => $row['PublicationYear'],
        'publisher' => $row['Publisher'],
        'format' => $row['Format'],
        'language' => $row['Language'],
        'pages' => $row['Pages'],
        'genre' => $row['Genre'],
        'copies' => $row['CopiesAvailable'],
        'status' => $row['Status'],
        'callnumber' => $row['CallNumber'],
    ];
}

echo json_encode(['status' => 'success', 'books' => $books]);
$stmt->close();
$conn->close();
?>
