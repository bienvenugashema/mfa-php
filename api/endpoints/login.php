<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("Processing login request");

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

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid JSON data',
        'debug' => [
            'json_error' => json_last_error_msg(),
            'raw_input' => $input
            ]
        ]);
        exit();
    }
    
    try {
        // Validate required fields
        if (empty($data['email']) || empty($data['password'])) {
            throw new Exception('Email and password are required');
        }
        
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $password = $data['password'];
        
        error_log("Attempting login for email: $email");
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])) {
                $otp = random_int(100000, 999999);
                $hashed_otp = password_hash($otp, PASSWORD_DEFAULT);
                $updateOtp = $conn->prepare("UPDATE users SET otp = ? WHERE email = ?");
                $updateOtp->bind_param("ss", $hashed_otp, $email);
                $updateOtp->execute();
                
                function sendOtpEmail($em, $ot) {
                    try {
                        //Server settings
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'bienvenugashema@gmail.com';
                    $mail->Password = 'ckgp iujo nveh yue'; // SMTP password	
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('bienvenugashema@gmail.com', 'OTP Verification');
                    $mail->addAddress($em); // Add a recipient
                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP Code for Login';
                    $mail->Body = "<h1>Your OTP Code is: $ot</h1><p>This OTP is valid for 5 minutes.</p>";
                    $mail->AltBody = "Your OTP Code is: $ot\nThis OTP is valid for 5 minutes.";
                    $mail->send();
                    return true;
                } catch (Exception $e) {
                    error_log("Email sending failed: " . $mail->ErrorInfo);
                    return false;
                }
            }
            sendOtpEmail($email, $otp);    
            // For testing
            echo json_encode([
                'success' => true,
                'message' => 'OTP generated',
                'data' => [
                    'email' => $email,
                    'otp' => $otp
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid password']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}