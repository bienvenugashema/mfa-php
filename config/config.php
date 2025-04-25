<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = mysqli_connect('localhost', 'root', '', 'otp');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

