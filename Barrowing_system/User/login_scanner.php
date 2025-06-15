<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Login</title>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(to bottom right, #14532d, #4ade80);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        #qr-video {
            width: 100%;
            max-width: 100%;
            border-radius: 0.5rem;
            opacity: 0;
            animation: fadeIn 1s ease-in forwards;
        }
        @keyframes fadeIn {
            to { opacity: 1; }
        }
        .result-enter {
            opacity: 0;
            transform: translateY(20px);
            animation: slideIn 0.5s ease-out forwards;
        }
        @keyframes slideIn {
            to { opacity: 1; transform: translateY(0); }
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .error-shake {
            animation: shake 0.3s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4">
        <div class="max-w-lg mx-auto card rounded-2xl shadow-xl p-8">
            <h1 class="text-3xl font-bold text-white text-center mb-6">Library Login</h1>
            <div class="relative">
                <video id="qr-video" class="border border-green-300/20"></video>
                <div class="absolute inset-0 border-4 border-green-500/50 rounded-lg pointer-events-none animate-pulse"></div>
            </div>
            <div id="loading" class="hidden mt-6 text-center">
                <svg class="animate-spin h-8 w-8 text-green-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <p class="text-gray-200 mt-2">Scanning QR Code...</p>
            </div>
            <div id="result" class="mt-6 hidden"></div>
        </div>
    </div>

    <script>
        const video = document.getElementById("qr-video");
        const resultElement = document.getElementById("result");
        const loadingElement = document.getElementById("loading");

        function startScan() {
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: "environment" }
            })
            .then(function(stream) {
                video.srcObject = stream;
                video.setAttribute("playsinline", true);
                video.play();
                requestAnimationFrame(scan);
            })
            .catch(function(err) {
                console.log("An error occurred: " + err);
                resultElement.innerHTML = `<p class="text-red-300 result-enter">Error accessing camera: ${err}</p>`;
                resultElement.classList.remove("hidden");
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
                    resultElement.innerText = "Detected: " + code.data;
                    loadingElement.classList.remove("hidden");
                    resultElement.classList.add("hidden");
                    video.pause();
                    video.srcObject.getTracks().forEach(track => track.stop());
                    verifyQrCode(code.data);
                    return;
                }
            }
            requestAnimationFrame(scan);
        }

        function verifyQrCode(qrCodeData) {
            fetch('verify_qr_code.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'qr_code=' + encodeURIComponent(qrCodeData)
            })
            .then(response => response.json())
            .then(data => {
                loadingElement.classList.add("hidden");
                resultElement.classList.remove("hidden");
                resultElement.classList.add("result-enter");

                if (data.status === 'success') {
                    resultElement.innerHTML = `
                        <div class="bg-green-100/20 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-300">${data.message}</h3>
                            <div class="mt-4 space-y-2 text-gray-200">
                                <p><strong>Name:</strong> ${data.data.name}</p>
                                <p><strong>Course/Department:</strong> ${data.data.course}</p>
                                <p><strong>Membership Type:</strong> ${data.data.membership_type}</p>
                                <p><strong>Contact:</strong> ${data.data.contact}</p>
                                <p><strong>Email:</strong> ${data.data.email}</p>
                            </div>
                            <button id="confirmButton" class="mt-4 w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-300 font-semibold pulse">Verify/Confirm Details</button>
                        </div>
                    `;
                    document.getElementById('confirmButton').addEventListener('click', function() {
                        window.location.href = 'homepage.php';
                    });
                } else {
                    resultElement.innerHTML = `
                        <div class="bg-red-100/20 p-4 rounded-lg error-shake">
                            <p class="text-red-300">${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                loadingElement.classList.add("hidden");
                resultElement.classList.remove("hidden");
                resultElement.classList.add("result-enter");
                resultElement.innerHTML = `
                    <div class="bg-red-100/20 p-4 rounded-lg error-shake">
                        <p class="text-red-300">Error verifying QR code</p>
                    </div>
                `;
                console.error('Error:', error);
            });
        }

        startScan();
    </script>
</body>
</html>