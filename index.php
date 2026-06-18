<?php
// ===========================================
// index.php — Project Dashboard
// Browser এ http://localhost:8000/ খুললে
// এই page দেখাবে — DB connected কিনা,
// কী কী API আছে, সব দেখা যাবে
// ===========================================

require_once __DIR__ . '/config/database.php';

$dbStatus  = false;
$dbError   = '';
$userCount = 0;
$users     = [];

try {
    $db = Database::getInstance()->getConnection();
    $dbStatus = true;

    // User count নাও
    $result = $db->query("SELECT COUNT(*) as total FROM users");
    $userCount = $result->fetch_assoc()['total'];

    // সব user দেখাও (password ছাড়া)
    $result = $db->query("SELECT id, name, email, created_at, last_login FROM users ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} catch (Exception $e) {
    $dbError = $e->getMessage();
}

$phpmyadminLink = "http://localhost/phpmyadmin/index.php?route=/database/structure&db=" . DB_NAME;
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PHP Auth API — Dashboard</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', system-ui, sans-serif;
        background: #0f172a;
        color: #e2e8f0;
        padding: 40px 20px;
    }
    .container { max-width: 900px; margin: 0 auto; }
    h1 { font-size: 28px; margin-bottom: 6px; }
    .subtitle { color: #94a3b8; margin-bottom: 30px; }
    .card {
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
    }
    .status-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }
    .dot {
        width: 12px; height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .dot.green { background: #22c55e; box-shadow: 0 0 8px #22c55e; }
    .dot.red { background: #ef4444; box-shadow: 0 0 8px #ef4444; }
    .card h2 { font-size: 16px; margin-bottom: 14px; color: #f1f5f9; }
    .info-grid {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 8px 16px;
        font-size: 14px;
    }
    .info-grid .label { color: #94a3b8; }
    .info-grid .value { color: #e2e8f0; font-family: monospace; }
    a.link-btn {
        display: inline-block;
        margin-top: 14px;
        background: #3b82f6;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
    }
    a.link-btn:hover { background: #2563eb; }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        margin-top: 10px;
    }
    th, td {
        text-align: left;
        padding: 10px 12px;
        border-bottom: 1px solid #334155;
    }
    th { color: #94a3b8; font-weight: 500; font-size: 13px; }
    .endpoint-list { display: flex; flex-direction: column; gap: 10px; }
    .endpoint {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 8px;
        padding: 10px 14px;
        font-family: monospace;
        font-size: 13px;
    }
    .method {
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        flex-shrink: 0;
    }
    .method.post { background: #1e3a5f; color: #60a5fa; }
    .method.get { background: #1e3a2f; color: #4ade80; }
    .path { color: #e2e8f0; }
    .desc { color: #64748b; margin-left: auto; font-family: 'Segoe UI', sans-serif; }
    .error-box {
        background: #2a1215;
        border: 1px solid #7f1d1d;
        color: #fca5a5;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-family: monospace;
        margin-top: 10px;
    }
    .empty { color: #64748b; font-size: 14px; padding: 10px 0; }
</style>
</head>
<body>
<div class="container">

    <h1>🔐 PHP Auth API</h1>
    <p class="subtitle">Local Development Dashboard</p>

    <!-- Database Status -->
    <div class="card">
        <h2>Database Connection</h2>
        <div class="status-row">
            <span class="dot <?= $dbStatus ? 'green' : 'red' ?>"></span>
            <strong><?= $dbStatus ? 'Connected' : 'Connection Failed' ?></strong>
        </div>

        <?php if ($dbStatus): ?>
        <div class="info-grid">
            <span class="label">Host</span><span class="value"><?= htmlspecialchars(DB_HOST) ?></span>
            <span class="label">Database</span><span class="value"><?= htmlspecialchars(DB_NAME) ?></span>
            <span class="label">Total Users</span><span class="value"><?= $userCount ?></span>
        </div>
        <a class="link-btn" href="<?= htmlspecialchars($phpmyadminLink) ?>" target="_blank">
            phpMyAdmin এ database দেখো →
        </a>
        <?php else: ?>
        <div class="error-box"><?= htmlspecialchars($dbError) ?></div>
        <p style="margin-top:10px; font-size:13px; color:#94a3b8;">
            <code>config/database.php</code> ফাইলে DB_HOST, DB_USER, DB_PASS, DB_NAME ঠিক করে দেখো।
        </p>
        <?php endif; ?>
    </div>

    <!-- API Endpoints -->
    <div class="card">
        <h2>API Endpoints</h2>
        <div class="endpoint-list">
            <div class="endpoint"><span class="method post">POST</span><span class="path">/api/signup.php</span><span class="desc">নতুন account তৈরি</span></div>
            <div class="endpoint"><span class="method post">POST</span><span class="path">/api/login.php</span><span class="desc">Login → JWT token</span></div>
            <div class="endpoint"><span class="method post">POST</span><span class="path">/api/forgot-password.php</span><span class="desc">OTP পাঠায়</span></div>
            <div class="endpoint"><span class="method post">POST</span><span class="path">/api/reset-password.php</span><span class="desc">OTP দিয়ে password reset</span></div>
            <div class="endpoint"><span class="method get">GET</span><span class="path">/api/profile.php</span><span class="desc">Protected — token লাগবে</span></div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <h2>Registered Users (<?= $userCount ?>)</h2>
        <?php if (empty($users)): ?>
            <p class="empty">এখনো কোনো user নেই। signup.php দিয়ে একটা account বানাও।</p>
        <?php else: ?>
        <table>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Created</th><th>Last Login</th>
            </tr>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= $u['created_at'] ?></td>
                <td><?= $u['last_login'] ?? '—' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
