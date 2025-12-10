<?php
require '../core/auth_admin.php';
require '../core/db_connect.php';

$block_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($block_id > 0) {
    // --- SECURITY CHECK ---
    $sql_lang = "SELECT m.language_id, cb.topic_id
                 FROM content_blocks cb
                 JOIN topics t ON cb.topic_id = t.id
                 JOIN modules m ON t.module_id = m.id
                 WHERE cb.id = ?";
    $stmt_lang = $conn->prepare($sql_lang);
    $stmt_lang->bind_param("i", $block_id);
    $stmt_lang->execute();
    $result = $stmt_lang->get_result()->fetch_assoc();
    $language_id = $result['language_id'] ?? null;
    $topic_id = $result['topic_id'] ?? null;
    $stmt_lang->close();

    if ($language_id) {
        $is_super_admin = ($_SESSION['assigned_language'] === null);
        if (!$is_super_admin && $_SESSION['assigned_language'] !== $language_id) {
            die("Access Denied.");
        }

        // Delete the content block
        $sql_delete = "DELETE FROM content_blocks WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $block_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        if ($topic_id) {
            header("Location: edit_content.php?topic_id=" . $topic_id);
            exit();
        }
    }
}
header("Location: index.php");
exit();
?>