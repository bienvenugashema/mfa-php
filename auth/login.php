<?php
session_start();
include_once 'controls.php';

if(isset($_POST['login'])) {
    $email = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);

    $select = "SELECT * FROM users where email = ? AND password = ? AND is_verified = true";
    $res =mysqli_query($conn, $select);
    $row = mysqli_fetch_assoc($res);

    if($row['email'] = $email && password_verify($password, $row['password'])) {
        $_SESSION['authenticated'] = true;
        $_SESSION['user_email'] = $email;
        header("Location: dashboard.php");
    } else {
        echo "<script>alert('Invalid email or password!');</script>";
    }
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