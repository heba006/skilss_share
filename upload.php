<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Generate CSRF token
if (empty($_SESSION["upload_csrf"])) {
    $_SESSION["upload_csrf"] = bin2hex(random_bytes(32));
}

$userName = $_SESSION["user_name"] ?? "Student";

$error = $_SESSION["upload_error"] ?? "";
$success = $_SESSION["upload_success"] ?? "";

unset($_SESSION["upload_error"], $_SESSION["upload_success"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Upload Material - Share Knowledge</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/upload.css">
<link rel="stylesheet" href="assets/css/brand-system.css">
<link rel="stylesheet" href="assets/css/unified-interface.css?v=3">

<link rel="stylesheet" href="assets/css/navbar-glass.css?v=1">
    <link rel="stylesheet" href="assets/css/upload-studio.css?v=1">
    <script src="assets/js/upload-studio.js?v=1" defer></script>
<script src="assets/js/navbar-glass.js?v=1" defer></script>
</head>

<body class="upload-studio">

    <!-- Navbar -->
    <header class="navbar">

        <div class="logo">
            <span><img class="brand-symbol" src="assets/images/slide-exchange-logo-light.svg" alt=""></span>
            <h2>Share Knowledge</h2>
        </div>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="browse.php">Browse</a>
            <a href="upload.php">Upload</a>
            <a href="messages.php">Messages</a>
        </nav>

        <div class="nav-buttons">

            <span class="user-name">
                Welcome,
                <?= htmlspecialchars(
                    $userName,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </span>

            <a href="logout.php" class="logout-btn">
                Logout
            </a>

        </div>

    </header>

    <main class="upload-studio-shell">
        <div class="upload-studio-content">
            <section class="studio-intro" aria-labelledby="upload-page-title">
                <span class="studio-intro-icon" aria-hidden="true"><i class="bi bi-file-earmark-arrow-up"></i></span>
                <div>
                    <div class="studio-eyebrow">Academic community</div>
                    <h1 id="upload-page-title">Share your knowledge.</h1>
                    <p>Upload your study materials and make learning easier for everyone.</p>
                </div>
            </section>

            <div class="upload-card">
                <?php if ($error): ?>
                    <div class="upload-message upload-error" role="alert">
                        <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="upload-message upload-success" role="status">
                        <?= htmlspecialchars($success, ENT_QUOTES, "UTF-8") ?>
                    </div>
                <?php endif; ?>

                <form action="save_material.php" method="POST" enctype="multipart/form-data" class="studio-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["upload_csrf"], ENT_QUOTES, "UTF-8") ?>">
                    <section class="studio-section" aria-labelledby="material-details-heading">
                        <div class="studio-section-heading">
                            <span class="studio-section-letter">A</span>
                            <div>
                                <h2 id="material-details-heading">Material details</h2>
                                <p>Tell students what this resource covers.</p>
                            </div>
                        </div>

                        <div class="studio-field-grid">
                            <div class="form-group">
                                <label for="subject">Subject <span aria-hidden="true">*</span></label>
                                <select id="subject" name="subject" required aria-describedby="subject-error">
                                    <option value="">Select a subject</option>
                                    <option value="Mathematics">Mathematics</option>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Business">Business</option>
                                    <option value="Medicine">Medicine</option>
                                    <option value="Pharmacy">Pharmacy</option>
                                    <option value="Physics">Physics</option>
                                    <option value="Chemistry">Chemistry</option>
                                    <option value="Other">Other</option>
                                </select>
                                <small class="field-error" id="subject-error" hidden>Please select a subject.</small>
                            </div>

                            <div class="form-group">
                                <label for="academic_year">Academic year / level <span aria-hidden="true">*</span></label>
                                <input type="text" id="academic_year" name="academic_year" maxlength="100" placeholder="e.g. 2nd Year — Engineering" required aria-describedby="academic-year-error">
                                <small class="field-error" id="academic-year-error" hidden>Add the academic year or level.</small>
                            </div>

                            <div class="form-group field-span">
                                <label for="chapter">Chapter / topic <span aria-hidden="true">*</span></label>
                                <input type="text" id="chapter" name="chapter" maxlength="200" placeholder="e.g. Chapter 3 — Thermodynamics" required aria-describedby="chapter-error">
                                <small class="field-error" id="chapter-error" hidden>Add a chapter or topic.</small>
                            </div>

                            <div class="form-group field-span">
                                <label for="description">Description <span aria-hidden="true">*</span></label>
                                <textarea id="description" name="description" rows="4" maxlength="5000" placeholder="Briefly describe what students will find in this material." required aria-describedby="description-error"></textarea>
                                <div class="field-foot"><small class="field-error" id="description-error" hidden>Add a short description.</small><small class="character-count" id="description-count">0 / 5000</small></div>
                            </div>
                        </div>
                    </section>

                    <section class="studio-section attachment-section" aria-labelledby="attachment-heading">
                        <div class="studio-section-heading">
                            <span class="studio-section-letter">B</span>
                            <div>
                                <h2 id="attachment-heading">Attachment</h2>
                                <p>Choose a clear, useful file students can open easily.</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="material_file">Study material <span aria-hidden="true">*</span></label>
                            <div class="upload-dropzone" id="upload-dropzone">
                                <input class="studio-file-input" type="file" id="material_file" name="material_file" accept=".pdf,.jpg,.jpeg,.png" required aria-describedby="file-help file-error">
                                <div class="drop-empty" id="drop-empty">
                                    <span class="upload-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-up"></i></span>
                                    <p class="drop-title">Drag and drop your file here</p>
                                    <p class="drop-copy">or <button type="button" class="browse-files" id="browse-files">browse files from your device</button></p>
                                    <small id="file-help">PDF, JPG or PNG <span aria-hidden="true">·</span> Maximum 10 MB</small>
                                </div>
                                <div class="selected-file" id="selected-file" hidden>
                                    <span class="selected-file-icon" id="selected-file-icon" aria-hidden="true"><i class="bi bi-file-earmark"></i></span>
                                    <span class="selected-file-info"><strong id="selected-file-name"></strong><small id="selected-file-meta"></small></span>
                                    <button type="button" class="remove-file" id="remove-file" aria-label="Remove selected file"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                                </div>
                            </div>
                            <small class="field-error file-error" id="file-error" hidden>Choose a PDF, JPG or PNG file up to 10 MB.</small>
                        </div>
                    </section>

                    <button type="submit" class="publish-btn" id="publish-button"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i><span>Publish Material</span></button>
                    <p class="publish-note"><i class="bi bi-shield-check" aria-hidden="true"></i> Your material will be shared with the student community.</p>
                </form>
            </div>
        </div>
    </main>
<script src="assets/js/ui-effects.js?v=3" defer></script>
</body>
</html>



