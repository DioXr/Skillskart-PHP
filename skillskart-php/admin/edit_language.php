<?php
session_start();
// Protect the page: only admins can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require '../core/db_connect.php';

$errors = [];
$language_id = isset($_GET['id']) ? $_GET['id'] : null;
$language = null;

// Redirect if no ID is provided
if (!$language_id) {
    header("Location: index.php");
    exit();
}

// --- HANDLE FORM SUBMISSION (WHEN UPDATING) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $image = trim($_POST['image']);

    if (empty($name) || empty($description) || empty($image)) {
        $errors[] = "All fields are required.";
    }

    if (empty($errors)) {
        $sql = "UPDATE languages SET name = ?, description = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $description, $image, $language_id);

        if ($stmt->execute()) {
            header("Location: index.php?status=updated");
            exit();
        } else {
            $errors[] = "Error: Could not update the language.";
        }
        $stmt->close();
    }
}

// --- FETCH EXISTING DATA TO DISPLAY IN THE FORM ---
$sql = "SELECT id, name, description, image FROM languages WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $language_id);
$stmt->execute();
$result = $stmt->get_result();
$language = $result->fetch_assoc();

// If language with that ID doesn't exist, redirect back to dashboard
if (!$language) {
    header("Location: index.php");
    exit();
}

$stmt->close();
$conn->close();

$page_title = "Edit Language";
require '../includes/header.php';
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h5 style="text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-bottom: 5px;">Editor</h5>
            <h1 style="margin: 0;">
                <i class="fa-brands fa-<?php echo strtolower($language['name']); ?>" style="color: var(--primary-color);"></i> 
                Edit Language
            </h1>
        </div>
        <a href="index.php" class="button" style="background: var(--background-color); color: var(--text-color); border: 1px solid var(--border-color); width: auto;">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

<?php if (!empty($errors)): ?>
    <div class="errors" style="color: red; margin-top: 15px;">
        <?php foreach ($errors as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form action="edit_language.php?id=<?php echo htmlspecialchars($language_id); ?>" method="post" style="margin-top: 15px;">
    <div class="form-group">
        <label for="id">ID:</label><br>
        <input type="text" id="id" name="id" value="<?php echo htmlspecialchars($language['id']); ?>" disabled>
    </div>
    <div class="form-group">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($language['name']); ?>" required>
    </div>
    <div class="form-group">
        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="4" style="width: 100%;"><?php echo htmlspecialchars($language['description']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="image">Image URL:</label><br>
        <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($language['image']); ?>" style="width: 100%;" required>
    </div>
    <div class="form-group" style="margin-top: 20px;">
        <button type="submit">Update Language</button>
    </div>
</form>

<?php require '../includes/footer.php'; ?>