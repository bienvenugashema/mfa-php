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
            <form class="form" method="POST">
                <label for="otp">Code sent to your email:</label><br>
                <input class="form-control" type="number" id="otp1" name="email_otp"><br><br>
                <label for="otp">Code sent to your phone:</label><br>
                <input class="form-control" type="number" id="otp1" name="phone_otp"><br><br>
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