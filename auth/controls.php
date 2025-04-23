<?php

function sanitizeInput($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if(isset($_POST['register'])) {
    $names = sanitizeInput($_POST['names']);
    $email = filter_var(sanitizeInput($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = sanitizeInput($_POST['phone']);
    $password = sanitizeInput($_POST['password']);
    
}

if(isset($_POST['login'])) {
    
    
}