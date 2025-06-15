<?php
require_once 'config.php';
require_once 'db_connection.php';
require_once 'header.php';

$db = new DBConnection(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $db->conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Login successful
            echo "<div class='green'>Login successful!</div>";
            // You can add session management here
            // header('Location: dashboard.php');
            exit;
        } else {
            echo "<div style='color: red;'>Incorrect password.</div>";
        }
    } else {
        echo "<div style='color: red;'>User not found.</div>";
    }
}

if (isset($_POST['signup'])) {
    header('Location: signup.php');
    exit;
}
?>

<form action="login.php" method="post">
    <label for="username">Username:</label><br>
    <input type="text" id="username" name="username" required><br>
    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required><br><br>
    <input type="submit" name="login" value="Login">
    <input type="submit" name="signup" value="Sign Up">
</form>

<?php
$db->close();
require_once 'footer.php';
?>
