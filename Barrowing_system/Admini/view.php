<?php
require_once("connection.php");

$sql = "SELECT ID, ISBN, Title, Author, PublicationYear, Publisher, Format, Language, Pages, Genre, CopiesAvailable, Status, CallNumber, AddedDate, UpdatedDate, QrCode FROM books";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<!DOCTYPE html>";
    echo "<html lang='en'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Manage Books</title>";
    echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>";
    echo "<style>";
    echo "body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; text-align: center; }";
    echo ".container { width: 90%; margin: auto; padding: 20px; background-color: #fff; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); overflow-x: auto; }";
    echo "table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }";
    echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
    echo "th { background-color: #5cb85c; color: white; }";
    echo "tr:nth-child(even) { background-color: #f2f2f2; }";
    echo ".action-buttons { display: flex; justify-content: center; gap: 10px; }";
    echo ".edit-button, .delete-button, .history-button { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; color: white; }";
    echo ".edit-button { background-color: #007bff; }";
    echo ".delete-button { background-color: #dc3545; }";
        echo ".history-button { background-color: #6c757d; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    echo "<div class='container'>";
    echo "<h2><i class='fas fa-book'></i> Manage Books</h2>";
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th><i class='fas fa-id-card'></i> ID</th>";
    echo "<th><i class='fas fa-barcode'></i> ISBN</th>";
    echo "<th><i class='fas fa-heading'></i> Title</th>";
    echo "<th><i class='fas fa-user'></i> Author</th>";
    echo "<th><i class='fas fa-calendar-alt'></i> Publication Year</th>";
    echo "<th><i class='fas fa-university'></i> Publisher</th>";
    echo "<th><i class='fas fa-file'></i> Format</th>";
    echo "<th><i class='fas fa-globe'></i> Language</th>";
    echo "<th><i class='fas fa-file-alt'></i> Pages</th>";
    echo "<th><i class='fas fa-tags'></i> Genre</th>";
    echo "<th><i class='fas fa-copy'></i> Copies Available</th>";
    echo "<th><i class='fas fa-info-circle'></i> Status</th>";
    echo "<th><i class='fas fa-hashtag'></i> Call Number</th>";
    echo "<th><i class='fas fa-calendar-plus'></i> Added Date</th>";
    echo "<th><i class='fas fa-calendar-check'></i> Updated Date</th>";
    echo "<th><i class='fas fa-qrcode'></i> QR Code</th>";
    echo "<th><i class='fas fa-cogs'></i> Actions</th>";
    echo "</tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".$row["ID"]."</td>";
        echo "<td>".$row["ISBN"]."</td>";
        echo "<td>".$row["Title"]."</td>";
        echo "<td>".$row["Author"]."</td>";
        echo "<td>".$row["PublicationYear"]."</td>";
        echo "<td>".$row["Publisher"]."</td>";
        echo "<td>".$row["Format"]."</td>";
        echo "<td>".$row["Language"]."</td>";
        echo "<td>".$row["Pages"]."</td>";
        echo "<td>".$row["Genre"]."</td>";
        echo "<td>".$row["CopiesAvailable"]."</td>";
        echo "<td>".$row["Status"]."</td>";
        echo "<td>".$row["CallNumber"]."</td>";
        echo "<td>".$row["AddedDate"]."</td>";
        echo "<td>".$row["UpdatedDate"]."</td>";
        echo "<td><a href='" . htmlspecialchars($row["QrCode"]) . "' target='_blank'><i class='fas fa-qrcode'></i> View QR Code</a></td>";
        echo "<td class='action-buttons'>";
        echo "<a href='edit.php?id=".$row["ID"]."' class='edit-button'><i class='fas fa-edit'></i> Edit</a>";
        echo "<a href='delete.php?id=".$row["ID"]."' class='delete-button'><i class='fas fa-trash-alt'></i> Delete</a>";
        echo "<a href='view_history.php?book_id=".$row["ID"]."' class='history-button'><i class='fas fa-history'></i> History</a>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
} else {
    echo "No books found";
}
$conn->close();
?>
