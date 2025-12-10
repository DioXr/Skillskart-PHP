<?php
require '../core/auth_admin.php'; 
require '../core/db_connect.php';

$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($topic_id <= 0) { header("Location: index.php"); exit(); }

// --- 1. SECURITY & FETCH CONTEXT ---
// We need the Language ID AND Name for the header/permissions
$sql_lang = "SELECT m.language_id, l.name as language_name 
             FROM topics t 
             JOIN modules m ON t.module_id = m.id 
             JOIN languages l ON m.language_id = l.id 
             WHERE t.id = ?";
$stmt_lang = $conn->prepare($sql_lang);
$stmt_lang->bind_param("i", $topic_id);
$stmt_lang->execute();
$result = $stmt_lang->get_result()->fetch_assoc();
$stmt_lang->close();

if (!$result) { die("Topic not found."); }

$language_id = $result['language_id'];
$language_name = $result['language_name'];

// Permission Check
$is_super_admin = ($_SESSION['assigned_language'] === null);
if (!$is_super_admin && $_SESSION['assigned_language'] !== $language_id) {
    die("Access Denied: You do not have permission to edit this topic.");
}

$errors = [];

// --- 2. HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $is_premium = isset($_POST['is_premium']) ? 1 : 0;

    if (empty($title) || empty($slug)) { $errors[] = "Title and Slug are required."; }
    
    if (empty($errors)) {
        $sql = "UPDATE topics SET title = ?, slug = ?, is_premium = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $title, $slug, $is_premium, $topic_id);
        
        if ($stmt->execute()) {
            header("Location: manage_roadmap.php?id=" . $language_id);
            exit();
        } else {
            $errors[] = "Database Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// --- 3. FETCH TOPIC DATA ---
$sql_fetch = "SELECT title, slug, is_premium FROM topics WHERE id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $topic_id);
$stmt_fetch->execute();
$topic = $stmt_fetch->get_result()->fetch_assoc();
$stmt_fetch->close();

$page_title = "Edit Topic";
require '../includes/header.php';
?>

<div class="container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h5 style="text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-bottom: 5px;">
                <?php echo htmlspecialchars($language_name); ?> / Editor
            </h5>
            <h1 style="margin: 0;">
                <i class="fa-solid fa-pen-to-square" style="color: var(--primary-color);"></i> 
                Edit Topic
            </h1>
        </div>
        <a href="manage_roadmap.php?id=<?php echo htmlspecialchars($language_id); ?>" class="button" style="background: var(--background-color); color: var(--text-color); border: 1px solid var(--border-color); width: auto;">
            <i class="fa-solid fa-arrow-left"></i> Back to Roadmap
        </a>
    </div>

    <div style="max-width: 600px; margin: 0 auto; padding: 40px; border: 1px solid var(--border-color);">
        
        <h3 style="margin-top: 0; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
            Topic Details
        </h3>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <?php foreach ($errors as $error): ?>
                    <p style="margin: 0; color: #dc3545;"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="edit_topic.php?id=<?php echo $topic_id; ?>" method="post">
            
            <div class="form-group">
                <label for="title">Topic Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($topic['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="slug">URL Slug (unique-identifier)</label>
                <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($topic['slug']); ?>" required 
                       style="font-family: monospace; color: var(--primary-color);">
            </div>

            <div class="form-group" style="background: var(--background-color); padding: 15px; border-radius: 6px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="is_premium" id="is_premium" value="1" <?php echo $topic['is_premium'] ? 'checked' : ''; ?> style="width: 20px; height: 20px; cursor: pointer;">
                <label for="is_premium" style="margin: 0; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-lock" style="color: #ffd700;"></i> 
                    Is this Premium Content?
                </label>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="button" style="flex: 2;">
                    Update Topic
                </button>
                
                <a href="manage_roadmap.php?id=<?php echo $language_id; ?>" class="button" style="flex: 1; background: transparent; border: 1px solid var(--border-color); color: var(--text-color); text-align: center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>

<?php 
$conn->close();
require '../includes/footer.php'; 
?>