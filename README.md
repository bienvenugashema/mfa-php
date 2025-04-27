# MFA Authentication API Documentation

## Base URL
```
http://localhost/mfa-php/api
```

## Endpoints

### 1. Register User
**Endpoint:** `/register`  
**Method:** POST  
**Content-Type:** application/json

**Request Body:**
```json
{
    "names": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "password": "securepassword123"
}
```

**Success Response:**
```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "email": "john@example.com",
        "secret": "ABCDEFGHIJKLMNOP" // Google Authenticator secret key
    }
}
```

**Error Response:**
```json
{
    "error": "User already exists"
}
```

### 2. Login
**Endpoint:** `/login`  
**Method:** POST  
**Content-Type:** application/json

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "securepassword123"
}
```

**Success Response:**
```json
{
    "success": true,
    "message": "OTP generated",
    "data": {
        "email": "john@example.com"
    }
}
```

**Error Response:**
```json
{
    "error": "Invalid credentials"
}
```

### 3. Verify MFA
**Endpoint:** `/verify`  
**Method:** POST  
**Content-Type:** application/json

**Request Body:**
```json
{
    "email": "john@example.com",
    "email_otp": "123456",     // Email OTP received after login
    "auth_code": "654321"      // Google Authenticator code
}
```

**Success Response:**
```json
{
    "success": true,
    "message": "Verification successful",
    "data": {
        "token": "jwt.token.here"
    }
}
```

**Error Response:**
```json
{
    "error": "Invalid OTP or authentication code"
}
```

## Error Codes
- 200: Success
- 400: Bad Request (Invalid input)
- 401: Unauthorized (Invalid credentials)
- 404: Not Found
- 405: Method Not Allowed
- 500: Server Error

## Authentication Flow
1. Register user account
2. Set up Google Authenticator using provided secret key
3. Login with email/password to receive email OTP
4. Verify using both email OTP and Google Authenticator code

## Security Notes
- All endpoints require HTTPS in production
- Passwords must be at least 8 characters
- OTP expires after 5 minutes
- Maximum 5 failed verification attempts allowed