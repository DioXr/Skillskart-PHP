<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }
require '../core/db_connect.php';

$language_id = isset($_GET['lang_id']) ? $_GET['lang_id'] : null;
if (!$language_id) { header("Location: index.php"); exit(); }

$errors = [];
$title = '';
$order_index = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $order_index = trim($_POST['order_index']);

    if (empty($title)) { $errors[] = "Title is required."; }
    if (!is_numeric($order_index)) { $errors[] = "Order index must be a number."; }

    if (empty($errors)) {
        $sql = "INSERT INTO modules (language_id, title, order_index) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $language_id, $title, $order_index);

        if ($stmt->execute()) {
            header("Location: manage_roadmap.php?id=" . urlencode($language_id));
            exit();
        } else {
            $errors[] = "Error: Could not add module.";
        }
        $stmt->close();
    }
    $conn->close();
}

$page_title = "Add Module";
require '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h5 style="text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-bottom: 5px;">Editor</h5>
            <h1 style="margin: 0;">
                <i class="fa-brands fa-<?php echo strtolower($language['name']); ?>" style="color: var(--primary-color);"></i> 
                Add New Module
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

<form action="add_module.php?lang_id=<?php echo htmlspecialchars($language_id); ?>" method="post" style="margin-top: 15px;">
    <div class="form-group">
        <label for="title">Module Title:</label><br>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
    </div>
    <div class="form-group" style="margin-top: 15px;">
        <label for="order_index">Order (e.g., 1, 2, 3):</label><br>
        <input type="number" id="order_index" name="order_index" value="<?php echo htmlspecialchars($order_index); ?>" required>
    </div>
    <div class="form-group" style="margin-top: 20px;">
        <button type="submit">Add Module</button>
    </div>
</form>

<?php require '../includes/footer.php'; ?>