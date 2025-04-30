<?php
include '../config/config.php';

require_once __DIR__ . '/../vendor/autoload.php';
require '../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();

// Add authentication check function
function checkAuth() {
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        header("Location: /mfa-php/auth/login");
        exit();
    }
}

// Add guest check function
function checkGuest() {
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        header("Location: /mfa-php/auth/dashboard");
        exit();
    }
}

function sanitizeInput($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function insertUser($n, $em, $p, $h, $eo, $po, $google_code) {
    global $conn;
    try {
        // Check if user exists using prepared statement
        $stmt = $conn->prepare("SELECT * FROM waiting_users WHERE email = ? OR phone = ?");
        $stmt->bind_param("ss", $em, $p);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "<script>alert('User already exists!');</script>";
            return false;
        }

        // Insert new user using prepared statement
        $stmt = $conn->prepare("INSERT INTO waiting_users (names, email, phone, password, email_otp, phone_otp, auth_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $n, $em, $p, $h, $eo, $po, $google_code);
        
        if ($stmt->execute()) {
            $_SESSION['email'] = $em;
            $_SESSION['code'] = $google_code;
            // Configure and send email
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Username = 'bienvenugashema@gmail.com';
            $mail->SMTPAuth = true;
            $mail->Password = $_ENV['MY_APP_PASSWORD']; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom('bienvenugashema@gmail.com', 'OTP Verification');
            $mail->addAddress($em, $n);
            
            $body = "<div>
                <h1 style='color:blue; text-align: center;'>OTP Verification</h1>
                <h2 style='color:green; text-align: center;'>Your OTP is: $eo</h2>
                <p>Notice that this OTP has validity of 5 min to expire</p>
            </div>";
            
            $mail->isHTML(true);
            $mail->Body = $body;
            $mail->Subject = 'OTP Verification';
            $mail->AltBody = 'This email is sent from IT Bienvenu, please verify your email';
            
            if($mail->send()) {
                return true;
            } else {
                throw new Exception("Failed to send email: " . $mail->ErrorInfo);
            }
        } else {
            throw new Exception("Database insertion failed: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        echo "<script>alert('Registration failed: " . addslashes($e->getMessage()) . "');</script>";
        return false;
    }
}

if(isset($_POST['register'])) {
    checkGuest(); // Add this line
    $names = sanitizeInput($_POST['names']);
    $email = filter_var(sanitizeInput($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/\D/', '', sanitizeInput($_POST['phone']));
    $password = sanitizeInput($_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $email_otp = random_int(100000, 999999);
    $phone_otp = random_int(100000, 999999);
    $secret = $google2fa->generateSecretKey();

    if(insertUser($names, $email, $phone, $hashed_password, $email_otp, $phone_otp, $secret)) {
        header("Location: /mfa-php/auth/otp.php");
        exit();
    }
}


if(isset($_POST['login'])) {
    checkGuest(); // Add this line
}