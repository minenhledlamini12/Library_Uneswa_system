<?php
session_start();



require_once 'connection.php';

// Initialize variables
$token = isset($_GET['token']) ? $_GET['token'] : '';
$show_form = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'], $_POST['new_password'], $_POST['confirm_password'], $_POST['csrf_token'])) {
    try {
        // Validate CSRF token
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid CSRF token");
        }

        $token = $_POST['token'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate passwords
        if ($new_password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }
        if (strlen($new_password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }

        // Check token in password_resets table
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->bind_result($email);
        if (!$stmt->fetch()) {
            throw new Exception("Invalid or expired token");
        }
        $stmt->close();

        // Update password in admini table
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admini SET Password = ? WHERE Email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        $stmt->execute();
        $stmt->close();

        // Delete used token
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "Password reset successfully. Please <a href='index.php' class='text-blue-900 hover:underline'>log in</a>.";
        $show_form = false;

        // Clear CSRF token
        unset($_SESSION['csrf_token']);
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        error_log(date('Y-m-d H:i:s') . " - " . $e->getMessage() . " - IP: " . $_SERVER['REMOTE_ADDR'] . "\n", 3, "error.log");
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Reset your password for the UNESWA Library access control system.">
    <title>UNESWA Library - Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Fade-in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 1s ease-out forwards;
        }

        /* Hover scale effect */
        .hover-scale {
            transition: transform 0.3s ease;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }

        /* Input focus effect */
        .input-focus {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .input-focus:focus {
            border-color: #1e40af;
            box-shadow: 0 0 5px rgba(30, 64, 175, 0.3);
            outline: none;
        }

        /* Button hover effect */
        .btn-hover {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-hover:hover {
            background-color: #1e40af;
            transform: translateY(-2px);
        }

        /* Error message animation */
        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .error-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Success message */
        .success-message {
            color: #15803d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .success-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Reduced motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            .fade-in, .hover-scale, .btn-hover, .error-message, .success-message {
                animation: none;
                transition: none;
            }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100 text-gray-800">
    <div class="reset-container bg-white rounded-lg shadow-lg p-8 w-full max-w-md mx-4 fade-in">
        <div class="flex items-center justify-center mb-6">
            <img src="download.png" alt="UNESWA Library Logo" class="h-12 mr-2 hover-scale">
            <h2 class="text-2xl font-semibold text-blue-900 flex items-center">
                <i class="fas fa-key mr-2"></i>Reset Your Password
            </h2>
        </div>
        <p class="text-gray-700 text-center mb-6">Enter your new password below.</p>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-4 text-red-600 text-sm text-center error-message show">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 text-green-700 text-sm text-center success-message show">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if ($show_form && $token): ?>
            <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST" id="resetForm" novalidate>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group mb-4 relative">
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-lock mr-2"></i>New Password
                    </label>
                    <input type="password" id="new_password" name="new_password" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" placeholder="Enter new password" required aria-required="true">
                    <button type="button" onclick="togglePassword('new_password')" class="absolute right-3 top-9 text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                    <p class="error-message" id="newPasswordError"></p>
                </div>
                <div class="form-group mb-6 relative">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-lock mr-2"></i>Confirm New Password
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" placeholder="Confirm new password" required aria-required="true">
                    <button type="button" onclick="togglePassword('confirm_password')" class="absolute right-3 top-9 text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                    <p class="error-message" id="confirmPasswordError"></p>
                </div>
                <button type="submit" id="submitButton" class="w-full bg-blue-900 text-white py-3 rounded-md btn-hover flex items-center justify-center">
                    <i class="fas fa-sync mr-2"></i>Reset Password
                    <i class="fas fa-spinner fa-spin hidden ml-2" id="loadingIcon"></i>
                </button>
            </form>
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Remember your password?
                    <a href="index.php" class="text-blue-900 hover:underline hover:text-blue-700">
                        <i class="fas fa-sign-in-alt mr-1"></i>Back to Login
                    </a>
                </p>
            </div>
        <?php elseif (!$token): ?>
            <div class="mb-4 text-red-600 text-sm text-center error-message show">
                No reset token provided. Please request a new reset link.
            </div>
            <div class="text-center">
                <a href="ForgotPassword.php" class="text-blue-900 hover:underline hover:text-blue-700">
                    <i class="fas fa-arrow-left mr-1"></i>Back to Forgot Password
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye', input.type === 'password');
            icon.classList.toggle('fa-eye-slash', input.type !== 'password');
        }

        document.getElementById('resetForm')?.addEventListener('submit', function(event) {
            event.preventDefault();
            let isValid = true;

            // Clear previous error messages
            document.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
                el.classList.remove('show');
            });

            // Validate New Password
            const newPassword = document.getElementById('new_password').value;
            if (!newPassword || newPassword.length < 8) {
                document.getElementById('newPasswordError').textContent = 'Password must be at least 8 characters.';
                document.getElementById('newPasswordError').classList.add('show');
                isValid = false;
            }

            // Validate Confirm Password
            const confirmPassword = document.getElementById('confirm_password').value;
            if (confirmPassword !== newPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Passwords do not match.';
                document.getElementById('confirmPasswordError').classList.add('show');
                isValid = false;
            }

            if (isValid) {
                // Show loading state
                const submitButton = document.getElementById('submitButton');
                const loadingIcon = document.getElementById('loadingIcon');
                submitButton.disabled = true;
                loadingIcon.classList.remove('hidden');

                // Submit the form
                this.submit();
            }
        });
    </script>
</body>
</html>