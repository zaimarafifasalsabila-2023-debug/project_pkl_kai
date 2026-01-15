<?php
// Test script to check if upload endpoint is working
header('Content-Type: application/json');

try {
    // Check if Laravel is working
    require __DIR__ . '/../vendor/autoload.php';
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    echo json_encode([
        'success' => true,
        'message' => 'Laravel is working',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
