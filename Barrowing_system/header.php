<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESWA Library - Self Service Book Borrowing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Hero section */
        .hero {
            background: linear-gradient(rgba(76, 175, 80, 0.8), rgba(76, 175, 80, 0.9)), url('/php_program/Barrowing_system/Images/kwaluseni.jpg');
            background-size: cover;
            background-position: center;
            color: var(--white);
            padding: 80px 20px;
            text-align: center;
            margin-bottom: 40px;
            border-radius: var(--border-radius);
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto 30px;
        }

        /* Login cards in hero section */
        .login-cards {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 40px;
            perspective: 1000px;
        }

        .login-card {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: var(--border-radius);
            padding: 30px;
            width: 250px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            transform-style: preserve-3d;
            position: relative;
        }

        .login-card:hover {
            transform: translateY(-15px) rotateY(5deg);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .admin-card {
            border-bottom: 5px solid var(--primary-dark);
        }

        .member-card {
            border-bottom: 5px solid var(--primary-color);
        }

        .card-icon {
            width: 80px;
            height: 80px;
            background-color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .admin-card .card-icon {
            background-color: var(--primary-dark);
            color: white;
        }

        .member-card .card-icon {
            background-color: var(--primary-color);
            color: white;
        }

        .card-icon i {
            font-size: 2.5rem;
        }

        .login-card h3 {
            color: var(--text-dark);
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .login-card p {
            color: var(--text-light);
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .login-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .admin-card .login-btn {
            background-color: var(--primary-dark);
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .admin-card .login-btn:hover {
            background-color: white;
            color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .member-card .login-btn:hover {
            background-color: white;
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Footer Styling */
        footer {
            background-color: #333;
            color: var(--white);
            padding: 40px 20px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .footer-column h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background-color: var(--primary-color);
        }

        .footer-column p, .footer-column a {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 10px;
            display: block;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-column a:hover {
            color: var(--white);
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .login-cards {
                flex-direction: column;
                align-items: center;
            }

            .login-card {
                width: 100%;
                max-width: 280px;
                margin-bottom: 20px;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section with Login Options -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to UNESWA Library</h1>
            <p>Empowering education through accessible resources and innovative services. Discover our self-service book borrowing system designed to enhance your academic journey.</p>
            <div class="login-cards">
                <div class="login-card admin-card">
                    <div class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Administrator</h3>
                    <p>Access the library management system</p>
                    <a href="Admini/Login.php" class="login-btn">Admin Login</a>
                </div>
                <div class="login-card member-card">
                    <div class="card-icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <h3>Library Member</h3>
                    <p>Borrow books and access resources</p>
                    <a href="User/landingpage.php" class="login-btn">Member Login</a>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Additional content can be added here -->
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3>About Us</h3>
                <p>The UNESWA Library at Kwaluseni Campus provides a wide range of resources to support academic excellence.</p>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                <a href="#services">Services</a>
                <a href="#staff">Our Team</a>
                <a href="#contact">Contact Us</a>
            </div>
            <div class="footer-column">
                <h3>Contact Info</h3>
                <p>📍 Kwaluseni Campus, Eswatini</p>
                <p>📞 2517 0448</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            © 2025 University of Eswatini Library. All rights reserved.
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>