<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign up as a librarian for the UNESWA Library access control system.">
    <title>Librarian Sign Up - UNESWA Library</title>
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

        /* Social icon hover effect */
        .social-icon {
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .social-icon:hover {
            color: #60a5fa;
            transform: translateY(-3px);
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

        /* Reduced motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            .fade-in, .hover-scale, .social-icon, .btn-hover, .error-message {
                animation: none;
                transition: none;
            }
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 text-gray-800">
    <header class="bg-blue-900 text-white py-4 px-6 shadow-lg" role="banner">
        <div class="container mx-auto">
            <div class="flex justify-between items-center mb-4">
                <div class="header-info text-sm flex flex-col sm:flex-row items-center sm:space-x-4 space-y-2 sm:space-y-0">
                    <span class="flex items-center">
                        <i class="far fa-clock mr-2"></i>
                        Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-phone mr-2"></i>
                        2517 0448
                    </span>
                </div>
                <div class="social-icons flex space-x-4">
                    <a href="https://facebook.com" aria-label="Visit our Facebook page" class="social-icon"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="https://twitter.com" aria-label="Visit our Twitter page" class="social-icon"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="https://youtube.com" aria-label="Visit our YouTube channel" class="social-icon"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>
            <div class="flex items-center">
                <img src="download.png" alt="UNESWA Library Logo" class="h-16 mr-4 hover-scale">
                <span class="text-2xl font-bold site-title">UNESWA Library</span>
            </div>
        </div>
    </header>

    <main class="flex items-center justify-center flex-grow py-8 px-6" role="main">
        <div class="signup-container bg-white rounded-lg shadow-lg p-8 w-full max-w-md mx-4 fade-in">
            <div class="flex items-center justify-center mb-6">
                <img src="download.png" alt="UNESWA Library Logo" class="h-12 mr-2 hover-scale">
                <h2 class="text-2xl font-semibold text-blue-900 flex items-center">
                    <i class="fas fa-user-shield mr-2"></i>Librarian Registration
                </h2>
            </div>
            <form action="Sign.php" method="POST" id="signupForm" novalidate>
                <div class="form-group mb-4">
                    <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user mr-2"></i>First Name
                    </label>
                    <input type="text" id="firstName" name="firstName" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="firstNameError"></p>
                </div>

                <div class="form-group mb-4">
                    <label for="secondName" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user mr-2"></i>Second Name
                    </label>
                    <input type="text" id="secondName" name="secondName" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus">
                    <p class="error-message" id="secondNameError"></p>
                </div>

                <div class="form-group mb-4">
                    <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user mr-2"></i>Last Name
                    </label>
                    <input type="text" id="lastName" name="lastName" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="lastNameError"></p>
                </div>

                <div class="form-group mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-briefcase mr-2"></i>Role
                    </label>
                    <input type="text" id="role" name="role" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="roleError"></p>
                </div>

                <div class="form-group mb-4">
                    <label for="contact" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-phone mr-2"></i>Contact
                    </label>
                    <input type="tel" id="contact" name="contact" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" pattern="[0-9]{8,12}">
                    <p class="error-message" id="contactError"></p>
                </div>

                <div class="form-group mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-envelope mr-2"></i>Email
                    </label>
                    <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="emailError"></p>
                </div>

                <div class="form-group mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="passwordError"></p>
                </div>

                <div class="form-group mb-6">
                    <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-lock mr-2"></i>Confirm Password
                    </label>
                    <input type="password" id="confirmPassword" name="confirmPassword" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="confirmPasswordError"></p>
                </div>

                <button type="submit" id="submitButton" class="w-full bg-blue-900 text-white py-3 rounded-md btn-hover flex items-center justify-center">
                    <i class="fas fa-user-plus mr-2"></i>Register
                    <i class="fas fa-spinner fa-spin hidden ml-2" id="loadingIcon"></i>
                </button>
            </form>

            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="index.php" class="text-blue-900 hover:underline hover:text-blue-700">
                        <i class="fas fa-sign-in-alt mr-1"></i>Login
                    </a>
                </p>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('signupForm').addEventListener('submit', function(event) {
            event.preventDefault();
            let isValid = true;

            // Clear previous error messages
            document.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
                el.classList.remove('show');
            });

            // Validate First Name
            const firstName = document.getElementById('firstName').value.trim();
            if (!firstName) {
                document.getElementById('firstNameError').textContent = 'First Name is required.';
                document.getElementById('firstNameError').classList.add('show');
                isValid = false;
            }

            // Validate Last Name
            const lastName = document.getElementById('lastName').value.trim();
            if (!lastName) {
                document.getElementById('lastNameError').textContent = 'Last Name is required.';
                document.getElementById('lastNameError').classList.add('show');
                isValid = false;
            }

            // Validate Role
            const role = document.getElementById('role').value.trim();
            if (!role) {
                document.getElementById('roleError').textContent = 'Role is required.';
                document.getElementById('roleError').classList.add('show');
                isValid = false;
            }

            // Validate Contact
            const contact = document.getElementById('contact').value.trim();
            if (contact && !/^[0-9]{8,12}$/.test(contact)) {
                document.getElementById('contactError').textContent = 'Contact must be 8-12 digits.';
                document.getElementById('contactError').classList.add('show');
                isValid = false;
            }

            // Validate Email
            const email = document.getElementById('email').value.trim();
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('emailError').textContent = 'Valid email is required.';
                document.getElementById('emailError').classList.add('show');
                isValid = false;
            }

            // Validate Password
            const password = document.getElementById('password').value;
            if (!password || password.length < 8) {
                document.getElementById('passwordError').textContent = 'Password must be at least 8 characters.';
                document.getElementById('passwordError').classList.add('show');
                isValid = false;
            }

            // Validate Confirm Password
            const confirmPassword = document.getElementById('confirmPassword').value;
            if (confirmPassword !== password) {
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

                // Simulate form submission (replace with actual fetch/AJAX call to SignUp.php)
                setTimeout(() => {
                    this.submit(); // Submit the form if valid
                }, 1000); // Simulate delay for loading animation
            }
        });
    </script>
</body>
</html>