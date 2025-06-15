<?php

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// Database connection details (mysqli)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library";
$tablename = "admini";  // Changed table name here!

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, 3306);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to display alert messages
function displayAlert($message, $type = 'danger') {
    echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">' . $message . '
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        body {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .reset-container {
            max-width: 500px;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.8s ease-out;
        }
        .reset-container h2 {
            color: #2e7d32;
            font-weight: 600;
            margin-bottom: 30px;
        }
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }
        .form-group i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #4caf50;
            font-size: 18px;
        }
        .form-control {
            padding-left: 45px;
            border-radius: 8px;
            border: 1px solid #c8e6c9;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #4caf50;
            box-shadow: 0 0 8px rgba(76, 175, 80, 0.3);
        }
        .btn-primary {
            background-color: #4caf50;
            border-color: #4caf50;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #388e3c;
            border-color: #388e3c;
            transform: translateY(-2px);
        }
        .btn-primary i {
            margin-right: 8px;
        }
        .alert {
            border-radius: 8px;
            animation: fadeIn 0.5s ease;
        }
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .alert-warning {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
        }
        /* Custom animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .form-group {
            animation: slideIn 0.5s ease forwards;
            animation-delay: calc(0.1s * var(--i));
        }
        /* Placeholder styling */
        .form-control::placeholder {
            color: #a5d6a7;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="reset-container animate__animated animate__fadeInUp">
            <h2 class="text-center mb-4"><i class="fas fa-key fa-spin fa-xs mr-2"></i> Reset Your Password</h2>

            <?php
            if (isset($_GET['token'])) {
                $token = $_GET['token'];
                ?>
                <form method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group" style="--i: 1">
                        <i class="fas fa-lock"></i>
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="Enter New Password">
                    </div>

                    <div class="form-group" style="--i: 2">
                        <i class="fas fa-lock"></i>
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Confirm New Password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-sync fa-spin-hover"></i> Reset Password</button>
                </form>
                <?php
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'], $_POST['new_password'], $_POST['confirm_password'])) {
                $token = $_POST['token'];
                $token_hash = hash('sha256', $token);
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                if ($new_password !== $confirm_password) {
                    displayAlert("Passwords do not match. Please try again.", "warning");
                } else {
                    $sql = "SELECT Member_ID, reset_token_expires_at FROM admini WHERE reset_token_hash = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $token_hash);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                    $stmt->close();

                    if ($user && strtotime($user['reset_token_expires_at']) > time()) {
                        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                        $sql = "UPDATE admini SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE Member_ID = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("si", $new_password_hash, $user['Member_ID']);
                        $stmt->execute();
                        $stmt->close();

                        displayAlert("Password reset successful. You can now <a href='login.php'>login</a>.", "success");
                    } else {
                        displayAlert("Invalid or expired token.", "danger");
                    }
                }
            }

            $conn->close();
            ?>
        </div>
    </div>

    <!-- Bootstrap and jQuery scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Custom script for additional interactivity -->
    <script>
        // Add hover animation for button
        document.querySelector('.btn-primary').addEventListener('mouseenter', function() {
            this.classList.add('animate__animated', 'animate__pulse');
        });
        document.querySelector('.btn-primary').addEventListener('animationend', function() {
            this.classList.remove('animate__animated', 'animate__pulse');
        });

        // Fade out alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.classList.add('animate__animated', 'animate__fadeOut');
                setTimeout(() => alert.remove(), 1000);
            });
        }, 5000);
    </script>
</body>
</html>