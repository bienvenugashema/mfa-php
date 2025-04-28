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

function sanitizeInput($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

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
    $names = sanitizeInput($data['names'] ?? '');
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/\D/', '', $data['phone'] ?? '');
    $password = $data['password'] ?? '';

    // Validate required fields
    if (empty($names) || empty($email) || empty($phone) || empty($password)) {
        throw new Exception('All fields are required');
    }

    // Generate credentials and tokens
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $email_otp = random_int(100000, 999999);
    $phone_otp = random_int(100000, 999999);
    $secret = $google2fa->generateSecretKey();
    $imageUrl = $google2fa->getQRCodeUrl('IT Bienvenu', $email, $secret);
    
    // Hash OTPs
    $hashed_email_otp = password_hash($email_otp, PASSWORD_DEFAULT);
    $hashed_phone_otp = password_hash($phone_otp, PASSWORD_DEFAULT);

    // Insert user data
    $stmt = $conn->prepare("INSERT INTO waiting_users (names, email, phone, password, email_otp, phone_otp, auth_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $names, $email, $phone, $hashed_password, $hashed_email_otp, $hashed_phone_otp, $secret);
    
    if (!$stmt->execute()) {
        throw new Exception("Database insertion failed: " . $stmt->error);
    }
    $stmt->close();

    // Send email with OTP
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'bienvenugashema@gmail.com';
    $mail->Password = 'ckgp iujo nveh yuex';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom('bienvenugashema@gmail.com', 'OTP Verification');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Your OTP Code for Login';
    $mail->Body = "<h1>Your OTP Code is: $email_otp</h1><p>This OTP is valid for 5 minutes.</p>";
    $mail->AltBody = "Your OTP Code is: $email_otp\nThis OTP is valid for 5 minutes.";
    
    if (!$mail->send()) {
        throw new Exception("Email sending failed: " . $mail->ErrorInfo);
    }

    // Log success and return response
    error_log("Registration successful for email: $email");
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'data' => [
            'email' => $email,
            'secret' => $secret,
            'imageUrl' => $imageUrl
        ]
    ]);

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}