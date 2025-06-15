<?php
session_start(); // Start session to access session variables
require_once("connection.php");

// Get the logged-in username from session, default to 'System' if not set
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'System';

// Handle search query
$search_query = isset($_POST['search']) ? trim($_POST['search']) : '';
$search_condition = '';
$search_params = [];
$param_types = '';

if (!empty($search_query)) {
    $search_condition = " WHERE (Title LIKE ? OR ISBN LIKE ? OR Author LIKE ?)";
    $search_term = "%$search_query%";
    $search_params = [$search_term, $search_term, $search_term];
    $param_types = "sss";
}

// -----------------------------------------------------------------------------
// 1. GET ADDED BOOKS
// -----------------------------------------------------------------------------
$added_sql = "SELECT AddedDate AS EventDate, ? AS User, 'Added' AS Action, ISBN, Title, Author, PublicationYear, Publisher 
              FROM books" . $search_condition . " 
              ORDER BY AddedDate DESC";
$added_stmt = $conn->prepare($added_sql);
if (!empty($search_query)) {
    $added_stmt->bind_param("s" . $param_types, $username, ...$search_params);
} else {
    $added_stmt->bind_param("s", $username);
}
$added_stmt->execute();
$added_result = $added_stmt->get_result();

// -----------------------------------------------------------------------------
// 2. GET DELETED BOOKS
// -----------------------------------------------------------------------------
$deleted_sql = "SELECT DeletedAt AS EventDate, ? AS User, 'Deleted' AS Action, ISBN, Title, Author, PublicationYear, Publisher 
                FROM deleted_books" . $search_condition . " 
                ORDER BY DeletedAt DESC";
$deleted_stmt = $conn->prepare($deleted_sql);
if (!empty($search_query)) {
    $deleted_stmt->bind_param("s" . $param_types, $username, ...$search_params);
} else {
    $deleted_stmt->bind_param("s", $username);
}
$deleted_stmt->execute();
$deleted_result = $deleted_stmt->get_result();

// -----------------------------------------------------------------------------
// 3. GET EDITED BOOKS
// -----------------------------------------------------------------------------
$edited_sql = "SELECT UpdatedDate AS EventDate, ? AS User, 'Edited' AS Action, ISBN, Title, Author, PublicationYear, Publisher 
               FROM books 
               WHERE Edit_Status = 'Edited'" . ($search_condition ? " AND" . substr($search_condition, 6) : '') . " 
               ORDER BY UpdatedDate DESC";
$edited_stmt = $conn->prepare($edited_sql);
if (!empty($search_query)) {
    $edited_stmt->bind_param("s" . $param_types, $username, ...$search_params);
} else {
    $edited_stmt->bind_param("s", $username);
}
$edited_stmt->execute();
$edited_result = $edited_stmt->get_result();

// For debugging - check if edited books query returns results
if (!$edited_result) {
    echo "<p>Error in edited books query: " . $conn->error . "</p>";
} else {
    $edited_count = $edited_result->num_rows;
}

// Combine all results into one array
$history = [];
if ($added_result && $added_result->num_rows > 0) {
    while ($row = $added_result->fetch_assoc()) {
        $history[] = $row;
    }
}
if ($deleted_result && $deleted_result->num_rows > 0) {
    while ($row = $deleted_result->fetch_assoc()) {
        $history[] = $row;
    }
}
if ($edited_result && $edited_result->num_rows > 0) {
    while ($row = $edited_result->fetch_assoc()) {
        $history[] = $row;
    }
}

