<?php
session_start();


require_once 'connection.php';



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Librarian form to register new members for the UNESWA Library.">
    <title>UNESWA Library - Member Registration</title>
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
            .fade-in, .hover-scale, .social-icon, .btn-hover, .error-message, .success-message {
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
    </header>

    <main class="flex items-center justify-center flex-grow py-8 px-6" role="main">
        <div class="container bg-white rounded-lg shadow-lg p-8 w-full max-w-lg mx-4 fade-in">
            <div class="flex items-center justify-center mb-6">
                <img src="download.png" alt="UNESWA Library Logo" class="h-12 mr-2 hover-scale">
                <h2 class="text-2xl font-semibold text-blue-900 flex items-center">
                    <i class="fas fa-user-plus mr-2"></i>Register Library Member
                </h2>
            </div>
            <p class="text-gray-700 text-center mb-6">Librarian form to register new library members.</p>
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
            <form action="register.php" method="POST" id="registerForm" novalidate>
                <div class="form-group mb-4">
                    <label for="Member_ID" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-id-card mr-2"></i>Member ID
                    </label>
                    <input type="number" id="Member_ID" name="Member_ID" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="memberIdError"></p>
                </div>
                <div class="form-group mb-4">
                    <label for="Name" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user mr-2"></i>First Name
                    </label>
                    <input type="text" id="Name" name="Name" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="nameError"></p>
                </div>
                <div class="form-group mb-4">
                    <label for="Surname" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user mr-2"></i>Surname
                    </label>
                    <input type="text" id="Surname" name="Surname" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="surnameError"></p>
                </div>
                <div class="form-group mb-4">
                    <label for="Course_Department_Affliation" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-book mr-2"></i>Course/Department/Affiliation
                    </label>
                    <input type="text" id="Course_Department_Affliation" name="Course_Department_Affliation" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="courseError"></p>
                </div>
                <div class="form-group mb-4">
                    <label for="Membership_Type" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-users mr-2"></i>Membership Type
                    </label>
                    <select id="Membership_Type" name="Membership_Type" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                        <option value="" disabled selected>Select Membership Type</option>
                        <option value="Student">Student</option>
                        <option value="Staff">Staff</option>
                        <option value="External Member">External Member</option>
                    </select>
                    <p class="error-message" id="membershipTypeError"></p>
                </div>
                <div class="form-group mb-4">
                    <label for="Contact" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-phone mr-2"></i>Contact
                    </label>
                    <input type="tel" id="Contact" name="Contact" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" pattern="[0-9]{8,12}" required aria-required="true">
                    <p class="error-message" id="contactError"></p>
                </div>
                <div class="form-group mb-4">
                    <label for="Email" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-envelope mr-2"></i>Email
                    </label>
                    <input type="email" id="Email" name="Email" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="emailError"></p>
                </div>
                <div class="form-group mb-4 relative">
                    <label for="Password" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <input type="password" id="Password" name="Password" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <button type="button" onclick="togglePassword('Password')" class="absolute right-3 top-9 text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                    <p class="error-message" id="passwordError"></p>
                </div>
                <div class="form-group mb-4 relative">
                    <label for="Confirm_Password" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-lock mr-2"></i>Confirm Password
                    </label>
                    <input type="password" id="Confirm_Password" name="Confirm_Password" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <button type="button" onclick="togglePassword('Confirm_Password')" class="absolute right-3 top-9 text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                    <p class="error-message" id="confirmPasswordError"></p>
                </div>
                <div class="form-group mb-6">
                    <label for="Joined_Date" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-calendar-alt mr-2"></i>Joined Date
                    </label>
                    <input type="date" id="Joined_Date" name="Joined_Date" class="w-full px-4 py-2 border border-gray-300 rounded-md input-focus" required aria-required="true">
                    <p class="error-message" id="joinedDateError"></p>
                </div>
                <button type="submit" id="submitButton" class="w-full bg-blue-900 text-white py-3 rounded-md btn-hover flex items-center justify-center">
                    <i class="fas fa-user-plus mr-2"></i>Register Member
                    <i class="fas fa-spinner fa-spin hidden ml-2" id="loadingIcon"></i>
                </button>
            </form>
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    <a href="index.php" class="text-blue-900 hover:underline hover:text-blue-700">
                        <i class="fas fa-home mr-1"></i>Back to Home
                    </a>
                </p>
                
            </div>
        </div>
    </main>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye', input.type === 'password');
            icon.classList.toggle('fa-eye-slash', input.type !== 'password');
        }

        document.getElementById('registerForm').addEventListener('submit', function(event) {
            event.preventDefault();
            let isValid = true;

            // Clear previous error messages
            document.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
                el.classList.remove('show');
            });

            // Validate Member ID
            const memberId = document.getElementById('Member_ID').value.trim();
            if (!memberId || memberId <= 0) {
                document.getElementById('memberIdError').textContent = 'Valid Member ID is required.';
                document.getElementById('memberIdError').classList.add('show');
                isValid = false;
            }

            // Validate First Name
            const name = document.getElementById('Name').value.trim();
            if (!name) {
                document.getElementById('nameError').textContent = 'First Name is required.';
                document.getElementById('nameError').classList.add('show');
                isValid = false;
            }

            // Validate Surname
            const surname = document.getElementById('Surname').value.trim();
            if (!surname) {
                document.getElementById('surnameError').textContent = 'Surname is required.';
                document.getElementById('surnameError').classList.add('show');
                isValid = false;
            }

            // Validate Course/Department/Affiliation
            const course = document.getElementById('Course_Department_Affliation').value.trim();
            if (!course) {
                document.getElementById('courseError').textContent = 'Course/Department/Affiliation is required.';
                document.getElementById('courseError').classList.add('show');
                isValid = false;
            }

            // Validate Membership Type
            const membershipType = document.getElementById('Membership_Type').value;
            if (!membershipType) {
                document.getElementById('membershipTypeError').textContent = 'Membership Type is required.';
                document.getElementById('membershipTypeError').classList.add('show');
                isValid = false;
            }

            // Validate Contact
            const contact = document.getElementById('Contact').value.trim();
            if (!contact || !/^[0-9]{8,12}$/.test(contact)) {
                document.getElementById('contactError').textContent = 'Contact must be 8-12 digits.';
                document.getElementById('contactError').classList.add('show');
                isValid = false;
            }

            // Validate Email
            const email = document.getElementById('Email').value.trim();
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('emailError').textContent = 'Valid email is required.';
                document.getElementById('emailError').classList.add('show');
                isValid = false;
            }

            // Validate Password
            const password = document.getElementById('Password').value;
            if (!password || password.length < 8) {
                document.getElementById('passwordError').textContent = 'Password must be at least 8 characters.';
                document.getElementById('passwordError').classList.add('show');
                isValid = false;
            }

            // Validate Confirm Password
            const confirmPassword = document.getElementById('Confirm_Password').value;
            if (confirmPassword !== password) {
                document.getElementById('confirmPasswordError').textContent = 'Passwords do not match.';
                document.getElementById('confirmPasswordError').classList.add('show');
                isValid = false;
            }

            // Validate Joined Date
            const joinedDate = document.getElementById('Joined_Date').value;
            if (!joinedDate) {
                document.getElementById('joinedDateError').textContent = 'Joined Date is required.';
                document.getElementById('joinedDateError').classList.add('show');
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