<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("connection.php");

header('Content-Type: application/json');

$response = [];

if (isset($_POST['memberType'])) {
    $memberType = $_POST['memberType'];

    try {
        // Explicitly specify the "members" table in the query, also replace `Membership_type` with the correct column name
        $sql = "SELECT Member_ID, Name, Surname, Email, Membership_type FROM members WHERE Membership_type = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $memberType);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verify that the selected member type matches the one in the members table, also replace `Membership_type` with the correct column name
            if ($row['Membership_type'] == $memberType) {
                $response['status'] = 'success';
                $response['data'] = [
                    'Member_ID' => htmlspecialchars($row['Member_ID']),
                    'Name' => htmlspecialchars($row['Name']) . " " . htmlspecialchars($row['Surname']),
                    'Email' => htmlspecialchars($row['Email']),
                    'Membership_type' => htmlspecialchars($row['Membership_type']),
                ];
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Selected membership type does not match the registered membership type. Please verify your credentials.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Member not found.';
        }

        $stmt->close();
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Member type is required.';
}

echo json_encode($response);
$conn->close();
?>
