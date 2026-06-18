Markdown
# 🔐 PHP Auth API

Complete PHP REST API with MySQL — Login, Signup & Forgot Password.

---

## 📁 Project Structure

auth-api/
├── config/
│   └── database.php        ← DB credentials
├── api/
│   ├── signup.php          ← POST /api/signup.php
│   ├── login.php           ← POST /api/login.php
│   ├── forgot-password.php ← POST /api/forgot-password.php
│   ├── reset-password.php  ← POST /api/reset-password.php
│   └── profile.php         ← GET  /api/profile.php (protected)
├── middleware/
│   └── auth.php            ← JWT token verification
├── utils/
│   ├── jwt.php             ← JWT generate/verify
│   └── response.php        ← JSON response helper
├── database.sql            ← MySQL schema
├── .htaccess               ← Apache config
└── README.md


---

## ⚡ Setup (5 Minutes)

### Step 1 — Create Database
```sql
-- Run in MySQL:
SOURCE /path/to/auth-api/database.sql;
Step 2 — Set DB credentials
In the config/database.php file:

PHP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'auth_api_db');
Step 3 — Change JWT Secret
In utils/jwt.php:

PHP
define('JWT_SECRET', 'random_secret_key');
Step 4 — Deployment
Place the auth-api/ folder in your server's public_html/ or htdocs/ directory.

📡 API Endpoints
1. Signup
POST /api/signup.php

Request:
{
  "name": "Rahim",
  "email": "rahim@example.com",
  "password": "mypassword123"
}

Response (201):
{
  "success": true,
  "message": "Account created successfully",
  "data": {
    "user_id": 1,
    "name": "Rahim",
    "email": "rahim@example.com"
  }
}
2. Login
POST /api/login.php

Request:
{
  "email": "rahim@example.com",
  "password": "mypassword123"
}

Response (200):
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ...",    ← Save this token in your app
    "user": {
      "id": 1,
      "name": "Rahim",
      "email": "rahim@example.com"
    }
  }
}
3. Forgot Password (OTP)
POST /api/forgot-password.php

Request:
{
  "email": "rahim@example.com"
}

Response (200) — OTP visible in dev mode:
{
  "success": true,
  "data": {
    "dev_otp": "482913"    ← Will not exist in production
  }
}
4. Reset Password (New password with OTP)
POST /api/reset-password.php

Request:
{
  "email": "rahim@example.com",
  "otp": "482913",
  "new_password": "newpassword456"
}

Response (200):
{
  "success": true,
  "message": "Password reset successful"
}
5. Profile (Protected — requires token)
GET /api/profile.php

Header:
  Authorization: Bearer eyJ...

Response (200):
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Rahim",
      "email": "rahim@example.com",
      "created_at": "2024-01-01 10:00:00",
      "last_login": "2024-06-17 15:30:00"
    }
  }
}
📱 Implementation in App
Flutter / Dart
Dart
// Login
final response = await http.post(
  Uri.parse('[https://yoursite.com/api/login.php](https://yoursite.com/api/login.php)'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({'email': email, 'password': password}),
);
final data = jsonDecode(response.body);
final token = data['data']['token']; // Save this in SharedPreferences

// Protected API call
final profileRes = await http.get(
  Uri.parse('[https://yoursite.com/api/profile.php](https://yoursite.com/api/profile.php)'),
  headers: {'Authorization': 'Bearer $token'},
);
React Native / JavaScript
JavaScript
// Login
const res = await fetch('[https://yoursite.com/api/login.php](https://yoursite.com/api/login.php)', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password })
});
const data = await res.json();
const token = data.data.token; // Save in AsyncStorage

// Protected API
const profile = await fetch('[https://yoursite.com/api/profile.php](https://yoursite.com/api/profile.php)', {
  headers: { 'Authorization': `Bearer ${token}` }
});
🔒 Security Features
✅ Password bcrypt hashing (never stores plain text)

✅ JWT token authentication

✅ SQL Injection protection (prepared statements)

✅ OTP expiry (1 hour)

✅ config/ folder access blocked

✅ Consistent error messages for security

📧 Production Email Setup
Configure PHPMailer in forgot-password.php:

Bash
composer require phpmailer/phpmailer
PHP
use PHPMailer\PHPMailer\PHPMailer;
$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'your@gmail.com';
$mail->Password = 'app_password';
$mail->Port = 587;
$mail->setFrom('your@gmail.com');
$mail->addAddress($email);
$mail->Subject = 'Password Reset OTP';
$mail->Body = "Your OTP: $otp";
$mail->send();
Note: Ensure DEV_MODE is set to false in production!