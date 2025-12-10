<?php
require '../core/auth_admin.php';
require '../core/db_connect.php';

$language_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$language_id) {
    header("Location: index.php");
    exit();
}

// Security Check for Language Expert
$is_super_admin = ($_SESSION['assigned_language'] === null);
if (!$is_super_admin && $_SESSION['assigned_language'] !== $language_id) {
    die("Access Denied: You do not have permission to manage this roadmap.");
}

// Fetch language name
$sql_lang = "SELECT name FROM languages WHERE id = ?";
$stmt_lang = $conn->prepare($sql_lang);
$stmt_lang->bind_param("s", $language_id);
$stmt_lang->execute();
$language = $stmt_lang->get_result()->fetch_assoc();
$stmt_lang->close();
if (!$language) { die("Language not found."); }

// Fetch modules and topics
$sql_roadmap = "SELECT m.id as module_id, m.title as module_title, t.id as topic_id, t.title as topic_title
                FROM modules m
                LEFT JOIN topics t ON m.id = t.module_id
                WHERE m.language_id = ?
                ORDER BY m.order_index, t.id";
$stmt_roadmap = $conn->prepare($sql_roadmap);
$stmt_roadmap->bind_param("s", $language_id);
$stmt_roadmap->execute();
$result = $stmt_roadmap->get_result();
$modules = [];
while ($row = $result->fetch_assoc()) {
    $modules[$row['module_id']]['title'] = $row['module_title'];
    if ($row['topic_id']) {
        $modules[$row['module_id']]['topics'][] = ['id' => $row['topic_id'], 'title' => $row['topic_title']];
    }
}
$stmt_roadmap->close();
$conn->close();

$page_title = "Manage Roadmap";
require '../includes/header.php';
?>

<div class="container" style="text-align: left;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h5 style="text-transform: uppercase; color: var(--text-secondary); margin-bottom: 5px;">Course Builder</h5>
            <h1 style="margin: 0;">Manage: <?php echo htmlspecialchars($language['name']); ?></h1>
        </div>
        <a href="index.php" class="button" style="background: var(--background-color); border: 1px solid var(--border-color); color: var(--text-color); width: auto;"> 
            &larr; Back to Dashboard
        </a>
    </div>

<div class="module-container">
    <a href="add_module.php?lang_id=<?php echo htmlspecialchars($language_id); ?>"><button>+ Add New Module</button></a>
    
    <?php if (!empty($modules)): ?>
        <?php foreach ($modules as $module_id => $module): ?>
            <div class="module-item">
                <div style="display:flex; justify-content: space-between; align-items: center;">
                    <h3><?php echo htmlspecialchars($module['title']); ?></h3>
                    <div>
                        <a href="edit_module.php?id=<?php echo $module_id; ?>" class="button" style="width: auto; display: inline-block; font-size: 0.8rem; padding: 5px 10px; background: transparent; border: 1px solid var(--primary-color); color: var(--primary-color);">
        <i class="fa-solid fa-pen"></i> Edit Module
    </a>

    <a href="delete_module.php?id=<?php echo $module_id; ?>" onclick="return confirm('Are you sure? This will delete all topics inside!');" class="button" style="width: auto; display: inline-block; font-size: 0.8rem; padding: 5px 10px; background: transparent; border: 1px solid var(--danger-color); color: var(--danger-color);">
        <i class="fa-solid fa-trash"></i> Delete Module
    </a>
                    </div>
                </div>
                
                <hr>
                
                <a href="add_topic.php?module_id=<?php echo $module_id; ?>"><button style="font-size: 0.8em; padding: 5px 10px;">+ Add Topic</button></a>
                
                <?php if (!empty($module['topics'])): ?>
                    <ul>
                        <?php foreach ($module['topics'] as $topic): ?>
                            <li>
                                <?php echo htmlspecialchars($topic['title']); ?>
                                <div style="display: flex; gap: 8px;">
                                    <a href="edit_content.php?topic_id=<?php echo $topic['id']; ?>" class="button" style="width: auto; display: inline-block; font-size: 0.8rem; padding: 5px 12px;">
                                       Content <i class="fa-solid fa-pen-nib"></i>
                                   </a>

                                    <a href="edit_topic.php?id=<?php echo $topic['id']; ?>" class="button" title="Rename" style="width: auto; display: inline-block; font-size: 0.8rem; padding: 5px 10px; background: var(--surface-color); border: 1px solid var(--border-color); color: var(--text-color);">
                                        Edit Title <i class="fa-solid fa-pen"></i>
                                    </a>

                                     <a href="delete_topic.php?id=<?php echo $topic['id']; ?>" onclick="return confirm('Are you sure you want to delete this topic?');" class="button" title="Delete" style="width: auto; display: inline-block; font-size: 0.8rem; padding: 5px 10px; background: var(--danger-color); color: white; border: none;">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No topics in this module yet.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="margin-top: 20px;">No modules created for this language yet.</p>
    <?php endif; ?>
</div>

<?php require '../includes/footer.php'; ?>a