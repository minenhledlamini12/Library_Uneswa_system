<!DOCTYPE html>
<html>
<head>
<title>Library Access Control</title>
<style>
body {
    background-color: green; /* Green background */
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    min-height: 100vh; /* Ensure full viewport height */
    margin: 0; /* Remove default margins */
    font-family: sans-serif;
}

.container {
    background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white container */
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

#result { /* Style for the result display */
    margin-top: 20px;
    font-size: 1.2em;
    font-weight: bold;
}
</style>
</head>
<body>

<div class="container">
    <h1>Library Access</h1>
    <p>Scan your QR code</p>
    <div id="result"></div>  </div>

<script>
    // Placeholder for receiving data from ESP32 (replace with your actual method)
    // This is how you would typically receive data (example using Fetch API)
    function receiveData(qrCodeData) {
        // Send the QR code data to your server for verification
        fetch('verify_qr.php', { // Replace with your server-side script
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'qr_code=' + qrCodeData, // Send data in the request body
        })
        .then(response => response.text())
        .then(result => {
            document.getElementById('result').textContent = result;
            // Here, you can add code to handle the result from the server.
            // For example, if the result is "Access Granted", you might
            // redirect the user to a different page or perform other actions.
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('result').textContent = "An error occurred.";
        });
    }

    // Example: Simulated QR code scan (for testing)
    // Replace this with the actual data received from the ESP32-CAM
    // In your actual implementation, the ESP32 will send the QR code data to this page.
    // You'll need to use a communication method like WebSockets or Server-Sent Events (SSE).
    // The following is just a placeholder to demonstrate the flow.

    // Simulate a QR code scan after a delay (replace with your real ESP32 data)
    setTimeout(() => {
        const simulatedQRData = "student_id=123&student_name=JohnDoe&email=john.doe@example.com"; // Example data
        receiveData(simulatedQRData); // Call the function to send data
    }, 3000); // Simulate a scan after 3 seconds (adjust as needed)


</script>

</body>
</html>