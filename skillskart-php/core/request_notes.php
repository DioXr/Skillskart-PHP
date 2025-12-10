<?php
session_start();
require 'db_connect.php';

// --- FIX: Check for student_id (your actual session variable) ---
if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['student_id'];
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

if ($topic_id > 0) {
    // Check if a request already exists
    $sql_check = "SELECT id FROM note_requests WHERE user_id = ? AND topic_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $user_id, $topic_id);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows == 0) {
        // No existing request, create new
        $sql_insert = "INSERT INTO note_requests (user_id, topic_id, status, created_at) VALUES (?, ?, 'pending', NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $user_id, $topic_id);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt_check->close();
}

// Redirect back to topic
$sql_slug = "SELECT slug FROM topics WHERE id = ?";
$stmt_slug = $conn->prepare($sql_slug);
$stmt_slug->bind_param("i", $topic_id);
$stmt_slug->execute();
$slug = $stmt_slug->get_result()->fetch_assoc()['slug'];

header("Location: ../topic.php?slug=" . urlencode($slug) . "&status=requested");
exit();
?>