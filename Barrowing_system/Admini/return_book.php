<?php
// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle book return processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => ''];
    
    if ($_POST['action'] === 'return_book') {
        $blacklist_id = (int)$_POST['blacklist_id'];
        $member_id = (int)$_POST['member_id'];
        $book_id = (int)$_POST['book_id'];
        $return_date = date('Y-m-d H:i:s');
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update borrowing_history to mark as returned AND set blacklist to 0
            $stmt = $conn->prepare("UPDATE borrowing_history SET Status = 'returned', ReturnDate = ?, blacklist = 0 WHERE ID = ? AND BookID = ? AND Status = 'borrowed'");
            $stmt->bind_param("sii", $return_date, $member_id, $book_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                // Update book status to available
                $stmt2 = $conn->prepare("UPDATE books SET Status = 'Available', CopiesAvailable = CopiesAvailable + 1 WHERE ID = ?");
                $stmt2->bind_param("i", $book_id);
                $stmt2->execute();
                
                // Remove from blacklist for this specific book
                $stmt3 = $conn->prepare("DELETE FROM blacklist WHERE BlacklistID = ?");
                $stmt3->bind_param("i", $blacklist_id);
                $stmt3->execute();
                
                // Check if member has any other overdue books
                $stmt4 = $conn->prepare("SELECT COUNT(*) as count FROM blacklist WHERE ID = ?");
                $stmt4->bind_param("i", $member_id);
                $stmt4->execute();
                $result = $stmt4->get_result();
                $remaining_overdue = $result->fetch_assoc()['count'];
                
                $conn->commit();
                
                $response['success'] = true;
                $response['message'] = 'Book returned successfully! Blacklist status updated.';
                $response['remaining_overdue'] = $remaining_overdue;
                
                $stmt2->close();
                $stmt3->close();
                $stmt4->close();
            } else {
                $conn->rollback();
                $response['message'] = 'Failed to update borrowing record.';
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error processing return: ' . $e->getMessage();
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Search functionality
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$member_data = null;
$overdue_books = [];

if ($search_query) {
    $search_query = $conn->real_escape_string($search_query);
    
    // Search for blacklisted member
    $sql = "SELECT DISTINCT 
                m.ID, m.Name, m.Surname, m.Member_ID, m.Email, m.Contact, m.Membership_type,
                COUNT(bl.BlacklistID) as total_overdue_books,
                SUM(bl.Charge) as total_charges
            FROM members m
            JOIN blacklist bl ON m.ID = bl.ID
            WHERE (m.Name LIKE '%$search_query%' 
                OR m.Surname LIKE '%$search_query%' 
                OR m.Member_ID LIKE '%$search_query%'
                OR m.Email LIKE '%$search_query%'
                OR CONCAT(m.Name, ' ', m.Surname) LIKE '%$search_query%')
            GROUP BY m.ID
            LIMIT 1";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $member_data = $result->fetch_assoc();
        
        // Get detailed overdue books for this member
        $books_sql = "SELECT 
                        bl.BlacklistID, bl.BookID, bl.BorrowDate, bl.DueDate, bl.Charge, bl.BlacklistedDate,
                        b.Title, b.Author, b.ISBN, b.CallNumber,
                        DATEDIFF(NOW(), bl.DueDate) as days_overdue
                      FROM blacklist bl
                      JOIN books b ON bl.BookID = b.ID
                      WHERE bl.ID = {$member_data['ID']}
                      ORDER BY bl.DueDate ASC";
        
        $books_result = $conn->query($books_sql);
        
        if ($books_result) {
            while ($book = $books_result->fetch_assoc()) {
                $overdue_books[] = $book;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Books - UNESWA Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #218838;
            --accent-color: #f4a261;
            --background-color: #f5f7fa;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --border-color: #e1e5e9;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Top Bar */
        .top-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.8s ease-out;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            margin-left: 0.5rem;
            transition: all 0.3s ease;
            padding: 0.25rem;
            border-radius: 4px;
        }

        .top-bar a:hover {
            color: var(--accent-color);
            transform: scale(1.2);
        }

        /* Header */
        .header-main {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: space-between;
            position: relative;
            animation: fadeInUp 1s ease-out 0.2s both;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .logo-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.75rem;
            border-radius: 1rem;
            backdrop-filter: blur(10px);
            animation: pulse 2s infinite;
        }

        .header-main img {
            height: 60px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .header-text h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0 0 0.25rem 0;
        }

        .header-text span {
            font-style: italic;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .back-home-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .back-home-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            animation: fadeInUp 1.2s ease-out 0.4s both;
        }

        /* Page Header */
        .page-header {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            animation: slideInLeft 1s ease-out 0.6s both;
        }

        .page-header h2 {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header p {
            color: #64748b;
            font-size: 1.1rem;
        }

        /* Search Section */
        .search-section {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
        }

        .search-form {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 300px;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }

        .search-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }

        /* Member Info Card */
        .member-info-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--danger-color);
        }

        .member-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .member-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
        }

        .blacklist-badge {
            background: linear-gradient(135deg, var(--danger-color), #c82333);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            animation: shake 2s ease-in-out infinite;
        }

        .member-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .detail-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 3px solid var(--primary-color);
        }

        .detail-item strong {
            color: var(--primary-color);
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .detail-item span {
            color: var(--text-color);
            font-weight: 500;
        }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: linear-gradient(135deg, var(--danger-color), #c82333);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .summary-card.warning {
            background: linear-gradient(135deg, var(--warning-color), #e0a800);
        }

        .summary-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .summary-card p {
            opacity: 0.9;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Books Table */
        .books-section {
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
        }

        .books-header {
            background: linear-gradient(135deg, var(--danger-color), #c82333);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .books-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .books-table {
            width: 100%;
            border-collapse: collapse;
        }

        .books-table th {
            background: #f8f9fa;
            color: var(--text-color);
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid var(--border-color);
        }

        .books-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .books-table tr:hover {
            background: rgba(220, 53, 69, 0.05);
        }

        .overdue-badge {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .charge-amount {
            font-weight: 700;
            color: var(--danger-color);
            font-size: 1.1rem;
        }

        /* Return Button */
        .return-btn {
            background: linear-gradient(135deg, var(--success-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .return-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .return-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.1);
            color: #721c24;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .alert-info {
            background: rgba(23, 162, 184, 0.1);
            color: #0c5460;
            border: 1px solid rgba(23, 162, 184, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        /* Loading State */
        .loading {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .spinner {
            width: 1rem;
            height: 1rem;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .header-main {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .header-left {
                flex-direction: column;
                gap: 1rem;
            }

            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input {
                min-width: auto;
            }

            .member-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .books-table {
                font-size: 0.9rem;
            }

            .books-table th,
            .books-table td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-left">
            <i class="fas fa-undo-alt"></i>
            <span>Book Return Processing - Blacklisted Members</span>
        </div>
        <div class="top-bar-right">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-phone"></i>
                <span>2517 0448</span>
            </div>
            <div>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="header-main">
        <div class="header-left">
            <div class="logo-container">
                <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo">
            </div>
            <div class="header-text">
                <h1>University of Eswatini Library</h1>
                <span>Kwaluseni Campus - Book Return System</span>
            </div>
        </div>
        <a href="homepage.php" class="back-home-btn">
            <i class="fas fa-home"></i>
            Back to Home
        </a>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-undo-alt"></i> Return Books - Blacklisted Members</h2>
            <p>Process book returns for blacklisted members and manage their account status.</p>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Search Section -->
        <div class="search-section">
            <h3 style="color: var(--primary-color); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-search"></i> Search Blacklisted Member
            </h3>
            <form class="search-form" method="GET">
                <input type="text" name="search" class="search-input" 
                       placeholder="🔍 Search by Name, Member ID, or Email..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if ($search_query): ?>
                    <a href="return_book.php" class="search-btn" style="background: #6c757d;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($member_data): ?>
            <!-- Member Info Card -->
            <div class="member-info-card">
                <div class="member-header">
                    <div class="member-name">
                        <?php echo htmlspecialchars($member_data['Name'] . ' ' . $member_data['Surname']); ?>
                    </div>
                    <div class="blacklist-badge">
                        <i class="fas fa-exclamation-triangle"></i> BLACKLISTED
                    </div>
                </div>

                <div class="member-details">
                    <div class="detail-item">
                        <strong><i class="fas fa-id-card"></i> Member ID</strong>
                        <span><?php echo htmlspecialchars($member_data['Member_ID']); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-envelope"></i> Email</strong>
                        <span><?php echo htmlspecialchars($member_data['Email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-phone"></i> Contact</strong>
                        <span><?php echo htmlspecialchars($member_data['Contact']); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-users"></i> Membership Type</strong>
                        <span><?php echo htmlspecialchars($member_data['Membership_type']); ?></span>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3><?php echo $member_data['total_overdue_books']; ?></h3>
                        <p>Overdue Books</p>
                    </div>
                    <div class="summary-card warning">
                        <h3>E<?php echo number_format($member_data['total_charges'], 2); ?></h3>
                        <p>Total Outstanding Fines</p>
                    </div>
                </div>
            </div>

            <!-- Overdue Books Section -->
            <div class="books-section">
                <div class="books-header">
                    <h3>
                        <i class="fas fa-book"></i>
                        Overdue Books to Return
                    </h3>
                    <span><?php echo count($overdue_books); ?> book(s)</span>
                </div>

                <?php if (!empty($overdue_books)): ?>
                    <table class="books-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-book"></i> Book Details</th>
                                <th><i class="fas fa-calendar-alt"></i> Borrowed Date</th>
                                <th><i class="fas fa-calendar-times"></i> Due Date</th>
                                <th><i class="fas fa-clock"></i> Days Overdue</th>
                                <th><i class="fas fa-money-bill-wave"></i> Fine Amount</th>
                                <th><i class="fas fa-cogs"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overdue_books as $book): ?>
                                <tr id="book-row-<?php echo $book['BlacklistID']; ?>">
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($book['Title']); ?></strong><br>
                                            <small style="color: #64748b;">by <?php echo htmlspecialchars($book['Author']); ?></small><br>
                                            <small style="color: #64748b;">ISBN: <?php echo htmlspecialchars($book['ISBN']); ?></small><br>
                                            <small style="color: #64748b;">Call: <?php echo htmlspecialchars($book['CallNumber']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($book['BorrowDate'])); ?></td>
                                    <td style="color: var(--danger-color); font-weight: 600;">
                                        <?php echo date('M d, Y', strtotime($book['DueDate'])); ?>
                                    </td>
                                    <td>
                                        <span class="overdue-badge">
                                            <i class="fas fa-clock"></i>
                                            <?php echo $book['days_overdue']; ?> days
                                        </span>
                                    </td>
                                    <td>
                                        <span class="charge-amount">E<?php echo number_format($book['Charge'], 2); ?></span>
                                    </td>
                                    <td>
                                        <button class="return-btn" 
                                                onclick="returnBook(<?php echo $book['BlacklistID']; ?>, <?php echo $member_data['ID']; ?>, <?php echo $book['BookID']; ?>)"
                                                id="return-btn-<?php echo $book['BlacklistID']; ?>">
                                            <i class="fas fa-check"></i> Return Book
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>No Overdue Books</h3>
                        <p>This member has no overdue books to return.</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($search_query): ?>
            <!-- No Results -->
            <div class="books-section">
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <h3>No Blacklisted Member Found</h3>
                    <p>No blacklisted member found matching "<?php echo htmlspecialchars($search_query); ?>".</p>
                    <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #999;">
                        Try searching by name, member ID, or email address.
                    </p>
                </div>
            </div>

        <?php else: ?>
            <!-- Initial State -->
            <div class="books-section">
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>Search for a Blacklisted Member</h3>
                    <p>Enter a member's name, ID, or email to view their overdue books and process returns.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            
            const icon = type === 'success' ? 'check-circle' : 
                        type === 'error' ? 'exclamation-triangle' : 'info-circle';
            
            alertDiv.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
            `;
            
            alertContainer.appendChild(alertDiv);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        function returnBook(blacklistId, memberId, bookId) {
            const button = document.getElementById(`return-btn-${blacklistId}`);
            const originalContent = button.innerHTML;
            
            // Show loading state
            button.disabled = true;
            button.innerHTML = '<div class="loading"><div class="spinner"></div> Processing...</div>';
            
            // Prepare data
            const formData = new FormData();
            formData.append('action', 'return_book');
            formData.append('blacklist_id', blacklistId);
            formData.append('member_id', memberId);
            formData.append('book_id', bookId);
            
            // Send request
            fetch('return_book.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    
                    // Remove the row from table
                    const row = document.getElementById(`book-row-${blacklistId}`);
                    row.style.transition = 'all 0.5s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-100%)';
                    
                    setTimeout(() => {
                        row.remove();
                        
                        // Check if no more books
                        const remainingRows = document.querySelectorAll('[id^="book-row-"]');
                        if (remainingRows.length === 0) {
                            if (data.remaining_overdue === 0) {
                                showAlert('All books returned! Member is no longer blacklisted.', 'success');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                window.location.reload();
                            }
                        }
                    }, 500);
                    
                } else {
                    showAlert(data.message, 'error');
                    button.disabled = false;
                    button.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Network error occurred. Please try again.', 'error');
                button.disabled = false;
                button.innerHTML = originalContent;
            });
        }

        // Auto-focus search input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
