<?php
// ===========================================
// POST /api/forgot-password.php
// Password reset OTP জেনারেট করে ৫ মিনিটের জন্য সেট করা
// ===========================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/response.php';

define('DEV_MODE', true); // Production এ false করে দেবেন

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$email = strtolower(trim($input['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Response::error('Valid email is required', 422);
}

$db = Database::getInstance()->getConnection();

// ইউজার আছে কি না চেক
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    // নিরাপত্তা: ইউজার থাকুক বা না থাকুক একই মেসেজ দেখানো ভালো
    Response::success([], 'If this email exists, a reset OTP has been sent.');
}

// ৬ ডিজিটের OTP জেনারেট
$otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

// সময় হিসাব (বর্তমান সময় + ৫ মিনিট)
// PHP-এর DateTime ব্যবহার করে নির্ভুল সময় তৈরি করছি
$now = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
$expiresAt = clone $now;
$expiresAt->modify('+5 minutes'); 
$formattedExpires = $expiresAt->format('Y-m-d H:i:s');

// পুরনো OTP ডিলিট করা
$del = $db->prepare("DELETE FROM password_resets WHERE email = ?");
$del->bind_param('s', $email);
$del->execute();

// নতুন OTP ইনসার্ট করা
$ins = $db->prepare("INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, ?)");
$ins->bind_param('sss', $email, $otp, $formattedExpires);
$ins->execute();

// রেসপন্স
$response = ['message' => 'OTP sent to your email'];
if (DEV_MODE) {
    $response['dev_otp'] = $otp; // ডেভেলপমেন্টের সুবিধার জন্য
}

Response::success($response, 'If this email exists, a reset OTP has been sent.');