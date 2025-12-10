<?php
session_start();
// Protect the page
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require '../core/db_connect.php';

$errors = [];
$id = $name = $description = $image = '';

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $id = trim($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $image = trim($_POST['image']);

    // Basic validation
    if (empty($id)) { $errors[] = "ID is required."; }
    if (empty($name)) { $errors[] = "Name is required."; }
    if (empty($description)) { $errors[] = "Description is required."; }
    if (empty($image)) { $errors[] = "Image URL is required."; }

    // If there are no errors, proceed with inserting into the database
    if (empty($errors)) {
        $sql = "INSERT INTO languages (id, name, description, image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // 'ssss' means all four parameters are strings
        $stmt->bind_param("ssss", $id, $name, $description, $image);

        if ($stmt->execute()) {
            // Redirect to the dashboard after successful insertion
            header("Location: index.php?status=success");
            exit();
        } else {
            // Check for a duplicate ID error
            if ($conn->errno == 1062) {
                $errors[] = "Error: A language with this ID already exists.";
            } else {
                $errors[] = "Error: Could not add the language.";
            }
        }
        $stmt->close();
    }
    $conn->close();
}

$page_title = "Add New Language";
require '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h5 style="text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-bottom: 5px;">Editor</h5>
            <h1 style="margin: 0;">
                <i class="fa-brands fa-<?php echo strtolower($language['name']); ?>" style="color: var(--primary-color);"></i> 
                Add New Language
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

<form action="add_language.php" method="post" style="margin-top: 15px;">
    <div class="form-group">
        <label for="id">ID (e.g., 'golang'):</label><br>
        <input type="text" id="id" name="id" value="<?php echo htmlspecialchars($id); ?>" required>
    </div>
    <div class="form-group" style="margin-top: 15px;">
        <label for="name">Name (e.g., 'Go'):</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
    </div>
    <div class="form-group" style="margin-top: 15px;">
        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="4" style="width: 300px;"><?php echo htmlspecialchars($description); ?></textarea>
    </div>
    <div class="form-group" style="margin-top: 15px;">
        <label for="image">Image URL:</label><br>
        <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($image); ?>" style="width: 300px;" required>
    </div>
    <div class="form-group" style="margin-top: 20px;">
        <button type="submit">Add Language</button>
    </div>
</form>

<?php require '../includes/footer.php'; ?>