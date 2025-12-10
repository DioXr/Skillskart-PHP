<?php
require '../core/auth_admin.php'; // Use our central auth file
require '../core/db_connect.php';

$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;
if ($module_id <= 0) { header("Location: index.php"); exit(); }

// --- SECURITY CHECK ---
// Find which language this module belongs to
$sql_lang = "SELECT language_id FROM modules WHERE id = ?";
$stmt_lang = $conn->prepare($sql_lang);
$stmt_lang->bind_param("i", $module_id);
$stmt_lang->execute();
$result = $stmt_lang->get_result()->fetch_assoc();
$language_id = $result['language_id'] ?? null;
$stmt_lang->close();

if (!$language_id) { die("Module not found."); }

// Now check permissions
$is_super_admin = ($_SESSION['assigned_language'] === null);
if (!$is_super_admin && $_SESSION['assigned_language'] !== $language_id) {
    die("Access Denied: You do not have permission to add topics to this roadmap.");
}
// --- END SECURITY CHECK ---


$errors = [];
$title = $slug = '';
$is_premium = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $is_premium = isset($_POST['is_premium']) ? 1 : 0;

    if (empty($title)) { $errors[] = "Title is required."; }
    if (empty($slug)) { $errors[] = "Slug is required."; }

    if (empty($errors)) {
        $sql = "INSERT INTO topics (module_id, title, slug, is_premium) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $module_id, $title, $slug, $is_premium);

        if ($stmt->execute()) {
            header("Location: manage_roadmap.php?id=" . urlencode($language_id));
            exit();
        } else {
            $errors[] = "Error: Could not add topic. Does a topic with that slug already exist?";
        }
        $stmt->close();
    }
}
$conn->close();

$page_title = "Add Topic";
require '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h5 style="text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-bottom: 5px;">Editor</h5>
            <h1 style="margin: 0;">
                <i class="fa-brands fa-<?php echo strtolower($language['name']); ?>" style="color: var(--primary-color);"></i> 
                Add new Topic
            </h1>
        </div>
        <a href="index.php" class="button" style="background: var(--background-color); color: var(--text-color); border: 1px solid var(--border-color); width: auto;">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>


<?php if (!empty($errors)): ?>
    <div class="errors" style="color: red; margin-top: 15px;">
        <p><?php echo implode('<br>', $errors); ?></p>
    </div>
<?php endif; ?>

<form action="add_topic.php?module_id=<?php echo $module_id; ?>" method="post" style="margin-top: 15px;">
    <div class="form-group">
        <label for="title">Topic Title:</label><br>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
    </div>
    <div class="form-group">
        <label for="slug">URL Slug (e.g., 'hello-world'):</label><br>
        <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($slug); ?>" required>
    </div>
    <div class="form-group">
        <label>
            <input type="checkbox" name="is_premium" value="1" <?php echo $is_premium ? 'checked' : ''; ?>>
            Is this premium content?
        </label>
    </div>
    <button type="submit">Add Topic</button>
</form>

<?php require '../includes/footer.php'; ?>