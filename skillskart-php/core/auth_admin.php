<?php
// 1. Ensure session is started properly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Check for the admin session ID.
// If it is NOT set, redirect to the login page immediately.
if (!isset($_SESSION['admin_id'])) {
    // Redirect to the admin login page from the perspective of the core file location.
    header("Location: /skillskart-php/admin/login.php");
    exit(); // Crucial: Stop script execution immediately after the redirect header is sent
}
// If the admin_id is set, the script continues to the file that included it (e.g., admin/index.php).
?>