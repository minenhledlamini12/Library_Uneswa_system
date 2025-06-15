<?php
if (!isset($_GET['qr_code_path'])) {
    header("Location: index.php");
    exit();
}

$qrCodePath = $_GET['qr_code_path'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Added Successfully</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 20px;
            color: #555;
            font-size: 18px;
        }

        .success-icon {
            color: #27ae60;
            font-size: 60px;
            margin-bottom: 20px;
        }

        .qr-code {
            margin: 30px 0;
        }

        .qr-code img {
            max-width: 200px;
            border: 1px solid #ddd;
            padding: 10px;
        }

        .button {
            background-color: #3498db;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            margin: 10px 5px;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #2980b9;
        }

        .print-button {
            background-color: #27ae60;
        }

        .print-button:hover {
            background-color: #219653;
        }
    </style>
</head>
<body>
    <div class="container">
        <i class="fas fa-check-circle success-icon"></i>
        <h1>Book Added Successfully</h1>
        <p>The book has been successfully added to the library database.</p>
        
        <div class="qr-code">
            <h2>QR Code for This Book</h2>
            <img src="<?php echo htmlspecialchars($qrCodePath); ?>" alt="Book QR Code">
        </div>
        
        <div>
            <a href="javascript:window.print();" class="button print-button">
                <i class="fas fa-print"></i> Print QR Code
            </a>
            <a href="add_book.php" class="button">
                <i class="fas fa-plus"></i> Add Another Book
            </a>
        </div>
    </div>
</body>
</html>
