<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESWA Library - Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .reset-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 400px;
            text-align: center;
        }

        .reset-container h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .reset-btn {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            width: 100%;
            margin-bottom: 15px;
            transition: background-color 0.3s ease;
        }

        .reset-btn:hover {
            background-color: #367c39;
        }

        .login-link {
            color: #007bff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .login-link:hover {
            text-decoration: underline;
        }

        .university-logo {
            max-width: 200px;
            margin-bottom: 20px;
        }

        /* Style for icons */
        .form-group i {
            margin-right: 10px;
            color: #4CAF50;
        }
    </style>
</head>
<body>

    <div class="reset-container">
        <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo" class="university-logo">
        <h2>Reset Your Password</h2>
        <p>Enter your email address to receive a link to reset your password.</p>
        <form action="send_password_reset.php" method="post">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="reset-btn">Send Reset Link <i class="fas fa-paper-plane"></i></button>
        </form>
        <p>Remember your password? <a href="index.html" class="login-link"><i class="fas fa-sign-in-alt"></i> Back to Login</a></p>
    </div>

</body>
</html>
