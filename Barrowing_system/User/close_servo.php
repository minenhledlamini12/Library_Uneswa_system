<?php
$esp32_ip = "192.168.183.69";  // Replace with the ESP32's IP address
$url = $esp32_ip . "/close";

$response = file_get_contents($url);

if ($response === false) {
    echo "Error connecting to ESP32.";
} else {
    echo "Servo close command sent.";  // For debugging
}
?>
