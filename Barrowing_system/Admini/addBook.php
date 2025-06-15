<?php
require_once("connection.php");
require_once __DIR__ . '/../vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Encryption Function
function encryptData($data, $key) {
    $method = 'aes-256-cbc';
    $ivSize = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivSize);
    
    $encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
    
    // Prepend IV to ciphertext and base64 encode
    return base64_encode($iv . $encrypted);
}

// Form processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve Data
    $ISBN = trim($_POST['ISBN']);
    $Title = trim($_POST['Title']);
    $Author = trim($_POST['Author']);
    $PublicationYear = trim($_POST['PublicationYear']);
    $Publisher = trim($_POST['Publisher']);
    $Format = trim($_POST['Format']);
    $Language = trim($_POST['Language']);
    $Pages = trim($_POST['Pages']);
    $Genre = trim($_POST['Genre']);
    $CopiesAvailable = trim($_POST['CopiesAvailable']);
    $Status = trim($_POST['Status']);
    $CallNumber = trim($_POST['CallNumber']);
    $AddedDate = date("Y-m-d H:i:s");  // Current timestamp
    $UpdatedDate = date("Y-m-d H:i:s");

    // Generate QR Code Data (Encrypt ISBN)
    $encryptionKey = "Pa@47781"; // Secure key
    $qrData = encryptData($ISBN, $encryptionKey);

    // Generate QR Code Image
    $qrCode = QrCode::create($qrData);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    // Save QR Code Image
    $qrCodePath = 'qrcodes/'. uniqid() . '.png';  // unique name
    $result->saveToFile($qrCodePath);

    // SQL Query
    $sql = "INSERT INTO books (ISBN, Title, Author, PublicationYear, Publisher, Format, Language, Pages, Genre, CopiesAvailable, Status, CallNumber, AddedDate, UpdatedDate, QrCode) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssssss", $ISBN, $Title, $Author, $PublicationYear, $Publisher, $Format, $Language, $Pages, $Genre, $CopiesAvailable, $Status, $CallNumber, $AddedDate, $UpdatedDate, $qrCodePath);

    // Execute statement
    if ($stmt->execute()) {
        // Redirect to success page with QR code path
        header("Location: success.php?qr_code_path=" . urlencode($qrCodePath));
        exit();
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }

    // Close statement
    $stmt->close();
}

// Function to generate year options
function yearOptions($startYear, $endYear) {
    $options = '';
    for ($year = $endYear; $year >= $startYear; $year--) {
        $options .= "<option value=\"$year\">$year</option>";
    }
    return $options;
}

$currentYear = date("Y");
$startYear = 1900;  // Earliest publication year
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Books</title>
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
            margin: auto;
            overflow: hidden;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            margin-top: 4px;
            margin-Bottom: 4px;
        }

        input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-book"></i> Add Books</h1>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

            <div class="form-group">
                <label for="ISBN"><i class="fas fa-barcode"></i> ISBN:</label>
                <input type="text" id="ISBN" name="ISBN" required>
            </div>

            <div class="form-group">
                <label for="Title"><i class="fas fa-heading"></i> Title:</label>
                <input type="text" id="Title" name="Title" required>
            </div>

            <div class="form-group">
                <label for="Author"><i class="fas fa-user"></i> Author:</label>
                <input type="text" id="Author" name="Author" required>
            </div>

            <div class="form-group">
                <label for="PublicationYear"><i class="fas fa-calendar-alt"></i> Publication Year:</label>
                <select id="PublicationYear" name="PublicationYear">
                    <?php echo yearOptions($startYear, $currentYear); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="Publisher"><i class="fas fa-university"></i> Publisher:</label>
                <input type="text" id="Publisher" name="Publisher" required>
            </div>

            <div class="form-group">
                <label for="Format"><i class="fas fa-file"></i> Format:</label>
                <select id="Format" name="Format">
                    <option value="Hardcover">Hardcover</option>
                    <option value="Paperback">Paperback</option>
                    <option value="E-book">E-book</option>
                    <option value="Audiobook">Audiobook</option>
                </select>
            </div>

            <div class="form-group">
                <label for="Language"><i class="fas fa-globe"></i> Language:</label>
                <input type="text" id="Language" name="Language" required>
            </div>

            <div class="form-group">
                <label for="Pages"><i class="fas fa-file-alt"></i> Pages:</label>
                <input type="number" id="Pages" name="Pages" required>
            </div>

            <div class="form-group">
                <label for="Genre"><i class="fas fa-tags"></i> Genre:</label>
                <input type="text" id="Genre" name="Genre" required>
            </div>

            <div class="form-group">
                <label for="CopiesAvailable"><i class="fas fa-copy"></i> Copies Available:</label>
                <input type="number" id="CopiesAvailable" name="CopiesAvailable" required>
            </div>

            <div class="form-group">
                <label for="Status"><i class="fas fa-info-circle"></i> Status:</label>
                <select id="Status" name="Status">
                    <option value="Available">Available</option>
                    <option value="Borrowed">Borrowed</option>
                    <option value="Reference">Reference</option>
                </select>
            </div>

            <div class="form-group">
                <label for="CallNumber"><i class="fas fa-hashtag"></i> Call Number:</label>
                <input type="text" id="CallNumber" name="CallNumber" required>
            </div>

            <input type="submit" value="Add Book">
        </form>
    </div>
</body>
</html>
