<?php
require '../core/auth_admin.php';
require '../core/db_connect.php';

$module_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($module_id > 0) {
    // Get language_id for security check and redirect
    $sql_lang = "SELECT language_id FROM modules WHERE id = ?";
    $stmt_lang = $conn->prepare($sql_lang);
    $stmt_lang->bind_param("i", $module_id);
    $stmt_lang->execute();
    $result = $stmt_lang->get_result()->fetch_assoc();
    $language_id = $result['language_id'] ?? null;
    $stmt_lang->close();

    if ($language_id) {
        $is_super_admin = ($_SESSION['assigned_language'] === null);
        if (!$is_super_admin && $_SESSION['assigned_language'] !== $language_id) {
            die("Access Denied.");
        }

        // Delete the module
        $sql_delete = "DELETE FROM modules WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $module_id);
        $stmt_delete->execute();
        $stmt_delete->close();
        
        header("Location: manage_roadmap.php?id=" . urlencode($language_id));
        exit();
    }
}
header("Location: index.php");
exit();
?>