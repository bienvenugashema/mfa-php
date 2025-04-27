<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PragmaRX\Google2FA\Google2FA;

// Set error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("Processing registration request");

$google2fa = new Google2FA();

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get and validate JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit();
}

try {
    // Sanitize and validate inputs
    $names = $data['names'] ?? '';
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/\D/', '', $data['phone'] ?? '');
    $password = $data['password'] ?? '';

    // Validate required fields
    if (empty($names) || empty($email) || empty($phone) || empty($password)) {
        throw new Exception('All fields are required');
    }

    // Hash password and generate OTPs
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $email_otp = random_int(100000, 999999);
    $phone_otp = random_int(100000, 999999);
    $secret = "1234567890"; // For testing only

    // Log registration attempt
    error_log("Attempting registration for email: $email");

    echo json_encode([
        'success' => true,
        'message' => 'Registration data received',
        'data' => [
            'email' => $email,
            'secret' => $secret
        ]
    ]);

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}