// Sort the combined history by EventDate (descending)
usort($history, function($a, $b) {
    return strtotime($b['EventDate']) - strtotime($a['EventDate']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Book History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #4CAF50; /* Main green color */
            --light-green: #6abf69; /* Lighter green for highlights */
            --dark-green: #388e3c; /* Darker green for hover effects */
            --accent-blue: #003366; /* Secondary blue for accents */
            --light-blue: #336699; /* Light blue for subtle highlights */
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
            background-color: var(--secondary-color);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Background decoration with green focus */
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
            background: var(--light-green);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        body::after {
            background: var(--primary-green);
            bottom: -100px;
            right: -100px;
            animation-delay: -7s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, 30px) scale(1.1); }
            100% { transform: translate(-30px, 15px) scale(0.9); }
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            animation: fadeIn 0.8s ease-out;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Decorative elements with green theme */
        .decoration {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-green), var(--light-green));
            opacity: 0.2;
            z-index: 0;
            animation: pulseDecoration 3s infinite alternate;
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

        @keyframes pulseDecoration {
            from { transform: scale(1); opacity: 0.2; }
            to { transform: scale(1.3); opacity: 0.3; }
        }

        h1 {
            color: var(--primary-green);
            margin-bottom: 25px;
            font-weight: 600;
            position: relative;
            z-index: 1;
            animation: slideUp 0.4s ease-out;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h1 i {
            color: var(--accent-blue);
            animation: pulseIcon 2s infinite alternate;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulseIcon {
            from { transform: scale(1); }
            to { transform: scale(1.2); }
        }

        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            animation: slideUp 0.4s 0.1s both;
            position: relative;
            z-index: 1;
        }

        .search-container {
            position: relative;
            flex-grow: 1;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-blue);
            transition: var(--transition);
        }

        .search-form input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 2px solid #e8f5e9;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
            background-color: #f9f9f9;
        }

        .search-form input:focus {
            outline: none;
            border-color: var(--primary-green);
            background-color: var(--white);
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);
        }

        .search-form input:focus + .search-icon {
            color: var(--primary-green);
        }

        .search-btn, .clear-btn {
            background: var(--primary-green);
            color: var(--white);
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-btn::before, .clear-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
            z-index: -1;
        }

        .search-btn:hover::before, .clear-btn:hover::before {
            left: 100%;
        }

        .search-btn:hover, .clear-btn:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(56, 142, 60, 0.3);
        }

        .clear-btn {
            background: #e8f5e9;
            color: var(--primary-green);
        }

        .clear-btn:hover {
            background: #d4edda;
        }

        .status-info {
            background: #d4edda;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border-left: 5px solid var(--primary-green);
            position: relative;
            z-index: 1;
            animation: slideUp 0.4s 0.2s both;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            color: var(--text-dark);
        }

        .status-info:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .status-info i {
            color: var(--accent-blue);
            animation: pulseIcon 2s infinite alternate;
        }

        .status-counts {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            gap: 15px;
            position: relative;
            z-index: 1;
        }

        .count-box {
            text-align: center;
            padding: 15px;
            border-radius: var(--border-radius);
            width: 30%;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            background: #e8f5e9;
            color: var(--primary-green);
        }

        .count-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
            z-index: -1;
        }

        .count-box:hover::before {
            left: 100%;
        }

        .count-box:hover {
            transform: scale(1.05) translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            background: var(--light-green);
        }

        .count-box i {
            color: var(--accent-blue);
            animation: pulseIcon 2s infinite alternate;
        }

        .count-box:nth-child(1) { animation: slideUp 0.4s 0.4s both; }
        .count-box:nth-child(2) { animation: slideUp 0.4s 0.5s both; }
        .count-box:nth-child(3) { animation: slideUp 0.4s 0.6s both; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2em;
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            position: relative;
            z-index: 1;
            box-shadow: var(--box-shadow);
            animation: fadeIn 0.8s ease-out;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #e8f5e9;
            text-align: left;
            transition: var(--transition);
        }

        th {
            background: var(--primary-green);
            color: var(--white);
            font-weight: 600;
        }

        tr {
            transition: var(--transition);
        }

        tr:nth-child(even) { background: #f8f9fa; }

        tr:hover {
            background: #d4edda;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .action-added { background-color: #d4edda; color: var(--primary-green); }
        .action-edited { background-color: #e8f5e9; color: var(--dark-green); }
        .action-deleted { background-color: #f8d7da; color: #721c24; }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            background: var(--primary-green);
            color: var(--white);
            padding: 12px 20px;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
            animation: slideUp 0.4s 0.6s both;
        }

        .back-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
            z-index: -1;
        }

        .back-btn:hover::before {
            left: 100%;
        }

        .back-btn:hover {
            background: var(--dark-green);
            transform: translateY(-3px);
            box-shadow: 0 7px 15px rgba(56, 142, 60, 0.3);
        }

        .back-btn i {
            transition: transform 0.3s ease;
            color: var(--white);
        }

        .back-btn:hover i {
            transform: translateX(-5px);
        }

        /* Bounce animation for table rows */
        @keyframes bounceIn {
            0% { opacity: 0; transform: translateY(20px) scale(0.95); }
            60% { opacity: 1; transform: translateY(-5px) scale(1.02); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .container { width: 90%; padding: 20px; }
            .search-form { flex-direction: column; align-items: stretch; }
            .search-container { width: 100%; }
            .search-btn, .clear-btn { width: 100%; }
            .status-counts { flex-direction: column; align-items: center; }
            .count-box { width: 100%; margin-bottom: 15px; }
            table { font-size: 0.9rem; }
            th, td { padding: 8px; }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Decorative elements -->
    <div class="decoration decoration-1"></div>
    <div class="decoration decoration-2"></div>

    <h1><i class="fas fa-history"></i> Complete Book History</h1>

    <!-- Search form -->
    <form class="search-form" method="post" action="">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" name="search" placeholder="Search by Title, ISBN, or Author" value="<?= htmlspecialchars($search_query) ?>">
        </div>
        <button type="submit" class="search-btn">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if (!empty($search_query)): ?>
        <a href="history.php" class="clear-btn">
            <i class="fas fa-times"></i> Clear Search
        </a>
        <?php endif; ?>
    </form>
    
    <div class="status-info">
        <i class="fas fa-info-circle"></i>
        <p>
            <?php if (!empty($search_query)): ?>
                Showing results for "<?php echo htmlspecialchars($search_query); ?>"
            <?php else: ?>
                This page shows the complete history of all book additions, edits, and deletions in the library system.
            <?php endif; ?>
        </p>
    </div>
    
    <div class="status-counts">
        <div class="count-box added-count">
            <i class="fas fa-plus-circle fa-2x"></i>
            <h3>Added</h3>
            <p><?= $added_result ? $added_result->num_rows : 0 ?> books</p>
        </div>
        <div class="count-box edited-count">
            <i class="fas fa-edit fa-2x"></i>
            <h3>Edited</h3>
            <p><?= $edited_result ? $edited_result->num_rows : 0 ?> books</p>
        </div>
        <div class="count-box deleted-count">
            <i class="fas fa-trash-alt fa-2x"></i>
            <h3>Deleted</h3>
            <p><?= $deleted_result ? $deleted_result->num_rows : 0 ?> books</p>
        </div>
    </div>

    <?php if (!empty($history)): ?>
    <table>
        <thead>
        <tr>
            <th>Event Date</th>
            <th>User</th>
            <th>Action</th>
            <th>ISBN</th>
            <th>Title</th>
            <th>Author</th>
            <th>Publication Year</th>
            <th>Publisher</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($history as $index => $row): ?>
            <tr class="<?= strtolower($row['Action']) === 'added' ? 'action-added' : (strtolower($row['Action']) === 'edited' ? 'action-edited' : 'action-deleted') ?>" style="animation: bounceIn 0.6s ease-out <?= 0.1 * $index ?>s both;">
                <td><?= htmlspecialchars($row['EventDate']) ?></td>
                <td><?= htmlspecialchars($row['User']) ?></td>
                <td><strong><?= htmlspecialchars($row['Action']) ?></strong></td>
                <td><?= htmlspecialchars($row['ISBN']) ?></td>
                <td><?= htmlspecialchars($row['Title']) ?></td>
                <td><?= htmlspecialchars($row['Author']) ?></td>
                <td><?= htmlspecialchars($row['PublicationYear']) ?></td>
                <td><?= htmlspecialchars($row['Publisher']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="status-info" style="background-color: #f8d7da; border-left-color: #721c24;">
            <i class="fas fa-exclamation-circle"></i>
            <p>
                <?php if (!empty($search_query)): ?>
                    No results found for "<?php echo htmlspecialchars($search_query); ?>".
                <?php else: ?>
                    No book history found in the database.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
    
    <a href="manage.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Manage Books
    </a>
</div>

<script>
    // Enhanced animations for table rows and count boxes
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('mouseover', function() {
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
                this.style.transform = 'translateY(-2px) scale(1.01)';
            });
            
            row.addEventListener('mouseout', function() {
                this.style.boxShadow = 'none';
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Animate count boxes with green-themed gradient
        const countBoxes = document.querySelectorAll('.count-box');
        countBoxes.forEach(box => {
            box.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                this.style.background = `radial-gradient(circle at ${x}px ${y}px, var(--light-green), #e8f5e9)`;
            });
            
            box.addEventListener('mouseleave', function() {
                this.style.background = '#e8f5e9';
            });
        });

        // Animate search input focus
        const searchInput = document.querySelector('.search-form input');
        searchInput.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        searchInput.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
</script>
</body>
</html>
<?php 
$added_stmt->close();
$deleted_stmt->close();
$edited_stmt->close();
$conn->close(); 
?>