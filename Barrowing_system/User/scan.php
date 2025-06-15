<?php
session_start();
require_once("connection.php");

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

// Get member details
$member_id = $_SESSION['member_id'];
$stmt = $conn->prepare("SELECT m.*, mt.type_name FROM members m JOIN membership_types mt ON m.membership_type_id = mt.type_id WHERE m.member_id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

// Get currently borrowed books
$stmt = $conn->prepare("SELECT b.*, bh.borrow_date, bh.due_date 
                       FROM borrowing_history bh 
                       JOIN books b ON bh.book_id = b.book_id 
                       WHERE bh.member_id = ? AND bh.return_date IS NULL
                       ORDER BY bh.due_date ASC");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$borrowed_books = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 90%;
            margin: 20px auto;
        }
        
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px 8px 0 0;
        }
        
        .welcome-text {
            font-size: 1.2em;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .logout-btn {
            margin-left: 20px;
            padding: 8px 15px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        @media (min-width: 768px) {
            .main-content {
                grid-template-columns: 1fr 2fr;
            }
        }
        
        .panel {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .panel h2 {
            color: #2c3e50;
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .member-details {
            margin-bottom: 20px;
        }
        
        .member-details p {
            margin: 10px 0;
        }
        
        .member-details strong {
            display: inline-block;
            width: 150px;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 15px;
            background-color: #3498db;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: transform 0.3s, background-color 0.3s;
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            background-color: #2980b9;
        }
        
        .action-btn i {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            text-align: left;
        }
        
        table th, table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background-color: #f8f9fa;
            color: #2c3e50;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-borrowed {
            background-color: #ffeaa7;
            color: #d35400;
        }
        
        .status-overdue {
            background-color: #ff7675;
            color: white;
        }
        
        .due-soon {
            color: #e67e22;
            font-weight: bold;
        }
        
        .overdue {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .no-books {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="welcome-text">
                <h1><i class="fas fa-book"></i> Library System</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['member_name']); ?>!</p>
            </div>
            <div class="user-info">
                <img src="https://via.placeholder.com/40" alt="User Avatar">
                <span><?php echo htmlspecialchars($_SESSION['member_name']); ?></span>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </header>
        
        <div class="main-content">
            <div class="panel">
                <h2>Member Information</h2>
                <div class="member-details">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></p>
                    <p><strong>Membership Type:</strong> <?php echo htmlspecialchars($member['type_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($member['email']); ?></p>
                    <p><strong>Join Date:</strong> <?php echo htmlspecialchars($member['join_date']); ?></p>
                    <p><strong>Expiry Date:</strong> <?php echo htmlspecialchars($member['expiry_date']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($member['status']); ?></p>
                </div>
                
                <h2>Quick Actions</h2>
                <div class="quick-actions">
                    <a href="scan_book.php" class="action-btn">
                        <i class="fas fa-qrcode"></i>
                        <span>Scan & Borrow</span>
                    </a>
                    <a href="search_books.php" class="action-btn">
                        <i class="fas fa-search"></i>
                        <span>Search Books</span>
                    </a>
                    <a href="borrowing_history.php" class="action-btn">
                        <i class="fas fa-history"></i>
                        <span>History</span>
                    </a>
                    <a href="profile.php" class="action-btn">
                        <i class="fas fa-user-edit"></i>
                        <span>Edit Profile</span>
                    </a>
                </div>
            </div>
            
            <div class="panel">
                <h2>Currently Borrowed Books</h2>
                
                <?php if ($borrowed_books->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Borrowed On</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($book = $borrowed_books->fetch_assoc()): ?>
                                <?php
                                    $due_date = new DateTime($book['due_date']);
                                    $today = new DateTime();
                                    $days_remaining = $today->diff($due_date)->days;
                                    $is_overdue = $today > $due_date;
                                    
                                    $status_class = $is_overdue ? 'status-overdue' : 'status-borrowed';
                                    $date_class = $is_overdue ? 'overdue' : ($days_remaining <= 3 ? 'due-soon' : '');
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['Title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['borrow_date']); ?></td>
                                    <td class="<?php echo $date_class; ?>">
                                        <?php echo htmlspecialchars($book['due_date']); ?>
                                        <?php if ($is_overdue): ?>
                                            <span>(Overdue)</span>
                                        <?php elseif ($days_remaining <= 3): ?>
                                            <span>(Due soon)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo $is_overdue ? 'OVERDUE' : 'BORROWED'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-books">
                        <p><i class="fas fa-info-circle"></i> You don't have any books borrowed at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
