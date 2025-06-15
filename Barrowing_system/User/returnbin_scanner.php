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
        /* (Use your previous CSS for consistency, or copy from borrowing scanner) */
        body { font-family: Arial, sans-serif; text-align: center; background: #f5f5f5; padding: 20px; }
        h1 { color: #333; margin-bottom: 20px; }
        #qr-video { width: 80%; max-width: 600px; border: 2px solid #ccc; border-radius: 8px; margin-bottom: 20px; }
        #loading { display: none; margin: 20px auto; padding: 10px; background-color: #f8f9fa; border-radius: 5px; color: #333; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; padding: 10px; background-color: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .success { color: #28a745; font-weight: bold; padding: 10px; background-color: #d4edda; border-radius: 5px; margin: 10px 0; }
        #result { margin: 20px auto; max-width: 600px; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        button { background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 10px; }
        button:hover { background-color: #0056b3; }
        .spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid rgba(0, 0, 0, 0.1); border-radius: 50%; border-top-color: #007bff; animation: spin 1s ease-in-out infinite; margin-right: 10px; vertical-align: middle; }
        @keyframes spin { to { transform: rotate(360deg); } }
        #status-message { margin-top: 10px; font-style: italic; color: #666; }
        #scan-again { display: none; }
    </style>
</head>
<body>
    <h1>Book Return Scanner</h1>
    <video id="qr-video" playsinline></video>
    <div id="status-message">Waiting for camera access...</div>
    <div id="loading"><div class="spinner"></div>Processing...</div>
    <div id="result"></div>
    <button id="scan-again" onclick="resetScanner()">Scan Another Book</button>
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
                statusMessage.textContent = 'Camera access failed. Please check permissions.';
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
                const response = await fetch('verify_book_binreturn.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `qr_code=${encodeURIComponent(qrCodeData)}`
                });
                const data = await response.json();
                loadingElement.style.display = 'none';

                if (data.status === 'success') {
                    scannedBook = data.book;
                    displayBookDetails(data.book);
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

        function displayBookDetails(book) {
            resultElement.innerHTML = `
                <h3>Book Details</h3>
                <p><strong>Title:</strong> ${book.Title}</p>
                <p><strong>Author:</strong> ${book.Author}</p>
                <p><strong>ISBN:</strong> ${book.ISBN}</p>
                <button id="confirmReturnBtn">Confirm Return</button>
            `;
            document.getElementById('confirmReturnBtn').addEventListener('click', () => {
                performReturnAction(book.ISBN, book.ID);
            });
        }

        async function performReturnAction(isbn, bookId) {
            loadingElement.style.display = 'block';
            statusMessage.textContent = 'Processing return...';
            resultElement.innerHTML = '';
            try {
                const formData = new FormData();
                formData.append('isbn', isbn);
                formData.append('book_id', bookId);

                const response = await fetch('returnbin_handle.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                loadingElement.style.display = 'none';
                statusMessage.textContent = 'Return process complete.';

                if (data.success) {
                    resultElement.innerHTML = `<p class="success">${data.message}</p>`;
                } else {
                    resultElement.innerHTML = `<p class="error">${data.message}</p>`;
                    scanAgainButton.style.display = 'block';
                }
            } catch (error) {
                loadingElement.style.display = 'none';
                statusMessage.textContent = 'Error during return process.';
                resultElement.innerHTML = `<p class="error">Error returning book: ${error.message}</p>`;
                scanAgainButton.style.display = 'block';
            }
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
