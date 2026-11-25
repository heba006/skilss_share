<?php
session_start();
require_once 'db.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Student';
$isAdmin = false;

if ($isLoggedIn) {
    $userStmt = $conn->prepare('SELECT name, role FROM users WHERE id = ?');
    $userStmt->execute([(int)$_SESSION['user_id']]);
    $currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);
    if ($currentUser) {
        $userName = $currentUser['name'];
        $isAdmin = ($currentUser['role'] === 'admin');
    } else {
        $isLoggedIn = false;
    }
}

$latestStmt = $conn->query(
    'SELECT m.id, m.subject, m.chapter, m.academic_year, m.created_at,
            u.name AS uploader
     FROM materials m
     LEFT JOIN users u ON u.id = m.user_id
     ORDER BY m.created_at DESC, m.id DESC
     LIMIT 3'
);
$latestMaterials = $latestStmt->fetchAll(PDO::FETCH_ASSOC);

$subjects = [
    ['icon' => 'bi bi-laptop', 'name' => 'Computer Science', 'label' => 'Coding & technology'],
    ['icon' => 'bi bi-calculator', 'name' => 'Mathematics', 'label' => 'Numbers & logic'],
    ['icon' => 'bi bi-gear', 'name' => 'Engineering', 'label' => 'Design & innovation'],
    ['icon' => 'bi bi-heart-pulse', 'name' => 'Medicine', 'label' => 'Health & science'],
    ['icon' => 'bi bi-beaker', 'name' => 'Chemistry', 'label' => 'Elements & reactions'],
    ['icon' => 'bi bi-graph-up-arrow', 'name' => 'Business', 'label' => 'Strategy & management'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Share Knowledge — Learn Together, Grow Together</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/home.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/brand-system.css">
<link rel="stylesheet" href="assets/css/unified-interface.css?v=3">

<link rel="stylesheet" href="assets/css/navbar-glass.css?v=1">
<link rel="stylesheet" href="assets/css/home-refresh.css?v=1">
<script src="assets/js/navbar-glass.js?v=1" defer></script>
</head>
<body class="home-page">
<header class="site-header">
  <div class="header-inner">
    <a href="index.php" class="brand"><span class="brand-icon"><img class="brand-symbol" src="assets/images/slide-exchange-logo-light.svg" alt=""></span> Share Knowledge</a>
    <nav class="site-nav" aria-label="Main navigation">
      <a href="index.php" class="active"><i class="bi bi-house-door" aria-hidden="true"></i> Home</a>
      <a href="browse.php"><i class="bi bi-search" aria-hidden="true"></i> Explore</a>
      <a href="upload.php"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Share material</a>
      <?php if ($isLoggedIn): ?>
        <a href="messages.php"><i class="bi bi-chat-dots" aria-hidden="true"></i> Messages</a>
        <a href="favorites.php"><i class="bi bi-bookmark-heart" aria-hidden="true"></i> Saved</a>
        <a href="profile.php"><i class="bi bi-person-circle" aria-hidden="true"></i> My account</a>
        <?php if ($isAdmin): ?><a href="admin.php"><i class="bi bi-gear" aria-hidden="true"></i> Admin</a><?php endif; ?>
      <?php endif; ?>
    </nav>
    <div class="auth">
      <?php if ($isLoggedIn): ?>
        <span class="hello">Hi, <?= e($userName) ?></span>
        <a href="logout.php" class="small-btn light"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Log out</a>
      <?php else: ?>
        <a href="login.php" class="small-btn light">Log in</a>
        <a href="signup.php" class="small-btn">Create account</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<section class="hero-wrap">
  <div class="hero">
    <div class="hero-content">
      <div class="eyebrow"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i> Yarmouk University · Student-powered learning</div>
      <h1>Share Knowledge.<br><span>Exchange Success.</span></h1>
      <p>Discover lecture slides and study materials shared by Yarmouk students. Find what you need, keep useful resources close, and help the next student succeed.</p>
      <div class="hero-actions">
        <a href="browse.php" class="hero-btn primary"><i class="bi bi-search" aria-hidden="true"></i> Explore Slides</a>
        <a href="upload.php" class="hero-btn outline"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Share Your Materials</a>
      </div>
      <div class="hero-note"><i class="bi bi-shield-check" aria-hidden="true"></i> A shared library for the Yarmouk community</div>
    </div>
    <div class="hero-scene" aria-hidden="true">
      <div class="scene-glow"></div><div class="scene-orbit"></div><div class="hero-orb"></div>
      <div class="hero-slide hero-slide-back">
        <div class="slide-topline"><span>Course presentation</span><i class="bi bi-layers-fill"></i></div>
        <div class="slide-preview-title">Ideas grow<br>when shared.</div>
        <div class="slide-preview-lines"><span></span><span></span><span></span></div>
      </div>
      <div class="hero-slide hero-slide-front">
        <div class="slide-topline"><span>Share Knowledge</span><i class="bi bi-stars"></i></div>
        <div class="slide-preview-title">Learn together.<br>Move forward.</div>
        <div class="slide-chart"><i></i><i></i><i></i><i></i><i></i></div>
      </div>
      <div class="scene-chip scene-chip-pdf"><i class="bi bi-file-earmark-pdf-fill"></i><span>Study materials<small>Slides · notes · guides</small></span></div>
      <div class="scene-chip scene-chip-exchange"><i class="bi bi-arrow-left-right"></i><span>Knowledge exchange<small>Shared by students</small></span></div>
    </div>
  </div>
  <a class="scroll-hint" href="#library-overview"><span>Scroll to explore</span><i class="bi bi-chevron-down" aria-hidden="true"></i></a>
</section>
<main class="container">
<section class="section">
    <div class="section-heading"><div><div class="tag">Start with a subject</div><h2>Explore the library</h2><p>Choose a topic to find resources shared by students.</p></div><a class="view-all" href="browse.php">View all materials <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
    <div class="subject-grid">
      <?php foreach ($subjects as $subject): ?>
        <a class="subject-card" href="browse.php?<?= e(http_build_query(['subject' => $subject['name']])) ?>">
          <div class="subject-icon"><i class="bi <?= e($subject['icon']) ?>" aria-hidden="true"></i></div>
          <div><h3><?= e($subject['name']) ?></h3><p><?= e($subject['label']) ?></p></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="section">
    <div class="section-heading"><div><div class="tag">Recently added</div><h2>New in the library</h2><p>Browse the latest resources shared by your community.</p></div><a class="view-all" href="browse.php">View all materials <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
    <?php if (!$latestMaterials): ?>
      <div class="empty"><div style="font-size:30px;color:#286cdb"><i class="bi bi-journal-plus" aria-hidden="true"></i></div><h3>Start the shared library</h3><p>No materials have been uploaded yet. Add the first resource for your classmates.</p><a href="upload.php">Share a material <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
    <?php else: ?>
      <div class="material-grid">
        <?php foreach ($latestMaterials as $material): ?>
          <article class="material-card">
            <div class="card-top"><span class="chip"><?= e($material['subject']) ?></span><time><?= e(date('M j, Y', strtotime($material['created_at']))) ?></time></div>
            <h3><?= e($material['chapter']) ?></h3>
            <p><i class="bi bi-mortarboard" aria-hidden="true"></i> <?= e($material['academic_year']) ?></p>
            <p><i class="bi bi-person" aria-hidden="true"></i> Shared by <?= e($material['uploader'] ?? 'Unknown') ?></p>
            <a class="card-link" href="view_material.php?id=<?= (int)$material['id'] ?>" target="_blank" rel="noopener noreferrer">Open material <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
<footer class="footer">© <?= date('Y') ?> Share Knowledge · A shared library for student learning.</footer>
<script src="assets/js/ui-effects.js?v=3" defer></script>
</body>
</html>
