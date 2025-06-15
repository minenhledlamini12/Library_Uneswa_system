<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;

$qrCode = QrCode::create('test');
echo "QR Code class loaded successfully.";
