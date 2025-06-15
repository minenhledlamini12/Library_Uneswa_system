<?php
session_start(); // Start the session

require_once("connection.php");



?>

<!DOCTYPE html>
<html>

<head>
    <title>QR Code Scanner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include jsQR library -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
        }

        #qr-video {
            width: 80%;
            max-width: 600px;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>
    <h1>Scan Your QR Code</h1>
    <video id="qr-video"></video>
    <p id="result"></p>

    <script>
        const video = document.getElementById("qr-video");
        const resultElement = document.getElementById("result");

        // Function to scan QR code
        function startScan() {
            navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: "environment"
                    }
                })
                .then(function(stream) {
                    video.srcObject = stream;
                    video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
                    video.play();
                    requestAnimationFrame(scan);
                })
                .catch(function(err) {
                    console.log("An error occurred: " + err);
                    alert("Error accessing camera: " + err);
                });
        }

        function scan() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                const canvasElement = document.createElement('canvas');
                canvasElement.width = video.videoWidth;
                canvasElement.height = video.videoHeight;
                const canvasContext = canvasElement.getContext('2d');
                canvasContext.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                const imageData = canvasContext.getImageData(0, 0, canvasElement.width, canvasElement.height);

                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });

                if (code) {
                    console.log("Found QR code", code.data);
                    resultElement.innerText = "Member Email: " + code.data;
                    // Send data to server for verification
                    verifyQrCode(code.data);
                    video.pause();
                    video.srcObject.getTracks().forEach(track => track.stop());
                    return;
                }
            }
            requestAnimationFrame(scan);
        }

        // Function to verify QR code with the server
        function verifyQrCode(qrCodeData) {
            fetch('verify_qr_code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'qr_code=' + qrCodeData
                })
                .then(response => response.text())
                .then(data => {
                    alert(data); // Show response from server
                    // Redirect or update UI based on verification result
                    if (data === 'Authentication successful') {
                        window.location.href = 'success.php'; // Redirect to success page
                    } else {
                        resultElement.innerText = data; // Display error message
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultElement.innerText = 'Error verifying QR code';
                });
        }

        // Start scanning when the page loads
        startScan();
    </script>
</body>

</html>
