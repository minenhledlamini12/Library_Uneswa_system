<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Borrowing Scanner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <style>
        /* Unchanged styles */
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
            margin: 10px;
        }

        button:hover {
            background-color: #0056b3;
        }

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

        #status-message {
            margin-top: 10px;
            font-style: italic;
            color: #666;
        }

        #scan-again {
            display: none;
        }

        #debug-info {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: left;
            font-family: monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
            display: none; /* Hide debug info by default */
        }
    </style>
</head>
<body>
    <h1>Book Borrowing Scanner</h1>
    <video id="qr-video" playsinline></video>
    <div id="status-message">Waiting for camera access...</div>
    <div id="loading"><div class="spinner"></div>Processing...</div>
    <div id="result"></div>
    <button id="scan-again" onclick="resetScanner()">Scan Another Book</button>
    <div id="debug-info"></div>

    <script>
        const video = document.getElementById('qr-video');
        const resultElement = document.getElementById('result');
        const loadingElement = document.getElementById('loading');
        const statusMessage = document.getElementById('status-message');
        const scanAgainButton = document.getElementById('scan-again');
        const debugInfo = document.getElementById('debug-info');
        let stream = null;

        // Log debug messages
        function logDebug(message) {
            console.log(message);
            debugInfo.innerHTML += `[${new Date().toISOString()}] ${message}<br>`;
            debugInfo.scrollTop = debugInfo.scrollHeight;
        }

        // Initialize camera
        async function initCamera() {
            try {
                statusMessage.textContent = 'Accessing camera...';
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                });
                video.srcObject = stream;
                video.play();
                statusMessage.textContent = 'Camera active. Scanning for QR code...';
                scan();
            } catch (err) {
                logDebug(`Camera access failed: ${err.message}`);
                statusMessage.textContent = 'Camera access failed. Please check permissions.';
                resultElement.innerHTML = `<p class="error">Error accessing camera: ${err.message}</p>`;
                scanAgainButton.style.display = 'block';
            }
        }

        // Scan QR code
        function scan() {
            if (video.readyState !== video.HAVE_ENOUGH_DATA) {
                requestAnimationFrame(scan);
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'dontInvert'
            });

            if (code) {
                logDebug(`QR code detected: ${code.data}`);
                statusMessage.textContent = 'QR code detected! Verifying...';
                stopCamera();
                verifyBookQrCode(code.data);
            } else {
                requestAnimationFrame(scan);
            }
        }

        // Stop camera
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }

        // Verify QR code
        async function verifyBookQrCode(qrCodeData) {
            loadingElement.style.display = 'block';
            resultElement.innerHTML = '';

            const timeout = setTimeout(() => {
                loadingElement.style.display = 'none';
                statusMessage.textContent = 'Verification timed out.';
                resultElement.innerHTML = '<p class="error">Request timed out. Server might be busy.</p>';
                scanAgainButton.style.display = 'block';
            }, 30000); // 30 seconds timeout for verification

            try {
                logDebug(`Sending verification request for QR code: ${qrCodeData}`);
                const response = await fetch('verify_book_qr_code.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `qr_code=${encodeURIComponent(qrCodeData)}`
                });
                clearTimeout(timeout);

                if (!response.ok) {
                    throw new Error(`Server error: ${response.status} ${response.statusText}`);
                }

                const text = await response.text();
                logDebug(`Raw response from verify_book_qr_code.php: ${text}`);

                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error(`Invalid JSON response: ${text}`);
                }

                loadingElement.style.display = 'none';
                statusMessage.textContent = 'Verification complete.';

                if (data.status === 'success') {
                    displayBookDetails(data.book);
                } else {
                    resultElement.innerHTML = `<p class="error">${data.message}</p>`;
                    scanAgainButton.style.display = 'block';
                }
            } catch (error) {
                clearTimeout(timeout);
                loadingElement.style.display = 'none';
                statusMessage.textContent = 'Error during verification.';
                resultElement.innerHTML = `<p class="error">Error verifying QR code: ${error.message}</p>`;
                scanAgainButton.style.display = 'block';
                logDebug(`Verification error: ${error.message}`);
            }
        }

        // Display book details and borrowing form
        function displayBookDetails(book) {
            // Correctly retrieve session variables directly from $_SESSION
            const memberId = '<?php echo isset($_SESSION['Member_ID']) ? addslashes($_SESSION['Member_ID']) : ''; ?>';
            const name = '<?php echo isset($_SESSION['Name']) ? addslashes($_SESSION['Name']) : ''; ?>';
            const surname = '<?php echo isset($_SESSION['Surname']) ? addslashes($_SESSION['Surname']) : ''; ?>';
            const membershipType = '<?php echo isset($_SESSION['Membership_type']) ? addslashes($_SESSION['Membership_type']) : ''; ?>';
            const email = '<?php echo isset($_SESSION['Email']) ? addslashes($_SESSION['Email']) : ''; ?>';
            const contact = '<?php echo isset($_SESSION['Contact']) ? addslashes($_SESSION['Contact']) : ''; ?>';
            const affiliation = '<?php echo isset($_SESSION['CourseDepartmentAffliation']) ? addslashes($_SESSION['CourseDepartmentAffliation']) : ''; ?>';


            // Check if essential user information is available
            if (!memberId || !name || !membershipType) {
                resultElement.innerHTML = '<p class="error">User information not available. Please log in.</p>';
                scanAgainButton.style.display = 'block';
                return;
            }

            resultElement.innerHTML = `
                <h3>Book Details</h3>
                <p><strong>Title:</strong> ${book.Title}</p>
                <p><strong>Author:</strong> ${book.Author}</p>
                <p><strong>ISBN:</strong> ${book.ISBN}</p>
                <h3>Borrower Details</h3>
                <p><strong>Name:</strong> ${name} ${surname}</p>
                <p><strong>Member ID:</strong> ${memberId}</p>
                <p><strong>Membership Type:</strong> ${membershipType}</p>
                <p><strong>Email:</strong> ${email}</p>
                <p><strong>Affiliation:</strong> ${affiliation || 'N/A'}</p>

                <form id="borrowForm">
                    <input type="hidden" name="isbn" value="${book.ISBN}">
                    <input type="hidden" name="book_id" value="${book.ID}">
                    <input type="hidden" name="member_ID_session" value="${memberId}">
                    <input type="hidden" name="membershipType_session" value="${membershipType}">

                    <p><strong>Confirm Borrower:</strong></p>
                    <p><strong>Member ID:</strong> ${memberId}</p>
                    <p><strong>Membership Type:</strong> ${membershipType}</p>
                    <input type="checkbox" id="terms_agree" name="terms_agree" required>
                    <label for="terms_agree">I agree to the <a href="terms_conditions.php" target="_blank">terms and conditions</a></label><br><br>
                    <button type="button" id="confirmBorrowBtn">Borrow Book</button>
                </form>`;

            document.getElementById('confirmBorrowBtn').addEventListener('click', () => {
                if (!document.getElementById('terms_agree').checked) {
                    resultElement.innerHTML += '<p class="error">You must agree to the terms.</p>';
                    return;
                }
                // Pass relevant session data to the borrowing action
                performBorrowAction(book.ISBN, book.ID, memberId, membershipType);
            });
        }

        // Perform borrowing action
        async function performBorrowAction(isbn, bookId, memberId, membershipType) { // Removed 'id' parameter
            loadingElement.style.display = 'block';
            statusMessage.textContent = 'Processing borrowing request...';
            resultElement.innerHTML = '';

            const timeout = setTimeout(() => {
                loadingElement.style.display = 'none';
                statusMessage.textContent = 'Borrowing request timed out.';
                resultElement.innerHTML = '<p class="error">Request timed out. Server might be busy.</p>';
                scanAgainButton.style.display = 'block';
            }, 60000); // 60 seconds timeout for borrowing action

            try {
                const formData = new FormData();
                formData.append('isbn', isbn);
                formData.append('book_id', bookId);
                // Directly use memberId and membershipType from function parameters
                formData.append('member_ID', memberId);
                formData.append('membershipType', membershipType);
                formData.append('terms_agree', '1');

                logDebug(`Sending borrow request: isbn=${isbn}, book_id=${bookId}, member_ID=${memberId}, membershipType=${membershipType}`);

                const response = await fetch('scan_handle.php', {
                    method: 'POST',
                    body: formData
                });
                clearTimeout(timeout);

                if (!response.ok) {
                    throw new Error(`Server error: ${response.status} ${response.statusText}`);
                }

                const text = await response.text();
                logDebug(`Raw response from scan_handle.php: ${text}`);

                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error(`Invalid JSON response: ${text}`);
                }

                loadingElement.style.display = 'none';
                statusMessage.textContent = 'Borrowing process complete.';

                if (data.success) {
                    resultElement.innerHTML = `
                        <p class="success">${data.message}</p>
                        <p>Redirecting to success page...</p>`;
                    setTimeout(() => {
                        window.location.href = 'borrow_success.php';
                    }, 1500); // Redirect after 1.5 seconds
                } else {
                    resultElement.innerHTML = `<p class="error">${data.message}</p>`;
                    scanAgainButton.style.display = 'block';
                }
            } catch (error) {
                clearTimeout(timeout);
                loadingElement.style.display = 'none';
                statusMessage.textContent = 'Error during borrowing process.';
                resultElement.innerHTML = `<p class="error">Error borrowing book: ${error.message}</p>`;
                scanAgainButton.style.display = 'block';
                logDebug(`Borrow error: ${error.message}`);
            }
        }

        // Reset scanner
        function resetScanner() {
            stopCamera();
            resultElement.innerHTML = '';
            scanAgainButton.style.display = 'none';
            statusMessage.textContent = 'Initializing camera...';
            initCamera();
        }

        // Start scanning on load
        window.addEventListener('load', initCamera);
    </script>
</body>
</html>