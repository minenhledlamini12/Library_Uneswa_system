<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESWA Library - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #003366;
            --primary-light: #6abf69;
            --secondary-color: #f5f5f5;
            --text-dark: #333;
            --text-light: #666;
            --white: #fff;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before, body::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            z-index: -1;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 15s infinite alternate ease-in-out;
        }

        body::before {
            background: var(--primary-light);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        body::after {
            background: var(--primary-dark);
            bottom: -100px;
            right: -100px;
            animation-delay: -7s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, 30px) scale(1.1); }
            100% { transform: translate(-30px, 15px) scale(0.9); }
        }

        .login-container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
            width: 420px;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px);}
            to { opacity: 1; transform: translateY(0);}
        }

        .decoration {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
            opacity: 0.1;
            z-index: 0;
        }

        .decoration-1 {
            width: 150px;
            height: 150px;
            top: -75px;
            right: -75px;
        }

        .decoration-2 {
            width: 100px;
            height: 100px;
            bottom: -50px;
            left: -50px;
        }

        .login-container h2 {
            color: var(--text-dark);
            margin-bottom: 25px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .university-logo {
            max-width: 180px;
            margin-bottom: 25px;
            animation: pulse 2s infinite alternate;
            position: relative;
            z-index: 1;
        }

        @keyframes pulse {
            from { transform: scale(1);}
            to { transform: scale(1.05);}
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
            position: relative;
            z-index: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-light);
            font-weight: 500;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .form-group:focus-within label {
            color: var(--primary-color);
        }

        .input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            color: #aaa;
            transition: var(--transition);
        }

        .form-group:focus-within .input-icon {
            color: var(--primary-color);
        }

        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #eee;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
            background-color: #f9f9f9;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: var(--white);
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);
        }

        .login-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 15px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 500;
            width: 100%;
            margin-bottom: 20px;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
            z-index: 1;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
            z-index: -1;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            background: linear-gradient(45deg, var(--primary-dark), var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 7px 15px rgba(76, 175, 80, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .login-btn:hover i {
            transform: translateX(5px);
        }

        .forgot-password {
            margin: 15px 0;
            font-size: 0.9rem;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .forgot-password a {
            color: var(--primary-dark);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
            font-weight: 500;
        }

        .forgot-password a:hover {
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .forgot-password a i {
            font-size: 1rem;
        }

        .signup-text {
            margin-top: 20px;
            color: var(--text-light);
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }

        .signup-link {
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            position: relative;
        }

        .signup-link::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary-color);
            transform: scaleX(0);
            transform-origin: bottom right;
            transition: transform 0.3s ease;
        }

        .signup-link:hover {
            color: var(--primary-color);
        }

        .signup-link:hover::after {
            transform: scaleX(1);
            transform-origin: bottom left;
        }

        .back-to-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--text-light);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            transition: var(--transition);
            z-index: 10;
        }

        .back-to-home:hover {
            color: var(--primary-color);
            transform: translateX(-3px);
        }

        @media (max-width: 500px) {
            .login-container {
                width: 90%;
                padding: 30px 20px;
                margin: 0 15px;
            }
            .university-logo {
                max-width: 150px;
            }
            .back-to-home {
                top: 10px;
                left: 10px;
                font-size: 0.8rem;
            }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px);}
            to { opacity: 1; transform: translateY(0);}
        }

        .form-group:nth-child(1) { animation: slideUp 0.4s 0.2s both; }
        .form-group:nth-child(2) { animation: slideUp 0.4s 0.4s both; }
        .login-btn { animation: slideUp 0.4s 0.6s both; }
        .forgot-password { animation: slideUp 0.4s 0.7s both; }
        .signup-text { animation: slideUp 0.4s 0.8s both; }
    </style>
</head>
<body>
    <a href="/php_program/Barrowing_system/homeabout.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>

    <div class="login-container">
        <!-- Decorative elements -->
        <div class="decoration decoration-1"></div>
        <div class="decoration decoration-2"></div>
        
        <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo" class="university-logo">
        <h2>Login to UNESWA Library</h2>
        
        <form action="loginprocessing.php" method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-container">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-container">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>
            
            <button type="submit" class="login-btn">
                Login <i class="fas fa-sign-in-alt"></i>
            </button>
        </form>
        
        <p class="forgot-password">
            <a href="forgot_password.php">
                <i class="fas fa-question-circle"></i> Forgot Password?
            </a>
        </p>
        
        <p class="signup-text">
            Not registered? <a href="admini_form.php" class="signup-link">Sign up here</a>
        </p>
    </div>

    <script>
        // Add focus and blur event listeners to inputs for enhanced interaction
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.parentElement.classList.remove('focused');
                }
            });
        });

        // Add subtle animation to login button on hover
        const loginBtn = document.querySelector('.login-btn');
        loginBtn.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            this.style.background = `radial-gradient(circle at ${x}px ${y}px, var(--primary-light), var(--primary-color))`;
        });
        
        loginBtn.addEventListener('mouseleave', function() {
            this.style.background = 'linear-gradient(45deg, var(--primary-color), var(--primary-light))';
        });
    </script>
</body>
</html>
