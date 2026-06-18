<?php
// ===========================================
// GET /api/profile.php
// Protected route — JWT token লাগবে
// ===========================================
// Header:
//   Authorization: Bearer <token>
// ===========================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Method not allowed', 405);
}

// Token verify করো — invalid হলে এখানেই 401 দেবে
$authUser = requireAuth();

$db   = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT id, name, email, created_at, last_login FROM users WHERE id = ?");
$stmt->bind_param('i', $authUser['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    Response::error('User not found', 404);
}

Response::success(['user' => $user], 'Profile fetched successfully');
