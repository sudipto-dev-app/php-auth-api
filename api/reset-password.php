<?php
// ===========================================
// POST /api/reset-password.php
// OTP দিয়ে নতুন password set করার API
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

$input        = json_decode(file_get_contents('php://input'), true);
$email        = strtolower(trim($input['email'] ?? ''));
$otp          = trim($input['otp'] ?? '');
$newPassword  = $input['new_password'] ?? '';

// --- Validation ---
$errors = [];
if (empty($email))       $errors['email'] = 'Email is required';
if (empty($otp))         $errors['otp']   = 'OTP is required';
if (strlen($newPassword) < 6) $errors['new_password'] = 'Password must be at least 6 characters';

if (!empty($errors)) {
    Response::error('Validation failed', 422, $errors);
}

$db = Database::getInstance()->getConnection();

// --- সমাধান: PHP থেকে বর্তমান সময় নেওয়া ---
// ডাটাবেসের NOW() ব্যবহার না করে PHP-এর সময় ব্যবহার করছি যাতে টাইমজোন মিল থাকে
$currentTime = date('Y-m-d H:i:s');

// OTP চেক করো (expires_at > current_time)
$stmt = $db->prepare(
    "SELECT id FROM password_resets 
     WHERE email = ? AND otp = ? AND expires_at > ? 
     LIMIT 1"
);
$stmt->bind_param('sss', $email, $otp, $currentTime);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // যদি ডাটাবেস এরর না থাকে, তবে এর মানে OTP হয় ভুল, না হয় এক্সপায়ার হয়ে গেছে
    Response::error('Invalid or expired OTP', 400);
}
$stmt->close();

// নতুন password hash করো
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

// User এর password update করো
$upd = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?");
$upd->bind_param('ss', $hashedPassword, $email);
$upd->execute();

if ($upd->affected_rows === 0) {
    Response::error('User not found', 404);
}

// ব্যবহার হয়ে যাওয়া OTP মুছে দাও
$del = $db->prepare("DELETE FROM password_resets WHERE email = ?");
$del->bind_param('s', $email);
$del->execute();

Response::success([], 'Password reset successful. Please login with your new password.');