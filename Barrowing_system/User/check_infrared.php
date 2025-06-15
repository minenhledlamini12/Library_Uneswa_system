<?php
$esp32_ip = "http://your-esp32-ip192.168.183.69
_ip . "/check_infrared";

$response = file_get_contents($url);

if ($response === false) {
    echo json_encode(['objectDetected' => false, 'error' => 'Failed to connect to ESP32']);
} else {
    // Assuming ESP32 sends back either "1" for object detected or "0" for not detected
    $objectDetected = (trim($response) === '1');
    echo json_encode(['objectDetected' => $objectDetected]);
}
?>
