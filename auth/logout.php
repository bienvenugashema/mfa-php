<?php
require_once 'controls.php';
session_destroy();
header("Location: /mfa-php/auth/login");
exit();