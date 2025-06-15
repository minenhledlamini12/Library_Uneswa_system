<?php
require_once("connection.php");

// Fetch Book Data
if (isset($_GET['id'])) {
    $bookID = $_GET['id'];
    $sql = "SELECT * FROM books WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $book = $result->fetch_assoc();
    } else {
        echo "<p class='error-message animate__animated animate__fadeIn'>Book not found.</p>";
        exit;
    }
    $stmt->close();
} else {
    echo "<p class='error-message animate__animated animate__fadeIn'>Book ID not provided.</p>";
    exit;
}

// Update Book Data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    $UpdatedDate = date("Y-m-d H:i:s");
    
    // Set Edit_Status when a book is edited
    $EditStatus = "Edited";  // You can customize this value as needed

    $sql = "UPDATE books SET 
            ISBN = ?, Title = ?, Author = ?, PublicationYear = ?, Publisher = ?, 
            Format = ?, Language = ?, Pages = ?, Genre = ?, CopiesAvailable = ?, 
            Status = ?, CallNumber = ?, UpdatedDate = ?, Edit_Status = ?
            WHERE ID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssssi", $ISBN, $Title, $Author, $PublicationYear, $Publisher, 
                      $Format, $Language, $Pages, $Genre, $CopiesAvailable, 
                      $Status, $CallNumber, $UpdatedDate, $EditStatus, $bookID);

    if ($stmt->execute()) {
        echo "<p class='success-message animate__animated animate__fadeIn'>Book updated successfully.</p>";
    } else {
        echo "<p class='error-message animate__animated animate__fadeIn'>Error updating book: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            --border-radius: 12px;
            --box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
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
            background: linear-gradient(135deg, #f8f9fa, #e8ecef);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            overflow-x: hidden;
        }

        .container {
            max-width: 900px;
            background: linear-gradient(145deg, var(--white), #f9f9f9);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(50px);
        }

        .container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(76, 175, 80, 0.1), transparent);
            animation: pulse 10s ease infinite;
        }

        .container.visible {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        h2 {
            color: var(--primary-dark);
            font-size: 2.2rem;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1s ease;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            transition: width 0.4s ease;
        }

        .container:hover h2::after {
            width: 120px;
        }

        form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateX(-50px);
        }

        .form-group.visible {
            opacity: 1;
            transform: translateX(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
            transition-delay: calc(0.1s * var(--animation-order));
        }

        label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 10px;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        label i {
            color: var(--primary-color);
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        label:hover i {
            transform: scale(1.2) rotate(360deg);
        }

        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 14px 14px 14px 40px;
            border: none;
            border-radius: var(--border-radius);
            background-color: var(--secondary-color);
            font-size: 1rem;
            transition: var(--transition);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        input[type="text"]:focus, input[type="number"]:focus, select:focus {
            outline: none;
            background-color: var(--white);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.3);
            transform: scale(1.02);
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.1rem;
            pointer-events: none;
        }

        input[type="submit"] {
            grid-column: span 2;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            color: var(--white);
            padding: 14px 30px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            margin: 20px auto 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        input[type="submit"]::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: 0.5s;
        }

        input[type="submit"]:hover::before {
            left: 100%;
        }

        input[type="submit"]:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        input[type="submit"]:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--primary-dark);
            font-weight: 500;
            font-size: 1rem;
            text-decoration: none;
            margin-top: 20px;
            transition: var(--transition);
            position: relative;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            background-color: var(--secondary-color);
        }

        .back-link:hover {
            background-color: var(--primary-dark);
            color: var(--white);
            transform: translateX(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .back-link i {
            transition: transform 0.3s ease;
        }

        .back-link:hover i {
            transform: translateX(-5px);
        }

        .success-message {
            color: var(--primary-color);
            background: rgba(76, 175, 80, 0.15);
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            box-shadow: var(--box-shadow);
        }

        .error-message {
            color: #d9534f;
            background: rgba(217, 83, 79, 0.15);
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            box-shadow: var(--box-shadow);
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInLeft {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.3; }
            100% { transform: scale(1); opacity: 0.5; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 20px;
            }

            form {
                grid-template-columns: 1fr;
            }

            input[type="submit"] {
                grid-column: span 1;
            }

            h2 {
                font-size: 1.8rem;
            }

            input[type="text"], input[type="number"], select {
                padding: 12px 12px 12px 36px;
            }
        }

        @media (max-width: 576px) {
            h2 {
                font-size: 1.5rem;
            }

            .form-group {
                margin-bottom: 15px;
            }

            label {
                font-size: 0.9rem;
            }

            input[type="submit"] {
                padding: 12px 20px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-edit"></i> Edit Book</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $bookID; ?>">
            <div class="form-group" style="--animation-order: 1;">
                <label for="ISBN"><i class="fas fa-barcode"></i> ISBN:</label>
                <i class="fas fa-barcode input-icon"></i>
                <input type="text" name="ISBN" value="<?php echo htmlspecialchars($book['ISBN']); ?>">
            </div>
            
            <div class="form-group" style="--animation-order: 2;">
                <label for="Title"><i class="fas fa-heading"></i> Title:</label>
                <i class="fas fa-heading input-icon"></i>
                <input type="text" name="Title" value="<?php echo htmlspecialchars($book['Title']); ?>">
            </div>

            <div class="form-group" style="--animation-order: 3;">
                <label for="Author"><i class="fas fa-user"></i> Author:</label>
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="Author" value="<?php echo htmlspecialchars($book['Author']); ?>">
            </div>

            <div class="form-group" style="--animation-order: 4;">
                <label for="PublicationYear"><i class="fas fa-calendar-alt"></i> Publication Year:</label>
                <i class="fas fa-calendar-alt input-icon"></i>
                <input type="number" name="PublicationYear" value="<?php echo htmlspecialchars($book['PublicationYear']); ?>">
            </div>
            
            <div class="form-group" style="--animation-order: 5;">
                <label for="Publisher"><i class="fas fa-university"></i> Publisher:</label>
                <i class="fas fa-university input-icon"></i>
                <input type="text" name="Publisher" value="<?php echo htmlspecialchars($book['Publisher']); ?>">
            </div>
            
            <div class="form-group" style="--animation-order: 6;">
                <label for="Format"><i class="fas fa-file"></i> Format:</label>
                <i class="fas fa-file input-icon"></i>
                <select name="Format">
                    <option value="Hardcover" <?php if ($book['Format'] == 'Hardcover') echo 'selected'; ?>>Hardcover</option>
                    <option value="Paperback" <?php if ($book['Format'] == 'Paperback') echo 'selected'; ?>>Paperback</option>
                    <option value="E-book" <?php if ($book['Format'] == 'E-book') echo 'selected'; ?>>E-book</option>
                    <option value="Audiobook" <?php if ($book['Format'] == 'Audiobook') echo 'selected'; ?>>Audiobook</option>
                </select>
            </div>

            <div class="form-group" style="--animation-order: 7;">
                <label for="Language"><i class="fas fa-globe"></i> Language:</label>
                <i class="fas fa-globe input-icon"></i>
                <input type="text" name="Language" value="<?php echo htmlspecialchars($book['Language']); ?>">
            </div>

            <div class="form-group" style="--animation-order: 8;">
                <label for="Pages"><i class="fas fa-file-alt"></i> Pages:</label>
                <i class="fas fa-file-alt input-icon"></i>
                <input type="number" name="Pages" value="<?php echo htmlspecialchars($book['Pages']); ?>">
            </div>

            <div class="form-group" style="--animation-order: 9;">
                <label for="Genre"><i class="fas fa-tags"></i> Genre:</label>
                <i class="fas fa-tags input-icon"></i>
                <input type="text" name="Genre" value="<?php echo htmlspecialchars($book['Genre']); ?>">
            </div>

            <div class="form-group" style="--animation-order: 10;">
                <label for="CopiesAvailable"><i class="fas fa-copy"></i> Copies Available:</label>
                <i class="fas fa-copy input-icon"></i>
                <input type="number" name="CopiesAvailable" value="<?php echo htmlspecialchars($book['CopiesAvailable']); ?>">
            </div>

            <div class="form-group" style="--animation-order: 11;">
                <label for="Status"><i class="fas fa-info-circle"></i> Status:</label>
                <i class="fas fa-info-circle input-icon"></i>
                <select name="Status">
                    <option value="Available" <?php if ($book['Status'] == 'Available') echo 'selected'; ?>>Available</option>
                    <option value="Borrowed" <?php if ($book['Status'] == 'Borrowed') echo 'selected'; ?>>Borrowed</option>
                    <option value="Reference" <?php if ($book['Status'] == 'Reference') echo 'selected'; ?>>Reference</option>
                </select>
            </div>
            
            <div class="form-group" style="--animation-order: 12;">
                <label for="CallNumber"><i class="fas fa-hashtag"></i> Call Number:</label>
                <i class="fas fa-hashtag input-icon"></i>
                <input type="text" name="CallNumber" value="<?php echo htmlspecialchars($book['CallNumber']); ?>">
            </div>

            <input type="submit" value="Update Book">
        </form>
        <a href="manage.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Manage Books</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            document.querySelectorAll('.container, .form-group').forEach(element => {
                observer.observe(element);
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>