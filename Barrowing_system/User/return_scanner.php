<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Return Scanner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background: #f5f5f5; padding: 20px; }
        #qr-video { width: 80%; max-width: 600px; border: 2px solid #ccc; border-radius: 8px; margin-bottom: 20px; }
        #status-message, #result { margin: 20px auto; max-width: 600px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 10px; }
        button:disabled { background: #aaa; }
        #loading { display: none; }
        .spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid rgba(0,0,0,0.1); border-radius: 50%; border-top-color: #007bff; animation: spin 1s infinite linear; margin-right: 10px; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <h1>Book Return Scanner</h1>
    <video id="qr-video" playsinline></video>
    <div id="status-message">Waiting for camera access...</div>
    <div id="loading"><div class="spinner"></div>Processing...</div>
    <div id="result"></div>
    <button id="scan-again" style="display:none;" onclick="resetScanner()">Scan Another Book</button>

    <script>
        const video = document.getElementById('qr-video');
        const resultElement = document.getElementById('result');
        const loadingElement = document.getElementById('loading');
        const statusMessage = document.getElementById('status-message');
        const scanAgainButton = document.getElementById('scan-again');
        let stream = null;
        let scannedBook = null;

        async function initCamera() {
            try {
                statusMessage.textContent = 'Accessing camera...';
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = stream;
                video.play();
                statusMessage.textContent = 'Camera active. Scanning for QR code...';
                scan();
            } catch (err) {
                statusMessage.textContent = 'Camera access failed.';
                resultElement.innerHTML = `<p class="error">Error accessing camera: ${err.message}</p>`;
                scanAgainButton.style.display = 'block';
            }
        }

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
            const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });

            if (code) {
                statusMessage.textContent = 'QR code detected! Verifying...';
                stopCamera();
                verifyBookQrCode(code.data);
            } else {
                requestAnimationFrame(scan);
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }

        async function verifyBookQrCode(qrCodeData) {
            loadingElement.style.display = 'block';
            resultElement.innerHTML = '';
            try {
                const response = await fetch('verify_return_qr.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `qr_code=${encodeURIComponent(qrCodeData)}`
                });
                const data = await response.json();
                loadingElement.style.display = 'none';

                if (data.status === 'success') {
                    scannedBook = data.book;
                    showBookDetails(data.book);
                } else {
                    resultElement.innerHTML = `<p class="error">${data.message}</p>`;
                    scanAgainButton.style.display = 'block';
                }
            } catch (error) {
                loadingElement.style.display = 'none';
                resultElement.innerHTML = `<p class="error">Error verifying QR code: ${error.message}</p>`;
                scanAgainButton.style.display = 'block';
            }
        }

        function showBookDetails(book) {
            resultElement.innerHTML = `
                <h3>Book Details</h3>
                <p><strong>Title:</strong> ${book.Title}</p>
                <p><strong>Author:</strong> ${book.Author}</p>
                <p><strong>ISBN:</strong> ${book.ISBN}</p>
                <button id="confirmReturnBtn">Confirm Return</button>
            `;
            document.getElementById('confirmReturnBtn').onclick = confirmReturn;
        }

        async function confirmReturn() {
            loadingElement.style.display = 'block';
            statusMessage.textContent = 'Opening bin door...';
            resultElement.innerHTML = '';
            try {
                const response = await fetch('process_return.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `book_id=${encodeURIComponent(scannedBook.ID)}`
                });
                const data = await response.json();
                loadingElement.style.display = 'none';
                if (data.status === 'waiting_drop') {
                    resultElement.innerHTML = `<p class="success">Bin door is open. Please drop your book in the bin.</p>`;
                    statusMessage.textContent = 'Waiting for book drop...';
                    waitForDrop(scannedBook.ID);
                } else {
                    resultElement.innerHTML = `<p class="error">${data.message}</p>`;
                    scanAgainButton.style.display = 'block';
                }
            } catch (error) {
                loadingElement.style.display = 'none';
                resultElement.innerHTML = `<p class="error">Error: ${error.message}</p>`;
                scanAgainButton.style.display = 'block';
            }
        }

        // Poll backend to check if book was dropped and door closed
        function waitForDrop(bookId) {
            let interval = setInterval(async () => {
                try {
                    const response = await fetch(`check_drop_status.php?book_id=${encodeURIComponent(bookId)}`);
                    const data = await response.json();
                    if (data.status === 'returned') {
                        clearInterval(interval);
                        resultElement.innerHTML = `<p class="success">Book returned successfully! Confirmation email sent.</p>`;
                        statusMessage.textContent = 'Return complete.';
                        scanAgainButton.style.display = 'block';
                    }
                } catch (e) {
                    // Optionally handle polling errors
                }
            }, 2000); // Poll every 2 seconds
        }

        function resetScanner() {
            stopCamera();
            resultElement.innerHTML = '';
            scanAgainButton.style.display = 'none';
            statusMessage.textContent = 'Initializing camera...';
            scannedBook = null;
            initCamera();
        }

        window.addEventListener('load', initCamera);
    </script>
</body>
</html>
