<?php
require '../core/auth_admin.php';
require '../core/db_connect.php';

// Super Admins only
if ($_SESSION['assigned_language'] !== null) {
    die("Access Denied.");
}

$admin_id_to_delete = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_admin_id = $_SESSION['admin_id'];

// Safety Check: Prevent an admin from deleting themselves
if ($admin_id_to_delete > 0 && $admin_id_to_delete != $current_admin_id) {
    $sql = "DELETE FROM admins WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id_to_delete);
    $stmt->execute();
    $stmt->close();
}

header("Location: manage_admins.php");
exit();
?>