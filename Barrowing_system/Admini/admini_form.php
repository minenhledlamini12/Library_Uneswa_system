<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Sign Up - UNESWA Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .signup-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            color: #2c5f2d;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
        }

        input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76,175,80,0.3);
        }

        button {
            background: #4CAF50;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #367c39;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .login-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .fa-icon {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
   

    <div class="signup-container">
        <h2><i class="fas fa-user-shield fa-icon"></i>Librarian Registration</h2>
        <form action="SignUp.php" method="POST">
            <div class="form-group">
                <label for="firstName"><i class="fas fa-user fa-icon"></i>First Name</label>
                <input type="text" id="firstName" name="firstName" required>
            </div>

            <div class="form-group">
                <label for="secondName"><i class="fas fa-user fa-icon"></i>Second Name</label>
                <input type="text" id="secondName" name="secondName">
            </div>

            <div class="form-group">
                <label for="lastName"><i class="fas fa-user fa-icon"></i>Last Name</label>
                <input type="text" id="lastName" name="lastName" required>
            </div>

            <div class="form-group">
                <label for="role"><i class="fas fa-briefcase fa-icon"></i>Role</label>
                <input type="text" id="role" name="role" required>
            </div>

            <div class="form-group">
                <label for="contact"><i class="fas fa-phone fa-icon"></i>Contact</label>
                <input type="tel" id="contact" name="contact">
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope fa-icon"></i>Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock fa-icon"></i>Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirmPassword"><i class="fas fa-lock fa-icon"></i>Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>

            <button type="submit"><i class="fas fa-user-plus fa-icon"></i>Register</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="Login.php"><i class="fas fa-sign-in-alt"></i>Login</a>
        </div>
    </div>
</body>
</html>
