<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['Member_ID'])) {
    header("Location: login_scanner.php");
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Book Borrowing Scanner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        #qr-video {
            width: 80%;
            max-width: 600px;
            border: 2px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        #loading {
            display: none;
            margin: 20px auto;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            color: #333;
            font-weight: bold;
        }
        
        .error {
            color: #dc3545;
            font-weight: bold;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .success {
            color: #28a745;
            font-weight: bold;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 5px;
            margin: 10px 0;
        }

        #result {
            margin: 20px auto;
            max-width: 600px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }

        button:hover {
            background-color: #0056b3;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin: 10px 0;
        }
        
        /* Spinner animation */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: #007bff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Status message */
        #status-message {
            margin-top: 10px;
            font-style: italic;
            color: #666;
        }
        
        /* Scan again button */
        #scan-again {
            display: none;
            margin: 20px auto;
        }
        
        /* Debug info */
        #debug-info {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: left;
            font-family: monospace;
            font-size: 12px;
            display: none;
        }
    </style>
</head>

<body>
    <h1>Book Borrowing Scanner</h1>
    <video id="qr-video"></video>
    <div id="status-message">Waiting for camera access...</div>
    <div id="result"></div>
    <div id="loading"><div class="spinner"></div></div>
    <button id="scan-again" onclick="location.reload()">Scan Another Book</button>
    <div id="debug-info"></div>

    <script>
        const video = document.getElementById("qr-video");
        const resultElement = document.getElementById("result");
        const loadingElement = document.getElementById("loading");
        const statusMessage = document.getElementById("status-message");
        const scanAgainButton = document.getElementById("scan-again");
        const debugInfo = document.getElementById("debug-info");
        
        function logDebug(message) {
            console.log(message);
            debugInfo.style.display = "block";
            debugInfo.innerHTML += message + "</br>";
        }
        
        // Set a timeout to detect if scanning is taking too long
        let scanningTimeout = setTimeout(() => {
            statusMessage.innerHTML = "Scanning is taking longer than expected. Please ensure good lighting and a clear QR code.";
        }, 10000); // 10 seconds
        
        function startScan() {
            statusMessage.innerHTML = "Accessing camera...";
            
            navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: "environment"
                    }
                })
                .then(function(stream) {
                    video.srcObject = stream;
                    video.setAttribute("playsinline", true);
                    video.play();
                    statusMessage.innerHTML = "Camera active. Scanning for QR code...";
                    requestAnimationFrame(scan);
                })
                .catch(function(err) {
                    console.error("An error occurred: " + err);
                    statusMessage.innerHTML = "Camera access failed. Please check permissions.";
                    resultElement.innerHTML = `<p class="error">Error accessing camera: ${err}</p>`;
                    scanAgainButton.style.display = "block";
                });
        }

        function scan() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                statusMessage.innerHTML = "Analyzing video feed...";
                
                const canvasElement = document.createElement('canvas');
                canvasElement.width = video.videoWidth;
                canvasElement.height = video.videoHeight;
                const canvasContext = canvasElement.getContext('2d');
                canvasContext.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                const imageData = canvasContext.getImageData(0, 0, canvasElement.width, canvasElement.height);

                const code = jsQR(imageData.data, imageData.width, canvasElement.height, {
                    inversionAttempts: "dontInvert"
                });

                if (code) {
                    console.log("Found QR code", code.data);
                    clearTimeout(scanningTimeout); // Clear the timeout
                    statusMessage.innerHTML = "QR code detected! Verifying...";
                    verifyBookQrCode(code.data);
                    video.pause();
                    video.srcObject.getTracks().forEach(track => track.stop());
                    return;
                }
            }
            requestAnimationFrame(scan);
        }

        function verifyBookQrCode(qrCodeData) {
            loadingElement.style.display = "block";
            resultElement.innerHTML = "";
            
            // Set a timeout for the verification request
            const verifyTimeout = setTimeout(() => {
                loadingElement.style.display = "none";
                statusMessage.innerHTML = "Verification request timed out. Please try again.";
                resultElement.innerHTML = '<p class="error">Request timed out. Server might be busy.</p>';
                scanAgainButton.style.display = "block";
            }, 30000); // 30 seconds timeout
            
            logDebug("Sending verification request for QR code: " + qrCodeData);
            
            fetch('verify_book_qr_code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'qr_code=' + encodeURIComponent(qrCodeData)
                })
                .then(response => {
                    clearTimeout(verifyTimeout); // Clear the timeout
                    if (!response.ok) {
                        throw new Error('Server returned ' + response.status + ': ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    loadingElement.style.display = "none";
                    statusMessage.innerHTML = "Verification complete.";
                    logDebug("Verification response: " + JSON.stringify(data));
                    
                    if (data.status === 'success') {
                        // Display book details and prompt for member ID and membership type
                        resultElement.innerHTML = `
                    <h3>Book Details</h3>
                    <p><strong>Title:</strong> ${data.book.Title}</p>
                    <p><strong>Author:</strong> ${data.book.Author}</p>
                    <p><strong>ISBN:</strong> ${data.book.ISBN}</p>

                    <form id="borrowForm">
                        <input type="hidden" name="isbn" value="${data.book.ISBN}">
                        <input type="hidden" name="book_id" value="${data.book.book_id}">
                        
                        <p><strong>Member ID:</strong> <span><?php echo isset($_SESSION['user']['Member_ID']) ? $_SESSION['user']['Member_ID'] : ''; ?></span></p>
                        <p><strong>Membership Type:</strong> <span><?php echo isset($_SESSION['user']['Membership_type']) ? $_SESSION['user']['Membership_type'] : ''; ?></span> </p>

                        <input type="checkbox" id="terms_agree" name="terms_agree" required>
                        <label for="terms_agree">I agree to the <a href="terms_conditions.php" target="_blank">library terms and conditions</a></label><br><br>
                        <button type="button" id="confirmBorrowBtn">Borrow Book</button>
                    </form>`;

                        // Handle form submission
                        document.getElementById('confirmBorrowBtn').addEventListener('click', function() {
                            if (!document.getElementById('terms_agree').checked) {
                                alert("You must agree to the terms and conditions to borrow a book.");
                                return;
                            }
                            performBorrowAction(data.book.ISBN, data.book.book_id);
                        });
                    } else {
                        // Display error message
                        resultElement.innerHTML = `<p class="error">${data.message}</p>`;
                        scanAgainButton.style.display = "block";
                    }
                })
                .catch(error => {
                    clearTimeout(verifyTimeout); // Clear the timeout
                    loadingElement.style.display = "none";
                    console.error('Error:', error);
                    statusMessage.innerHTML = "Error during verification.";
                    resultElement.innerHTML = `<p class="error">Error verifying book QR code: ${error.message}</p>`;
                    scanAgainButton.style.display = "block";
                    logDebug("Verification error: " + error.message);
                });
        }

        function performBorrowAction(isbn, bookId) {
            const memberId = "<?php echo isset($_SESSION['user']['Member_ID']) ? $_SESSION['user']['Member_ID'] : ''; ?>";
            const membershipType = "<?php echo isset($_SESSION['user']['Membership_type']) ? $_SESSION['user']['Membership_type'] : ''; ?>";
            
            if (!memberId || !membershipType) {
                resultElement.innerHTML = '<p class="error">Member information not available. Please log in again.</p>';
                scanAgainButton.style.display = "block";
                return;
            }

            // Check if terms checkbox exists and is checked
            const termsCheckbox = document.getElementById('terms_agree');
            if (!termsCheckbox || !termsCheckbox.checked) {
                resultElement.innerHTML = '<p class="error">You must agree to the terms and conditions to borrow a book.</p>';
                return;
            }

            // Show loading indicator
            loadingElement.style.display = "block";
            statusMessage.innerHTML = "Processing borrowing request...";
            resultElement.innerHTML = '';
            
            // Set a timeout for the borrowing request
            const borrowTimeout = setTimeout(() => {
                loadingElement.style.display = "none";
                statusMessage.innerHTML = "Borrowing request timed out. Please try again.";
                resultElement.innerHTML = '<p class="error">Request timed out. Server might be busy.</p>';
                scanAgainButton.style.display = "block";
            }, 60000);
            
            const formData = new FormData();
            formData.append('isbn', isbn);
            formData.append('book_id', bookId);
            formData.append('member_ID', memberId);
            formData.append('membershipType', membershipType);
            formData.append('terms_agree', termsCheckbox.checked ? '1' : '0');
            
            logDebug("Sending borrow request with data: ISBN=" + isbn + ", book_id=" + bookId + ", member_ID=" + memberId);

            fetch('scan_handle.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    clearTimeout(borrowTimeout);
                    if (!response.ok) {
                        throw new Error('Server returned ' + response.status + ': ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    clearTimeout(borrowTimeout);
                    loadingElement.style.display = "none";
                    statusMessage.innerHTML = "Borrowing process complete.";
                    console.log('Response from scan_handle.php:', data);
                    logDebug("Borrow response: " + JSON.stringify(data));
                    
                    if (data.success) {
                        resultElement.innerHTML = `<p class="success">${data.message}</p>
                                                <p>Borrowing successful! You will be redirected to the success page...</p>`;
                        setTimeout(() => {
                            window.location.href = 'borrow_success.php';
                        }, 1500);
                    } else {
                        resultElement.innerHTML = `<p class="error">${data.message}</p>`;
                        scanAgainButton.style.display = "block";
                    }
                })
                .catch(error => {
                    clearTimeout(borrowTimeout);
                    loadingElement.style.display = "none";
                    console.error('Fetch error:', error);
                    statusMessage.innerHTML = "Error during borrowing process.";
                    resultElement.innerHTML = `<p class="error">Error borrowing book: ${error.message}</p>`;
                    scanAgainButton.style.display = "block";
                    logDebug("Borrow error: " + error.message);
                });
        }

        // Start scanning when page loads
        window.onload = function() {
            startScan();
        };
    </script>
</body>

</html>