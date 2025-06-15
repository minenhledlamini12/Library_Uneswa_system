<?php
include 'connection.php';

// Set the number of records to display per page
$recordsPerPage = 10;

// Get the current page number from the query string
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the starting record number for the current page
$start = ($page - 1) * $recordsPerPage;

// Handle search query
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $search_condition = "WHERE Name LIKE '%$search_safe%' OR ID = '$search_safe'";
}

// Query to get total number of members for pagination
$totalQuery = "SELECT COUNT(*) AS total FROM members " . $search_condition;
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Main query to fetch members for the current page
$query = "SELECT * FROM members " . $search_condition . " LIMIT $start, $recordsPerPage";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members Portal | UNESWA Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset & base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7f9;
            color: #333;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .wrapper {
            flex: 1;
        }

        /* Header Styles */
        .top-bar {
            background-color: #2e7d32;
            color: white;
            padding: 10px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9em;
        }

        .top-bar-contact a {
            color: white;
            margin-left: 15px;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .top-bar-contact a:hover {
            opacity: 0.8;
        }

        .header-main {
            background: linear-gradient(135deg, #4CAF50 0%, #2e7d32 100%);
            color: white;
            padding: 30px 5%;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .header-main img {
            max-width: 100px;
            margin-bottom: 15px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .header-main h1 {
            font-size: 2.2rem;
            margin-bottom: 5px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        
        .header-main span {
            font-size: 1.1rem;
            font-weight: 300;
        }

        /* Main Content */
        .container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            max-width: 1200px;
            width: 90%;
            margin: -50px auto 50px;
            padding: 30px;
            position: relative;
            z-index: 10;
        }

        h1.portal-title {
            text-align: center;
            margin: 0 0 30px;
            font-weight: 700;
            font-size: 2.4rem;
            color: #1b5e20;
            position: relative;
            padding-bottom: 15px;
        }
        
        h1.portal-title:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: #4CAF50;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        /* Remove redundant heading */
        h1.search-results {
            display: none;
        }

        /* Navigation Buttons */
        .nav-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 35px;
        }
        
        .btn {
            background-color: #388e3c;
            color: white;
            padding: 12px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 10px rgba(56, 142, 60, 0.3);
            transition: all 0.3s ease;
            display: inline-block;
            user-select: none;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #2e7d32;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(46, 125, 50, 0.5);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 10px rgba(56, 142, 60, 0.3);
        }

        /* Search Section */
        .search-section {
            max-width: 600px;
            margin: 0 auto 40px;
            text-align: center;
            background: #f9fbf9;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
        }
        
        .search-section h2 {
            margin-bottom: 15px;
            font-weight: 600;
            color: #2e7d32;
            font-size: 1.5rem;
        }
        
        .search-section form {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .search-section input[type="text"] {
            padding: 12px 18px;
            border-radius: 30px;
            border: 2px solid #a5d6a7;
            width: 100%;
            max-width: 400px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .search-section input[type="text"]:focus {
            outline: none;
            border-color: #388e3c;
            box-shadow: 0 0 8px #81c784;
        }
        
        .search-section button.btn {
            padding: 12px 28px;
            border-radius: 30px;
            font-weight: 600;
        }

        /* Members Table */
        .members-table h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 700;
            color: #1b5e20;
            font-size: 1.8rem;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
            font-size: 0.95rem;
            table-layout: fixed;
            margin-bottom: 20px;
        }
        
        thead tr {
            background-color: #a5d6a7;
            color: #1b5e20;
            font-weight: 700;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(46, 125, 50, 0.15);
        }
        
        thead th {
            padding: 16px 15px;
            text-align: left;
            user-select: none;
            font-size: 1rem;
        }
        
        thead th:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        
        thead th:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        
        tbody tr {
            background-color: #f5fbf5;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(46, 125, 50, 0.1);
            transition: all 0.3s ease;
            cursor: default;
        }
        
        tbody tr:hover {
            background-color: #e8f5e9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(46, 125, 50, 0.15);
        }
        
        tbody td {
            padding: 16px 15px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        tbody td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        
        tbody td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        
        tbody td.actions {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: nowrap;
        }

        /* Small buttons for actions */
        .btn-small {
            font-size: 0.85rem;
            padding: 8px 16px;
            border-radius: 20px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            user-select: none;
            display: inline-block;
            text-align: center;
            white-space: nowrap;
            text-decoration: none;
            font-weight: 500;
        }
        
        .btn-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .btn-small:active {
            transform: translateY(0);
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        
        .btn-small.edit {
            background-color: #66bb6a;
            color: white;
        }
        
        .btn-small.edit:hover {
            background-color: #388e3c;
        }
        
        .btn-small.delete {
            background-color: #ef5350;
            color: white;
        }
        
        .btn-small.delete:hover {
            background-color: #d32f2f;
        }
        
        .btn-small.view {
            background-color: #4db6ac;
            color: white;
        }
        
        .btn-small.view:hover {
            background-color: #00897b;
        }

        /* Pagination Styling */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            color: #388e3c;
            background-color: #e8f5e9;
            border: 1px solid #a5d6a7;
            transition: all 0.3s ease;
            min-width: 40px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .pagination a:hover {
            background-color: #388e3c;
            color: white;
            border-color: #388e3c;
        }

        .pagination .current {
            background-color: #388e3c;
            color: white;
            border-color: #388e3c;
            font-weight: 600;
            box-shadow: 0 3px 8px rgba(56, 142, 60, 0.3);
        }

        /* Footer Styles */
        footer {
            background: linear-gradient(135deg, #4CAF50 0%, #2e7d32 100%);
            color: white;
            padding: 40px 0 20px;
            font-size: 0.95em;
            margin-top: auto;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            padding: 0 5%;
        }

        .footer-section {
            margin-bottom: 30px;
            flex: 1;
            min-width: 250px;
            padding: 0 15px;
        }

        .footer-section h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.3rem;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-section h3:after {
            content: '';
            position: absolute;
            width: 40px;
            height: 3px;
            background: rgba(255,255,255,0.5);
            bottom: 0;
            left: 0;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section li {
            margin-bottom: 12px;
        }

        .footer-section a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .footer-section a:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 25px;
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.9em;
        }

        /* "Get In Touch" Section */
        .get-in-touch img {
            height: 50px;
            margin-bottom: 15px;
            filter: brightness(0) invert(1);
        }

        .get-in-touch p {
            margin-bottom: 8px;
        }

        /* "Follow Us" Section */
        .follow-us a {
            display: inline-block;
            margin-right: 15px;
            font-size: 1.4em;
            color: white;
            transition: transform 0.3s;
        }
        
        .follow-us a:hover {
            transform: translateY(-3px);
        }

        /* No results message styling */
        .no-results {
            text-align: center;
            font-style: italic;
            color: #4caf50;
            padding: 30px;
            background: #f5fbf5;
            border-radius: 8px;
            margin: 20px 0;
        }

        /* Delete confirmation modal */
        .delete-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
        }
        
        .delete-modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 80%;
            max-width: 500px;
            animation: modalFade 0.3s ease;
        }
        
        .delete-modal-content h3 {
            color: #d32f2f;
            margin-bottom: 15px;
            text-align: center;
            font-size: 1.5rem;
        }
        
        .delete-modal-content p {
            margin-bottom: 25px;
            text-align: center;
        }
        
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .btn-cancel {
            background-color: #90a4ae;
        }
        
        .btn-confirm-delete {
            background-color: #ef5350;
        }
        
        .btn-confirm-delete:hover {
            background-color: #d32f2f;
        }
        
        @keyframes modalFade {
            from {opacity: 0; transform: translateY(-30px);}
            to {opacity: 1; transform: translateY(0);}
        }

        /* Responsive adjustments */
        @media (max-width: 1100px) {
            .container {
                width: 95%;
                padding: 25px 20px;
            }
            
            h1.portal-title {
                font-size: 2.2rem;
            }
        }
        
        @media (max-width: 900px) {
            .header-main h1 {
                font-size: 1.8rem;
            }
            
            .header-main span {
                font-size: 1rem;
            }
            
            .nav-buttons {
                flex-direction: column;
                gap: 15px;
                max-width: 300px;
                margin-left: auto;
                margin-right: auto;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
            
            .search-section form {
                flex-direction: column;
            }
            
            .search-section button.btn {
                width: 100%;
                margin-top: 10px;
            }
            
            .footer-section {
                flex: 0 0 100%;
                text-align: center;
                margin-bottom: 25px;
            }
            
            .footer-section h3:after {
                left: 50%;
                transform: translateX(-50%);
            }
        }
        
        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                gap: 8px;
                padding: 10px;
                text-align: center;
            }
            
            table {
                font-size: 0.85rem;
            }
            
            thead {
                display: none;
            }
            
            tbody tr {
                display: block;
                margin-bottom: 20px;
                padding: 10px;
            }
            
            tbody td {
                display: block;
                text-align: right;
                padding: 10px 15px;
                position: relative;
                padding-left: 50%;
            }
            
            tbody td:before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                font-weight: bold;
                text-align: left;
            }
            
            tbody td.actions {
                display: flex;
                justify-content: center;
                padding-left: 15px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeInUp 0.8s ease forwards;
        }
        
        .slide-in {
            animation: slideIn 0.6s ease forwards;
        }
        
        @keyframes slideIn {
            0% {
                opacity: 0;
                transform: translateX(-20px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="top-bar">
            <span><i class="far fa-clock"></i> Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM</span>
            <div class="top-bar-contact">
                <i class="fas fa-phone-alt"></i> 2517 0448
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        <div class="header-main">
            <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo">
            <h1>University of Eswatini Library</h1>
            <span>Kwaluseni Campus - Self-Service Book Borrowing</span>
        </div>

        <div class="container fade-in" role="main">
            <h1 class="search-results">Search results:</h1>
            <h1 class="portal-title">Manage Members Portal</h1>

            <!-- Navigation Buttons -->
            <nav class="nav-buttons" aria-label="Primary navigation">
                <a href="form.php" class="btn slide-in" role="button"><i class="fas fa-user-plus"></i> Register New Member</a>
                <a href="history.php" class="btn slide-in" role="button"><i class="fas fa-history"></i> View History</a>
            </nav>

            <!-- Search Form -->
            <section class="search-section" aria-labelledby="search-heading">
                <h2 id="search-heading"><i class="fas fa-search"></i> Search Members</h2>
                <form method="GET" action="index.php" role="search" aria-describedby="search-desc">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search by Name or ID"
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        aria-label="Search members by Name or ID"
                    />
                    <button type="submit" class="btn" aria-label="Submit search">Search</button>
                </form>
            </section>

            <!-- Members Table -->
            <section class="members-table" aria-labelledby="members-heading">
                <h2 id="members-heading"><i class="fas fa-users"></i> All Members</h2>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <table role="table" aria-describedby="members-desc" aria-live="polite">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Course/Dept/Affiliation</th>
                                <th scope="col">Membership Type</th>
                                <th scope="col">Status</th>
                                <th scope="col" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Escape output
                                $id = htmlspecialchars($row['ID']);
                                $name = htmlspecialchars($row['Name']);
                                $courseDept = htmlspecialchars($row['Course/Department/Affliation']);
                                $memberType = htmlspecialchars($row['Membership_type']);
                                $status = $row['Status'] == 1 ? 'Active' : 'Inactive';

                                echo "<tr>";
                                echo "<td data-label='ID' title='Member ID'>$id</td>";
                                echo "<td data-label='Name' title='Member Name'>$name</td>";
                                echo "<td data-label='Course/Dept' title='Course, Department or Affiliation'>$courseDept</td>";
                                echo "<td data-label='Type' title='Membership Type'>$memberType</td>";
                                echo "<td data-label='Status' title='Status'>";
                                if ($row['Status'] == 1) {
                                    echo "<span style='color: #2e7d32; font-weight: 500;'><i class='fas fa-circle' style='font-size: 10px; margin-right: 5px;'></i>Active</span>";
                                } else {
                                    echo "<span style='color: #d32f2f; font-weight: 500;'><i class='fas fa-circle' style='font-size: 10px; margin-right: 5px;'></i>Inactive</span>";
                                }
                                echo "</td>";
                                echo "<td class='actions' data-label='Actions'>";
                                echo "<a href='view.php?id=$id' class='btn-small view' title='View full details of $name' aria-label='View details of $name'><i class='fas fa-eye'></i> View</a>";
                                echo "<a href='edit.php?id=$id' class='btn-small edit' title='Edit details of $name' aria-label='Edit details of $name'><i class='fas fa-edit'></i> Edit</a>";
                                echo "<a href='javascript:void(0)' onclick='confirmDelete($id, \"$name\")' class='btn-small delete' title='Delete $name' aria-label='Delete $name'><i class='fas fa-trash-alt'></i> Delete</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 15px; color: #a5d6a7;"></i>
                        <p>No members found matching your search criteria.</p>
                        <?php if (!empty($search)): ?>
                            <p style="margin-top: 10px;"><a href="index.php" style="color: #4CAF50; text-decoration: underline;">Clear search and show all members</a></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
           适用

            <!-- Pagination Links -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo ($page - 1); echo !empty($search) ? '&search=' . htmlspecialchars($search) : ''; ?>" aria-label="Previous Page"><i class="fas fa-chevron-left"></i> Prev</a>
                    <?php endif; ?>

                    <?php 
                    // Show limited page numbers with ellipsis
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1) {
                        echo '<a href="?page=1' . (!empty($search) ? '&search=' . htmlspecialchars($search) : '') . '" aria-label="Go to first page">1</a>';
                        if ($startPage > 2) {
                            echo '<span style="padding: 10px 5px;">...</span>';
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current" aria-current="page"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; echo !empty($search) ? '&search=' . htmlspecialchars($search) : ''; ?>" aria-label="Go to page <?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; 
                    
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<span style="padding: 10px 5px;">...</span>';
                        }
                        echo '<a href="?page=' . $totalPages . (!empty($search) ? '&search=' . htmlspecialchars($search) : '') . '" aria-label="Go to last page">' . $totalPages . '</a>';
                    }
                    ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo ($page + 1); echo !empty($search) ? '&search=' . htmlspecialchars($search) : ''; ?>" aria-label="Next Page">Next <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="delete-modal">
        <div class="delete-modal-content">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
            <p>Are you sure you want to delete <span id="deleteMemberName"></span>?</p>
            <div class="modal-buttons">
                <button class="btn btn-small btn-cancel" onclick="closeModal()">Cancel</button>
                <a id="confirmDeleteLink" href="#" class="btn btn-small btn-confirm-delete">Delete</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-section get-in-touch">
                <h3>Get In Touch</h3>
                <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo">
                <p><i class="fas fa-map-marker-alt"></i> Kwaluseni Campus, Eswatini</p>
                <p><i class="fas fa-phone-alt"></i> 2517 0448</p>
                <p><i class="fas fa-envelope"></i> library@uniswa.sz</p>
            </div>
            <div class="footer-section quick-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Library Catalogue</a></li>
                    <li><a href="#">E-Resources</a></li>
                    <li><a href="#">Borrowing Rules</a></li>
                    <li><a href="#">Ask a Librarian</a></li>
                </ul>
            </div>
            <div class="footer-section follow-us">
                <h3>Follow Us</h3>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> University of Eswatini Library. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function confirmDelete(id, name) {
            document.getElementById('deleteMemberName').textContent = name.replace(/</g, '<').replace(/>/g, '>');
            document.getElementById('confirmDeleteLink').href = 'delete.php?id=' + id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>