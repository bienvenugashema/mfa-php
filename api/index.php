<?php

require_once '../config/config.php';
require_once '../vendor/autoload.php';

// Set error log path
ini_set('error_log', __DIR__ . '/api_error.log');
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Handle CORS
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Get the request path and method
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_GET['path'] ?? '';
    
    // Log the incoming request
    error_log("Request received - Method: $method, Path: $path");

    // Extract endpoint from path
    $endpoint = explode('/', trim($path, '/'))[0] ?? '';
    error_log("Endpoint: $endpoint");

    // Route to appropriate endpoint
    switch($endpoint) {
        case 'register':
            require_once __DIR__ . '/endpoints/register.php';
            break;
        case 'login':
            require_once __DIR__ . '/endpoints/login.php';
            break;
        case 'verify':
            require_once __DIR__ . '/endpoints/verify.php';
            break;
        default:
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => "Endpoint not found: '$endpoint'"
            ]);
    }

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}