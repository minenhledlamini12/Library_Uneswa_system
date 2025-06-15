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

// Current date for checking overdue status (04:30 AM SAST, May 25, 2025)
$current_date = new DateTime('2025-05-25 04:30:00');
$current_date_only = $current_date->format('Y-m-d'); // Date without time for daily charge updates

// Step 1: Check borrowing_history for overdue books and manage blacklist
$sql_check_overdue = "SELECT BorrowingID, BookID, ID, BorrowDate, DueDate, ReturnDate, Status, blacklist 
                     FROM borrowing_history 
                     WHERE Status = 'borrowed' AND (blacklist IS NULL OR blacklist = 0)";
$result_check = $conn->query($sql_check_overdue);

while ($row = $result_check->fetch_assoc()) {
    $due_date = new DateTime($row['DueDate']);
    $due_date_only = $due_date->format('Y-m-d'); // Date without time
    $borrowing_id = $row['BorrowingID'];
    $member_id = $row['ID'];
    $book_id = $row['BookID'];
    $borrow_date = $row['BorrowDate'];
    $due_date_str = $row['DueDate'];

    // Check if the book is overdue (due date passed, not returned)
    if ($current_date > $due_date && $row['Status'] == 'borrowed') {
        // Fetch the member's email and member type
        $stmt_member = $conn->prepare("SELECT Email, Membership_type FROM members WHERE ID = ?");
        $stmt_member->bind_param("i", $member_id);
        $stmt_member->execute();
        $member_result = $stmt_member->get_result();
        $member_row = $member_result->fetch_assoc();
        $contact_email = $member_row['Email'];
        $member_type = strtoupper(str_replace(' ', '_', $member_row['Membership_type']));
        $stmt_member->close();

        // Fine rates based on membership type
        $fine_rates = [
            'STAFF' => 1.00,           // E1.00 per day per item
            'STUDENT' => 0.50,         // E0.50 per day per item
            'EXTERNAL_BORROWER' => 2.00 // E2.00 per day per item
        ];
        $fine_rate = isset($fine_rates[$member_type]) ? $fine_rates[$member_type] : 0;

        // Add to blacklist table since blacklist flag is NULL or 0
        $reason = "Book not returned by due date: $due_date_str";
        $blacklisted_date = $current_date->format('Y-m-d H:i:s');
        $charge = $fine_rate; // Initial charge for the first overdue day

        $stmt_insert = $conn->prepare("INSERT INTO blacklist (ID, BookID, Email, BorrowDate, DueDate, Reason, BlacklistedDate, Charge, LastChargeUpdate) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("iisssssds", $member_id, $book_id, $contact_email, $borrow_date, $due_date_str, $reason, $blacklisted_date, $charge, $blacklisted_date);
        $stmt_insert->execute();
        $stmt_insert->close();

        // Update blacklist flag in borrowing_history
        $stmt_update_borrow = $conn->prepare("UPDATE borrowing_history SET blacklist = 1 WHERE BorrowingID = ?");
        $stmt_update_borrow->bind_param("i", $borrowing_id);
        $stmt_update_borrow->execute();
        $stmt_update_borrow->close();
    }
}

// Step 2: Update charges for existing blacklist entries
$sql_update_charges = "SELECT bl.BlacklistID, bl.ID, bl.BookID, bl.BorrowDate, bl.Charge, bl.LastChargeUpdate, bl.DueDate, 
                      bh.Status, bh.BorrowingID
                      FROM blacklist bl
                      JOIN borrowing_history bh ON bl.ID = bh.ID AND bl.BookID = bh.BookID AND bl.BorrowDate = bh.BorrowDate
                      WHERE bh.Status = 'borrowed'";
$result_update = $conn->query($sql_update_charges);

while ($row = $result_update->fetch_assoc()) {
    $blacklist_id = $row['BlacklistID'];
    $member_id = $row['ID'];
    $book_id = $row['BookID'];
    $borrow_date = $row['BorrowDate'];
    $current_charge = $row['Charge'];
    $last_charge_update = $row['LastChargeUpdate'] ? new DateTime($row['LastChargeUpdate']) : new DateTime($row['DueDate']);
    $last_charge_update_only = $last_charge_update->format('Y-m-d');
    $due_date_only = (new DateTime($row['DueDate']))->format('Y-m-d');
    $borrowing_id = $row['BorrowingID'];

    // Fetch member type for fine rate
    $stmt_member = $conn->prepare("SELECT Membership_type FROM members WHERE ID = ?");
    $stmt_member->bind_param("i", $member_id);
    $stmt_member->execute();
    $member_result = $stmt_member->get_result();
    $member_row = $member_result->fetch_assoc();
    $member_type = strtoupper(str_replace(' ', '_', $member_row['Membership_type']));
    $stmt_member->close();

    $fine_rates = [
        'STAFF' => 1.00,
        'STUDENT' => 0.50,
        'EXTERNAL_BORROWER' => 2.00
    ];
    $fine_rate = isset($fine_rates[$member_type]) ? $fine_rate : 0;

    // Update charge if it's a new day and book is still borrowed
    if ($current_date_only > $last_charge_update_only && $current_date_only > $due_date_only && $row['Status'] == 'borrowed') {
        $new_charge = $current_charge + $fine_rate; // Increment by one day's fine
        $blacklisted_date = $current_date->format('Y-m-d H:i:s');

        // Update blacklist entry
        $stmt_update_blacklist = $conn->prepare("UPDATE blacklist SET Charge = ?, LastChargeUpdate = ? WHERE BlacklistID = ?");
        $stmt_update_blacklist->bind_param("dsi", $new_charge, $blacklisted_date, $blacklist_id);
        $stmt_update_blacklist->execute();
        $stmt_update_blacklist->close();

        // Ensure blacklist flag is set
        $stmt_update_borrow = $conn->prepare("UPDATE borrowing_history SET blacklist = 1 WHERE BorrowingID = ?");
        $stmt_update_borrow->bind_param("i", $borrowing_id);
        $stmt_update_borrow->execute();
        $stmt_update_borrow->close();
    }
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_query = $search ? "WHERE (m.Name LIKE '%$search%' OR m.Surname LIKE '%$search%' OR m.Email LIKE '%$search%' OR b.Title LIKE '%$search%')" : "";

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Count total records
$total_sql = "SELECT COUNT(*) FROM blacklist bl
              JOIN members m ON bl.ID = m.ID
              JOIN books b ON bl.BookID = b.ID
              $search_query";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $records_per_page);

// Calculate total charges
$charges_sql = "SELECT SUM(bl.Charge) as total_charges FROM blacklist bl
                JOIN members m ON bl.ID = m.ID
                JOIN books b ON bl.BookID = b.ID
                $search_query";
$charges_result = $conn->query($charges_sql);
$total_charges = $charges_result->fetch_row()[0] ?? 0;

// Step 3: Display blacklisted members with book details and charges
$sql_display = "SELECT bl.BlacklistID, bl.ID, bl.BookID, bl.Email, bl.BorrowDate, bl.DueDate, bl.Reason, bl.BlacklistedDate, bl.Charge,
                m.Name, m.Surname, m.Membership_type, m.Contact,
                b.Title, b.ISBN
                FROM blacklist bl
                JOIN members m ON bl.ID = m.ID
                JOIN books b ON bl.BookID = b.ID
                $search_query
                ORDER BY bl.BlacklistedDate DESC
                LIMIT $offset, $records_per_page";
$result_display = $conn->query($sql_display);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESWA Library - Blacklisted Members</title>
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
            background: linear-gradient(135deg, var(--danger-color), #c82333);
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

        /* Header */
        .header-main {
            background: linear-gradient(135deg, var(--danger-color), #c82333);
            color: white;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            justify-content: flex-start;
            position: relative;
            animation: slideInUp 1s ease-out 0.2s both;
        }

        .header-main .logo-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.75rem;
            border-radius: 1rem;
            backdrop-filter: blur(10px);
            animation: pulse 2s infinite;
            flex-shrink: 0;
        }

        .header-main img {
            height: 50px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .header-main .title-container {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
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

        /* Page Header */
        .page-header {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(220, 53, 69, 0.1);
            animation: slideInLeft 1s ease-out 0.6s both;
        }

        .page-header h2 {
            color: var(--danger-color);
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: shake 2s ease-in-out infinite;
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
            background: linear-gradient(135deg, var(--danger-color), #c82333);
            color: white;
            padding: 1.25rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .stat-card.warning {
            background: linear-gradient(135deg, var(--warning-color), #e0a800);
        }

        .stat-card.info {
            background: linear-gradient(135deg, var(--info-color), #138496);
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

        /* Search Section */
        .search-section {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            animation: fadeIn 1.5s ease-out 0.8s both;
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
            border-color: var(--danger-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .search-btn {
            background: linear-gradient(135deg, var(--danger-color), #c82333);
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
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
        }

        .clear-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .clear-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* Table Container */
        .table-container {
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            animation: fadeIn 1.5s ease-out 1s both;
        }

        .table-header {
            background: linear-gradient(135deg, var(--danger-color), #c82333);
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
            background: rgba(220, 53, 69, 0.05);
        }

        tr.overdue {
            background: rgba(220, 53, 69, 0.05);
            border-left: 4px solid var(--danger-color);
        }

        /* Member Type Badges */
        .member-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-student {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .badge-staff {
            background: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
        }

        .badge-external {
            background: rgba(255, 193, 7, 0.1);
            color: #856404;
        }

        /* Overdue Badge */
        .overdue-badge {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .charge-amount {
            font-weight: 700;
            color: var(--danger-color);
            font-size: 1rem;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1.5rem 0;
            animation: slideInUp 1s ease-out 1.2s both;
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
            background: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
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
            animation: slideInUp 1.5s ease-out 1.4s both;
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
            color: var(--danger-color);
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            border: none;
            cursor: pointer;
        }

        .btn-remove {
            background: rgba(40, 167, 69, 0.1);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .btn-remove:hover {
            background: #28a745;
            color: white;
            transform: translateY(-1px);
        }

        .btn-contact {
            background: rgba(23, 162, 184, 0.1);
            color: #0c5460;
            border: 1px solid rgba(23, 162, 184, 0.3);
        }

        .btn-contact:hover {
            background: #17a2b8;
            color: white;
            transform: translateY(-1px);
        }

        /* Debug Info */
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
            font-family: monospace;
            font-size: 0.85rem;
            display: none;
        }

        .debug-toggle {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 1rem;
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

            .header-main .title-container {
                position: static;
                transform: none;
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

            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input {
                min-width: auto;
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

            .action-buttons {
                flex-direction: column;
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
            <i class="fas fa-exclamation-triangle"></i>
            <span>Blacklist Management System - Monitor Overdue Books & Fines</span>
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
            <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo">
        </div>
        <div class="title-container">
            <h1>University of Eswatini Library</h1>
            <div class="subtitle">Kwaluseni Campus - Blacklist Management System</div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Debug Section -->
        <button class="debug-toggle" onclick="toggleDebug()">🔧 Toggle Debug Info</button>
        <div id="debugInfo" class="debug-info">
            <strong>Debug Information:</strong><br>
            <span id="debugContent">Click "Send Email" to see debug info...</span>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h2>
                <i class="fas fa-user-slash"></i>
                Blacklisted Members
            </h2>
            <p>Monitor overdue books, manage fines, and track blacklisted library members</p>
            
            <div class="stats-container">
                <div class="stat-card">
                    <h3><?= $total_rows ?></h3>
                    <p>Blacklisted Members</p>
                </div>
                <div class="stat-card warning">
                    <h3>E<?= number_format($total_charges, 2) ?></h3>
                    <p>Total Outstanding Fines</p>
                </div>
                <div class="stat-card info">
                    <h3><?= $total_pages ?></h3>
                    <p>Total Pages</p>
                </div>
                <div class="stat-card">
                    <h3><?= $page ?></h3>
                    <p>Current Page</p>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <form class="search-form" method="GET">
                <input type="text" name="search" class="search-input" placeholder="🔍 Search by Name, Email, or Book Title..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <?php if ($search): ?>
                    <a href="blacklisted_members.php" class="clear-btn">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <h3>
                    <i class="fas fa-ban"></i>
                    Blacklisted Member Records
                </h3>
                <div class="records-info">
                    Showing <?= min($offset + 1, $total_rows) ?>-<?= min($offset + $records_per_page, $total_rows) ?> of <?= $total_rows ?> blacklisted members
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Member</th>
                            <th><i class="fas fa-users"></i> Type</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-book"></i> Book Details</th>
                            <th><i class="fas fa-calendar-alt"></i> Dates</th>
                            <th><i class="fas fa-clock"></i> Overdue</th>
                            <th><i class="fas fa-money-bill-wave"></i> Fine</th>
                            <th><i class="fas fa-exclamation-circle"></i> Reason</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_display && $result_display->num_rows > 0): ?>
                            <?php while ($row = $result_display->fetch_assoc()): ?>
                                <?php
                                // Calculate overdue days for display (using current date)
                                $due_date = new DateTime($row['DueDate']);
                                $interval = $due_date->diff($current_date);
                                $overdue_days = $interval->days;
                                if ($due_date > $current_date) {
                                    $overdue_days = 0; // Not overdue if due date is in the future
                                }

                                $memberType = $row['Membership_type'];
                                $badgeClass = 'badge-student';
                                if ($memberType == 'Staff') $badgeClass = 'badge-staff';
                                elseif ($memberType == 'External Member') $badgeClass = 'badge-external';
                                ?>
                                <tr class="overdue">
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['Name'] . ' ' . $row['Surname']); ?></strong>
                                        <br>
                                        <small style="color: #64748b;">ID: <?php echo $row['ID']; ?></small>
                                    </td>
                                    <td>
                                        <span class="member-badge <?= $badgeClass ?>">
                                            <?php echo htmlspecialchars($memberType); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['Title']); ?></strong>
                                        <br>
                                        <small style="color: #64748b;">ISBN: <?php echo htmlspecialchars($row['ISBN']); ?></small>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem;">
                                            <div><strong>Borrowed:</strong> <?php echo date('M d, Y', strtotime($row['BorrowDate'])); ?></div>
                                            <div><strong>Due:</strong> <?php echo date('M d, Y', strtotime($row['DueDate'])); ?></div>
                                            <div><strong>Blacklisted:</strong> <?php echo date('M d, Y', strtotime($row['BlacklistedDate'])); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="overdue-badge">
                                            <i class="fas fa-clock"></i>
                                            <?php echo $overdue_days; ?> days
                                        </span>
                                    </td>
                                    <td>
                                        <span class="charge-amount">E<?php echo number_format($row['Charge'], 2); ?></span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($row['Reason']); ?></small>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn btn-remove" onclick="removeFromBlacklist(<?php echo $row['BlacklistID']; ?>)">
                                                <i class="fas fa-user-check"></i> Remove
                                            </button>
                                            <button class="action-btn btn-contact" onclick="showContactInfo('<?php echo htmlspecialchars($row['Name'] . ' ' . $row['Surname']); ?>', '<?php echo htmlspecialchars($row['Email']); ?>', '<?php echo htmlspecialchars($row['Contact']); ?>', <?php echo $row['ID']; ?>)">
                                                <i class="fas fa-phone"></i> Contact
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="fas fa-user-check"></i>
                                        <h3>No Blacklisted Members</h3>
                                        <p><?= $search ? 'No members match your search criteria.' : 'Great! No members are currently blacklisted.' ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="nav-button">‹ Previous</a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                    if ($i == $page):
                ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                <?php
                    endif;
                endfor;
                ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="nav-button">Next ›</a>
                    <a href="?page=<?= $total_pages ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="nav-button">Last »</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Back Button -->
    <button class="back-button" onclick="window.location.href='homepage.php'" title="Back to Dashboard">
        <i class="fas fa-home"></i>
    </button>

    <script>
        function toggleDebug() {
            const debugInfo = document.getElementById('debugInfo');
            debugInfo.style.display = debugInfo.style.display === 'none' ? 'block' : 'none';
        }

        function removeFromBlacklist(blacklistId) {
            if (confirm('Are you sure you want to remove this member from the blacklist? This action cannot be undone.')) {
                fetch('remove_from_blacklist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        blacklist_id: blacklistId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error removing member from blacklist');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error removing member from blacklist');
                });
            }
        }

        function showContactInfo(name, email, contact, memberId) {
            console.log('showContactInfo called with:', { name, email, contact, memberId });
            
            const content = `
        <div style="text-align: center; margin-bottom: 1rem;">
            <div style="background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(255, 193, 7, 0.1)); padding: 0.75rem; border-radius: 8px; margin-bottom: 0.75rem;">
                <h4 style="color: var(--danger-color); margin-bottom: 0.25rem; font-size: 1rem; line-height: 1.2;">${name}</h4>
                <span style="background: rgba(220, 53, 69, 0.1); color: var(--danger-color); padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.7rem; font-weight: 600;">BLACKLISTED MEMBER</span>
            </div>
        </div>
        
        <div style="display: grid; gap: 0.75rem;">
            <div style="background: #f8f9fa; padding: 0.75rem; border-radius: 6px; border-left: 3px solid var(--info-color);">
                <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.4rem;">
                    <i class="fas fa-envelope" style="color: var(--info-color); font-size: 0.9rem;"></i>
                    <strong style="font-size: 0.9rem;">Email:</strong>
                </div>
                <div style="font-family: monospace; background: white; padding: 0.4rem; border-radius: 4px; border: 1px solid #dee2e6; font-size: 0.85rem; word-break: break-all;">
                    ${email || 'No email provided'}
                </div>
            </div>
            
            <div style="background: #f8f9fa; padding: 0.75rem; border-radius: 6px; border-left: 3px solid var(--success-color);">
                <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.4rem;">
                    <i class="fas fa-phone" style="color: var(--success-color); font-size: 0.9rem;"></i>
                    <strong style="font-size: 0.9rem;">Phone:</strong>
                </div>
                <div style="font-family: monospace; background: white; padding: 0.4rem; border-radius: 4px; border: 1px solid #dee2e6; font-size: 0.9rem; font-weight: 600;">
                    ${contact || 'No phone number provided'}
                </div>
            </div>
            
            <div style="background: #f8f9fa; padding: 0.75rem; border-radius: 6px; border-left: 3px solid var(--warning-color);">
                <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.4rem;">
                    <i class="fas fa-id-card" style="color: var(--warning-color); font-size: 0.9rem;"></i>
                    <strong style="font-size: 0.9rem;">Member ID:</strong>
                </div>
                <div style="font-family: monospace; background: white; padding: 0.4rem; border-radius: 4px; border: 1px solid #dee2e6; font-size: 0.9rem; font-weight: 600;">
                    ${memberId}
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contactContent').innerHTML = content;
    document.getElementById('phoneLink').href = contact ? `tel:${contact}` : '#';
    
    // Store member info for email sending - NOW INCLUDING MEMBER ID
    document.getElementById('contactModal').setAttribute('data-member-email', email);
    document.getElementById('contactModal').setAttribute('data-member-name', name);
    document.getElementById('contactModal').setAttribute('data-member-id', memberId);
    document.getElementById('contactModal').style.display = 'block';
}

        function sendOverdueEmail() {
            const modal = document.getElementById('contactModal');
            const memberEmail = modal.getAttribute('data-member-email');
            const memberName = modal.getAttribute('data-member-name');
            const memberId = modal.getAttribute('data-member-id'); // FIXED: Now getting member ID
            
            console.log('sendOverdueEmail called with:', { memberEmail, memberName, memberId });
            
            if (!memberId) {
                alert('No member ID available.');
                return;
            }
            
            if (!memberEmail) {
                alert('No email address available for this member.');
                return;
            }
            
            // Show loading state
            const emailBtn = document.getElementById('emailLink');
            const originalText = emailBtn.innerHTML;
            emailBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            emailBtn.style.pointerEvents = 'none';
            
            // Prepare data - NOW SENDING MEMBER_ID INSTEAD OF EMAIL/NAME
            const emailData = {
                member_id: parseInt(memberId) // FIXED: Sending member_id as expected by PHP script
            };
            
            console.log('Sending email data:', emailData);
            
            // Update debug info
            document.getElementById('debugContent').innerHTML = `
                <strong>Sending Email...</strong><br>
                Member ID: ${memberId}<br>
                Email: ${memberEmail}<br>
                Name: ${memberName}<br>
                Data: ${JSON.stringify(emailData)}<br>
                Timestamp: ${new Date().toISOString()}
            `;
            
            // Send email via AJAX
            fetch('send_overdue_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(emailData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                
                // Update debug info
                document.getElementById('debugContent').innerHTML += `<br><strong>Raw Response:</strong><br>${text}`;
                
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed response:', data);
                    
                    // Update debug info
                    document.getElementById('debugContent').innerHTML += `<br><strong>Parsed Response:</strong><br>${JSON.stringify(data, null, 2)}`;
                    
                    if (data.success) {
                        alert('Overdue notice email sent successfully!');
                        closeContactModal();
                    } else {
                        alert('Failed to send email: ' + data.message);
                        if (data.debug) {
                            console.log('Debug info:', data.debug);
                        }
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    alert('Error parsing server response: ' + text);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Network error sending email: ' + error.message);
                
                // Update debug info
                document.getElementById('debugContent').innerHTML += `<br><strong>Error:</strong><br>${error.message}`;
            })
            .finally(() => {
                // Restore button state
                emailBtn.innerHTML = originalText;
                emailBtn.style.pointerEvents = 'auto';
            });
        }

        function closeContactModal() {
            document.getElementById('contactModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const contactModal = document.getElementById('contactModal');
            if (event.target == contactModal) {
                contactModal.style.display = 'none';
            }
        }

        // Auto-refresh every 5 minutes to check for new overdue books
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 minutes
    </script>

    <!-- Contact Modal -->
    <div id="contactModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); overflow-y: auto;" data-member-email="" data-member-name="" data-member-id="">
    <div style="background-color: white; margin: 2% auto; padding: 1.5rem; border-radius: 16px; width: 90%; max-width: 450px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); max-height: 90vh; overflow-y: auto; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 2px solid #e1e5e9; position: sticky; top: 0; background: white; z-index: 10;">
            <h3 style="color: var(--danger-color); font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                <i class="fas fa-address-book"></i> Contact Information
            </h3>
            <span onclick="closeContactModal()" style="color: #aaa; font-size: 24px; font-weight: bold; cursor: pointer; padding: 0.25rem; line-height: 1;">×</span>
        </div>
        
        <div id="contactContent" style="margin-bottom: 1rem;">
            <!-- Content will be loaded here -->
        </div>
        
        <div style="text-align: center; display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; position: sticky; bottom: 0; background: white; padding-top: 1rem; border-top: 1px solid #e1e5e9;">
            <button id="emailLink" onclick="sendOverdueEmail()" style="background: linear-gradient(135deg, var(--danger-color), #c82333); color: white; padding: 0.625rem 1.25rem; border-radius: 8px; border: none; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; flex: 1; min-width: 140px; justify-content: center;">
                <i class="fas fa-envelope"></i> Send Notice
            </button>
            <a id="phoneLink" href="#" style="background: linear-gradient(135deg, var(--success-color), var(--secondary-color)); color: white; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; flex: 1; min-width: 140px; justify-content: center;">
                <i class="fas fa-phone"></i> Call Now
            </a>
        </div>
    </div>
</div>

    <?php $conn->close(); ?>
</body>
</html>
