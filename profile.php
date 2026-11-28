<?php

session_start();

require_once 'db.php';

if (!isset($_SESSION['user_id'])) {

    header('Location: login.php');

    exit;

}

function e($value) {

    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

}

$userId = (int)$_SESSION['user_id'];

$stmt = $conn->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');

$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {

    $_SESSION = [];

    session_destroy();

    header('Location: login.php');

    exit;

}

$stmt = $conn->prepare('SELECT id, subject, chapter, academic_year, description, created_at FROM materials WHERE user_id = ? ORDER BY created_at DESC, id DESC');

$stmt->execute([$userId]);

$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare('SELECT m.id, m.subject, m.chapter, m.academic_year, m.description, m.created_at, u.name AS uploader FROM favorites f JOIN materials m ON m.id = f.material_id LEFT JOIN users u ON u.id = m.user_id WHERE f.user_id = ? ORDER BY m.created_at DESC, m.id DESC');

$stmt->execute([$userId]);

$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');

$stmt->execute([$userId]);

$totalReviews = (int)$stmt->fetchColumn();

$totalMaterials = count($materials);

$totalFavorites = count($favorites);

$memberSince = !empty($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : '—';

$initial = function_exists('mb_substr') ? mb_substr(trim($user['name']), 0, 1, 'UTF-8') : substr(trim($user['name']), 0, 1);

$roleLabel = ($user['role'] ?? '') === 'admin' ? 'Administrator' : 'Student';

?>

<!DOCTYPE html>

<html lang="en">

<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">

<title>My Space — Share Knowledge</title>

<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/profile.css">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/brand-system.css">
<link rel="stylesheet" href="assets/css/unified-interface.css?v=3">
<link rel="stylesheet" href="assets/css/profile-dashboard.css?v=2">

<link rel="stylesheet" href="assets/css/navbar-glass.css?v=1">
<script src="assets/js/navbar-glass.js?v=1" defer></script>
</head>

<body class="profile">

<header class="topbar">

<a href="index.php" class="brand"><span class="brand-mark"><img class="brand-symbol" src="assets/images/slide-exchange-logo-light.svg" alt=""></span>Share Knowledge</a>

<nav class="topnav" aria-label="Main navigation">

<a href="index.php">Home</a>

<a href="browse.php">Explore</a>

<a href="upload.php">Upload</a>

<a href="my_uploads.php">My Uploads</a>

<a href="messages.php">Messages</a>

<a href="favorites.php">Favorites</a>

<a href="profile.php" class="current">My Profile</a>

<?php if (($user['role'] ?? '') === 'admin'): ?><a href="admin.php">Admin</a><?php endif; ?>

<a href="logout.php" class="logout">Log out</a>

</nav>

</header>

<main class="page profile-dashboard">

<div class="intro"><div><div class="eyebrow">Your student dashboard</div><h1>My Profile <span aria-hidden="true"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i></span></h1><p>Your own corner to learn, share, and grow.</p></div><div class="date-pill"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i> Share Knowledge Community</div></div>

<section class="hero" aria-label="Profile summary">

<div class="hero-inner">

<div class="identity">

<div class="avatar-wrap"><span><?= e($initial) ?></span></div>

<div><div class="hello">Welcome back</div><h2><?= e($user['name']) ?></h2><p><i class="bi bi-envelope" aria-hidden="true"></i> <?= e($user['email']) ?></p><span class="role-pill"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i> <?= e($roleLabel) ?> Member</span></div>

</div>

<div class="hero-cta"><a href="upload.php" class="btn-white"><i class="bi bi-plus-lg" aria-hidden="true"></i> Share a Material</a><a href="my_uploads.php" class="btn-ghost"><i class="bi bi-pencil-square" aria-hidden="true"></i> Manage Uploads</a></div>

</div>

</section>

<div class="stats">

<div class="stat"><div class="stat-emoji stat-icon-material"><i class="bi bi-journals" aria-hidden="true"></i></div><div><strong><?= $totalMaterials ?></strong><small>Materials Shared</small></div></div>

<div class="stat"><div class="stat-emoji stat-icon-favorites"><i class="bi bi-bookmark-heart" aria-hidden="true"></i></div><div><strong><?= $totalFavorites ?></strong><small>Saved Favorites</small></div></div>

<div class="stat"><div class="stat-emoji stat-icon-reviews"><i class="bi bi-star-fill" aria-hidden="true"></i></div><div><strong><?= $totalReviews ?></strong><small>Reviews Written</small></div></div>

</div>

<div class="content-grid">

<div>

<section class="panel">

<div class="panel-heading"><div><h2><span class="dashboard-icon icon-blue"><i class="bi bi-journals" aria-hidden="true"></i></span> My Contributions</h2><p>Study resources you've shared with the community</p></div><a href="my_uploads.php" class="text-link">Manage uploads <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>

<?php if (!$materials): ?>

<div class="empty"><div class="empty-symbol"><i class="bi bi-journal-bookmark" aria-hidden="true"></i></div><h3>Your journey starts here</h3><p>You haven't uploaded materials from this account yet. Share your first resource and help someone learn.</p><a href="upload.php" class="purple-btn"><i class="bi bi-plus-lg" aria-hidden="true"></i> Upload a Material</a></div>

<?php else: ?>

<?php foreach ($materials as $material): ?>

<article class="material"><div class="material-head"><span class="chip"><?= e($material['subject']) ?></span><time><?= e(date('M j, Y', strtotime($material['created_at']))) ?></time></div><h3><?= e($material['chapter']) ?></h3><p><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i> <?= e($material['academic_year']) ?></p><p><?= nl2br(e($material['description'])) ?></p><a class="view" href="view_material.php?id=<?= (int)$material['id'] ?>" target="_blank" rel="noopener noreferrer">View material <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></article>

<?php endforeach; ?>

<?php endif; ?>

</section>

<section class="panel">

<div class="panel-heading"><div><h2><span class="dashboard-icon icon-purple"><i class="bi bi-bookmark-heart" aria-hidden="true"></i></span> Saved for Later</h2><p>Materials you bookmarked to revisit</p></div><a href="favorites.php" class="text-link">All favorites <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>

<?php if (!$favorites): ?>

<div class="empty"><div class="empty-symbol"><i class="bi bi-bookmark-heart" aria-hidden="true"></i></div><h3>Build your study collection</h3><p>Save useful materials from the library, and they'll appear here automatically.</p><a href="browse.php" class="purple-btn"><i class="bi bi-search" aria-hidden="true"></i> Explore Materials</a></div>

<?php else: ?>

<?php foreach ($favorites as $material): ?>

<article class="material"><div class="material-head"><span class="chip"><?= e($material['subject']) ?></span><time><?= e(date('M j, Y', strtotime($material['created_at']))) ?></time></div><h3><?= e($material['chapter']) ?></h3><p>Shared by <?= e($material['uploader'] ?? 'Unknown') ?> · <?= e($material['academic_year']) ?></p><a class="view" href="view_material.php?id=<?= (int)$material['id'] ?>" target="_blank" rel="noopener noreferrer">Open saved material <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></article>

<?php endforeach; ?>

<?php endif; ?>

</section>

</div>

<aside class="side-stack">

<section class="panel">

<div class="panel-heading"><div><h2><span class="dashboard-icon icon-lavender"><i class="bi bi-person" aria-hidden="true"></i></span> About Me</h2><p>Your account information</p></div></div>

<div class="detail"><div class="detail-icon icon-profile"><i class="bi bi-person" aria-hidden="true"></i></div><div><span>Full name</span><strong><?= e($user['name']) ?></strong></div></div>

<div class="detail"><div class="detail-icon icon-email"><i class="bi bi-envelope" aria-hidden="true"></i></div><div><span>Email address</span><strong><?= e($user['email']) ?></strong></div></div>

<div class="detail"><div class="detail-icon icon-role"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></div><div><span>Account type</span><strong><?= e($roleLabel) ?></strong></div></div>

<div class="detail"><div class="detail-icon icon-date"><i class="bi bi-calendar3" aria-hidden="true"></i></div><div><span>Member since</span><strong><?= e($memberSince) ?></strong></div></div>

</section>

<section class="panel">

<div class="panel-heading"><div><h2><span class="dashboard-icon icon-teal"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i></span> Quick Access</h2><p>Everything you need, one click away</p></div></div>

<div class="quicklinks">
<a href="upload.php"><span class="quick-label"><span class="quick-icon quick-upload"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i></span><span>Upload Material</span></span><i class="bi bi-arrow-up-right quick-arrow" aria-hidden="true"></i></a>
<a href="my_uploads.php"><span class="quick-label"><span class="quick-icon quick-library"><i class="bi bi-collection" aria-hidden="true"></i></span><span>My Uploads</span></span><i class="bi bi-arrow-up-right quick-arrow" aria-hidden="true"></i></a>
<a href="browse.php"><span class="quick-label"><span class="quick-icon quick-browse"><i class="bi bi-search" aria-hidden="true"></i></span><span>Browse Library</span></span><i class="bi bi-arrow-up-right quick-arrow" aria-hidden="true"></i></a>
<a href="favorites.php"><span class="quick-label"><span class="quick-icon quick-favorite"><i class="bi bi-bookmark-heart" aria-hidden="true"></i></span><span>My Favorites</span></span><i class="bi bi-arrow-up-right quick-arrow" aria-hidden="true"></i></a>
<a href="messages.php"><span class="quick-label"><span class="quick-icon quick-message"><i class="bi bi-chat-dots" aria-hidden="true"></i></span><span>Messages</span></span><i class="bi bi-arrow-up-right quick-arrow" aria-hidden="true"></i></a>
</div>
</section>

</aside>

</div>

<div class="footer"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i> Share Knowledge — Learning is better together.</div>

</main>

<script src="assets/js/ui-effects.js?v=3" defer></script>
</body>

</html>
