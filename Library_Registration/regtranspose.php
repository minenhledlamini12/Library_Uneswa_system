<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__FILE__) . '/phpqrcode-2010100721_1.1.4/phpqrcode/qrinput.php';
require_once("connection.php");

//Load Composer's autoloader
require __DIR__ . '/vendor/autoload.php';

// Initialize variables for error messages
$id_error = $email_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $ID = trim($_POST['ID']);  // Trim whitespace
    $Name = trim($_POST['Name']);
    $Surname = trim($_POST['Surname']);
    $CourseDepartmentAffliation = trim($_POST['Course/Department/Affliation']);
    $Membership_type = $_POST['Membership_Type'];
    $Contact = trim($_POST['Contact']);
    $Email = trim($_POST['Email']);
    $Password = $_POST['Password']; // Don't hash yet, need to validate
    $Joined_Date = $_POST['Joined_Date'];

    // Validate Student ID (Uniqueness)
    $sql_check_id = "SELECT Counter FROM members WHERE ID = ?";
    $stmt_check_id = $conn->prepare($sql_check_id);
    $stmt_check_id->bind_param("i", $ID);
    $stmt_check_id->execute();
    $stmt_check_id->store_result();

    if ($stmt_check_id->num_rows > 0) {
        $id_error = "This Student ID is already registered.";
    }
    $stmt_check_id->close();

    // Validate Email (Uniqueness and Format)
    if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format.";
    } else {
        $sql_check_email = "SELECT Counter FROM members WHERE Email = ?";
        $stmt_check_email = $conn->prepare($sql_check_email);
        $stmt_check_email->bind_param("s", $Email);
        $stmt_check_email->execute();
        $stmt_check_email->store_result();

        if ($stmt_check_email->num_rows > 0) {
            $email_error = "This email is already registered.";
        }
        $stmt_check_email->close();
    }

    // If no validation errors, proceed with registration
    if (empty($id_error) && empty($email_error)) {

        // Hash the password *only* if validation passes
        $Password_Hashed = password_hash($Password, PASSWORD_DEFAULT);

        // SQL to insert data into the members table (PREPARED STATEMENT)
        

	$sql = "INSERT INTO members (ID, Name, Surname, `Course/Department/Affliation`, Membership_type, Contact, Email, Password, Joined_Date)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
 		$sql = "INSERT INTO members (ID, Name, Surname, `Course/Department/Affliation`, Membership_type, Contact, Email, Password, Joined_Date)
	VALUES ('$ID','$Name','$Surname','$CourseDepartmentAffliation','$Membership_type','$Contact','$Email','$Password_Hashed','$Joined_Date')";
				
		$stmt = $conn->query($sql);
        

        if ($stmt===TRUE) {
            // Get the last inserted ID
            $ID = $conn->insert_id;

            // Generate QR code
            $qr_data = $ID; // Use member ID as QR code data
            $qr_file = 'qrcodes/' . $ID . '.png'; // Path to save QR code
            QRcode::png($qr_data, $qr_file, 'L', 4, 2); // Generate QR code

            // Store QR code path in the database
            $update_sql = "UPDATE members SET Qr_code = ? WHERE Counter = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $qr_file, $member_id); // Bind parameters for update

            if ($update_stmt->execute()) {

                // Email sending logic (PHPMailer) -  REPLACE WITH YOUR ACTUAL EMAIL SETTINGS
                $mail = new PHPMailer(true); // Enable exceptions
                try {
                    //Server settings
                    $mail->SMTPDebug = SMTP::DEBUG_OFF;  // Disable debugging.  Change to DEBUG_SERVER for detailed output
                    $mail->isSMTP();                                      // Set mailer to use SMTP
                    $mail->Host       = 'your_smtp_host';  // SMTP server, e.g., smtp.gmail.com
                    $mail->SMTPAuth   = true;                               // Enable SMTP authentication
                    $mail->Username   = 'your_email@example.com';   // SMTP username
                    $mail->Password   = 'your_email_password';       // SMTP password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also possible
                    $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

                    //Recipients
                    $mail->setFrom('your_email@example.com', 'UNESWA Library');
                    $mail->addAddress($Email, $Name);     // Add a recipient

                    // Attachments
                    $mail->addAttachment($qr_file, 'UNESWA_Library_QR_Code.png');    // Optional name

                    // Content
                    $mail->isHTML(true);                                  // Set email format to HTML
                    $mail->Subject = 'UNESWA Library Registration Successful!';
                    $mail->Body    = 'Dear ' . htmlspecialchars($Name) . ',<br><br>Thank you for registering with the UNESWA Library!<br><br>Your QR code is attached to this email. Please download and save it for easy access to the library.<br><br>Sincerely,<br>The UNESWA Library Team';
                    $mail->AltBody = 'Dear ' . htmlspecialchars($Name) . ', Thank you for registering with the UNESWA Library! Your QR code is attached to this email. Please download and save it. Sincerely, The UNESWA Library Team';

                    $mail->send();


                    // Start of Success Page HTML (Moved inside the try block)
                    echo '<!DOCTYPE html>';
                    echo '<html lang="en">';
                    echo '<head>';
                    echo '<meta charset="UTF-8">';
                    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
                    echo '<title>Registration Successful</title>';
                    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />';
                    echo '<style>';
                    echo 'body {
                            font-family: Arial, sans-serif;
                            background-color: #ADD8E6; /* Light blue background */
                            color: #333;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            min-height: 100vh;
                            margin: 0;
                        }';
                    echo '.success-container {
                            background-color: rgba(255, 255, 255, 0.9); /* White container with slight transparency */
                            padding: 40px;
                            border-radius: 15px;
                            text-align: center;
                            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                            width: 80%; /* Adjust width as needed */
                            max-width: 700px;
                        }';
                    echo 'h1 {
                            color: #004A99; /* Dark blue heading */
                            margin-bottom: 20px;
                        }';
                    echo 'p {
                            font-size: 18px;
                            margin-bottom: 30px;
                        }';
                    echo '.qr-code-container {
                            margin-top: 20px;
                        }';
                    echo '.qr-code-container img {
                            max-width: 200px; /* Adjust size as needed */
                            border: 1px solid #ccc;
                            border-radius: 5px;
                        }';

                    echo '.back-button {
                            background-color: #007BFF;
                            color: white;
                            padding: 14px 24px;
                            border: none;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 16px;
                            transition: background-color 0.3s ease;
                            text-decoration: none; /* Remove underline from link */
                            display: inline-block; /* Make it behave like a block-level element */
                            margin-top: 20px;

                        }';
                    echo '.back-button:hover {
                            background-color: #0056b3;
                        }';
                    echo '</style>';
                    echo '</head>';
                    echo '<body>';
                    echo '<div class="success-container">';
                    echo '<h1><i class="fas fa-check-circle" style="color: green;"></i> Registration Successful!</h1>';
                    echo '<p><i class="fas fa-user"></i> Welcome to the UNESWA Library!</p>';
                    echo '<p><i class="fas fa-envelope"></i> A confirmation email with your QR code has been sent to ' . htmlspecialchars($Email) . '.</p>'; //Escape the email
                    echo '<p><i class="fas fa-id-card"></i> Your Student ID is: ' . htmlspecialchars($ID) . '</p>'; // Escape the Student ID
                    echo '<div class="qr-code-container">';
                    echo '<p><i class="fas fa-qrcode"></i> Your QR Code:</p>';
                    echo '<img src="' . $qr_file . '" alt="QR Code">';
                    echo '</div>';
                    echo '<a href="index.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to Home</a>'; // Link back to your homepage

                    echo '</div>';
                    echo '</body>';
                    echo '</html>';
                    // End of Success Page HTML


                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }


            } else {
                echo "Error updating QR code path: " . $conn->error;
            }
            $update_stmt->close();

        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        // Display validation errors *inside* the form (or wherever you want)
        echo "<div style='color: red;'>";
        if (!empty($id_error)) echo "<p>" . $id_error . "</p>";
        if (!empty($email_error)) echo "<p>" . $email_error . "</p>";
        echo "</div>";
    }
}

$conn->close();
?>
