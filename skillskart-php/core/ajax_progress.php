<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db_connect.php'; // this is relative to /core. Adjust if needed.

// 1. Get user id from session (student OR user)
$user_id = null;

if (isset($_SESSION['student_id'])) {
    $user_id = $_SESSION['student_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

// 2. Basic validation (logged in + required POST fields)
if (!$user_id || !isset($_POST['topic_id'], $_POST['completed'])) {
    http_response_code(400);
    echo "error_login_or_input";
    exit;
}

$topic_id  = (int) $_POST['topic_id'];

// JS sends "true" / "false" (string)
$completed = ($_POST['completed'] === 'true');

// 3. DB logic: insert or delete progress
if ($completed) {
    $sql = "INSERT IGNORE INTO user_progress (user_id, topic_id) VALUES (?, ?)";
} else {
    $sql = "DELETE FROM user_progress WHERE user_id = ? AND topic_id = ?";
}

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo "error_prepare";
    exit;
}

$stmt->bind_param("ii", $user_id, $topic_id);

if ($stmt->execute()) {
    // IMPORTANT:
    // Your JS in topic.php looks specifically for the substring "success_saved".
    // Do NOT echo anything else on success.
    echo "success_saved";
} else {
    http_response_code(500);
    echo "error_db";
}

$stmt->close();
$conn->close();
exit;
