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

// Handle soft delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("UPDATE members SET deleted_at = NOW() WHERE ID = $id");
    header("Location: manage_students.php?deleted=1");
    exit();
}

// Handle restore action
if (isset($_GET['restore'])) {
    $id = $_GET['restore'];
    $conn->query("UPDATE members SET deleted_at = NULL WHERE ID = $id");
    header("Location: manage_students.php?restored=1");
    exit();
}

// Handle permanent delete action
if (isset($_GET['permanent_delete'])) {
    $id = $_GET['permanent_delete'];
    $conn->query("DELETE FROM members WHERE ID = $id");
    header("Location: manage_students.php?permanently_deleted=1");
    exit();
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$show_deleted = isset($_GET['show_deleted']) ? true : false;

// Build search query
$search_conditions = [];
if ($search) {
    $search_conditions[] = "(Name LIKE '%$search%' OR Surname LIKE '%$search%' OR Email LIKE '%$search%')";
}

// Add deleted filter
if ($show_deleted) {
    $search_conditions[] = "deleted_at IS NOT NULL";
} else {
    $search_conditions[] = "deleted_at IS NULL";
}

$search_query = $search_conditions ? "WHERE " . implode(" AND ", $search_conditions) : "";

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Count total records
$total_sql = "SELECT COUNT(*) FROM members $search_query";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $records_per_page);

// Count deleted records
$deleted_count_sql = "SELECT COUNT(*) FROM members WHERE deleted_at IS NOT NULL";
$deleted_count_result = $conn->query($deleted_count_sql);
$deleted_count = $deleted_count_result->fetch_row()[0];

