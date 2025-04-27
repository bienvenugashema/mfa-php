<?php
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $password = $data['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])) {
            $otp = random_int(100000, 999999);
            $updateOtp = $conn->prepare("UPDATE users SET otp = ? WHERE email = ?");
            $updateOtp->bind_param("ss", $otp, $email);
            $updateOtp->execute();
            
            // Send email with OTP
            sendCodes($email, $conn);
            
            echo json_encode([
                'success' => true,
                'message' => 'OTP sent',
                'data' => [
                    'email' => $email
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}