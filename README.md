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
        "email": "email@gmail.com",
        "secret": "JAUHMAITSAY6D",
        "imageUrl": "this://willbegenerated.automaticaly"
    }
}
```
***To acces the image you have to use this api as image source***
src='https://api.qrserver.com/v1/create-qr-code/"imageUrl."'
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
    "email_otp": "123456",     // Email OTP received
    "auth_code": "654321",     // Google Authenticator code
    "type": "register"         // "register" for new users, "login" for existing users
}
```

**Success Response (Register):**
```json
{
    "success": true,
    "message": "Registration verification successful",
    "data": {
        "email": "john@example.com"
    }
}
```

**Success Response (Login):**
```json
{
    "success": true,
    "message": "Login verification successful",
    "data": {
        "token": "jwt.token.here"
    }
}
```

**Error Responses:**
```json
{
    "success": false,
    "error": "Invalid OTP or authentication code"
}
```
```json
{
    "success": false,
    "error": "Pending registration not found"
}
```
```json
{
    "success": false,
    "error": "User not found"
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
1. Register user account (data stored in waiting_users)
2. Set up Google Authenticator using provided secret key
3. Verify registration with email OTP and authenticator code
4. Login with email/password to receive email OTP
5. Verify login with email OTP and authenticator code

## Database Tables
### waiting_users
- Stores pending registrations awaiting verification
- Contains user info, hashed password, email OTP, and auth secret

### users
- Stores verified users
- Moved from waiting_users after successful verification
- Contains user info, hashed password, and auth secret

## Security Notes
- All endpoints require HTTPS in production
- Passwords must be at least 8 characters
- Email OTPs are hashed in database
- Auth secrets are stored in plain text (required for TOTP)
- OTP expires after 5 minutes
- Maximum 5 failed verification attempts allowed