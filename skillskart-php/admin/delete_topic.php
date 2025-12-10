<?php
require '../core/auth_admin.php';
require '../core/db_connect.php';

$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($topic_id > 0) {
    // Get language_id for security check and redirect
    $sql_lang = "SELECT m.language_id FROM topics t JOIN modules m ON t.module_id = m.id WHERE t.id = ?";
    $stmt_lang = $conn->prepare($sql_lang);
    $stmt_lang->bind_param("i", $topic_id);
    $stmt_lang->execute();
    $result = $stmt_lang->get_result()->fetch_assoc();
    $language_id = $result['language_id'] ?? null;
    $stmt_lang->close();

    if ($language_id) {
        $is_super_admin = ($_SESSION['assigned_language'] === null);
        if (!$is_super_admin && $_SESSION['assigned_language'] !== $language_id) {
            die("Access Denied.");
        }

        // Delete the topic
        $sql_delete = "DELETE FROM topics WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $topic_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        header("Location: manage_roadmap.php?id=" . urlencode($language_id));
        exit();
    }
}
header("Location: index.php");
exit();
?>