// Fetch members with pagination
$sql = "SELECT * FROM members $search_query ORDER BY Membership_type DESC LIMIT $offset, $records_per_page";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESWA Library - Manage Students</title>
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

        /* Top Bar */
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

        /* Header */
        .header-main {
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
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

.header-main .title-container {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    flex-grow: 1;
}

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
            animation: slideInUp 1.2s ease-out 0.4s both;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            animation: slideInUp 0.5s ease-out;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .alert-info {
            background: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
            border: 1px solid rgba(23, 162, 184, 0.3);
        }

        .alert-warning {
            background: rgba(255, 193, 7, 0.1);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        /* Page Header */
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

        .stat-card.deleted {
            background: linear-gradient(135deg, var(--danger-color), #c82333);
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

        /* Control Section */
        .control-section {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            animation: fadeIn 1.5s ease-out 0.8s both;
        }

        .control-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .view-toggle {
            display: flex;
            gap: 0.5rem;
        }

        .toggle-btn {
            padding: 0.5rem 1rem;
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            background: white;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toggle-btn.active {
            background: var(--primary-color);
            color: white;
        }

        .toggle-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header.deleted {
            background: linear-gradient(135deg, var(--danger-color), #c82333);
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

        tr.deleted {
            background: rgba(220, 53, 69, 0.05);
            opacity: 0.8;
        }

        tr.deleted:hover {
            background: rgba(220, 53, 69, 0.1);
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
        }

        .btn-edit {
            background: rgba(255, 193, 7, 0.1);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .btn-edit:hover {
            background: #ffc107;
            color: white;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: rgba(220, 53, 69, 0.1);
            color: #721c24;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .btn-delete:hover {
            background: #dc3545;
            color: white;
            transform: translateY(-1px);
        }

        .btn-restore {
            background: rgba(40, 167, 69, 0.1);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .btn-restore:hover {
            background: #28a745;
            color: white;
            transform: translateY(-1px);
        }

        .btn-view {
            background: rgba(23, 162, 184, 0.1);
            color: #0c5460;
            border: 1px solid rgba(23, 162, 184, 0.3);
        }

        .btn-view:hover {
            background: #17a2b8;
            color: white;
            transform: translateY(-1px);
        }

        .btn-permanent-delete {
            background: rgba(108, 117, 125, 0.1);
            color: #495057;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }

        .btn-permanent-delete:hover {
            background: #6c757d;
            color: white;
            transform: translateY(-1px);
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

        /* Deleted Badge */
        .deleted-badge {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-checked-in {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .status-checked-out {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .status-no-activity {
            background: rgba(255, 193, 7, 0.1);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        /* Login Activity Section */
        .login-activity {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(23, 162, 184, 0.05));
            border: 1px solid rgba(40, 167, 69, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .login-activity h4 {
            color: var(--primary-color);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .activity-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .activity-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
        }

        .activity-card.checkin {
            border-left-color: var(--success-color);
        }

        .activity-card.checkout {
            border-left-color: var(--danger-color);
        }

        .activity-card h5 {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .activity-card .time {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }

        .current-status {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            background-color: var(--card-bg);
            margin: 2% auto;
            padding: 2rem;
            border-radius: 16px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideInUp 0.3s ease-out;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .modal-header h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: var(--danger-color);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }

        .form-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
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

            .control-header {
                flex-direction: column;
                align-items: stretch;
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

            .modal-content {
                margin: 5% auto;
                width: 95%;
                padding: 1.5rem;
            }

            .activity-grid {
                grid-template-columns: 1fr;
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
            <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo">
        </div>
        <div class="title-container">
            <h1>University of Eswatini Library</h1>
            <div class="subtitle">Kwaluseni Campus - Student Management System</div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Alert Messages -->
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Member has been moved to trash. You can restore it from the deleted members section.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['restored'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-undo"></i>
                Member has been successfully restored!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['permanently_deleted'])): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Member has been permanently deleted and cannot be recovered.
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="page-header">
            <h2>
                <i class="fas fa-users"></i>
                <?= $show_deleted ? 'Deleted Members' : 'Manage Students' ?>
            </h2>
            <p><?= $show_deleted ? 'Restore deleted members or permanently remove them' : 'Manage library members, view details, and maintain student records' ?></p>
            
            <div class="stats-container">
                <div class="stat-card">
                    <h3><?= $total_rows ?></h3>
                    <p><?= $show_deleted ? 'Deleted Members' : 'Active Members' ?></p>
                </div>
                <?php if (!$show_deleted): ?>
                <div class="stat-card deleted">
                    <h3><?= $deleted_count ?></h3>
                    <p>In Trash</p>
                </div>
                <?php endif; ?>
                <div class="stat-card">
                    <h3><?= $total_pages ?></h3>
                    <p>Total Pages</p>
                </div>
                <div class="stat-card">
                    <h3><?= $page ?></h3>
                    <p>Current Page</p>
                </div>
            </div>
        </div>

        <!-- Control Section -->
        <div class="control-section">
            <div class="control-header">
                <div class="view-toggle">
                    <a href="manage_students.php" class="toggle-btn <?= !$show_deleted ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        Active Members
                    </a>
                    <a href="manage_students.php?show_deleted=1" class="toggle-btn <?= $show_deleted ? 'active' : '' ?>">
                        <i class="fas fa-trash"></i>
                        Deleted Members (<?= $deleted_count ?>)
                    </a>
                </div>
            </div>

            <form class="search-form" method="GET">
                <?php if ($show_deleted): ?>
                    <input type="hidden" name="show_deleted" value="1">
                <?php endif; ?>
                <input type="text" name="search" class="search-input" placeholder="🔍 Search by Name, Surname, or Email..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <?php if ($search): ?>
                    <a href="manage_students.php<?= $show_deleted ? '?show_deleted=1' : '' ?>" class="clear-btn">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header <?= $show_deleted ? 'deleted' : '' ?>">
                <h3>
                    <i class="fas fa-<?= $show_deleted ? 'trash' : 'table' ?>"></i>
                    <?= $show_deleted ? 'Deleted Member Records' : 'Member Records' ?>
                </h3>
                <div class="records-info">
                    Showing <?= min($offset + 1, $total_rows) ?>-<?= min($offset + $records_per_page, $total_rows) ?> of <?= $total_rows ?> members
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-id-card"></i> Member ID</th>
                            <th><i class="fas fa-user"></i> Name</th>
                            <th><i class="fas fa-graduation-cap"></i> Course/Department</th>
                            <th><i class="fas fa-users"></i> Member Type</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <?php if ($show_deleted): ?>
                                <th><i class="fas fa-calendar-times"></i> Deleted Date</th>
                            <?php endif; ?>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr <?= $show_deleted ? 'class="deleted"' : '' ?>>
                                    <td><strong><?php echo $row['ID']; ?></strong></td>
                                    <td><code><?php echo $row['Member_ID']; ?></code></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['Name'] . ' ' . $row['Surname']); ?></strong>
                                        <?php if ($show_deleted): ?>
                                            <br><span class="deleted-badge">Deleted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['Course/Department/Affliation']); ?></td>
                                    <td>
                                        <?php
                                        $memberType = $row['Membership_type'];
                                        $badgeClass = 'badge-student';
                                        if ($memberType == 'Staff') $badgeClass = 'badge-staff';
                                        elseif ($memberType == 'External Member') $badgeClass = 'badge-external';
                                        ?>
                                        <span class="member-badge <?= $badgeClass ?>">
                                            <?php echo htmlspecialchars($memberType); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                    <?php if ($show_deleted): ?>
                                        <td><?php echo $row['deleted_at'] ? date('M d, Y H:i', strtotime($row['deleted_at'])) : '-'; ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($show_deleted): ?>
                                                <a href="manage_students.php?restore=<?php echo $row['ID']; ?>&show_deleted=1" class="action-btn btn-restore" onclick="return confirm('Are you sure you want to restore this member?');">
                                                    <i class="fas fa-undo"></i> Restore
                                                </a>
                                                <a href="#" class="action-btn btn-view" onclick="openViewModal(<?php echo $row['ID']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="manage_students.php?permanent_delete=<?php echo $row['ID']; ?>&show_deleted=1" class="action-btn btn-permanent-delete" onclick="return confirm('Are you sure you want to PERMANENTLY delete this member? This action cannot be undone!');">
                                                    <i class="fas fa-trash-alt"></i> Delete Forever
                                                </a>
                                            <?php else: ?>
                                                <a href="#" class="action-btn btn-edit" onclick="openEditModal(<?php echo $row['ID']; ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="manage_students.php?delete=<?php echo $row['ID']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to move this member to trash?');">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                                <a href="#" class="action-btn btn-view" onclick="openViewModal(<?php echo $row['ID']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $show_deleted ? '8' : '7' ?>">
                                    <div class="empty-state">
                                        <i class="fas fa-<?= $show_deleted ? 'trash' : 'users-slash' ?>"></i>
                                        <h3><?= $show_deleted ? 'No Deleted Members' : 'No Members Found' ?></h3>
                                        <p><?= $search ? 'No members match your search criteria.' : ($show_deleted ? 'No members have been deleted.' : 'No members are registered yet.') ?></p>
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
                    <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $show_deleted ? '&show_deleted=1' : '' ?>" class="nav-button">‹ Previous</a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                    if ($i == $page):
                ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $show_deleted ? '&show_deleted=1' : '' ?>"><?= $i ?></a>
                <?php
                    endif;
                endfor;
                ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $show_deleted ? '&show_deleted=1' : '' ?>" class="nav-button">Next ›</a>
                    <a href="?page=<?= $total_pages ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $show_deleted ? '&show_deleted=1' : '' ?>" class="nav-button">Last »</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-eye"></i> Member Details</h3>
                <span class="close" onclick="closeModal('viewModal')">&times;</span>
            </div>
            <div id="viewContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Member</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Name:</label>
                    <input type="text" name="name" id="editName" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Surname:</label>
                    <input type="text" name="surname" id="editSurname" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-graduation-cap"></i> Course/Department:</label>
                    <input type="text" name="course" id="editCourse" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Contact Email:</label>
                    <input type="email" name="email" id="editEmail" required>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" name="update" class="btn-primary">
                        <i class="fas fa-save"></i> Update Member
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Back Button -->
    <button class="back-button" onclick="window.location.href='homepage.php'" title="Back to Dashboard">
        <i class="fas fa-home"></i>
    </button>

    <script>
        // Modal functions
        function openEditModal(id) {
            fetch(`get_member.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editId').value = data.ID;
                    document.getElementById('editName').value = data.Name;
                    document.getElementById('editSurname').value = data.Surname;
                    document.getElementById('editCourse').value = data['Course/Department/Affliation'];
                    document.getElementById('editEmail').value = data.Email;
                    document.getElementById('editModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading member data');
                });
        }

        function openViewModal(id) {
            fetch(`get_member.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    // Format dates
                    const formatDateTime = (dateString) => {
                        if (!dateString || dateString === 'NULL') return 'No activity recorded';
                        const date = new Date(dateString);
                        return date.toLocaleString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        });
                    };

                    // Determine current status
                    const checkinTime = data.Checkin_Time && data.Checkin_Time !== 'NULL' ? new Date(data.Checkin_Time) : null;
                    const checkoutTime = data.Checkout_Time && data.Checkout_Time !== 'NULL' ? new Date(data.Checkout_Time) : null;
                    
                    let currentStatus = '';
                    let statusClass = '';
                    let statusIcon = '';
                    
                    if (!checkinTime && !checkoutTime) {
                        currentStatus = 'No Login Activity';
                        statusClass = 'status-no-activity';
                        statusIcon = 'fas fa-question-circle';
                    } else if (checkinTime && !checkoutTime) {
                        currentStatus = 'Currently Checked In';
                        statusClass = 'status-checked-in';
                        statusIcon = 'fas fa-sign-in-alt';
                    } else if (!checkinTime && checkoutTime) {
                        currentStatus = 'Previously Checked Out';
                        statusClass = 'status-checked-out';
                        statusIcon = 'fas fa-sign-out-alt';
                    } else if (checkinTime > checkoutTime) {
                        currentStatus = 'Currently Checked In';
                        statusClass = 'status-checked-in';
                        statusIcon = 'fas fa-sign-in-alt';
                    } else {
                        currentStatus = 'Currently Checked Out';
                        statusClass = 'status-checked-out';
                        statusIcon = 'fas fa-sign-out-alt';
                    }

                    const deletedInfo = data.deleted_at ? `
                        <div style="background: rgba(220, 53, 69, 0.1); color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid rgba(220, 53, 69, 0.3);">
                            <strong><i class="fas fa-exclamation-triangle"></i> This member was deleted on:</strong> ${new Date(data.deleted_at).toLocaleString()}
                        </div>
                    ` : '';
                    
                    const content = `
                        ${deletedInfo}
                        
                        <!-- Basic Information -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div><strong>ID:</strong> ${data.ID}</div>
                            <div><strong>Member ID:</strong> ${data.Member_ID}</div>
                            <div><strong>Name:</strong> ${data.Name}</div>
                            <div><strong>Surname:</strong> ${data.Surname}</div>
                            <div><strong>Course/Department:</strong> ${data['Course/Department/Affliation']}</div>
                            <div><strong>Member Type:</strong> ${data.Membership_type}</div>
                            <div><strong>Email:</strong> ${data.Email}</div>
                            <div><strong>Joined Date:</strong> ${data.Joined_Date}</div>
                        </div>

                        <!-- Login Activity Section -->
                        <div class="login-activity">
                            <h4><i class="fas fa-clock"></i> Login Activity</h4>
                            
                            <div class="activity-grid">
                                <div class="activity-card checkin">
                                    <h5><i class="fas fa-sign-in-alt" style="color: var(--success-color);"></i> Last Check-in</h5>
                                    <div class="time">${formatDateTime(data.Checkin_Time)}</div>
                                </div>
                                
                                <div class="activity-card checkout">
                                    <h5><i class="fas fa-sign-out-alt" style="color: var(--danger-color);"></i> Last Check-out</h5>
                                    <div class="time">${formatDateTime(data.Checkout_Time)}</div>
                                </div>
                            </div>
                            
                            <div class="current-status">
                                <span class="status-badge ${statusClass}">
                                    <i class="${statusIcon}"></i>
                                    ${currentStatus}
                                </span>
                            </div>
                        </div>

                        <div style="text-align: center; margin-top: 2rem;">
                            <button class="btn-secondary" onclick="closeModal('viewModal')">Close</button>
                        </div>
                    `;
                    document.getElementById('viewContent').innerHTML = content;
                    document.getElementById('viewModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading member data');
                });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const viewModal = document.getElementById('viewModal');
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>

    <?php
    // Handle update
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $course = $_POST['course'];
        $email = $_POST['email'];
        $conn->query("UPDATE members SET Name='$name', Surname='$surname', `Course/Department/Affliation`='$course', Email='$email' WHERE ID=$id");
        echo "<script>window.location.href='manage_students.php';</script>";
    }

    $conn->close();
    ?>
</body>
</html>
