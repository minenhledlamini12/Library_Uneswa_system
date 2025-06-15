<?php
session_start();
require_once("connection.php");

// Fetch log updates from library_access_log table with pagination
function fetchUpdates($page = 1, $perPage = 10) {
    global $conn;
    $offset = ($page - 1) * $perPage;
    // Join with members table for names
    $sql = "SELECT 
                log.Member_ID, 
                log.Email, 
                CONCAT(m.Name, ' ', m.Surname) AS Name, 
                log.Entry_Time, 
                log.Exit_Time, 
                log.status 
            FROM library_access_log log
            LEFT JOIN members m ON log.Member_ID = m.ID
            ORDER BY log.Entry_Time DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total FROM library_access_log";
    $countResult = $conn->query($countSql);
    $totalRows = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $perPage);

    echo json_encode(['data' => $data, 'totalPages' => $totalPages]);
    $stmt->close();
}

// AJAX handler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    fetchUpdates($page);
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Access Log Report</title>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: linear-gradient(135deg, #f0f9ff 0%, #bae6fd 100%);
        }
        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
        }
        .report-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            width: 90%;
            max-width: 1200px;
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
            background-color: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: linear-gradient(to right, #1e40af, #3b82f6);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background-color: #eff6ff;
            transition: background-color 0.3s ease;
        }
        .status-icon {
            font-size: 18px;
            margin-right: 8px;
            transition: transform 0.3s ease;
        }
        .in-library {
            color: #10b981;
        }
        .out-library {
            color: #ef4444;
        }
        .status-icon:hover {
            transform: scale(1.2);
        }
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            background: linear-gradient(to right, #1e40af, #3b82f6);
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .back-button:hover {
            background: linear-gradient(to right, #1e3a8a, #2563eb);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }
        .pagination button {
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        .pagination button:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        .pagination button:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }
        .pagination span {
            font-size: 0.875rem;
            color: #1e40af;
            font-weight: 500;
        }
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: #1e40af;
            color: #fff;
            border-radius: 0.375rem;
            transform: translateY(-1px);
        }
        .social-icon {
            transition: all 0.3s ease;
        }
        .social-icon:hover {
            color: #3b82f6;
            transform: translateY(-3px);
        }
        .footer-link {
            transition: color 0.3s ease;
        }
        .footer-link:hover {
            color: #3b82f6;
        }
        .footer-heading {
            color: #f97316;
        }
        .loader {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .loader i {
            font-size: 24px;
            color: #3b82f6;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <header class="bg-blue-900 text-white py-4 px-6 shadow-lg">
        <div class="container mx-auto">
            <div class="flex justify-between items-center mb-4">
                <div class="header-info text-sm flex items-center space-x-4">
                    <span class="flex items-center">
                        <i class="far fa-clock mr-2"></i>
                        Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-phone mr-2"></i>
                        2517 0448
                    </span>
                </div>
                <div class="social-icons flex space-x-4">
                    <a href="#" class="social-icon"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>
            <div class="flex items-center">
                <img src="download.png" alt="UNESWA Logo" class="h-16 mr-4 transition-transform duration-300 hover:scale-105">
                <span class="text-2xl font-bold site-title">UNESWA Library</span>
            </div>
            <?php if (isset($_SESSION['username'])) echo "<p class='text-white bg-blue-800 px-4 py-2 rounded-lg animate-pulse'>Welcome, " . $_SESSION['username'] . "!</p>"; ?>
        </div>
    </header>

    <nav class="bg-gray-800 text-white py-3 px-6 shadow-md">
        <ul class="flex justify-center space-x-6">
            <li><a href="about.php" class="nav-link px-4 py-2">About</a></li>
            <li><a href="form.php" class="nav-link px-4 py-2">Registration</a></li>
            <li><a href="control.php" class="nav-link px-4 py-2">Control</a></li>
            <li><a href="report.php" class="nav-link px-4 py-2">Report</a></li>
        </ul>
    </nav>

    <div class="content">
        <div class="report-container">
            <h1><i class="fas fa-book-reader animate-bounce"></i> Library Access Log Report</h1>
            <div class="loader">
                <i class="fas fa-spinner"></i> Loading...
            </div>
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-id-card mr-2"></i>Member ID</th>
                        <th><i class="fas fa-envelope mr-2"></i>Email</th>
                        <th><i class="fas fa-user mr-2"></i>Name</th>
                        <th><i class="fas fa-clock mr-2"></i>Entry Time</th>
                        <th><i class="fas fa-clock mr-2"></i>Exit Time</th>
                        <th><i class="fas fa-door-open mr-2"></i>Status</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <!-- Rows will be filled by JS -->
                </tbody>
            </table>
            <div class="pagination" id="pagination">
                <!-- Pagination controls will be filled by JS -->
            </div>
            <a href="homepage.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>

    <footer class="bg-gray-800 text-white py-6 px-6">
        <div class="container mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="footer-section">
				<h3 class="text-lg font-semibold mb-3 footer-heading flex items-center">
                        <i class="fas fa-envelope mr-2"></i> Get In Touch
                    </h3>
					<img src="download.png" alt="UNESWA Footer Logo" class="h-12 mb-4 hover-scale">
                    <ul class="space-y-1 text-sm">
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <a href="#" class="footer-link">Kwaluseni, Luyengo & Mbabane</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            <a href="#" class="footer-link">2517 0448</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            <a href="mailto:library@uniswa.sz" class="footer-link">library@uniswa.sz</a>
                        </li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3 class="text-lg font-semibold mb-3 footer-heading flex items-center">
                        <i class="fas fa-database mr-2"></i> Popular Databases
                    </h3>
                    <ul class="space-y-1 text-sm">
                        <li><a href="#" class="footer-link">Science Direct</a></li>
                        <li><a href="#" class="footer-link">Ebscohost</a></li>
                        <li><a href="#" class="footer-link">ERIC</a></li>
                        <li><a href="#" class="footer-link">Taylor & Francis</a></li>
                        <li><a href="#" class="footer-link">Sabinet</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3 class="text-lg font-semibold mb-3 footer-heading flex items-center">
                        <i class="fas fa-users mr-2"></i> Follow Us
                    </h3>
                    <ul class="space-y-1 text-sm">
                        <li class="flex items-center">
                            <i class="fab fa-twitter mr-2 social-icon"></i>
                            <a href="#" class="footer-link">Twitter</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fab fa-facebook mr-2 social-icon"></i>
                            <a href="#" class="footer-link">Facebook</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fab fa-instagram mr-2 social-icon"></i>
                            <a href="#" class="footer-link">Instagram</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom mt-6 text-center border-t border-gray-700 pt-4">
                <p class="text-sm">© 2025 UNESWA Library. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        let currentPage = 1;
        let totalPages = 1;

        function updateTable(page = 1) {
            const tableBody = document.getElementById('table-body');
            const pagination = document.getElementById('pagination');
            const loader = document.querySelector('.loader');
            loader.style.display = 'block';
            tableBody.style.opacity = '0.5';

            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ajax=true&page=${page}`,
            })
            .then(response => response.json())
            .then(({data, totalPages: pages}) => {
                totalPages = pages;
                tableBody.innerHTML = '';
                data.forEach(row => {
                    let statusText, statusIcon;
                    if (row.status === 'in') {
                        statusText = 'In Library';
                        statusIcon = '<i class="fas fa-door-open status-icon in-library"></i>';
                    } else if (row.status === 'out') {
                        statusText = 'Out of Library';
                        statusIcon = '<i class="fas fa-door-closed status-icon out-library"></i>';
                    } else {
                        statusText = row.status;
                        statusIcon = '';
                    }
                    const rowHtml = `
                        <tr class="animate-fade-in">
                            <td>${row.Member_ID}</td>
                            <td>${row.Email}</td>
                            <td>${row.Name ?? ''}</td>
                            <td>${row.Entry_Time}</td>
                            <td>${row.Exit_Time ? row.Exit_Time : '---'}</td>
                            <td>${statusIcon} ${statusText}</td>
                        </tr>
                    `;
                    tableBody.insertAdjacentHTML('beforeend', rowHtml);
                });

                // Update pagination
                pagination.innerHTML = `
                    <button onclick="updateTable(${Math.max(1, page - 1)})" ${page === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>
                    <span>Page ${page} of ${totalPages}</span>
                    <button onclick="updateTable(${Math.min(totalPages, page + 1)})" ${page === totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>
                `;
                loader.style.display = 'none';
                tableBody.style.opacity = '1';
            })
            .catch(error => {
                console.error('Error updating table:', error);
                loader.style.display = 'none';
                tableBody.style.opacity = '1';
            });

            // Refresh every 30 seconds to reduce server load
            setTimeout(() => updateTable(currentPage), 30000);
        }

        // Animation for table rows
        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes fade-in {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in {
                animation: fade-in 0.5s ease-out;
            }
        `;
        document.head.appendChild(style);

        updateTable(currentPage);
    </script>