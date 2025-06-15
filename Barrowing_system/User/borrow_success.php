<?php
session_start();

// Check if there's a message in the session
$message = isset($_SESSION['message']) ? $_SESSION['message'] : "Book borrowed successfully!";

// Clear the message from session to avoid displaying it again on refresh
$_SESSION['message'] = "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Borrowing Success - UNESWA Library</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            color: #28a745;
            font-size: 60px;
            margin-bottom: 20px;
        }
        h1 {
            color: #28a745;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            margin-bottom: 25px;
        }
        .email-notice {
            background-color: #e8f4fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            font-size: 16px;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✓</div>
        <h1>Borrowing Successful!</h1>
        <p><?php echo $message; ?></p>
        
        <div class="email-notice">
            <strong>Note:</strong> You will receive a confirmation email shortly with all the details.
        </div>
        
        <a href="barrowpage.php" class="btn">Return to Dashboard</a>
    </div>
</body>
</html>

