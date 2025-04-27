<?php
use Firebase\JWT\JWT;

function generateJWT($userId) {
    $key = "mwimule@2025";
    $payload = [
        "user_id" => $userId,
        "iat" => time(),
        "exp" => time() + (60 * 60) // 1 hour expiry
    ];
    
    return JWT::encode($payload, $key, 'HS256');
}

function verifyJWT($token) {
    try {
        $key = "mwimule@2025";
        return JWT::decode($token, $key, ['HS256']);
    } catch (Exception $e) {
        return false;
    }
}

