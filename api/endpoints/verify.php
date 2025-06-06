<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("Starting verification process");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();
function sanitizeInput($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate request method
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get and validate JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Debug logging
error_log("Raw input: " . $input);
error_log("Decoded data: " . print_r($data, true));

// Validate JSON data
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit();
}

try {
    // Add verification type to required fields
    if (empty($data['email']) || empty($data['email_otp']) || empty($data['auth_code']) || empty($data['type'])) {
        throw new Exception('Email, OTP, authentication code and type are required');
    }

    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $email_otp = sanitizeInput($data['email_otp']);
    $auth_code = sanitizeInput($data['auth_code']);
    $type = sanitizeInput($data['type']); // 'register' or 'login'

    error_log("Verification type: $type for email: $email");

    if ($type === 'register') {
        // Check waiting_users table
        $stmt = $conn->prepare("SELECT * FROM waiting_users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $isVerify = $google2fa->verifyKey($user['auth_code'], $auth_code);
            
            if (password_verify($email_otp, $user['email_otp']) && $isVerify) {
                // Begin transaction
                $conn->begin_transaction();
                try {
                    // Insert into users table
                    $insertStmt = $conn->prepare("INSERT INTO users (names, email, phone, password, auth_code) VALUES (?, ?, ?, ?, ?)");
                    $insertStmt->bind_param("sssss", $user['names'], $user['email'], $user['phone'], $user['password'], $user['auth_code']);
                    $insertStmt->execute();

                    // Delete from waiting_users
                    $deleteStmt = $conn->prepare("DELETE FROM waiting_users WHERE email = ?");
                    $deleteStmt->bind_param("s", $email);
                    $deleteStmt->execute();

                    $conn->commit();
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration verification successful',
                        'data' => ['email' => $email]
                    ]);
                } catch (Exception $e) {
                    $conn->rollback();
                    throw new Exception("Registration completion failed: " . $e->getMessage());
                }
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid OTP or authentication code'
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Pending registration not found'
            ]);
        }
    } else if ($type === 'login') {
        // Existing login verification code
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Log stored values
            error_log("Stored values - OTP: {$user['otp']}, Auth Code: {$user['auth_code']}");
            
            $isVerify = $google2fa->verifyKey($user['auth_code'], $auth_code);
            error_log("2FA Verification result: " . ($isVerify ? 'true' : 'false'));
            
            if ( password_verify($email_otp, $user['otp']) && $isVerify) {
                // Success case
                error_log("Verification successful for: $email");
                echo json_encode([
                    'success' => true,
                    'message' => 'Verification successful',
                    'data' => [
                        'email' => $email
                    ]
                ]);
            } else {
                // Invalid OTP or auth code
                error_log("Verification failed - OTP match: " . ($user['otp'] == $email_otp ? 'true' : 'false'));
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid OTP or authentication code'
                ]);
            }
        } else {
            // User not found
            error_log("User not found: $email");
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'User not found'
            ]);
        }
    } else {
        throw new Exception('Invalid verification type');
    }

} catch (Exception $e) {
    error_log("Verification error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}