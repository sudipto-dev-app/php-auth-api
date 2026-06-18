<?php
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../utils/response.php';

// ===========================================
// Auth Middleware
// Protected routes এ এই middleware চলবে
// App থেকে Bearer token পাঠাতে হবে
// ===========================================

function requireAuth() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
        Response::error('Authorization token missing', 401);
    }

    $token = substr($authHeader, 7); // "Bearer " সরিয়ে token নাও
    $decoded = JWT::verify($token);

    if (!$decoded) {
        Response::error('Invalid or expired token', 401);
    }

    return $decoded; // user info return করবে
}
