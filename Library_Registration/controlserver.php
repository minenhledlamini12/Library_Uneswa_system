<?php
// ... (Database connection - if you need to log actions, etc.)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $state = $_POST['state']; // 1 for ON, 0 for OFF
    $controlType = $_POST['control']; // 'led' or 'door'

    // 1. Communicate with ESP32 (This is the crucial part you'll need to adapt)
    // Here, you'll need to use a method to send the $state and $controlType
    // to your ESP32.  Examples:
    // a. Serial communication (if the web server is on the same machine as the ESP32)
    // b. Network communication (using sockets, MQTT, or a similar protocol)
    // c. Writing to a file that the ESP32 reads (less ideal, but possible)

    // Example using file writing (replace with your actual communication method):
    $controlFile = "control_signal.txt"; // File to store the control signal
    $data = $controlType . ":" . $state;
    file_put_contents($controlFile, $data);


    // 2. Respond to the web page (optional - you can send a confirmation message)
    if ($controlType == 'led') {
        $message = ($state == 1) ? "Turning LED On" : "Turning LED Off";
    } else if ($controlType == 'door') {
        $message = ($state == 1) ? "Opening Door" : "Closing Door";
    }
    echo $message; // Send the message back to the web page
}

// ... (Database close connection)
?>