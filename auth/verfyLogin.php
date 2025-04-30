<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require '../vendor/autoload.php';
require_once 'controls.php';
use PragmaRX\Google2FA\Google2FA;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
global $google2fa;
$google2fa = new Google2FA();

if(isset($_SESSION['email_login'])) {
    if(isset($_POST['verify_log'])){
        $email2 = $_SESSION['email_login'];
        $email_otp = sanitizeInput($_POST['email_otp']);
        $auth_code = sanitizeInput($_POST['auth_otp']);

        // Add debug output
        error_log("Verifying login for email: " . $email2);

        // Get user data including trials
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email2);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        
        if (!$result || $result->num_rows === 0) {
            error_log("User not found in database: " . $email2);
            echo "<script>alert('User not found in database');</script>";
            exit();
        }

        $row = $result->fetch_assoc();
        
        // Debug output
        error_log("Database values - OTP: " . $row['otp'] . ", Auth Code: " . $row['auth_code']);
        error_log("Input values - OTP: " . $email_otp . ", Auth Code: " . $auth_code);

        $email_code = $row['otp'];
        $isVerify = $google2fa->verifyKey($row['auth_code'], $auth_code, 2);
        
        // Debug verification results
        error_log("Email OTP match: " . ($email_code == $email_otp ? "true" : "false"));
        error_log("Auth code verify: " . ($isVerify ? "true" : "false"));

        if($email_code == $email_otp && $isVerify){
            // Success - Reset trials and clear OTP
            $update = $conn->prepare("UPDATE users SET trials = ?, otp = NULL WHERE email = ?");
            if (!$update) {
                die("Prepare update failed: " . $conn->error);
            }

            $trial = 0; // Reset trials to 0
            if (!$update->bind_param("is", $trial, $email2)) {
                die("Binding parameters failed: " . $update->error);
            }

            if (!$update->execute()) {
                die("Execute failed: " . $update->error);
            }
            
            $_SESSION['authenticated'] = true;
            $_SESSION['user_email'] = $email2;
            
            header("Location: dashboard.php");
            exit();
        } else {
            // Increment trial counter with error checking
            $newTrialCount = $row['trials'] + 1;
            $update = $conn->prepare("UPDATE users SET trial = ? WHERE email = ?");
            if (!$update) {
                die("Prepare update failed: " . $conn->error);
            }

            if (!$update->bind_param("is", $newTrialCount, $email2)) {
                die("Binding parameters failed: " . $update->error);
            }

            if (!$update->execute()) {
                die("Execute failed: " . $update->error);
            }

            $remainingAttempts = 5 - $newTrialCount;
            
            if($remainingAttempts <= 0) {
                echo "<script>
                    alert('Maximum attempts reached. Account locked. Please contact support.');
                    window.location.href = 'login.php';
                </script>";
            } else {
                $message = $email_code != $email_otp ? 'Invalid Email OTP!' : 'Invalid Authentication Code!';
                echo "<script>alert('" . $message . " " . $remainingAttempts . " attempts remaining.');</script>";
            }
        }
        $stmt->close();
        $update->close();
    }
} else {
    error_log("No email_login session variable set");
    echo "<script>alert('Session expired. Please login again.'); window.location.href = 'login.php';</script>";
    exit();
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
        <p class="text-center text-danger"></p>
        <div class=" mx-auto container mt-5 w-50 p-10 bg-secondary-subtle rounded p-1">
            <form class="form" method="POST" action="verfyLogin.php">
                <label for="otp">Code sent to your email:</label><br>
                <input class="form-control" type="number" id="otp1" name="email_otp"><br><br>
                <label for="otp">Code from your authenticator</label><br>
                <input class="form-control" type="number" id="otp1" name="auth_otp"><br><br>
                <button name="verify_log" class="form-control btn text-light btn-dark" type="submit">Verify OTP</button><br><br>
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

        // Add attempt counter display
        const attemptDisplay = document.createElement('p');
        attemptDisplay.className = 'text-center text-danger';
        document.querySelector('.container').insertBefore(attemptDisplay, document.querySelector('form'));

        // Function to update attempts display
        function updateAttemptsDisplay(attempts) {
            attemptDisplay.textContent = `Remaining attempts: ${5 - attempts}`;
        }

        // Initial attempts display
        <?php if(isset($row['trial'])): ?>
        updateAttemptsDisplay(<?php echo $row['trials']; ?>);
        <?php endif; ?>
    </script>
    </body>
</html>