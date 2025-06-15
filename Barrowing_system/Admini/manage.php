<?php
require_once("connection.php");

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if ($search_query) {
    $search_query = $conn->real_escape_string($search_query);
    $search_condition = " WHERE Title LIKE '%$search_query%' OR ISBN LIKE '%$search_query%' OR Author LIKE '%$search_query%'";
}

// Sorting functionality
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'ID';
$sort_order = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC';
$valid_columns = ['ID', 'ISBN', 'Title', 'Author', 'Status'];
if (!in_array($sort_column, $valid_columns)) {
    $sort_column = 'ID';
}

// Get total records for pagination
$total_sql = "SELECT COUNT(*) as total FROM books" . $search_condition;
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $records_per_page);

// Fetch books with pagination, search, and sorting
$sql = "SELECT ID, ISBN, Title, Author, PublicationYear, Publisher, Format, Language, Pages, Genre, CopiesAvailable, Status, CallNumber, AddedDate, UpdatedDate, QrCode 
        FROM books" . $search_condition . 
        " ORDER BY $sort_column $sort_order 
        LIMIT $offset, $records_per_page";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - UNESWA Library</title>
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
            --footer-color: #343a40;
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
            display: flex;
            flex-direction: column;
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

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(-50px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
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
            background: rgba(255, 255, 255, 0.1);
        }

        /* Header Main */
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

        /* Back to Home Button */
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
            flex: 1;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
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
            margin-bottom: 1.5rem;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-card p {
            opacity: 0.9;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Action Section */
        .action-section {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
        }

        .button-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .action-btn.secondary {
            background: linear-gradient(135deg, var(--info-color), #138496);
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }

        .action-btn.secondary:hover {
            box-shadow: 0 8px 25px rgba(23, 162, 184, 0.4);
        }

        /* Search Bar */
        .search-container {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .search-bar {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-bar input {
            flex: 1;
            min-width: 300px;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary-color);
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

        /* Table Container */
        .table-container {
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .table-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .records-info {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Table */
        .table-wrapper {
            overflow-x: auto;
        }

        .book-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .book-table th {
            background: #f8f9fa;
            color: var(--text-color);
            font-weight: 600;
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 2px solid var(--border-color);
            white-space: nowrap;
        }

        .book-table th a {
            color: var(--text-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
        }

        .book-table th a:hover {
            color: var(--primary-color);
        }

        .book-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .book-table tr:hover {
            background: rgba(40, 167, 69, 0.05);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-buttons a,
        .action-buttons button {
            padding: 0.5rem 0.875rem;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .view-button {
            background: linear-gradient(135deg, var(--info-color), #138496);
        }

        .edit-button {
            background: linear-gradient(135deg, var(--warning-color), #e0a800);
        }

        .delete-button {
            background: linear-gradient(135deg, var(--danger-color), #c82333);
        }

        .action-buttons a:hover,
        .action-buttons button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 0;
        }

        .pagination {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .pagination a {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid var(--border-color);
            background: white;
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .pagination a.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2.5rem;
            max-width: 700px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            animation: modalSlideIn 0.4s ease-out;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .modal-header h3 {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            color: #999;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 50%;
        }

        .close-modal:hover {
            color: var(--danger-color);
            background: rgba(220, 53, 69, 0.1);
            transform: rotate(90deg);
        }

        .modal-content .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 0;
        }

        .modal-content .detail-item:last-child {
            border-bottom: none;
        }

        .modal-content .detail-item strong {
            color: var(--primary-color);
            font-weight: 600;
            flex: 1;
        }

        .modal-content .detail-item span {
            flex: 2;
            text-align: right;
            color: var(--text-color);
        }

        .download-qr {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-left: 1rem;
        }

        .download-qr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
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

        /* Footer */
        footer {
            background: var(--footer-color);
            color: white;
            padding: 3rem 2rem;
            margin-top: auto;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }

        .footer-section p,
        .footer-section a {
            color: #dfe6e9;
            margin-bottom: 0.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-section a:hover {
            color: var(--accent-color);
            transform: translateX(5px);
        }

        .footer-section img {
            height: 50px;
            margin-bottom: 1rem;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-container {
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .header-main {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .header-left {
                flex-direction: column;
                gap: 1rem;
            }

            .back-home-btn {
                order: -1;
            }

            .search-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-bar input {
                min-width: auto;
            }

            .button-container {
                flex-direction: column;
                align-items: stretch;
            }

            .table-wrapper {
                overflow-x: auto;
            }

            .book-table {
                min-width: 800px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }

            .modal-content {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-left">
            <i class="fas fa-clock"></i>
            <span>Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM</span>
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
                <span>Kwaluseni Campus - Book Management System</span>
            </div>
        </div>
        <a href="homepage.php" class="back-home-btn">
            <i class="fas fa-home"></i>
            Back to Home
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-books"></i> Book Management Dashboard</h2>
            <p>Manage your library's book collection with ease. Add, edit, view, and organize books efficiently.</p>
            
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3><?php echo $total_rows; ?></h3>
                    <p><span style="color: #ffc107;">Total Books</span></p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_pages; ?></h3>
                    <p><span style="color: #ffc107;">Total Pages</span></p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $page; ?></h3>
                    <p><span style="color: #ffc107;">Current Page</span></p>
                </div>
            </div>
        </div>

        <!-- Action Section -->
        <div class="action-section">
            <!-- Button Container -->
            <div class="button-container">
                <a href="addBook.php" class="action-btn">
                    <i class="fas fa-plus-circle"></i> Add New Book
                </a>
                <a href="view_history.php" class="action-btn secondary">
                    <i class="fas fa-history"></i> View All History
                </a>
            </div>

            <!-- Search Container -->
            <div class="search-container">
                <form class="search-bar" action="manage_books.php" method="GET">
                    <input type="text" name="search" placeholder="🔍 Search by Title, ISBN, or Author..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if ($search_query): ?>
                        <a href="manage_books.php" class="action-btn" style="background: #6c757d;">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <h3>
                    <i class="fas fa-table"></i>
                    Book Collection
                </h3>
                <div class="records-info">
                    Showing <?php echo min($offset + 1, $total_rows); ?>-<?php echo min($offset + $records_per_page, $total_rows); ?> of <?php echo $total_rows; ?> books
                </div>
            </div>

            <div class="table-wrapper">
                <?php if ($result->num_rows > 0) { ?>
                    <table class="book-table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="?sort=ID&order=<?php echo $sort_column == 'ID' && $sort_order == 'ASC' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search_query); ?>">
                                        <i class="fas fa-hashtag"></i> ID 
                                        <?php echo $sort_column == 'ID' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=ISBN&order=<?php echo $sort_column == 'ISBN' && $sort_order == 'ASC' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search_query); ?>">
                                        <i class="fas fa-barcode"></i> ISBN 
                                        <?php echo $sort_column == 'ISBN' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=Title&order=<?php echo $sort_column == 'Title' && $sort_order == 'ASC' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search_query); ?>">
                                        <i class="fas fa-book"></i> Title 
                                        <?php echo $sort_column == 'Title' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=Author&order=<?php echo $sort_column == 'Author' && $sort_order == 'ASC' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search_query); ?>">
                                        <i class="fas fa-user-edit"></i> Author 
                                        <?php echo $sort_column == 'Author' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=Status&order=<?php echo $sort_column == 'Status' && $sort_order == 'ASC' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search_query); ?>">
                                        <i class="fas fa-info-circle"></i> Status 
                                        <?php echo $sort_column == 'Status' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['ID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ISBN']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['Title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['Author']); ?></td>
                                    <td>
                                        <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; 
                                            background: <?php echo $row['Status'] == 'Available' ? 'rgba(40, 167, 69, 0.1)' : 'rgba(220, 53, 69, 0.1)'; ?>;
                                            color: <?php echo $row['Status'] == 'Available' ? '#28a745' : '#dc3545'; ?>;">
                                            <?php echo htmlspecialchars($row['Status']); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="view-button" onclick="openModal(<?php echo $row['ID']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <a href="edit.php?id=<?php echo $row['ID']; ?>" class="edit-button">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete.php?id=<?php echo $row['ID']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this book?')">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </td>
                                </tr>

                                <!-- Modal for Additional Details -->
                                <div class="modal" id="modal-<?php echo $row['ID']; ?>">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3><i class="fas fa-book-open"></i> Book Details</h3>
                                            <button class="close-modal" onclick="closeModal(<?php echo $row['ID']; ?>)">×</button>
                                        </div>
                                        
                                        <div class="detail-item">
                                            <strong>Title:</strong>
                                            <span><?php echo htmlspecialchars($row['Title']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Author:</strong>
                                            <span><?php echo htmlspecialchars($row['Author']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>ISBN:</strong>
                                            <span><?php echo htmlspecialchars($row['ISBN']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Publication Year:</strong>
                                            <span><?php echo htmlspecialchars($row['PublicationYear']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Publisher:</strong>
                                            <span><?php echo htmlspecialchars($row['Publisher']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Format:</strong>
                                            <span><?php echo htmlspecialchars($row['Format']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Language:</strong>
                                            <span><?php echo htmlspecialchars($row['Language']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Pages:</strong>
                                            <span><?php echo htmlspecialchars($row['Pages']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Genre:</strong>
                                            <span><?php echo htmlspecialchars($row['Genre']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Call Number:</strong>
                                            <span><?php echo htmlspecialchars($row['CallNumber']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Copies Available:</strong>
                                            <span><?php echo htmlspecialchars($row['CopiesAvailable']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Status:</strong>
                                            <span><?php echo htmlspecialchars($row['Status']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Added Date:</strong>
                                            <span><?php echo htmlspecialchars($row['AddedDate']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Updated Date:</strong>
                                            <span><?php echo htmlspecialchars($row['UpdatedDate']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>QR Code:</strong>
                                            <span>
                                                <?php if (!empty($row['QrCode'])) { ?>
                                                    <a href="<?php echo htmlspecialchars($row['QrCode']); ?>" target="_blank">View QR Code</a>
                                                    <a href="<?php echo htmlspecialchars($row['QrCode']); ?>" download class="download-qr">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                <?php } else { ?>
                                                    No QR Code available
                                                <?php } ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        <div class="pagination">
                            <?php if ($page > 1) { ?>
                                <a href="?page=<?php echo $page - 1; ?>&sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>&search=<?php echo urlencode($search_query); ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php } ?>
                            
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++) { ?>
                                <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>&search=<?php echo urlencode($search_query); ?>" 
                                   class="<?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php } ?>
                            
                            <?php if ($page < $total_pages) { ?>
                                <a href="?page=<?php echo $page + 1; ?>&sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>&search=<?php echo urlencode($search_query); ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>No Books Found</h3>
                        <p><?php echo $search_query ? 'No books match your search criteria.' : 'No books have been added to the library yet.'; ?></p>
                        <?php if (!$search_query): ?>
                            <a href="addBook.php" class="action-btn" style="margin-top: 1rem;">
                                <i class="fas fa-plus-circle"></i> Add Your First Book
                            </a>
                        <?php endif; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Get In Touch</h3>
                <img src="/php_program/Barrowing_system/Images/download.png" alt="University of Eswatini Library Logo">
                <p>Kwaluseni, Luyengo & Mbabane</p>
                <p><i class="fas fa-phone"></i> 2517 0448</p>
                <p><i class="fas fa-envelope"></i> <a href="mailto:library@uniswa.sz">library@uniswa.sz</a></p>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <p><a href="#">Eswatini National Bibliography</a></p>
                <p><a href="#">UNESWA IR</a></p>
                <p><a href="#">Notices</a></p>
                <p><a href="#">Past Exam Papers</a></p>
                <p><a href="#">UNESWA</a></p>
            </div>

            <div class="footer-section">
                <h3>Popular Databases</h3>
                <p><a href="#">Science Direct</a></p>
                <p><a href="#">Ebscohost</a></p>
                <p><a href="#">ERIC</a></p>
                <p><a href="#">Taylor & Francis</a></p>
                <p><a href="#">Sabinet</a></p>
            </div>

            <div class="footer-section">
                <h3>Follow Us</h3>
                <p><a href="#"><i class="fab fa-twitter"></i> Twitter</a></p>
                <p><a href="#"><i class="fab fa-facebook"></i> Facebook</a></p>
                <p><a href="#"><i class="fab fa-instagram"></i> Instagram</a></p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© 2025 UNESWA Library. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function openModal(bookId) {
            document.getElementById('modal-' + bookId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(bookId) {
            document.getElementById('modal-' + bookId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    if (modal.style.display === 'flex') {
                        modal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });
    </script>
</body>

</html>

<?php $conn->close(); ?>
