<?php
if (isset($_GET['qr_code_path'])) {
    $qrCodePath = urldecode($_GET['qr_code_path']);  // Decode the URL-encoded path
} else {
    echo "<p style='color:red;'>QR Code Path is missing!</p>";
    exit();
}
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
            text-align: center;
        }

        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .qr-code-container {
            margin: 20px 0;
            text-align: center;
        }

        .qr-code-image {
            max-width: 200px;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: inline-block;
        }

        .download-button {
            background-color: #5cb85c;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        .download-button:hover {
            background-color: #4cae4c;
        }

        .instruction {
            margin-top: 20px;
            font-style: italic;
        }

        h2 {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-check-circle" style="color: green;"></i> Book Added Successfully!</h2>
        <p>A new book has been successfully added to the library system.</p>

        <div class="qr-code-container">
            <h3><i class="fas fa-qrcode"></i> Generated QR Code:</h3>
            <img src="<?php echo htmlspecialchars($qrCodePath); ?>" alt="QR Code" class="qr-code-image">
            <br>
            <a href="<?php echo htmlspecialchars($qrCodePath); ?>" class="download-button" download="book_qr_code.png">
                <i class="fas fa-download"></i> Download QR Code
            </a>
        </div>

        <p class="instruction"><i class="fas fa-info-circle"></i> Print this QR code on a sticker and paste it on the book for easy identification and scanning.</p>
    </div>
</body>
</html>
