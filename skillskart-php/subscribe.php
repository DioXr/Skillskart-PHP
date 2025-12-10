<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'core/db_connect.php';

// 1. ROBUST AUTH CHECK (Checks both session names)
$user_id = null;
if (isset($_SESSION['student_id'])) {
    $user_id = $_SESSION['student_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

if (!$user_id) {
    // If neither exists, THEN redirect
    header("Location: login.php");
    exit();
}

// 2. Update the Database
$sql = "UPDATE users SET subscription = 'premium' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    // 3. Update Session IMMEDIATELY (So you don't have to re-login)
    $_SESSION['subscription'] = 'premium';
    
    header("Location: profile.php?status=upgraded");
    exit();
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();