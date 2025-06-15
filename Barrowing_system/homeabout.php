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
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
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
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Header styling - Top Bar */
        .top-bar {
            background-color: var(--primary-dark);
            color: white;
            padding: 0.5em 1em;
            font-size: 0.9em;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideInDown 0.5s ease;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            margin-left: 0.8em;
            transition: var(--transition);
        }

        .top-bar a:hover {
            color: var(--primary-light);
            transform: scale(1.1);
        }

        .top-bar-contact {
            display: flex;
            align-items: center;
        }

        /* Header Main (Green Section) */
        .header-main {
            background-color: var(--primary-color);
            color: white;
            padding: 1.5em;
            display: flex;
            justify-content: space-around;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease;
        }

        .header-main img {
            height: 60px;
            transition: var(--transition);
            animation: bounceIn 1s ease;
        }

        .header-main img:hover {
            transform: scale(1.05) rotate(5deg);
        }

        .header-main h1 {
            font-size: 2em;
            margin: 0;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .header-main span {
            font-style: italic;
            font-weight: 300;
        }

        /* Navigation Bar */
        .nav-bar {
            background-color: var(--primary-color);
            padding: 0.5em 1em;
            text-align: right;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: background-color 0.3s ease;
        }

        .nav-bar.sticky {
            background-color: rgba(76, 175, 80, 0.95);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .nav-bar a {
            display: inline-block;
            margin-left: 1em;
            text-decoration: none;
            color: var(--text-dark);
            padding: 0.5em 1.2em;
            border-radius: var(--border-radius);
            background-color: var(--white);
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-bar a:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Hero section */
        .hero {
            background: linear-gradient(45deg, rgba(76, 175, 80, 0.8), rgba(0, 51, 102, 0.8)), url('/php_program/Barrowing_system/Images/kwaluseni.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--white);
            padding: 80px 20px;
            text-align: center;
            margin-bottom: 40px;
            border-radius: var(--border-radius);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(76, 175, 80, 0.5), rgba(0, 51, 102, 0.5));
            animation: gradientShift 10s ease infinite;
            z-index: 0;
        }

        .hero h1, .hero p, .login-cards {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto 30px;
            animation: fadeInUp 1.2s ease;
        }

        /* Frame styling */
        .frame {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 40px;
            transition: var(--transition);
            opacity: 0;
            transform: translateY(50px);
        }

        .frame.visible {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .frame:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .frame h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .frame h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
            transition: width 0.3s ease;
        }

        .frame:hover h2::after {
            width: 100px;
        }

        /* Image-text frame */
        .image-text-frame {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .image-text-frame img {
            max-width: 40%;
            height: auto;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            object-fit: cover;
            animation: slideInLeft 1s ease;
            loading: lazy;
        }

        .image-text-frame img:hover {
            transform: scale(1.02) rotate(2deg);
        }

        .image-text-frame.image-left {
            flex-direction: row;
        }

        .image-text-frame.image-right {
            flex-direction: row-reverse;
        }

        .image-text-frame.image-right img {
            animation: slideInRight 1s ease;
        }

        .image-text-content p {
            margin-bottom: 15px;
            color: var(--text-light);
            animation: fadeIn 1.5s ease;
        }

        /* Core Values and Why Choose Us styling */
        .values-grid, .why-choose-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .value-item, .why-choose-item {
            padding: 20px;
            border-radius: var(--border-radius);
            background-color: var(--secondary-color);
            text-align: center;
            transition: var(--transition);
            opacity: 0;
            transform: translateY(20px);
        }

        .value-item.visible, .why-choose-item.visible {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .value-item:hover, .why-choose-item:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: var(--box-shadow);
            background-color: var(--white);
        }

        .value-item i, .why-choose-item i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .value-item:hover i, .why-choose-item:hover i {
            transform: rotate(360deg);
        }

        .value-item h3, .why-choose-item h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .value-item p, .why-choose-item p {
            font-size: 0.95rem;
            color: var(--text-light);
        }

        /* Services styling */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .service-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-radius: var(--border-radius);
            background-color: var(--secondary-color);
            transition: var(--transition);
            opacity: 0;
            transform: translateY(20px);
        }

        .service-item.visible {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .service-item:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: var(--box-shadow);
            background-color: var(--white);
        }

        .service-item i {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-right: 15px;
            min-width: 40px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .service-item:hover i {
            transform: rotate(360deg);
        }

        .service-item p {
            margin: 0;
            font-weight: 500;
        }

        /* Staff styling */
        .staff-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            justify-content: center;
        }

        .staff-member {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary-color);
            opacity: 0;
            transform: scale(0.9);
        }

        .staff-member.visible {
            opacity: 1;
            transform: scale(1);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .staff-member:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .staff-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            border: 3px solid var(--primary-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            loading: lazy;
        }

        .staff-photo:hover {
            transform: scale(1.05) rotate(5deg);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .staff-member h3 {
            margin-bottom: 10px;
            color: var(--text-dark);
            font-size: 1.3rem;
        }

        .staff-member p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        /* Contact form styling */
        .contact-frame {
            background: linear-gradient(135deg, var(--primary-light), var(--primary-dark));
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .contact-frame::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2), transparent);
            animation: pulse 8s ease infinite;
        }

        .contact-frame h2 {
            color: var(--white);
            z-index: 1;
            position: relative;
        }

        .contact-frame h2::after {
            background-color: var(--white);
        }

        .contact-frame p {
            color: rgba(255, 255, 255, 0.9);
            z-index: 1;
            position: relative;
        }

        .contact-form {
            max-width: 600px;
            margin: 30px auto 0;
            position: relative;
            z-index: 1;
        }

        .form-group {
            margin-bottom: 20px;
            opacity: 0;
            transform: translateX(-50px);
        }

        .form-group.visible {
            opacity: 1;
            transform: translateX(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: var(--border-radius);
            background-color: rgba(255, 255, 255, 0.9);
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
            background-color: var(--white);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background-color: var(--white);
            color: var(--primary-dark);
            border: none;
            padding: 15px 30px;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Footer styling */
        .site-footer {
            background-color: #333;
            color: var(--white);
            padding: 40px 0 20px;
            animation: fadeIn 1s ease;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
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
            transform: translateX(5px);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--white);
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px) scale(1.1);
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
            animation: fadeIn 1.5s ease;
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
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            padding: 30px;
            width: 250px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.4s ease;
            transform-style: preserve-3d;
            position: relative;
            opacity: 0;
            transform: translateY(50px);
        }

        .login-card.visible {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .login-card:hover {
            transform: translateY(-15px) rotateY(10deg);
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
            background-color: var (--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .login-card:hover .card-icon {
            transform: scale(1.1) rotate(360deg);
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
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .admin-card .login-btn {
            background-color: var(--primary-dark);
        }

        .login-btn:hover {
            transform: translateY(-3px) scale(1.05);
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

        /* Keyframe animations */
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.3; }
            100% { transform: scale(1); opacity: 0.5; }
        }

        @keyframes slideInLeft {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.2); opacity: 0.7; }
            70% { transform: scale(0.9); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .image-text-frame {
                flex-direction: column !important;
            }

            .image-text-frame img {
                max-width: 100%;
                margin-bottom: 20px;
            }
            
            .header-main {
                flex-direction: column;
                text-align: center;
                padding: 1em;
            }
            
            .header-main img {
                margin-bottom: 10px;
            }
            
            .header-main span {
                margin-top: 5px;
            }
            
            .staff-grid, .values-grid, .why-choose-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }

            .hero {
                background-attachment: scroll;
            }
        }

        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                padding: 0.5em;
            }
            
            .top-bar span {
                margin-bottom: 5px;
                text-align: center;
                font-size: 0.8em;
            }
            
            .top-bar-contact {
                margin-top: 5px;
            }
            
            .nav-bar {
                text-align: center;
                padding: 0.8em 0.5em;
            }
            
            .nav-bar a {
                margin: 0.3em;
                padding: 0.5em 1em;
                font-size: 0.9em;
            }

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

            .frame {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 20px 15px;
            }

            .frame h2 {
                font-size: 1.5rem;
            }

            .service-item, .value-item, .why-choose-item {
                padding: 15px;
            }
            
            .header-main h1 {
                font-size: 1.5em;
            }
            
            .header-main span {
                font-size: 0.9em;
            }
            
            .staff-grid, .values-grid, .why-choose-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="top-bar animate__animated animate__slideInDown">
        <span>🕒 Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM</span>
        <div class="top-bar-contact">
            📞 2517 0448
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
    </div>

    <div class="header-main animate__animated animate__fadeIn">
        <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo" class="animate__animated animate__bounceIn">
        <h1>University of Eswatini Library</h1>
        <span>Kwaluseni Campus - Self-Service Book Borrowing</span>
    </div>

    <!-- Navigation Bar -->
    <div class="nav-bar">
        <a href="#about"><i class="fas fa-university"></i> About</a>
        <a href="#services"><i class="fas fa-info-circle"></i> Services</a>
        <a href="#staff"><i class="fas fa-users"></i> Our Team</a>
        <a href="#contact"><i class="fas fa-envelope"></i> Contact</a>
    </div>

    <!-- Hero Section with Login Options -->
    <section class="hero">
        <div class="container">
            <h1 class="animate__animated animate__fadeInUp">Welcome to UNESWA Library</h1>
            <p class="animate__animated animate__fadeInUp animate__delay-1s">Empowering education through accessible resources and innovative services. Discover our self-service book borrowing system designed to enhance your academic journey.</p>
        
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

        <section class="frame" id="about">
            <div class="image-text-frame image-right">
                <img src="/php_program/Barrowing_system/Images/kwaluseni.jpg" alt="Library Description Image">
                <div class="image-text-content">
                    <h2>The University of Eswatini Library</h2>
                    <p>The University of Eswatini Library System comprises three decentralized branches—Kwaluseni Library, Mbabane Library, and another Kwaluseni Library—each uniquely staffed and stocked, yet coordinated under the Kwaluseni campus. This structure enables a seamless internal loan program, allowing resources from each unit to be accessible across all libraries.</p>
                    <p>Our diverse collection primarily features undergraduate textbooks, scholarly journals, essential reference materials, and unique special collections. The Kwaluseni Library, as the flagship branch, serves a wide range of faculties, including Commerce, Education, Humanities, Science & Engineering, Social Sciences, as well as the Institute of Distance Education and the Institute of Post-Graduate Studies.</p>
                    <p>With a seating capacity of over 700, the library also offers dedicated study carrels for postgraduate students and faculty, providing an ideal environment for academic success.</p>
                </div>
            </div>
            <div class="image-text-content">
                <h2>Vision and Mission</h2>
                <p><strong>Vision:</strong> To be a leading academic library in the region, driving knowledge creation, innovation, and lifelong learning through digitalization and automation of resources and services.</p>
                <p><strong>Mission:</strong> To provide high-quality, digitally accessible information services and resources that support the teaching, learning, and research needs of the University of Eswatini community, while promoting intellectual growth and academic excellence.</p>
            </div>
            <div class="image-text-content">
                <h2>Core Values</h2>
                <div class="values-grid">
                    <div class="value-item">
                        <i class="fas fa-lightbulb"></i>
                        <h3>Innovation</h3>
                        <p>Embracing cutting-edge technology to enhance access and efficiency.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-book-open"></i>
                        <h3>Accessibility</h3>
                        <p>Ensuring resources are available to all members of our community.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-handshake"></i>
                        <h3>Collaboration</h3>
                        <p>Fostering partnerships to support academic and research excellence.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-star"></i>
                        <h3>Excellence</h3>
                        <p>Striving for the highest standards in service and resource provision.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="frame" id="why-choose-us">
            <h2>Why Choose Us</h2>
            <p>Discover what sets the University of Eswatini Library apart as your academic partner.</p>
            <div class="why-choose-grid">
                <div class="why-choose-item">
                    <i class="fas fa-laptop-code"></i>
                    <h3>Advanced Technology</h3>
                    <p>Our self-service borrowing system and e-resources platform offer unmatched convenience.</p>
                </div>
                <div class="why-choose-item">
                    <i class="fas fa-books"></i>
                    <h3>Extensive Collections</h3>
                    <p>Access a vast array of textbooks, journals, and special collections across multiple disciplines.</p>
                </div>
                <div class="why-choose-item">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Support for All</h3>
                    <p>Dedicated spaces and resources for undergraduates, postgraduates, and faculty.</p>
                </div>
                <div class="why-choose-item">
                    <i class="fas fa-headset"></i>
                    <h3>Expert Assistance</h3>
                    <p>Our knowledgeable staff is always ready to support your research and academic needs.</p>
                </div>
            </div>
        </section>

        <section class="frame" id="purpose">
            <div class="image-text-frame image-left">
                <img src="/php_program/Barrowing_system/Images/kiosk.jpg" alt="Purpose Image">
                <div class="image-text-content">
                    <h2>Streamline Your Book Borrowing Journey</h2>
                    <p>Welcome to the University of Eswatini Library portal, designed to empower you with a seamless self-service book borrowing experience. This platform allows registered patrons to effortlessly explore our extensive collections, locate their desired reads, and initiate borrowing requests—all from the comfort of their own screens.</p>
                    <p>Our goal is to enhance your library experience by providing easy access to resources and streamlining the borrowing process, making it more convenient and efficient for you. Enjoy the freedom to manage your library needs at your fingertips!</p>
                </div>
            </div>
        </section>

        <section class="frame" id="services">
            <h2>Explore Our Services</h2>
            <p>Discover the wide range of services available to support your academic journey and research needs.</p>

            <div class="services-grid">
                <div class="service-item">
                    <i class="fas fa-print"></i>
                    <p>Print, Copy & Scan</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-desktop"></i>
                    <p>Computer Labs & Apple Hub of Creativity</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-book"></i>
                    <p>Growing Collection of E-resources and E-books</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-graduation-cap"></i>
                    <p>Academic Reserves</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-exchange-alt"></i>
                    <p>Inter-library Loans</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-search"></i>
                    <p>Research Support</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-barcode"></i>
                    <p>ISBN Subscription</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-users"></i>
                    <p>External Membership (Individuals and Institutions)</p>
                </div>
            </div>
        </section>

        <section class="frame" id="staff">
            <h2>Meet Our Dedicated Team</h2>
            <p>Our knowledgeable staff is here to assist you with all your library needs.</p>
            
            <div class="staff-grid">
                <div class="staff-member">
                    <img src="/php_program/Barrowing_system/Images/Minenhle.jpeg" alt="Minenhle Dlamini" class="staff-photo">
                    <h3>Ms. T. Dlamini</h3>
                    <p>Librarian</p>
                </div>
                <div class="staff-member">
                    <img src="/php_program/Barrowing_system/Images/Dr Lihle.jpeg" alt="Dr. T. Dlamini" class="staff-photo">
                    <h3>Dr. T. Dlamini</h3>
                    <p>Senior Librarian</p>
                </div>
				<div class="staff-member">
                    <img src="/php_program/Barrowing_system/Images/Sanele.png" alt="Dr. T. Dlamini" class="staff-photo">
                    <h3>Eng. S. Motsa</h3>
                    <p>Library Director</p>
                </div>
                <div class="staff-member">
                    <img src="/php_program/Barrowing_system/Images/Dr Lupupa.jpeg" alt="Dr. Lupupa" class="staff-photo">
                    <h3>Dr. M. Lupupa</h3>
                    <p>Senior Assistant Librarian</p>
                </div>
            </div>
        </section>

        <section class="frame contact-frame" id="contact">
            <h2>Get in Touch <i class="fas fa-envelope"></i></h2>
            <p>We'd love to hear from you! Send us a message and we'll get back to you as soon as possible.</p>
            
            <div class="contact-form">
                <form id="contact-form">
                    <div class="form-group">
                        <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" name="message" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </section>

    </div>

    <?php include 'footer.php'; ?>

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

            // Sticky navigation
            const navBar = document.querySelector('.nav-bar');
            const stickyOffset = navBar.offsetTop;

            window.addEventListener('scroll', () => {
                if (window.pageYOffset >= stickyOffset) {
                    navBar.classList.add('sticky');
                } else {
                    navBar.classList.remove('sticky');
                }
            });

            // IntersectionObserver for scroll animations
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.frame, .service-item, .staff-member, .login-card, .form-group, .value-item, .why-choose-item').forEach(element => {
                observer.observe(element);
            });

            // Form submission animation (mock)
            const form = document.getElementById('contact-form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const submitBtn = form.querySelector('.submit-btn');
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                    setTimeout(() => {
                        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
                        alert('Message sent successfully! (This is a demo)');
                        form.reset();
                    }, 2000);
                });
            }
        });
    </script>
</body>
</html>