<?php
require '../core/auth_admin.php'; 
require '../core/db_connect.php';

$module_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($module_id <= 0) { header("Location: index.php"); exit(); }

// --- 1. SECURITY CHECK ---
// First, find which language this module belongs to.
$sql_lang = "SELECT m.language_id, l.name as language_name 
             FROM modules m 
             JOIN languages l ON m.language_id = l.id 
             WHERE m.id = ?";
$stmt_lang = $conn->prepare($sql_lang);
$stmt_lang->bind_param("i", $module_id);
$stmt_lang->execute();
$res_lang = $stmt_lang->get_result();

if ($res_lang->num_rows === 0) { die("Module not found."); }

$module_data = $res_lang->fetch_assoc();
$module_language_id = $module_data['language_id'];
$language_name = $module_data['language_name'];
$stmt_lang->close();

$is_super_admin = ($_SESSION['assigned_language'] === null);
$assigned_language = isset($_SESSION['assigned_language']) ? $_SESSION['assigned_language'] : null;

// Permission Check
if (!$is_super_admin && $assigned_language !== $module_language_id) {
    die("Access Denied: You do not have permission to edit this module.");
}
// --- END SECURITY CHECK ---

$errors = [];
$title = '';
$order_index = 0;

// --- 2. HANDLE FORM SUBMISSION (UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $order_index = (int)$_POST['order_index'];

    if (empty($title)) {
        $errors[] = "Module title is required.";
    }

    if (empty($errors)) {
        $sql = "UPDATE modules SET title = ?, order_index = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $title, $order_index, $module_id);
        
        if ($stmt->execute()) {
            header("Location: manage_roadmap.php?id=" . $module_language_id);
            exit();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}

// --- 3. FETCH CURRENT DATA ---
if (empty($title)) {
    $sql_fetch = "SELECT title, order_index FROM modules WHERE id = ?";
    $stmt = $conn->prepare($sql_fetch);
    $stmt->bind_param("i", $module_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $title = $row['title'];
    $order_index = $row['order_index'];
    $stmt->close();
}

$page_title = "Edit Module";
require '../includes/header.php';
?>

<div class="container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h5 style="text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-bottom: 5px;">
                <i class="fa-brands fa-<?php echo strtolower($language_name); ?>"></i> <?php echo htmlspecialchars($language_name); ?>
            </h5>
            <h1>Edit Module</h1>
        </div>
        <a href="manage_roadmap.php?id=<?php echo $module_language_id; ?>" class="button" style="background: var(--background-color); color: var(--text-color); border: 1px solid var(--border-color); width: auto;">
            <i class="fa-solid fa-arrow-left"></i> Back to Roadmap
        </a>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto; padding: 40px;">
        
        <h3 style="margin-top: 0; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
            <i class="fa-solid fa-pen-to-square" style="color: var(--primary-color);"></i> Update Details
        </h3>

        <?php if (!empty($errors)): ?>
            <div class="card" style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 20px;">
                <?php foreach ($errors as $error): ?>
                    <p style="margin: 0; color: #dc3545;"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="form-group">
                <label for="title">Module Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required style="font-size: 1.1rem;">
            </div>

            <div class="form-group">
                <label for="order_index">Order Index</label>
                <input type="number" id="order_index" name="order_index" value="<?php echo htmlspecialchars($order_index); ?>" required>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 5px;">
                    <i class="fa-solid fa-sort"></i> Determines the position of this module in the roadmap (Lower numbers appear first).
                </p>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="button" style="flex: 2;">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
                
                <a href="manage_roadmap.php?id=<?php echo $module_language_id; ?>" class="button" style="flex: 1; background: transparent; border: 1px solid var(--border-color); color: var(--text-color); text-align: center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>

<?php require '../includes/footer.php'; ?>