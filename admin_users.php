<?php
session_start();
require_once 'db.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$adminStmt = $conn->prepare('SELECT id, name, role FROM users WHERE id = ? LIMIT 1');
$adminStmt->execute([(int)$_SESSION['user_id']]);
$admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

if (!$admin || $admin['role'] !== 'admin') {
    http_response_code(403);
    exit('Access Denied. Admins only.');
}

$usersStmt = $conn->query(
    'SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC, id DESC'
);
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
$totalUsers = count($users);
$totalAdmins = count(array_filter($users, static function ($user) {
    return $user['role'] === 'admin';
}));
$totalStudents = $totalUsers - $totalAdmins;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Share Knowledge</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin_users.css">
<link rel="stylesheet" href="assets/css/brand-system.css">
<link rel="stylesheet" href="assets/css/unified-interface.css?v=3">

<link rel="stylesheet" href="assets/css/navbar-glass.css?v=1">
<script src="assets/js/navbar-glass.js?v=1" defer></script>
</head>
<body class="midnight-users">
<header class="mu-nav">
    <a href="index.php" class="mu-brand"><span class="mu-brand-icon"><img class="brand-symbol" src="assets/images/slide-exchange-logo-light.svg" alt=""></span><span>Share Knowledge</span></a>
    <nav class="mu-links" aria-label="Main navigation">
        <a href="index.php">Home</a>
        <a href="browse.php">Browse</a>
        <a href="upload.php">Upload</a>
        <a href="messages.php">Messages</a>
        <a href="favorites.php">My Favorites <i class="bi bi-bookmark-heart" aria-hidden="true"></i></a>
        <a href="admin.php" class="active">Admin <i class="bi bi-shield-lock" aria-hidden="true"></i></a>
    </nav>
    <div class="mu-user"><span class="mu-welcome">Welcome, <strong><?= e($admin['name']) ?></strong></span><a href="logout.php" class="mu-logout">Logout</a></div>
</header>
<main class="mu-main">
    <a href="admin.php" class="mu-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to Admin Dashboard</a>
    <section class="mu-hero">
        <span class="mu-eyebrow"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i> COMMUNITY MANAGEMENT</span>
        <h1>Manage <span>Students.</span> <i class="bi bi-people" aria-hidden="true"></i></h1>
        <p>Explore registered accounts, review user roles, and keep your community organized.</p>
    </section>
    <section class="mu-overview" aria-label="Account statistics">
        <div class="mu-stat"><div class="mu-stat-top"><div class="mu-stat-icon"><i class="bi bi-people" aria-hidden="true"></i></div></div><strong><?= $totalUsers ?></strong><span>Total Users</span></div>
        <div class="mu-stat"><div class="mu-stat-top"><div class="mu-stat-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></div></div><strong><?= $totalAdmins ?></strong><span>Administrators</span></div>
        <div class="mu-stat"><div class="mu-stat-top"><div class="mu-stat-icon"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i></div></div><strong><?= $totalStudents ?></strong><span>Students</span></div>
    </section>
    <section class="mu-panel">
        <div class="mu-panel-head">
            <div><h2>Registered Accounts</h2><p>All students and administrators in your platform.</p></div>
            <input id="userSearch" class="mu-search" type="search" placeholder="Search by name or email..." aria-label="Search registered accounts">
        </div>
        <div class="mu-table-scroll">
            <table class="mu-table">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
                <tbody id="usersBody">
                    <?php foreach ($users as $user): ?>
                    <tr class="mu-user-row">
                        <td>#<?= (int)$user['id'] ?></td>
                        <td><div class="mu-person"><span class="mu-avatar"><?= e(mb_strtoupper(mb_substr((string)$user['name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span><span class="mu-person-name"><?= e($user['name']) ?><?php if ((int)$user['id'] === (int)$admin['id']): ?><span class="mu-you">YOU</span><?php endif; ?></span></div></td>
                        <td class="mu-email"><?= e($user['email']) ?></td>
                        <td><span class="mu-role <?= $user['role'] === 'admin' ? 'mu-role-admin' : 'mu-role-student' ?>"><?= $user['role'] === 'admin' ? '<i class="bi bi-shield-lock" aria-hidden="true"></i> Admin' : '<i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i> Student' ?></span></td>
                        <td><?= e($user['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$users): ?><tr><td colspan="5" class="mu-empty">No registered accounts found.</td></tr><?php endif; ?>
                    <tr id="noSearchResults" class="mu-hidden"><td colspan="5" class="mu-empty">No accounts match your search.</td></tr>
                </tbody>
            </table>
        </div>
    </section>
    <p class="mu-footer">Share Knowledge · Administrator workspace</p>
</main>
<script>
(function () {
    const input = document.getElementById('userSearch');
    const rows = Array.from(document.querySelectorAll('.mu-user-row'));
    const empty = document.getElementById('noSearchResults');
    input.addEventListener('input', function () {
        const query = input.value.trim().toLocaleLowerCase();
        let visible = 0;
        rows.forEach(function (row) {
            const matched = row.textContent.toLocaleLowerCase().includes(query);
            row.classList.toggle('mu-hidden', !matched);
            if (matched) visible++;
        });
        empty.classList.toggle('mu-hidden', visible !== 0 || rows.length === 0);
    });
})();
</script>
<script src="assets/js/ui-effects.js?v=3" defer></script>
</body>
</html>
