<?php
include("connection.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESWA Library Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #218838;
            --accent-color: #f4a261;
            --background-color: #f5f7fa;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --input-border: #e1e5e9;
            --input-focus: #28a745;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--background-color) 0%, #e8f5e9 100%);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(40, 167, 69, 0.05) 0%, transparent 70%);
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Top Bar */
        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            animation: slideInUp 0.8s ease-out;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            margin-left: 0.5rem;
            transition: all 0.3s ease;
            padding: 0.25rem;
            border-radius: 4px;
        }

        .top-bar a:hover {
            color: var(--accent-color);
            transform: scale(1.1);
        }

        /* Header */
        .header-main {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            justify-content: center;
            z-index: 999;
            animation: slideInUp 1s ease-out 0.2s both;
        }

        .header-main .logo-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.75rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            animation: pulse 2s infinite;
        }

        .header-main .logo-container img {
            height: 50px;
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .header-main .title-container h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .header-main .subtitle {
            font-style: italic;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        /* Main Container */
        .container {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            position: relative;
            margin-top: 180px;
            animation: slideInUp 1.2s ease-out 0.4s both;
            border: 1px solid rgba(40, 167, 69, 0.1);
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px 20px 0 0;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
            animation: fadeIn 1.5s ease-out 0.6s both;
        }

        .form-header .icon-container {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
            animation: pulse 2s infinite;
        }

        .form-header .icon-container i {
            font-size: 2rem;
            color: white;
        }

        .form-header h2 {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: #64748b;
            font-size: 1rem;
        }

        /* Form Styling - Single Column Layout */
        .form-container {
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            animation: slideInLeft 0.8s ease-out both;
        }

        .form-group:nth-child(1) { animation-delay: 0.8s; }
        .form-group:nth-child(2) { animation-delay: 0.9s; }
        .form-group:nth-child(3) { animation-delay: 1.0s; }
        .form-group:nth-child(4) { animation-delay: 1.1s; }
        .form-group:nth-child(5) { animation-delay: 1.2s; }
        .form-group:nth-child(6) { animation-delay: 1.3s; }
        .form-group:nth-child(7) { animation-delay: 1.4s; }
        .form-group:nth-child(8) { animation-delay: 1.5s; }
        .form-group:nth-child(9) { animation-delay: 1.6s; }

        label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        label i {
            color: var(--primary-color);
            width: 16px;
            text-align: center;
        }

        .input-container {
            position: relative;
        }

        input, select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--input-focus);
            background: white;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
            transform: translateY(-2px);
        }

        input:hover, select:hover {
            border-color: var(--primary-color);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
            transition: color 0.3s ease;
            padding: 0.25rem;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        /* Submit Button */
        .submit-container {
            text-align: center;
            animation: slideInUp 1s ease-out 1.7s both;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(40, 167, 69, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        /* Back Link */
        .back-link {
            text-align: center;
            margin-top: 2rem;
            animation: fadeIn 1s ease-out 1.8s both;
        }

        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .back-link a:hover {
            background: rgba(40, 167, 69, 0.1);
            transform: translateX(-5px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                margin: 160px 1rem 2rem;
                padding: 2rem 1.5rem;
            }

            .header-main {
                flex-direction: column;
                text-align: center;
                padding: 1rem;
            }

            .header-main h1 {
                font-size: 1.5rem;
            }

            .top-bar {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
                padding: 0.5rem;
            }

            .form-header h2 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                margin-top: 200px;
            }

            .top-bar-left span {
                display: none;
            }
        }

        /* Loading Animation */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .loading .submit-btn {
            background: #6c757d;
        }

        .loading .submit-btn::after {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-left">
            <i class="fas fa-clock"></i>
            <span>Mon-Fri: 08:30 AM - 11:00 PM | Sat: 10:00 AM - 05:00 PM | Sun: 03:00 PM - 10:00 PM</span>
        </div>
        <div class="top-bar-right">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-phone"></i>
                <span>2517 0448</span>
            </div>
            <div>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="header-main">
        <div class="logo-container">
            <img src="/php_program/Barrowing_system/Images/download.png" alt="University of Eswatini Library Logo">
        </div>
        <div class="title-container">
            <h1>University of Eswatini Library</h1>
            <div class="subtitle">Kwaluseni Campus - Member Registration</div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <div class="form-header">
            <div class="icon-container">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>Member Registration</h2>
            <p>Join the UNESWA Library community and access our extensive resources</p>
        </div>

        <form method="post" action="register.php" id="registrationForm">
            <div class="form-container">
                <div class="form-group">
                    <label for="ID">
                        <i class="fas fa-id-card"></i>
                        Member ID
                    </label>
                    <div class="input-container">
                        <input type="number" id="ID" name="ID" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="Name">
                        <i class="fas fa-user"></i>
                        First Name
                    </label>
                    <div class="input-container">
                        <input type="text" id="Name" name="Name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="Surname">
                        <i class="fas fa-user"></i>
                        Surname
                    </label>
                    <div class="input-container">
                        <input type="text" id="Surname" name="Surname" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="Course/Department/Affliation">
                        <i class="fas fa-graduation-cap"></i>
                        Course/Department/Affiliation
                    </label>
                    <div class="input-container">
                        <input type="text" id="Course/Department/Affliation" name="Course/Department/Affliation" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="Membership_Type">
                        <i class="fas fa-users"></i>
                        Membership Type
                    </label>
                    <div class="input-container">
                        <select id="Membership_Type" name="Membership_Type" required>
                            <option value="">Select membership type</option>
                            <option value="Student">Student</option>
                            <option value="Staff">Staff</option>
                            <option value="External Member">External Member</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="Contact">
                        <i class="fas fa-phone"></i>
                        Contact Number
                    </label>
                    <div class="input-container">
                        <input type="tel" id="Contact" name="Contact" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="Email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <div class="input-container">
                        <input type="email" id="Email" name="Email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="Password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-container">
                        <input type="password" id="Password" name="Password" required>
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="Joined_Date">
                        <i class="fas fa-calendar-alt"></i>
                        Joined Date
                    </label>
                    <div class="input-container">
                        <input type="date" id="Joined_Date" name="Joined_Date" required>
                    </div>
                </div>
            </div>

            <div class="submit-container">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-user-plus"></i>
                    Register Member
                </button>
            </div>
        </form>

        <div class="back-link">
            <a href="homepage.php">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
        </div>
    </div>

    <script>
        // Password toggle functionality
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#Password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        // Form submission with loading state
        const form = document.getElementById('registrationForm');
        const submitBtn = document.querySelector('.submit-btn');

        form.addEventListener('submit', function(e) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
            submitBtn.disabled = true;
            document.body.classList.add('loading');
        });

        // Set today's date as default for joined date
        document.getElementById('Joined_Date').valueAsDate = new Date();

        // Input validation and styling
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() !== '') {
                    this.style.borderColor = 'var(--success-color)';
                } else {
                    this.style.borderColor = 'var(--input-border)';
                }
            });
        });

        // Phone number formatting
        const phoneInput = document.getElementById('Contact');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 8) {
                value = value.substring(0, 8);
            }
            e.target.value = value;
        });

        // Email validation
        const emailInput = document.getElementById('Email');
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.style.borderColor = 'var(--error-color)';
            } else if (this.value) {
                this.style.borderColor = 'var(--success-color)';
            }
        });
    </script>
</body>
</html>