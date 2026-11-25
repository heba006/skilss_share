
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Student';
if (empty($_SESSION['delete_material_csrf'])) {
    $_SESSION['delete_material_csrf'] = bin2hex(random_bytes(32));
}
$editSuccess = $_SESSION['material_edit_success'] ?? '';
unset($_SESSION['material_edit_success']);
$deleteSuccess = $_SESSION['material_delete_success'] ?? '';
$deleteError = $_SESSION['material_delete_error'] ?? '';

unset(
    $_SESSION['material_delete_success'],
    $_SESSION['material_delete_error']
);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

try {
    $stmt = $conn->prepare(
        'SELECT id, subject, chapter, academic_year,
                description, created_at
         FROM materials
         WHERE user_id = ?
         ORDER BY created_at DESC, id DESC'
    );

    $stmt->execute([$userId]);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('My Uploads: ' . $e->getMessage());
    $materials = [];
    $loadError = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Uploads - Share Knowledge</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="stylesheet" href="assets/css/my_uploads.css">
<link rel="stylesheet" href="assets/css/brand-system.css">
<link rel="stylesheet" href="assets/css/unified-interface.css?v=3">

<link rel="stylesheet" href="assets/css/navbar-glass.css?v=1">
<script src="assets/js/navbar-glass.js?v=1" defer></script>
</head>

<body>

    <header class="my-navbar">
        <div class="my-logo">
            <img class="brand-symbol" src="assets/images/slide-exchange-logo-light.svg" alt=""> Share Knowledge
        </div>

        <nav class="my-links">
            <a href="index.php">Home</a>
            <a href="browse.php">Browse</a>
            <a href="upload.php">Upload</a>
            <a href="my_uploads.php">My Uploads</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="my-page">
        <?php if ($deleteSuccess !== ''): ?>
    <div class="my-notice-success">
        <i class="bi bi-check-circle-fill" aria-hidden="true"></i> <?= e($deleteSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($deleteError !== ''): ?>
    <div class="my-notice-error">
        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i> <?= e($deleteError) ?>
    </div>
<?php endif; ?>
        <?php if ($editSuccess !== ''): ?>
    <div class="my-notice-success">
        <i class="bi bi-check-circle-fill" aria-hidden="true"></i> <?= e($editSuccess) ?>
    </div>
<?php endif; ?>

        <div class="my-header">
            <div>
                <h1><i class="bi bi-journals" aria-hidden="true"></i> My Uploads</h1>
                <p>
                    Welcome, <?= e($userName) ?>!
                    Manage your shared study materials.
                </p>
            </div>

            <a href="upload.php" class="my-btn">
                + Upload New Material
            </a>
        </div>

        <?php if (!empty($loadError)): ?>

            <div class="my-alert">
                Unable to load your materials.
                Please try again later.
            </div>

        <?php else: ?>

            <p class="my-count">
                Total uploaded materials:
                <strong><?= count($materials) ?></strong>
            </p>

            <?php if (empty($materials)): ?>

                <div class="my-empty">
                    <h2><i class="bi bi-inbox" aria-hidden="true"></i> No uploads yet</h2>
                    <p>
                        You haven't uploaded any materials yet.
                        Start sharing your knowledge!
                    </p>

                    <a href="upload.php" class="my-btn">
                        Upload Your First Material
                    </a>
                </div>

            <?php else: ?>

                <div class="my-grid">

                    <?php foreach ($materials as $material): ?>

                        <article class="my-card">

                            <span class="my-subject">
                                <?= e($material['subject']) ?>
                            </span>

                            <h3>
                                <?= e($material['chapter']) ?>
                            </h3>

                            <p class="my-year">
                                <i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i> <?= e($material['academic_year']) ?>
                            </p>

                            <p class="my-description"><?= e($material['description']) ?></p>

                            <p class="my-date">
                                <i class="bi bi-calendar3" aria-hidden="true"></i> Uploaded:
                                <?= e($material['created_at']) ?>
                            </p>

                            <div class="my-actions">
                                
<form action="delete_material.php"
      method="POST"
      onsubmit="return confirm('Are you sure you want to delete this material? This action cannot be undone.');">

    <input type="hidden"
           name="csrf_token"
           value="<?= e($_SESSION['delete_material_csrf']) ?>">

    <input type="hidden"
           name="material_id"
           value="<?= (int)$material['id'] ?>">

    <button type="submit" class="my-delete-btn">
        <i class="bi bi-trash" aria-hidden="true"></i>Delete
    </button>
</form>

                                <a class="my-view"
   href="edit_material.php?id=<?= (int)$material['id'] ?>">
    <i class="bi bi-pencil-square" aria-hidden="true"></i> Edit
</a>
                                <a class="my-view"
                                   href="view_material.php?id=<?= (int)$material['id'] ?>"
                                   target="_blank"
                                   rel="noopener noreferrer">
                                    <i class="bi bi-eye" aria-hidden="true"></i> View File
                                </a>
                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        <?php endif; ?>

    </main>

<script src="assets/js/ui-effects.js?v=3" defer></script>
</body>
</html>
