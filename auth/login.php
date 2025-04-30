<?php
require_once 'controls.php';
checkGuest();
include_once 'controls.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Move the sendCodes function outside of the if statement
function sendCodes($em, $conn) {
    $_SESSION['email_login'] = $em;
    $otp = random_int(100000, 999999);
    
    // Use prepared statement instead of direct query
    $stmt = $conn->prepare("UPDATE users SET otp = ? WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $otp, $em);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    try {
        // Email configuration
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Username = 'bienvenugashema@gmail.com';
        $mail->SMTPAuth = true;
        $mail->Password = "ckgp iujo nveh yue";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('bienvenugashema@gmail.com', 'OTP Verification');
        $mail->addAddress($em, 'User');    

        $body = "<div>
            <h1 style='color:blue; text-align: center;'>OTP Verification</h1>
            <h2 style='color:green; text-align: center;'>Your OTP is: $otp</h2>
            <p>Notice that this OTP has validity of 5 min to expire</p>
        </div>";

        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->Subject = 'OTP Verification';
        $mail->AltBody = 'This email is sent from IT Bienvenu, please verify your email';
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Mail Error: " . $e->getMessage());
        return false;
    }
}

if(isset($_POST['login'])) {
    $email = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);

    // Use prepared statement instead of direct query
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_verified = true");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    if (!$stmt->bind_param("s", $email)) {
        die("Binding parameters failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $v = password_verify($password, $row['password']);
        echo "trials: " . $v;
        if($v) {
            // Reset trial counter on new login attempt
            $resetTrials = $conn->prepare("UPDATE users SET trials = 0 WHERE email = ?");
            $resetTrials->bind_param("s", $email);
            $resetTrials->execute();
            
            // Pass $conn when calling the function
            if(sendCodes($email, $conn)) {
                header("Location: verfyLogin.php"); // Fixed typo in filename
                exit();
            } else {
                echo "<script>alert('Failed to send verification code!');</script>";
            }
        } else {
            echo "<script>alert('Invalid password!');</script>";
        }
    } else {
        echo "<script>alert('User not found or not verified!');</script>";
    }
    
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <style>
        .cursor-pointer{
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-dark">
    <div class="container mt-5 center w-50 bg-secondary-subtle rounded p-1">
        <div class="text-center">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25231.png" alt="logo" width="100" height="100">
        </div>
        <h1 class="text-center">Welcome to the Login Page</h1>
        <p class="text-center">Please enter your credentials to log in.</p>
    <div class="container mt-5">
        <form class="form" method="POST">
            <label for="username">Email</label><br>
            <input class="form-control" type="email" id="username" name="username"><br><br>
            <label for="password">Password:</label><br>
            <input class="form-control" type="password" id="password" name="password"><br><br>
            <button name="login" class="form-control btn text-light btn-dark" type="submit">Click to Log In</button><br><br>
            <button name="register" class="btn btn-link text-primary register" type="submit">Register</button> here</p><br><br>
        </form>
    </div>  
    </div>  

    <script>
        const register = document.querySelector(".register");
        register.addEventListener("click", function(event) {
            event.preventDefault();
            window.location.href = "register.php";
        });
    </script>
</body>
</html>