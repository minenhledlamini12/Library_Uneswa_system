<?php
require_once 'connection.php';

// Pagination settings
$records_per_page = 5; // Increased for better display
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Count total records
$total_sql = "SELECT COUNT(*) FROM borrowing_history";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $records_per_page);

// Fetch records for current page with joins to books and members tables
$sql = "SELECT 
            bh.BorrowingID,
            bh.BorrowDate,
            bh.ReturnDate,
            bh.DueDate,
            bh.Status,
            bh.ISBN,
            bh.Returned,
            bh.blacklist,
            b.ISBN AS BookISBN,
            b.Title,
            m.Member_ID,
            m.Name,
            m.Surname,
            m.Membership_type
        FROM borrowing_history bh
        JOIN books b ON bh.BookID = b.ID
        JOIN members m ON bh.ID = m.ID
        ORDER BY bh.BorrowingID DESC
        LIMIT $offset, $records_per_page";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESWA Library - Borrowing History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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

        /* Top Bar - Reduced padding */
        .top-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.5rem 1.5rem;
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            animation: slideInUp 0.8s ease-out;
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
            transform: scale(1.1);
        }

        /* Header - Adjusted for left-aligned logo */
        .header-main {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            animation: slideInUp 1s ease-out 0.2s both;
        }

        .header-main .logo-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.75rem;
            border-radius: 1rem;
            backdrop-filter: blur(10px);
        }

        .header-main img {
            height: 50px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .header-main .title-container {
            text-align: center;
            flex-grow: 1;
        }

        .header-main h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 0.25rem 0;
        }

        .header-main .subtitle {
            font-style: italic;
            opacity: 0.9;
            font-size: 1rem;
        }

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
            animation: slideInUp 1.2s ease-out 0.4s both;
        }

        /* Page Header - Reduced padding */
        .page-header {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(40, 167, 69, 0.1);
            animation: slideInLeft 1s ease-out 0.6s both;
        }

        .page-header h2 {
            color: var(--primary-color);
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header p {
            color: #64748b;
            font-size: 1rem;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.25rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .stat-card h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .stat-card p {
            opacity: 1;
            font-size: 0.9rem;
            font-weight: 500;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        }
		#totalRecordsHeading {
    color: #fbc828; /* This targets only the element with this specific ID */
	}

        /* Table Container */
        .table-container {
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            animation: fadeIn 1.5s ease-out 0.8s both;
        }

        .table-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .records-info {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        /* Table Styling */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th {
            background: #f8f9fa;
            color: var(--text-color);
            font-weight: 600;
            padding: 0.875rem 0.75rem;
            text-align: left;
            border-bottom: 2px solid var(--border-color);
            white-space: nowrap;
        }

        td {
            padding: 0.875rem 0.75rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        tr:hover {
            background: rgba(40, 167, 69, 0.05);
        }

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .status-overdue {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        .status-returned {
            background: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
        }

        .yes-badge {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .no-badge {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .blacklist-badge {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Pagination - Updated to match the attached image */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1.5rem 0;
            animation: slideInUp 1s ease-out 1s both;
        }

        .pagination {
            display: flex;
            gap: 0.25rem;
            align-items: center;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            background: white;
            color: #6c757d;
            min-width: 40px;
            text-align: center;
        }

        .pagination a:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
        }

        .pagination .current {
            background: #6f42c1;
            color: white;
            border-color: #6f42c1;
        }

        .pagination .nav-button {
            padding: 0.5rem 1rem;
            background: white;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .pagination .nav-button:hover {
            background: #e9ecef;
            color: #495057;
        }

        /* Back Button */
        .back-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
            animation: slideInUp 1.5s ease-out 1.2s both;
        }

        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(40, 167, 69, 0.4);
        }

        .back-button i {
            font-size: 1.2rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .header-main {
                flex-direction: column;
                text-align: center;
                padding: 1rem;
            }

            .header-main h1 {
                font-size: 1.5rem;
            }

            .page-header {
                padding: 1rem;
            }

            .page-header h2 {
                font-size: 1.25rem;
                flex-direction: column;
                gap: 0.5rem;
            }

            .table-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1rem;
            }

            .top-bar {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
                padding: 0.5rem;
            }

            th, td {
                padding: 0.5rem;
                font-size: 0.8rem;
            }

            .back-button {
                bottom: 1rem;
                right: 1rem;
            }
        }

        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }

            .top-bar-left span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-left">
            <i class="fas fa-clock"></i>
            <span>Mon-Fri: 08:30 AM - 11:00 PM | Sat: 10:00 AM - 05:00 PM | Sun: 03:00 PM - 10:00 PM</span>
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
        <div class="logo-container">
            <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo" style="margin-right: 1.5rem;">
        </div>
        <div class="title-container">
            <h1>University of Eswatini Library</h1>
            <div class="subtitle">Kwaluseni Campus - Borrowing History Management</div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h2>
               
                Borrowing History
            </h2>
            <p>Track and monitor all book borrowing activities and member transactions</p>
            
            <div class="stats-container">
                <div class="stat-card">
                    <h3><?= $total_rows ?></h3>
                     <p id="totalRecordsHeading">Total Records</p>
                </div>
                <div class="stat-card">
                    <h3><?= $total_pages ?></h3>
					<p id="totalRecordsHeading">Total Pages</p>
                </div>
                <div class="stat-card">
                    <h3><?= $page ?></h3>
					<p id="totalRecordsHeading">Current Page</p>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <h3>
                    <i class="fas fa-table"></i>
                    Borrowing Records
                </h3>
                <div class="records-info">
                    Showing <?= min($offset + 1, $total_rows) ?>-<?= min($offset + $records_per_page, $total_rows) ?> of <?= $total_rows ?> records
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-barcode"></i> ISBN</th>
                            <th><i class="fas fa-book"></i> Title</th>
                            <th><i class="fas fa-user"></i> Member ID</th>
                            <th><i class="fas fa-user-circle"></i> Name</th>
                            <th><i class="fas fa-users"></i> Type</th>
                            <th><i class="fas fa-calendar-plus"></i> Borrow Date</th>
                            <th><i class="fas fa-calendar-minus"></i> Return Date</th>
                            <th><i class="fas fa-calendar-times"></i> Due Date</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                            <th><i class="fas fa-check-circle"></i> Returned</th>
                            <th><i class="fas fa-ban"></i> Blacklist</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['BorrowingID']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($row['BookISBN']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($row['Title']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['Member_ID']) ?></td>
                                    <td><?= htmlspecialchars($row['Name'] . ' ' . $row['Surname']) ?></td>
                                    <td>
                                        <span class="status-badge status-active">
                                            <?= htmlspecialchars($row['Membership_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($row['BorrowDate'])) ?></td>
                                    <td><?= $row['ReturnDate'] ? date('M d, Y', strtotime($row['ReturnDate'])) : '-' ?></td>
                                    <td><?= date('M d, Y', strtotime($row['DueDate'])) ?></td>
                                    <td>
                                        <?php
                                        $status = htmlspecialchars($row['Status']);
                                        $statusClass = 'status-active';
                                        if (strpos(strtolower($status), 'overdue') !== false) {
                                            $statusClass = 'status-overdue';
                                        } elseif (strpos(strtolower($status), 'returned') !== false) {
                                            $statusClass = 'status-returned';
                                        }
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= $status ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['Returned']): ?>
                                            <span class="yes-badge">Yes</span>
                                        <?php else: ?>
                                            <span class="no-badge">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['blacklist']): ?>
                                            <span class="blacklist-badge">Yes</span>
                                        <?php else: ?>
                                            <span class="no-badge">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h3>No Records Found</h3>
                                        <p>No borrowing history records are available at the moment.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination - Updated to match the attached image style -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="nav-button">‹</a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                    if ($i == $page):
                ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php
                    endif;
                endfor;
                ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="nav-button">›</a>
                    <a href="?page=<?= $total_pages ?>" class="nav-button">»</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Back Button -->
    <button class="back-button" onclick="window.location.href='homepage.php'" title="Back to Dashboard">
        <i class="fas fa-home"></i>
    </button>
</body>
</html>