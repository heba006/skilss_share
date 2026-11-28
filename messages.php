<?php

session_start();

require_once "db.php";



if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit;

}



$userId = (int) $_SESSION["user_id"];



function e($value) {

    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");

}



$stmt = $conn->prepare("SELECT id, name FROM users WHERE id = ?");

$stmt->execute([$userId]);

$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {

    header("Location: logout.php");

    exit;

}



// Handle sending before marking any messages as read.

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $receiverId = filter_input(INPUT_POST, "receiver_id", FILTER_VALIDATE_INT);

    $message = trim($_POST["message"] ?? "");

    $token = (string) ($_POST["csrf_token"] ?? "");



    if (!hash_equals($_SESSION["messages_csrf"] ?? "", $token)) {

        http_response_code(403);

        exit("Invalid request. Refresh the page and try again.");

    }



    if (!$receiverId || $receiverId === $userId || $message === "" || mb_strlen($message, "UTF-8") > 2000) {

        $_SESSION["message_error"] = "Invalid message or recipient.";

        header("Location: messages.php");

        exit;

    }



    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");

    $stmt->execute([$receiverId]);

    if (!$stmt->fetch()) {

        $_SESSION["message_error"] = "Student not found.";

        header("Location: messages.php");

        exit;

    }



    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");

    $stmt->execute([$userId, $receiverId, $message]);

    header("Location: messages.php?user=" . $receiverId);

    exit;

}



if (empty($_SESSION["messages_csrf"])) {

    $_SESSION["messages_csrf"] = bin2hex(random_bytes(32));

}



$selectedId = filter_input(INPUT_GET, "user", FILTER_VALIDATE_INT);

$selectedUser = null;

if ($selectedId && $selectedId !== $userId) {

    $stmt = $conn->prepare("SELECT id, name FROM users WHERE id = ?");

    $stmt->execute([$selectedId]);

    $selectedUser = $stmt->fetch(PDO::FETCH_ASSOC);

}



// Opening a conversation marks only incoming messages from that student as read.

if ($selectedUser) {

    $stmt = $conn->prepare(

        "UPDATE messages SET is_read = 1

         WHERE sender_id = ? AND receiver_id = ? AND is_read = 0"

    );

    $stmt->execute([$selectedId, $userId]);

}



// Count unread messages per sender.

$stmt = $conn->prepare(

    "SELECT sender_id, COUNT(*) AS unread_count

     FROM messages

     WHERE receiver_id = ? AND is_read = 0

     GROUP BY sender_id"

);

$stmt->execute([$userId]);

$unreadBySender = [];

$totalUnread = 0;

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {

    $count = (int) $row["unread_count"];

    $unreadBySender[(int) $row["sender_id"]] = $count;

    $totalUnread += $count;

}



