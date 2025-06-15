<?php
session_start();
require_once("connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4"><i class="fas fa-users"></i> Manage Members Portal</h1>

        <!-- Registration Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-user-plus"></i> Register New Member</h5>
            </div>
            <div class="card-body">
                <form action="process_actions.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ID" class="form-label">Member ID</label>
                            <input type="text" class="form-control" id="ID" name="ID" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="Name" name="Name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Surname" class="form-label">Surname</label>
                            <input type="text" class="form-control" id="Surname" name="Surname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Course/Department/Affliation" class="form-label">Course/Department/Affiliation</label>
                            <input type="text" class="form-control" id="Course/Department/Affliation" name="Course/Department/Affliation">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Membership_Type" class="form-label">Membership Type</label>
                            <select class="form-select" id="Membership_Type" name="Membership_Type" required>
                                <option value="Student">Student</option>
                                <option value="Staff">Staff</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Contact" class="form-label">Contact</label>
                            <input type="text" class="form-control" id="Contact" name="Contact">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="Email" name="Email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="Password" name="Password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Joined_Date" class="form-label">Joined Date</label>
                            <input type="date" class="form-control" id="Joined_Date" name="Joined_Date" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Register</button>
                </form>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-search"></i> Search Member</h5>
            </div>
            <div class="card-body">
                <form action="manage_members.php" method="GET" class="d-flex">
                    <input type="text" class="form-control me-2" name="search" placeholder="Enter Member ID or Email" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>
        </div>

        <!-- Members Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-table"></i> All Members</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Member ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Membership Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                        if ($search) {
                            $sql = "SELECT * FROM members WHERE Member_ID LIKE ? OR Email LIKE ?";
                            $stmt = $conn->prepare($sql);
                            $search_param = "%$search%";
                            $stmt->bind_param("ss", $search_param, $search_param);
                            $stmt->execute();
                            $result = $stmt->get_result();
                        } else {
                            $sql = "SELECT * FROM members";
                            $result = $conn->query($sql);
                        }

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['Member_ID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Name'] . " " . $row['Surname']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Membership_type']) . "</td>";
                                echo "<td>";
                                echo "<a href='?view=" . htmlspecialchars($row['Member_ID']) . "' class='btn btn-info btn-sm'><i class='fas fa-eye'></i> View</a> ";
                                echo "<a href='?edit=" . htmlspecialchars($row['Member_ID']) . "' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i> Edit</a> ";
                                echo "<a href='process_actions.php?action=delete&Member_ID=" . htmlspecialchars($row['Member_ID']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this member?\");'><i class='fas fa-trash'></i> Delete</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No members found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- View/Edit Member Modal -->
        <?php
        if (isset($_GET['view']) || isset($_GET['edit'])) {
            $action = isset($_GET['view']) ? 'view' : 'edit';
            $Member_ID = isset($_GET['view']) ? $_GET['view'] : $_GET['edit'];
            $sql = "SELECT * FROM members WHERE Member_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $Member_ID);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $member = $result->fetch_assoc();
        ?>
        <div class="modal fade show" id="memberModal" tabindex="-1" style="display:block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $action == 'view' ? 'View' : 'Edit'; ?> Member</h5>
                        <a href="manage_members.php" class="btn-close    
    
    </div>
</div>