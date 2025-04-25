<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../vendor/autoload.php';
require_once 'controls.php';
use PragmaRX\Google2FA\Google2FA;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
global $google2fa;
$google2fa = new Google2FA();

if(isset($_POST['verify_otp'])) {
    // Add debug logging
    error_log("Starting OTP verification");
    
    $email2 = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $email_otp = sanitizeInput($_POST['email_otp']);
    $auth_otp = sanitizeInput($_POST['auth_otp']);
    $auth_code = sanitizeInput($_POST['auth_code']);

    // Debug received values
    error_log("Email: $email2");
    error_log("Email OTP: $email_otp");
    error_log("Auth OTP: $auth_otp");
    error_log("Auth Code: $auth_code");

    // Check if the connection is valid
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Prepare statement with error checking
    $stmt = $conn->prepare("SELECT * FROM waiting_users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind and execute with error checking
    if (!$stmt->bind_param("s", $email2)) {
        die("Binding parameters failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // Debug database values
        error_log("DB Email OTP: " . $row['email_otp']);
        error_log("DB Auth Code: " . $row['auth_code']);
        
        // Verify both OTPs
        $emailOtpValid = ($row['email_otp'] === $email_otp);
        // Remove any spaces from the auth code
        $auth_otp = preg_replace('/\s+/', '', $auth_otp);
        $authOtpValid = $google2fa->verifyKey($row['auth_code'], $auth_otp);
        
        // Debug verification results
        error_log("Email OTP Valid: " . ($emailOtpValid ? 'true' : 'false'));
        error_log("Auth Code Valid: " . ($authOtpValid ? 'true' : 'false'));

        if($emailOtpValid && $authOtpValid) {
            // Both OTPs are valid - move user to verified users table
            $insert = $conn->prepare("INSERT INTO users (names, email, phone, password, is_verified, auth_code) 
                                    VALUES (?, ?, ?, ?, true, ?)");
            if (!$insert) {
                die("Prepare insert failed: " . $conn->error);
            }

            if (!$insert->bind_param("sssss", $row['names'], $row['email'], $row['phone'], $row['password'], $row['auth_code'])) {
                die("Binding insert parameters failed: " . $insert->error);
            }

            if (!$insert->execute()) {
                die("Insert execute failed: " . $insert->error);
            }
            
            // Remove from waiting list
            $delete = $conn->prepare("DELETE FROM waiting_users WHERE email = ?");
            if (!$delete) {
                die("Prepare delete failed: " . $conn->error);
            }

            if (!$delete->bind_param("s", $email2)) {
                die("Binding delete parameters failed: " . $delete->error);
            }

            if (!$delete->execute()) {
                die("Delete execute failed: " . $delete->error);
            }
            
            $_SESSION['authenticated'] = true;
            $_SESSION['user_email'] = $email2;
            
            echo "<script>
            alert('Verification successful!');
            window.location.href = 'dashboard.php';
            </script>";
        } 
        elseif(!$emailOtpValid) {
            echo "<script>alert('Invalid Email OTP!');</script>";
        }
        else {
            echo "<script>alert('Invalid Authentication Code!');</script>";
        }
    } else {
        echo "<script>alert('User not found!');</script>";
    }
    
    // Close statements
    $stmt->close();
    if(isset($insert)) $insert->close();
    if(isset($delete)) $delete->close();
}
?>

<html>
    <head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">
    </head>
    <body>
    <div class="container">
        <div class="text-center">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25231.png" alt="logo" width="100" height="100">
        </div>
        
        <h1 class="text-center">Welcome to the OTP Verification Page</h1>
        <div>
            <p class="text-center"> <b class="counter">5</b> min remainig for otp to expire</p>
        </div>
        <div class=" mx-auto container mt-5 w-50 p-10 bg-secondary-subtle rounded p-1">
            // Add this before your form for testing
            <?php if(isset($row)): ?>
            <div style="display: none;">
                <p>Debug Info:</p>
                <p>Stored Auth Code: <?php echo htmlspecialchars($row['auth_code']); ?></p>
                <p>Email OTP: <?php echo htmlspecialchars($row['email_otp']); ?></p>
            </div>
            <?php endif; ?>
            <form class="form" method="POST" action="otp.php">
                <label for="otp">Code sent to your email:</label><br>
                <input class="form-control" type="number" id="otp1" name="email_otp"><br><br>
                <div>
                    <p>Copy this code and link it to your google authenticator</p>
                    <p>Code: <b><?php
                    if(isset($_SESSION['email'])) {
                        try {
                            
                            $email = $_SESSION['email'];
                            
                            // Sanitize email
                            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
                            
                            $search = "SELECT * FROM waiting_users WHERE email = ?";
                            $stmt = mysqli_prepare($conn, $search);
                            mysqli_stmt_bind_param($stmt, "s", $email);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            
                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                                $secret = $row['auth_code'];
                                echo htmlspecialchars($secret);
                                
                                // Generate QR code URL
                                $qrCodeUrl = $google2fa->getQRCodeUrl(
                                    'BienvenuOTP', // Your app name
                                    $email,
                                    $secret
                                );
                                // Display QR code

                                echo '</b></p>';
                                echo '<p>Scan this QR code with your Google Authenticator app:</p>';
                                echo "<img src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data="
                                .urlencode($qrCodeUrl)."' alt='QR Code' />";// echo '<img src="'.htmlspecialchars($qrCodeImageUrl).'" alt="QR Code" style="width: 200px; height: 200px;" />';
                            } else {
                                echo "User not found.";
                            }
                        } catch (Exception $e) {
                            echo "Error generating QR code: " . htmlspecialchars($e->getMessage());
                        }
                    } else {
                        echo "Email session not set.";
                    }
                    ?>
                </div><br><br>
                <label for="otp">Code from your authenticator</label><br>
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>">
                <input type="hidden" name="auth_code" value="<?php echo htmlspecialchars($row['auth_code']); ?>">
                <input class="form-control" type="number" id="otp1" name="auth_otp"><br><br>
                <button name="verify_otp" class="form-control btn text-light btn-dark" type="submit">Verify OTP</button><br><br>
                <p>If you didn't receive the OTP? <i class="resend-otp text-primary cursor-pointer">Resend OTP</i></p><br><br>
            </form>
        </div>
    </div>
    <script>
        const resendOtp = document.querySelector(".resend-otp");
        const counter = document.querySelector(".counter");
        let timeLeft = 5 * 60;

        function updateCounter() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            counter.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            timeLeft--;
            if (timeLeft < 0) {
                clearInterval(timerInterval);
                alert("OTP expired. Please request a new one.");
            }
        }

        const timerInterval = setInterval(updateCounter, 1000);

        resendOtp.addEventListener("click", function(event) {
            event.preventDefault();
            alert("Resending OTP...");
            // Add your AJAX call to resend the OTP here
        });
    </script>
    </body>
</html>