$stmt = $conn->prepare(
    "SELECT u.id, u.name, u.email,
            (SELECT m.message FROM messages m
             WHERE (m.sender_id = u.id AND m.receiver_id = ?)
                OR (m.sender_id = ? AND m.receiver_id = u.id)
             ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_message,
            (SELECT m.created_at FROM messages m
             WHERE (m.sender_id = u.id AND m.receiver_id = ?)
                OR (m.sender_id = ? AND m.receiver_id = u.id)
             ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_message_at
     FROM users u WHERE u.id != ? ORDER BY last_message_at DESC, u.name ASC"
);

$stmt->execute([$userId, $userId, $userId, $userId, $userId]);

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);



$messages = [];

if ($selectedUser) {

    $stmt = $conn->prepare(

        "SELECT id, sender_id, receiver_id, message, created_at

         FROM messages

         WHERE (sender_id = ? AND receiver_id = ?)

            OR (sender_id = ? AND receiver_id = ?)

         ORDER BY created_at ASC, id ASC"

    );

    $stmt->execute([$userId, $selectedId, $selectedId, $userId]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

}



$error = $_SESSION["message_error"] ?? "";

unset($_SESSION["message_error"]);

?>

<!DOCTYPE html>

<html lang="en">

<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Messages - Share Knowledge</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/messages.css">

<link rel="stylesheet" href="assets/css/brand-system.css">
<link rel="stylesheet" href="assets/css/unified-interface.css?v=3">

<link rel="stylesheet" href="assets/css/navbar-glass.css?v=1">
<link rel="stylesheet" href="assets/css/messages-premium.css?v=1">
<script src="assets/js/navbar-glass.js?v=1" defer></script>
</head>

<body class="messages-premium">

<header class="mid-nav">

<a class="mid-brand" href="index.php"><span><img class="brand-symbol" src="assets/images/slide-exchange-logo-light.svg" alt=""></span>Share Knowledge</a>

<nav class="mid-links" aria-label="Main navigation">

<a href="index.php">Home</a><a href="browse.php">Explore</a><a href="upload.php">Upload</a>

<a href="messages.php" class="active">Messages<?php if ($totalUnread > 0): ?> <span class="unread-badge nav-unread"><?= $totalUnread ?></span><?php endif; ?></a>

<a href="favorites.php">Favorites</a><a href="profile.php">My Profile</a>

<a href="logout.php" class="mid-logout">Log out</a>

</nav>

</header>

<main class="messages-page">
    <div class="messages-container">
        <div class="messages-toolbar">
            <div>
                <div class="messages-kicker">YARMOUK STUDENT COMMUNITY</div>
                <h1 class="messages-title">Messages</h1>
                <p>Study conversations, made simple.</p>
            </div>
            <?php if ($totalUnread > 0): ?>
                <div class="inbox-pill"><i class="bi bi-chat-left-text" aria-hidden="true"></i><span><?= $totalUnread ?> unread <?= $totalUnread === 1 ? 'message' : 'messages' ?></span></div>
            <?php endif; ?>
        </div>

        <?php if ($error): ?><div class="message-error" role="alert"><i class="bi bi-exclamation-circle" aria-hidden="true"></i><?= e($error) ?></div><?php endif; ?>

        <div class="messages-layout">
            <aside class="students-panel" aria-label="Student conversations">
                <div class="conversations-heading">
                    <div><span class="heading-mark" aria-hidden="true"><i class="bi bi-chat-square-text"></i></span><h2>Messages</h2></div>
                    <span class="chat-count"><?= count($students) ?> students</span>
                </div>
                <div class="student-search-wrap">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" id="studentSearch" class="student-search" placeholder="Search students..." aria-label="Search students" autocomplete="off">
                </div>
                <div class="conversation-list" id="conversationList">
                    <?php if (empty($students)): ?>
                        <p class="mid-empty-students">No other students registered yet.</p>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <?php
                                $studentId = (int) $student["id"];
                                $studentInitial = function_exists('mb_substr') ? mb_substr($student["name"], 0, 1, 'UTF-8') : substr($student["name"], 0, 1);
                                $lastMessageAt = !empty($student["last_message_at"]) ? strtotime($student["last_message_at"]) : false;
                                $lastMessageTime = $lastMessageAt ? (date('Y-m-d', $lastMessageAt) === date('Y-m-d') ? date('g:i A', $lastMessageAt) : date('M j', $lastMessageAt)) : '';
                                $lastMessagePreview = trim((string)($student["last_message"] ?? ''));
                            ?>
                            <a class="student-link <?= $selectedUser && $selectedId === $studentId ? "active" : "" ?>" href="messages.php?user=<?= $studentId ?>" data-search="<?= e($student["name"] . ' ' . $student["email"]) ?>" <?= $selectedUser && $selectedId === $studentId ? 'aria-current="page"' : '' ?>>
                                <span class="student-avatar" aria-hidden="true"><?= e($studentInitial) ?></span>
                                <span class="student-copy">
                                    <span class="student-topline"><strong><?= e($student["name"]) ?></strong><?php if ($lastMessageTime): ?><time><?= e($lastMessageTime) ?></time><?php endif; ?></span>
                                    <span class="student-preview"><?= e($lastMessagePreview !== '' ? $lastMessagePreview : 'Start a conversation') ?></span>
                                </span>
                                <?php if (($unreadBySender[$studentId] ?? 0) > 0): ?><span class="unread-badge" aria-label="<?= (int)$unreadBySender[$studentId] ?> unread messages"><?= (int)$unreadBySender[$studentId] ?></span><?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                        <p class="search-empty" id="searchEmpty" hidden>No students match your search.</p>
                    <?php endif; ?>
                </div>
            </aside>

            <section class="chat-panel" aria-label="Active conversation">
                <?php if (!$selectedUser): ?>
                    <div class="empty-chat">
                        <span class="empty-chat-art" aria-hidden="true"><i class="bi bi-chat-square-dots"></i></span>
                        <h2>Your conversations start here</h2>
                        <p>Select a student to start sharing ideas and discussing study materials.</p>
                    </div>
                <?php else: ?>
                    <?php $selectedInitial = function_exists('mb_substr') ? mb_substr($selectedUser["name"], 0, 1, 'UTF-8') : substr($selectedUser["name"], 0, 1); ?>
                    <header class="conversation-head">
                        <span class="conversation-avatar" aria-hidden="true"><?= e($selectedInitial) ?></span>
                        <div class="conversation-person"><strong><?= e($selectedUser["name"]) ?></strong><small>Student conversation</small></div>
                        <span class="conversation-label"><i class="bi bi-mortarboard" aria-hidden="true"></i> Yarmouk community</span>
                    </header>
                    <div class="chat-messages" id="chatMessages" aria-live="polite" aria-relevant="additions">
                        <?php if (empty($messages)): ?>
                            <div class="thread-empty"><span class="thread-empty-icon"><i class="bi bi-chat-left-dots" aria-hidden="true"></i></span><p>No messages yet. Start with a friendly hello.</p></div>
                        <?php else: ?>
                            <?php $previousDate = null; ?>
                            <?php foreach ($messages as $msg): ?>
                                <?php $messageDate = date('Y-m-d', strtotime($msg["created_at"])); ?>
                                <?php if ($messageDate !== $previousDate): ?>
                                    <div class="date-separator"><span><?= e(date('F j, Y', strtotime($msg["created_at"]))) ?></span></div>
                                    <?php $previousDate = $messageDate; ?>
                                <?php endif; ?>
                                <div class="chat-bubble <?= (int)$msg["sender_id"] === $userId ? "sent" : "received" ?>">
                                    <span class="message-text"><?= nl2br(e($msg["message"])) ?></span>
                                    <time datetime="<?= e($msg["created_at"]) ?>"><?= e(date('g:i A', strtotime($msg["created_at"]))) ?></time>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <form method="POST" class="chat-form" id="chatForm">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION["messages_csrf"]) ?>">
                        <input type="hidden" name="receiver_id" value="<?= (int)$selectedUser["id"] ?>">
                        <div class="composer-box">
                            <textarea name="message" id="messageInput" maxlength="2000" placeholder="Write a message..." rows="1" required aria-label="Write a message"></textarea>
                            <small class="composer-hint">Enter to send <span>·</span> Shift + Enter for a new line</small>
                        </div>
                        <button type="submit" class="send-btn" id="sendButton"><i class="bi bi-send-fill" aria-hidden="true"></i><span>Send</span></button>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>
<script src="assets/js/messages-workspace.js?v=1" defer></script>

<script src="assets/js/ui-effects.js?v=3" defer></script>
</body>

</html>

