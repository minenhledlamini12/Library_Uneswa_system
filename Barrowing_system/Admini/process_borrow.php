<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("connection.php");

use League\OAuth2\Client\Provider\Google;
use GuzzleHttp\Client;

require 'vendor/autoload.php';

// Function to send email using OAuth
function sendEmail($to, $subject, $body) {
   // OAuth and Gmail API settings
    $clientID = '648019949320-4r60vohhrj0i7u8703bpg8o22hg378j2.apps.googleusercontent.com';
    $clientSecret = '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';
    $redirectUri = 'http://localhost/php_program/Barrowing_system/process_borrow.php';  // Important!  The OAuth flow needs to work.  Make sure your authorized redirect URLs include this.

    $provider = new Google([
        'clientId' => $clientID,
        'clientSecret' => $clientSecret,
        'redirectUri' => $redirectUri,
    ]);

    // If we don't have an authorization code yet, start the authorization flow
     if (!isset($_GET['code'])) {

        // If we don't have an authorization code yet, start the authorization flow
        $authorizationUrl = $provider->getAuthorizationUrl([
            'scope' => ['https://mail.google.com/'],
            'access_type' => 'offline',   // Request a refresh token
            'prompt' => 'consent',         // Force consent every time
        ]);

        header('Location: ' . $authorizationUrl);
        exit;

    } else {

        // Try to get an access token (using the authorization code grant)
        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

           // Get the refresh token
           $refreshToken = $accessToken->getRefreshToken();

            // Use API to send an email
            $client = new Client();
            $url = 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send';
            $message = base64_encode(
                "From: testingsmtp910@gmail.com\r\n" .
                "To: " . $to . "\r\n" .
                "Subject: " . $subject . "\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-Type: text/html; charset=utf-8\r\n" .
                "Content-Transfer-Encoding: base64\r\n\r\n" .
                base64_encode($body)
            );
            $params = [
                'json' => [
                    'raw' => str_replace(['+', '/', '='], ['-', '_', ''], $message),
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken->getToken(),
                    'Content-Type' => 'application/json',
                ],
            ];
            $response = $client->post($url, $params);

            if ($response->getStatusCode() == 200) {
                return true;  // Indicate success
            } else {
                return "Email send failed with status code" . $response->getStatusCode();  // Indicate success
            }

        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            return 'Failed to get access token: ' . $e->getMessage();
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect post data
    $bookTitle = isset($_POST['bookTitle']) ? trim($_POST['bookTitle']) : '';
    $bookAuthor = isset($_POST['bookAuthor']) ? trim($_POST['bookAuthor']) : '';
    $bookISBN = isset($_POST['bookISBN']) ? trim($_POST['bookISBN']) : '';
   //$borrowerName = isset($_POST['borrowerName']) ? trim($_POST['borrowerName']) : '';
    $borrowerEmail = isset($_POST['borrowerEmail']) ? trim($_POST['borrowerEmail']) : '';
    $borrowDate = isset($_POST['borrowDate']) ? trim($_POST['borrowDate']) : '';
    $returnDate = isset($_POST['returnDate']) ? trim($_POST['returnDate']) : '';
    $memberType = isset($_POST['memberType']) ? trim($_POST['memberType']) : ''; // Get member type
    $termsAgreed = isset($_POST['termsAgreed']) ? true : false;

     // Fetch member details based on the member type from the members table
        $sql = "SELECT Member_ID, Name, Surname, Email, Membership_type FROM members WHERE Membership_type = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $memberType);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify that the selected member type matches the one in the members table
             $borrowerName  = $row['Name'] . " " . $row['Surname'];


    // Validate inputs
    if (empty($bookTitle) || empty($bookAuthor) || empty($bookISBN) || empty($borrowerName) || empty($borrowerEmail) || empty($borrowDate) || empty($returnDate) || empty($memberType) || !$termsAgreed) {
        echo "All fields are required, and you must agree to the terms and conditions!";
        exit;
    }

        // Update book status to 'Borrowed'
         $updateSql = "UPDATE books SET Status = 'Borrowed' WHERE ISBN = ?";
         $updateStmt = $conn->prepare($updateSql);
         $updateStmt->bind_param("s", $bookISBN);

          if ($updateStmt->execute()) {
           echo "Book borrowed successfully!";
           $borrowID = $conn->insert_id;  // Get ID of the new borrow record

            // Send confirmation email
              $subject = 'Book Borrow Confirmation';
              $body = "Dear $borrowerName,<br><br>You have successfully borrowed the book '$bookTitle' from our library.  Please return on or before $returnDate.<br><br>Thank you!";

             $emailStatus = sendEmail($borrowerEmail, $subject, $body);

            // If sendEmailNotification fails, it returns an error message.  We'll handle it,
            // and then continue with the rest of the borrowing process.
            if ($emailStatus !== true) {
                 echo "Book borrowed successfully, but email notification failed: " . $emailStatus;
              }else{
                 echo "Book borrowed successfully, Email send succesfully!";
                 }

               $updateStmt->close();
        }else {
               echo "Error: " . $updateStmt->error;
              }
         $stmt->close();
    $conn->close();


    // Prepare and execute SQL query to insert data into borrow table
    $sql = "INSERT INTO borrow (bookTitle, bookAuthor, bookISBN, borrowerName,borrowerEmail, borrowDate, returnDate, memberType) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $bookTitle, $bookAuthor, $bookISBN, $borrowerName, $borrowerEmail, $borrowDate, $returnDate, $memberType);

      if ($stmt->execute()) {

         // After successfully borrowing the book
                echo "Book borrowed successfully!";

            } else {
                 echo "Error: " . $stmt->error;
            }
            $stmt->close();
            $conn->close();

     // Schedule email reminders (example)
     scheduleEmailReminders($borrowerEmail, $bookTitle, $returnDate);

        // Redirect to a confirmation page or back to the search page
          header("Location: search.php");
          exit;

    }else{
          echo "Error: " . $stmt->error;
        }
} else {
    echo "Invalid request method!";
}

function scheduleEmailReminders($email, $bookTitle, $returnDate) {
        $reminderDays = [2, 1, 0]; // Send reminders 2 days, 1 day, and on the due date
        foreach ($reminderDays as $daysBefore) {
             $reminderDate = date('Y-m-d', strtotime("$returnDate -$daysBefore days"));

                // Create the command to run the email sending script in the background
                $command = "php send_reminder_email.php " .
                           escapeshellarg($email) . " " .
                           escapeshellarg($bookTitle) . " " .
                           escapeshellarg($returnDate) . " " .
                           escapeshellarg($reminderDate) .
                           " > /dev/null 2>&1 &";  // This ensures the command runs in the background

               // Execute the command
               exec($command, $output, $return_var);

             if ($return_var != 0) {
                error_log("Error scheduling email reminder: Command failed with return code $return_var");
               }
       }
}
?>
