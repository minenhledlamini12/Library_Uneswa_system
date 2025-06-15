<?php

require_once __DIR__ . '/vendor/autoload.php';

use Google\Client;

$client = new Google_Client();
$client->setApplicationName("Test Application");
echo "Google API Client Library loaded successfully!";
