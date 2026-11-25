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

$totalMaterials = (int)$conn->query('SELECT COUNT(*) FROM materials')->fetchColumn();
$totalUsers = (int)$conn->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalReviews = (int)$conn->query('SELECT COUNT(*) FROM reviews')->fetchColumn();

$latestStmt = $conn->query(
    'SELECT m.id, m.subject, m.chapter, m.academic_year, m.created_at,
            u.name AS uploader
     FROM materials m
     LEFT JOIN users u ON u.id = m.user_id
     ORDER BY m.created_at DESC, m.id DESC
     LIMIT 6'
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
</head>
<body>
<header class="site-header">
  <div class="header-inner">
    <a href="index.php" class="brand"><span class="brand-icon"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i></span> Share Knowledge</a>
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
      <div class="eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> A study library built by students</div>
      <h1>Find your next idea.<br><span>Share what you know.</span></h1>
      <p>One library for class notes, course guides, and ideas worth keeping. Explore your subjects, discover what works, then pass useful knowledge forward.</p>
      <div class="hero-actions">
        <a href="browse.php" class="hero-btn primary"><i class="bi bi-search" aria-hidden="true"></i> Explore materials</a>
        <a href="upload.php" class="hero-btn outline"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Share a resource</a>
      </div>
    </div>
    <div class="hero-decoration" aria-hidden="true"><i class="bi bi-book-half"></i></div>
  </div>
</section>
<main class="container">
  <section class="stats" aria-label="Platform statistics">
    <div class="stat"><div class="stat-icon"><i class="bi bi-journals" aria-hidden="true"></i></div><div><strong><?= $totalMaterials ?></strong><small>Resources ready to explore</small></div></div>
    <div class="stat"><div class="stat-icon"><i class="bi bi-people" aria-hidden="true"></i></div><div><strong><?= $totalUsers ?></strong><small>Students learning together</small></div></div>
    <div class="stat"><div class="stat-icon"><i class="bi bi-star" aria-hidden="true"></i></div><div><strong><?= $totalReviews ?></strong><small>Shared opinions and ratings</small></div></div>
  </section>
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
  <section class="section">
    <div class="section-heading"><div><div class="tag">A simple place to learn together</div><h2>How it works</h2><p>Three clear steps to get the most from the community.</p></div></div>
    <div class="steps">
      <article class="step"><span class="step-number">01</span><h3>Find a resource</h3><p>Browse by subject or search the library for the course material you need.</p></article>
      <article class="step"><span class="step-number">02</span><h3>Save and learn</h3><p>Open resources, read student reviews, and save useful materials to revisit.</p></article>
      <article class="step"><span class="step-number">03</span><h3>Share what you know</h3><p>Upload your notes to help classmates and grow the shared library.</p></article>
    </div>
  </section>
  <section class="cta"><h2>Make your next study session easier.</h2><p>Explore shared course materials or add a resource for your classmates.</p><a href="<?= $isLoggedIn ? 'upload.php' : 'signup.php' ?>" class="hero-btn primary"><i class="bi <?= $isLoggedIn ? 'bi-cloud-arrow-up' : 'bi-person-plus' ?>" aria-hidden="true"></i> <?= $isLoggedIn ? 'Share a material' : 'Create your account' ?></a></section>
</main>
<footer class="footer">© <?= date('Y') ?> Share Knowledge · A shared library for student learning.</footer>
</body>
</html>
