<?php
header('Content-Type: application/json');

// --- PHPMailer Autoloader ---
// This assumes your script is in 'Barrowing_system/Admini/'
// and the 'vendor' folder is directly in 'Barrowing_system/'.
// __DIR__ is the directory of the current file. '../' goes up one level.
require __DIR__ . '/../vendor/autoload.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Database Connection ---
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// --- Get JSON Input ---
$input = json_decode(file_get_contents('php://input'), true);
$member_id = $input['member_id'] ?? null;

if (!$member_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid member ID provided.']);
    exit();
}

try {
    // --- Get Member Details and Overdue Books ---
    $stmt = $conn->prepare("
        SELECT
            bl.BlacklistID, bl.ID, bl.BookID, bl.BorrowDate, bl.DueDate, bl.Charge, bl.BlacklistedDate,
            m.Name, m.Surname, m.Member_ID, m.Membership_type, m.Email,
            b.Title, b.Author, b.ISBN
        FROM blacklist bl
        JOIN members m ON bl.ID = m.ID
        JOIN books b ON bl.BookID = b.ID
        WHERE bl.ID = ?
        ORDER BY bl.DueDate ASC
    ");
    
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'No overdue books found for this member ID: ' . $member_id]);
        exit();
    }
    
    $overdue_books = [];
    $total_charges = 0;
    $member_info = null;
    
    while ($row = $result->fetch_assoc()) {
        if (!$member_info) {
            $member_info = $row; // Capture member info from the first row
        }
        $overdue_books[] = $row;
        $total_charges += $row['Charge'];
    }
    
    $member_email = $member_info['Email'];
    if (empty($member_email)) { // Use empty() for robust check
        echo json_encode(['success' => false, 'message' => 'No email address found for member ID: ' . $member_id]);
        exit();
    }
    
    // --- Calculate Overdue Days ---
    // Use 'now' for the current date unless you specifically need a fixed date.
    // Given the current time in Kwaluseni is May 25, 2025, 1:21:50 PM SAST,
    // using 'now' will reflect the actual current time.
    $current_date_time = new DateTime('now', new DateTimeZone('Africa/Johannesburg')); // Kwaluseni is in SAST/CAT
    
    // The most overdue book is the first one due to ORDER BY bl.DueDate ASC
    $most_overdue_date = new DateTime($overdue_books[0]['DueDate']);
    $overdue_days_for_template = $current_date_time->diff($most_overdue_date)->days;
    
    // --- Create Email Content ---
    $email_subject = "URGENT: Overdue Library Books - Action Required";
    $email_body = generateEmailTemplate($member_info, $overdue_books, $total_charges, $overdue_days_for_template, $current_date_time);
    
    // --- PHPMailer Configuration and Sending ---
    $mail = new PHPMailer(true); // Enable exceptions for detailed errors
    
    // Server settings
    $mail->SMTPDebug = 0; // Set to 2 for detailed debugging during development, 0 for production
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'dminenhle477@gmail.com'; // Your Gmail address
    $mail->Password   = 'hbzl wbju nedt lfdc';   // Your Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SMTPS for port 465
    $mail->Port       = 465;

    // Optional: Only use this if you encounter SSL certificate issues
    // and understand the security implications. It's generally better to
    // ensure your PHP environment has up-to-date CA certificates.
    // $mail->SMTPOptions = array(
    //     'ssl' => array(
    //         'verify_peer'       => false,
    //         'verify_peer_name'  => false,
    //         'allow_self_signed' => true
    //     )
    // );
    
    $mail->Timeout = 60; // Set a generous timeout
    $mail->SMTPKeepAlive = true; // Keep the connection alive for multiple sends if needed (not crucial for single send)
    
    // Recipients
    $mail->setFrom('dminenhle477@gmail.com', 'UNESWA Library System'); // Your sending address and name
    $mail->addAddress($member_email, $member_info['Name'] . ' ' . $member_info['Surname']); // Recipient
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = $email_subject;
    $mail->Body    = $email_body;
    $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $email_body)); // Plain-text version
    
    // Send the email
    $mail->send();
    
    // --- Log the email sending ---
    $log_stmt = $conn->prepare("INSERT INTO email_logs (member_id, email, subject, sent_date) VALUES (?, ?, ?, NOW())");
    $log_stmt->bind_param("iss", $member_info['ID'], $member_email, $email_subject);
    $log_stmt->execute();
    $log_stmt->close();
    
    echo json_encode(['success' => true, 'message' => 'Overdue notice sent successfully']);
    
} catch (Exception $e) {
    // --- Error Handling and Fallback ---
    // Log the detailed error
    error_log("Email sending error via PHPMailer for member ID {$member_id}: " . $e->getMessage() . " | PHPMailer Error Info: " . $mail->ErrorInfo);
    
    // Attempt fallback to PHP mail() function (if configured on server)
    try {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: UNESWA Library System <library@uniswa.sz>' . "\r\n"; // Ensure this 'From' is legitimate or a server-allowed address
        
        $success = mail($member_email, $email_subject, $email_body, $headers);
        
        if ($success) {
            // Log the email sending (via fallback)
            $log_stmt = $conn->prepare("INSERT INTO email_logs (member_id, email, subject, sent_date) VALUES (?, ?, ?, NOW())");
            $log_stmt->bind_param("iss", $member_info['ID'], $member_email, $email_subject);
            $log_stmt->execute();
            $log_stmt->close();
            
            echo json_encode(['success' => true, 'message' => 'Overdue notice sent successfully (via fallback method)']);
        } else {
            error_log("Fallback mail() function also failed for member ID {$member_id}.");
            echo json_encode(['success' => false, 'message' => 'Both SMTP and mail() function failed. Details: ' . $e->getMessage()]);
        }
    } catch (Exception $fallback_error) {
        // If fallback also throws an exception (unlikely for mail() but good practice)
        error_log("Fallback mail() function encountered an exception for member ID {$member_id}: " . $fallback_error->getMessage());
        echo json_encode(['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage() . ' (Fallback also failed with: ' . $fallback_error->getMessage() . ')']);
    }
} finally {
    // --- Close Connections ---
    if (isset($stmt)) $stmt->close();
    $conn->close();
}

/**
 * Generates the HTML content for the overdue notice email.
 *
 * @param array $member_info        Array of member details.
 * @param array $overdue_books      Array of overdue book details.
 * @param float $total_charges      Total outstanding fine amount.
 * @param int   $overdue_days_for_template The most overdue days for the main heading.
 * @param DateTime $current_date_time The current DateTime object for accurate calculations.
 * @return string HTML email body.
 */
function generateEmailTemplate($member_info, $overdue_books, $total_charges, $overdue_days_for_template, $current_date_time) {
    $member_name = htmlspecialchars($member_info['Name'] . ' ' . $member_info['Surname']);
    $member_id = htmlspecialchars($member_info['Member_ID']);
    $member_type = htmlspecialchars($member_info['Membership_type']);
    
    $books_html = '';
    foreach ($overdue_books as $book) {
        $due_date = new DateTime($book['DueDate']);
        $days_overdue = $current_date_time->diff($due_date)->days; // Use the passed DateTime object
        
        $books_html .= "
        <tr style='border-bottom: 1px solid #dee2e6;'>
            <td style='padding: 12px; border-right: 1px solid #dee2e6;'>
                <strong>" . htmlspecialchars($book['Title']) . "</strong><br>
                <small style='color: #6c757d;'>by " . htmlspecialchars($book['Author']) . "</small><br>
                <small style='color: #6c757d;'>ISBN: " . htmlspecialchars($book['ISBN']) . "</small>
            </td>
            <td style='padding: 12px; text-align: center; border-right: 1px solid #dee2e6;'>" . date('M d, Y', strtotime($book['BorrowDate'])) . "</td>
            <td style='padding: 12px; text-align: center; border-right: 1px solid #dee2e6; color: #dc3545; font-weight: bold;'>" . date('M d, Y', strtotime($book['DueDate'])) . "</td>
            <td style='padding: 12px; text-align: center; border-right: 1px solid #dee2e6; color: #dc3545; font-weight: bold;'>{$days_overdue} days</td>
            <td style='padding: 12px; text-align: center; color: #dc3545; font-weight: bold;'>E" . number_format($book['Charge'], 2) . "</td>
        </tr>";
    }
    
    return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Overdue Library Books Notice</title>
</head>
<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa;'>
    <div style='max-width: 800px; margin: 0 auto; background-color: white; box-shadow: 0 0 20px rgba(0,0,0,0.1);'>
        <div style='background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 30px; text-align: center;'>
            <h1 style='margin: 0; font-size: 28px; font-weight: bold;'>OVERDUE LIBRARY BOOKS NOTICE</h1>
            <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>University of Eswatini Library - Kwaluseni Campus</p>
        </div>
        
        <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 0; text-align: center;'>
            <strong>URGENT ACTION REQUIRED - LIBRARY REGULATION VIOLATION</strong>
        </div>
        
        <div style='padding: 30px;'>
            <div style='background-color: #f8f9fa; border-left: 4px solid #dc3545; padding: 20px; margin-bottom: 30px;'>
                <h2 style='color: #dc3545; margin: 0 0 15px 0; font-size: 20px;'>Member Information</h2>
                <p style='margin: 5px 0;'><strong>Name:</strong> {$member_name}</p>
                <p style='margin: 5px 0;'><strong>Member ID:</strong> {$member_id}</p>
                <p style='margin: 5px 0;'><strong>Member Type:</strong> {$member_type}</p>
                <p style='margin: 5px 0;'><strong>Email:</strong> " . htmlspecialchars($member_info['Email']) . "</p>
            </div>
            
            <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin-bottom: 30px;'>
                <h3 style='color: #856404; margin: 0 0 10px 0;'>Library Regulation Violation</h3>
                <p style='margin: 0; color: #856404;'>
                    This notice serves as an official notification that you are in violation of the University of Eswatini Library regulations. 
                    You have failed to return borrowed materials by their due dates, resulting in your account being flagged and restricted.
                </p>
            </div>
            
            <h3 style='color: #dc3545; margin-bottom: 20px; font-size: 18px;'>Overdue Books Details</h3>
            <div style='overflow-x: auto; margin-bottom: 30px;'>
                <table style='width: 100%; border-collapse: collapse; border: 1px solid #dee2e6; background-color: white;'>
                    <thead>
                        <tr style='background-color: #dc3545; color: white;'>
                            <th style='padding: 15px; text-align: left; border-right: 1px solid #c82333;'>Book Details</th>
                            <th style='padding: 15px; text-align: center; border-right: 1px solid #c82333;'>Borrowed Date</th>
                            <th style='padding: 15px; text-align: center; border-right: 1px solid #c82333;'>Due Date</th>
                            <th style='padding: 15px; text-align: center; border-right: 1px solid #c82333;'>Days Overdue</th>
                            <th style='padding: 15px; text-align: center;'>Fine Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$books_html}
                    </tbody>
                </table>
            </div>
            
            <div style='background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 30px;'>
                <h3 style='margin: 0 0 10px 0; font-size: 20px;'>Total Outstanding Charges</h3>
                <p style='margin: 0; font-size: 32px; font-weight: bold;'>E" . number_format($total_charges, 2) . "</p>
                <p style='margin: 10px 0 0 0; opacity: 0.9;'>Must be paid before account restoration</p>
            </div>
            
            <div style='background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px; padding: 20px; margin-bottom: 30px;'>
                <h3 style='color: #0c5460; margin: 0 0 15px 0;'>Immediate Action Required</h3>
                <ol style='color: #0c5460; margin: 0; padding-left: 20px;'>
                    <li style='margin-bottom: 10px;'><strong>Return all overdue books immediately</strong> to the University of Eswatini Library</li>
                    <li style='margin-bottom: 10px;'><strong>Pay the outstanding fine</strong> of E" . number_format($total_charges, 2) . " at bank to the Uneswa account 577 300 189 02</li>
                    <li style='margin-bottom: 10px;'><strong>Contact the library</strong> at 2517 0448 to discuss payment arrangements if needed</li>
                    <li><strong>Provide proof of return and payment</strong> to restore your library privileges</li>
                </ol>
            </div>
            
            <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin-bottom: 30px;'>
                <h3 style='color: #721c24; margin: 0 0 15px 0;'>Consequences of Non-Compliance</h3>
                <ul style='color: #721c24; margin: 0; padding-left: 20px;'>
                    <li style='margin-bottom: 8px;'>Continued restriction of library borrowing privileges</li>
                    <li style='margin-bottom: 8px;'>Escalation to academic administration</li>
                    <li style='margin-bottom: 8px;'>Potential impact on academic records and graduation</li>
                    <li style='margin-bottom: 8px;'>Additional late fees and penalties</li>
                    <li>Legal action for recovery of library materials and fees</li>
                </ul>
            </div>
            
            <div style='background-color: #e2e3e5; border-radius: 8px; padding: 20px; text-align: center;'>
                <h3 style='color: #383d41; margin: 0 0 15px 0;'>Contact Information</h3>
                <p style='margin: 5px 0; color: #383d41;'><strong>University of Eswatini Library</strong></p>
                <p style='margin: 5px 0; color: #383d41;'>Kwaluseni Campus</p>
                <p style='margin: 5px 0; color: #383d41;'><strong>Phone:</strong> 2517 0448</p>
                <p style='margin: 5px 0; color: #383d41;'><strong>Email:</strong> library@uniswa.sz</p>
                <p style='margin: 15px 0 5px 0; color: #383d41;'><strong>Operating Hours:</strong></p>
                <p style='margin: 0; color: #383d41; font-size: 14px;'>Mon-Fri: 08:30 AM - 11:00 PM | Sat: 10:00 AM - 05:00 PM | Sun: 03:00 PM - 10:00 PM</p>
            </div>
        </div>
        
        <div style='background-color: #343a40; color: white; padding: 20px; text-align: center;'>
            <p style='margin: 0; font-size: 14px;'>This is an automated message from the University of Eswatini Library System.</p>
            <p style='margin: 5px 0 0 0; font-size: 12px; opacity: 0.8;'>Please do not reply to this email. For assistance, contact the library directly.</p>
        </div>
    </div>
</body>
</html>";
}
?>
