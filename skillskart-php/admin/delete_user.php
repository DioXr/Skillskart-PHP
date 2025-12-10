<?php
require '../core/auth_admin.php';
require '../core/db_connect.php';

if ($_SESSION['assigned_language'] !== null) { die("Access Denied."); }

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id > 0) {
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}
header("Location: manage_users.php");
exit();
?>