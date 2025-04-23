<?php
include_once 'controls.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">
  <style>
    .cursor-pointer {
      cursor: pointer;
    }
    #phone {
      width: 100%;
      padding: 10px;
    }
  </style>
</head>
<body class="bg-dark">
  <div class="container mt-5 center w-50 bg-secondary-subtle rounded p-1">
    <div class="text-center">
      <img src="https://cdn-icons-png.flaticon.com/512/25/25231.png" alt="logo" width="100" height="100">
    </div>
    <h1 class="text-center">Welcome to the Registration Page</h1>
    <p class="text-center">Please enter your credentials to register.</p>
    <div class="container mt-5">
      <form class="form" method="POST" onsubmit="return setFullPhoneNumber();">
        <label for="names">Names:</label>
        <input class="form-control" type="text" id="names" name="names"><br><br>
        <label for="email">Email</label><br>
        <input class="form-control" type="email" id="email" name="email"><br><br>
        <label for="phone">Phone number</label><br>
        <input class="form-control" type="tel" id="phone" name="phone"><br><br>
        <!-- Hidden input to store full phone number -->
        <input type="hidden" name="phone" id="fullPhone">
        <label for="password">Password:</label><br>
        <input class="form-control" type="password" id="password" name="password"><br><br>
        <button name="register" class="form-control btn text-light btn-dark" type="submit">Click to Register</button><br><br>
        <p>If you have an account? <i class="login text-primary cursor-pointer">Login</i> here</p><br><br>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>

  <script>
    const phoneInput = document.querySelector("#phone");
    const iti = window.intlTelInput(phoneInput, {
      separateDialCode: true,
      initialCountry: "auto",
      geoIpLookup: function(callback) {
        fetch("https://ipapi.co/json")
          .then(res => res.json())
          .then(data => callback(data.country_code))
          .catch(() => callback("us"));
      },
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
    });

    function setFullPhoneNumber() {
      const fullPhone = iti.getNumber(); // e.g. +2348012345678
      document.getElementById("fullPhone").value = fullPhone;
      return true; // Continue with form submission
    }
  </script>
</body>
</html>
