<?php
require_once("connection.php");


$email = $conn->real_escape_string($_GET['email']);

$sql = "SELECT email FROM members WHERE email='$email'";
$result = $conn->query($sql);

echo ($result->num_rows > 0) ? "VALID" : "INVALID";
$conn->close();
?>
