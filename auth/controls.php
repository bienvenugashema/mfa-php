<?php
include '../config/config.php';
require_once __DIR__ . '/vendor/autoload.php';  // Adjust the path if needed

// Create a Dotenv instance and load the .env file from the current directory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Access environment variables
$key = $_ENV['AFRICAS_TALKING_API_KEY'];
$appPassword = $_ENV['MY_APP_PASSWORD'];

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';


function sanitizeInput($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
function sendSMS($phone, $message) {
    $apiKey = $key;
    $url = 'https://api.africastalking.com/version1/messaging/bulk';
    $username = 'sandbox';
    $data = [
        'username' => $username,
        'message' => $message,
        'senderId' => 'IT Bienvenu',
        'phoneNumbers' => ['+'.$phone],
    ];

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'apiKey: ' . $apiKey
    ];    


    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    } else {
        echo 'Response: ' . $response;
    }
    curl_close($ch);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "Response: $response\n";
}


if(isset($_POST['register'])) {
    echo "Everything is set!";
    $names = sanitizeInput($_POST['names']);
    $email = filter_var(sanitizeInput($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = sanitizeInput($_POST['phone']);
    $password = sanitizeInput($_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $phone = preg_replace('/\D/', '', $phone);
    $email_otp = random_int(100000, 999999);
    $phone_otp = random_int(100000, 999999);
    
    function insertUser($n,$em, $p, $h, $eo, $po) {

        $mail = new PHPMailer(true);
        global $conn;
        $search = "SELECT * FROM users WHERE email = '$em' OR phone = '$p'";	
        $result = mysqli_query($conn, $search);
        if (mysqli_num_rows($result) > 0) {
            echo "<script>alert('User already exists!');</script>";
        } else {
            $sql = "INSERT INTO waiting_users (names, email, phone, password, email_otp, phone_otp) VALUES ('$n', '$em', '$p', '$h', '$eo', '$po')";	
            if ( 1) {
                try{
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail -> Username = 'bienvenugashema@gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Password = $appPassword;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail -> setFrom('bienvenugashema@gmail.com', 'OTP Verification');
                    $mail -> addAddress($em, $n);
                    
                    // message content
                    
                    $body = "<div><h1 style='color:blue; text-align: center;'>OTP Verification
                    <br>
                    <h2 style='color:green; text-align: center;'>Your OTP is: $eo</h2>
                    <p>Notice that this ot has validity of 5 min to expire</p>   
                    </h1></div>";	
                    
                    $mail -> isHTML(true);
                    $mail -> Body = $body;
                    $mail -> Subject = 'OTP Verification';
                    $mail -> AltBody = 'This email if sent from IT Bienvenu, please verify your email';
                    $mail -> send();
                    $whatif = mysqli_query($conn, $sql);
                    sendSMS($phone, "Your OTP is: $phone_otp. Please verify your phone number.");
                } catch (Exception $e) {
                    echo "<script>alert('Error: " . $mail->ErrorInfo . "');</script>";
                }
            } else {
                echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
            }
        }
    }
    insertUser($names, $email, $phone, $hashed_password, $email_otp, $phone_otp);

    header("Location: otp.php?email=$email&phone=$phone&names=$names&password=$password");
    exit();
}

if(isset($_POST['login'])) {
    
    
}