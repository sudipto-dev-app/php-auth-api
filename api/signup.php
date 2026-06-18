<?php
// ===========================================
// POST /api/signup.php
// নতুন user register করার API
// ===========================================
// Request Body (JSON):
// {
//   "name": "Rahim",
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
require_once __DIR__ . '/../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

// Input নাও
$input = json_decode(file_get_contents('php://input'), true);

$name     = trim($input['name'] ?? '');
$email    = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

// --- Validation ---
$errors = [];

if (empty($name)) {
    $errors['name'] = 'Name is required';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Valid email is required';
}

if (strlen($password) < 6) {
    $errors['password'] = 'Password must be at least 6 characters';
}

if (!empty($errors)) {
    Response::error('Validation failed', 422, $errors);
}

// --- Database ---
$db   = Database::getInstance()->getConnection();

// Email already exists কিনা চেক করো
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    Response::error('Email already registered', 409);
}
$stmt->close();

// Password hash করো (plain text কখনো store করো না)
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// User insert করো
$stmt = $db->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param('sss', $name, $email, $hashedPassword);

if ($stmt->execute()) {
    $userId = $db->insert_id;
    Response::success(
        ['user_id' => $userId, 'name' => $name, 'email' => $email],
        'Account created successfully',
        201
    );
} else {
    Response::error('Registration failed. Please try again.', 500);
}
