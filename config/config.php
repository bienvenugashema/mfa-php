<?php  
session_start();
$conn = mysqli_connect("localhost", "root", "", "otp") or die("Connection failed: " . mysqli_connect_error());

