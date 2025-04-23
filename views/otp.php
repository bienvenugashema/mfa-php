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
        <p class="text-center">Please enter the OTP sent to your email.</p>
        <div class=" mx-auto container mt-5 w-50 p-10 bg-secondary-subtle rounded p-1">
            <form class="form" method="POST">
                <label for="otp">OTP:</label><br>
                <input class="form-control" type="text" id="otp1" name="email_otp"><br><br>
                <input class="form-control" type="text" id="otp2" name="phone_otp"><br><br>
                <button name="verify_otp" class="form-control btn text-light btn-dark" type="submit">Verify OTP</button><br><br>
                <p>If you didn't receive the OTP? <i class="resend-otp text-primary cursor-pointer">Resend OTP</i></p><br><br>
            </form>
        </div>
    </div>
    </body>
</html>