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

$stmt = $conn->prepare('SELECT id, name, role FROM users WHERE id = ?');
$stmt->execute([(int)$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Only users with the admin role may access this page.
if (!$admin || $admin['role'] !== 'admin') {
    http_response_code(403);
    exit('Access Denied. Admins only.');
}

$totalUsers = (int)$conn->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalMaterials = (int)$conn->query('SELECT COUNT(*) FROM materials')->fetchColumn();
$totalReviews = (int)$conn->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
$totalMessages = (int)$conn->query('SELECT COUNT(*) FROM messages')->fetchColumn();
$totalFavorites = (int)$conn->query('SELECT COUNT(*) FROM favorites')->fetchColumn();

$materialsStmt = $conn->query(
    'SELECT m.id, m.subject, m.chapter, m.created_at, u.name AS uploader
     FROM materials m
     LEFT JOIN users u ON m.user_id = u.id
     ORDER BY m.created_at DESC, m.id DESC
     LIMIT 10'
);
$latestMaterials = $materialsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Share Knowledge</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .admin-container { max-width: 1150px; margin: 35px auto; padding: 0 20px; }
        .admin-header { margin-bottom: 30px; }
        .admin-header h1 { color: #10213b; margin-bottom: 8px; }
        .admin-header p { color: #64748b; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: white; border-radius: 14px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 5px 18px rgba(0,0,0,.04); }
        .stat-card .icon { font-size: 30px; margin-bottom: 15px; }
        .stat-card h2 { font-size: 32px; color: #2563eb; margin-bottom: 8px; }
        .stat-card p { color: #64748b; font-weight: 600; }
        .admin-section { background: white; border-radius: 14px; padding: 25px; border: 1px solid #e2e8f0; overflow-x: auto; }
        .admin-section h2 { color: #10213b; margin-bottom: 20px; }
        .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
        .admin-table th, .admin-table td { padding: 14px; border-bottom: 1px solid #e2e8f0; }
        .admin-table th { background: #f8fafc; color: #334155; }
        .admin-table td { color: #475569; }
        .view-btn { display: inline-block; background: #2563eb; color: white; padding: 8px 14px; border-radius: 7px; text-decoration: none; }
        .back-link { display: inline-block; margin-top: 25px; color: #2563eb; text-decoration: none; }
    </style>
<link rel="stylesheet" href="assets/css/brand-system.css">
<link rel="stylesheet" href="assets/css/unified-interface.css?v=3">

<link rel="stylesheet" href="assets/css/navbar-glass.css?v=1">
<script src="assets/js/navbar-glass.js?v=1" defer></script>
</head>
<body>
<header class="navbar">
    <div class="logo"><span><img class="brand-symbol" src="assets/images/slide-exchange-logo-light.svg" alt=""></span><h2>Share Knowledge</h2></div>
    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="browse.php">Browse</a>
        <a href="upload.php">Upload</a>
        <a href="messages.php">Messages</a>
        <a href="favorites.php">My Favorites <i class="bi bi-bookmark-heart" aria-hidden="true"></i></a>
        <a href="admin.php">Admin <i class="bi bi-shield-lock" aria-hidden="true"></i></a>
    </nav>
    <div class="nav-buttons">
        <span style="color:white;">Welcome, <?= e($admin['name']) ?></span>
        <a href="logout.php" class="signup-btn">Logout</a>
    </div>
</header>
<main class="admin-container">
    <div class="admin-header">
        <h1><i class="bi bi-shield-lock" aria-hidden="true"></i> Admin Dashboard</h1>
        <p>Manage and monitor your Share Knowledge platform.</p>
    </div>
    <div class="stats-grid">
        <div class="stat-card"><div class="icon"><i class="bi bi-people" aria-hidden="true"></i></div><h2><?= $totalUsers ?></h2><p>Total Students & Users</p></div>
        <div class="stat-card"><div class="icon"><i class="bi bi-journals" aria-hidden="true"></i></div><h2><?= $totalMaterials ?></h2><p>Total Materials</p></div>
        <div class="stat-card"><div class="icon"><i class="bi bi-star" aria-hidden="true"></i></div><h2><?= $totalReviews ?></h2><p>Total Reviews</p></div>
        <div class="stat-card"><div class="icon"><i class="bi bi-chat-dots" aria-hidden="true"></i></div><h2><?= $totalMessages ?></h2><p>Total Messages</p></div>
        <div class="stat-card"><div class="icon"><i class="bi bi-bookmark-heart" aria-hidden="true"></i></div><h2><?= $totalFavorites ?></h2><p>Total Favorites</p></div>
    </div>
    <section class="admin-section">
        <h2><i class="bi bi-folder2-open" aria-hidden="true"></i> Latest Uploaded Materials</h2>
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Subject</th><th>Chapter</th><th>Uploaded By</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($latestMaterials as $material): ?>
                <tr>
                    <td><?= (int)$material['id'] ?></td>
                    <td><?= e($material['subject']) ?></td>
                    <td><?= e($material['chapter']) ?></td>
                    <td><?= e($material['uploader'] ?? 'Unknown') ?></td>
                    <td><?= e($material['created_at']) ?></td>
                    <td><a class="view-btn" href="view_material.php?id=<?= (int)$material['id'] ?>" target="_blank" rel="noopener noreferrer">View</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$latestMaterials): ?>
                <tr><td colspan="6">No materials uploaded yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
    <a href="browse.php" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to Browse Materials</a>
</main>
<script src="assets/js/ui-effects.js?v=3" defer></script>
</body>
</html>
