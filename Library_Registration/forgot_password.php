
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Reset your password for the UNESWA Library access control system.">
    <title>UNESWA Library - Forgot Password</title>
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
        <p class="text-gray-700 text-center mb-6">Enter your email address to receive a link to reset your password.</p>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-4 text-red-600 text-sm text-center error-message show">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 text-green-700 text-sm text-center success-message show">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <form action="send_reset_link.php" method="POST" id="resetForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group mb-6 relative">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-envelope mr-2"></i>Email
                </label>
                <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" placeholder="Enter your email" required aria-required="true">
                <button type="button" onclick="toggleEmail('email')" class="absolute right-3 top-9 text-gray-600">
                    <i class="fas fa-eye"></i>
                </button>
                <p class="error-message" id="emailError"></p>
            </div>
            <button type="submit" id="submitButton" class="w-full bg-blue-900 text-white py-3 rounded-md btn-hover flex items-center justify-center">
                <i class="fas fa-paper-plane mr-2"></i>Send Reset Link
                <i class="fas fa-spinner fa-spin hidden ml-2" id="loadingIcon"></i>
            </button>
        </form>
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">
                Remember your password?
                <a href="Login.php" class="text-blue-900 hover:underline hover:text-blue-700">
                    <i class="fas fa-sign-in-alt mr-1"></i>Back to Login
                </a>
            </p>
        </div>
    </div>

    <script>
        function toggleEmail(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            input.type = input.type === 'email' ? 'text' : 'email';
            icon.classList.toggle('fa-eye', input.type === 'email');
            icon.classList.toggle('fa-eye-slash', input.type !== 'email');
        }

        document.getElementById('resetForm').addEventListener('submit', function(event) {
            event.preventDefault();
            let isValid = true;

            // Clear previous error messages
            document.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
                el.classList.remove('show');
            });

            // Validate Email
            const email = document.getElementById('email').value.trim();
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('emailError').textContent = 'Valid email is required.';
                document.getElementById('emailError').classList.add('show');
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