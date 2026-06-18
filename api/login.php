<?php
// ===========================================
// POST /api/login.php
// User login API — JWT token return করবে
// ===========================================
// Request Body (JSON):
// {
//   "email": "rahim@example.com",
//   "password": "mypassword123"
// }
// ===========================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

$input    = json_decode(file_get_contents('php://input'), true);
$email    = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

// --- Validation ---
if (empty($email) || empty($password)) {
    Response::error('Email and password are required', 422);
}

// --- Database ---
$db   = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

// User নেই বা password ভুল — same message (security best practice)
if (!$user || !password_verify($password, $user['password'])) {
    Response::error('Invalid email or password', 401);
}

// JWT Token তৈরি করো
$token = JWT::generate([
    'user_id' => $user['id'],
    'email'   => $user['email'],
    'name'    => $user['name']
]);

// Last login update করো
$upd = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$upd->bind_param('i', $user['id']);
$upd->execute();

Response::success([
    'token' => $token,
    'user'  => [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email']
    ]
], 'Login successful');
