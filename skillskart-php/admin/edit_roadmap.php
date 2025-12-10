<?php
// Ensure admin is logged in
require '../core/auth_admin.php'; 
require '../core/db_connect.php';

$language_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($language_id <= 0) {
    header("Location: index.php");
    exit();
}

// --- 1. SECURITY & DATA FETCH ---
// Verify the language exists and get its name
$sql_lang = "SELECT name FROM languages WHERE id = ?";
$stmt = $conn->prepare($sql_lang);
$stmt->bind_param("i", $language_id);
$stmt->execute();
$res_lang = $stmt->get_result();

if ($res_lang->num_rows === 0) { die("Language not found."); }
$language_name = $res_lang->fetch_assoc()['name'];
$stmt->close();

// Check Permissions (Super Admin or Specific Language Admin)
$is_super_admin = ($_SESSION['assigned_language'] === null);
if (!$is_super_admin && $_SESSION['assigned_language'] != $language_id) {
    die("Access Denied: You cannot edit the roadmap for this language.");
}

$errors = [];
$success_msg = "";
$roadmap_content_json = "{}"; // Default empty JSON

// --- 2. HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $roadmap_content_json = $_POST['content'];
    
    // Server-side JSON Validation
    json_decode($roadmap_content_json);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errors[] = "Invalid JSON format: " . json_last_error_msg();
    }

    if (empty($errors)) {
        // Check if record exists
        $sql_check = "SELECT id FROM roadmaps WHERE language_id = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param("i", $language_id);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $sql = "UPDATE roadmaps SET content = ? WHERE language_id = ?";
        } else {
            $sql = "INSERT INTO roadmaps (content, language_id) VALUES (?, ?)";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $roadmap_content_json, $language_id);
        
        if ($stmt->execute()) {
            $success_msg = "Roadmap updated successfully!";
        } else {
            $errors[] = "Database Error: " . $conn->error;
        }
        $stmt->close();
    }
} else {
    // --- FETCH EXISTING JSON ---
    $sql_fetch = "SELECT content FROM roadmaps WHERE language_id = ?";
    $stmt = $conn->prepare($sql_fetch);
    $stmt->bind_param("i", $language_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row && !empty($row['content'])) {
        // Prettify the JSON for the editor
        $decoded = json_decode($row['content']);
        if ($decoded) {
            $roadmap_content_json = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } else {
            $roadmap_content_json = $row['content']; // Keep raw if invalid
        }
    }
    $stmt->close();
}

$page_title = "Edit Roadmap: $language_name";
require '../includes/header.php';
?>

<div class="container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h5 style="text-transform: uppercase; color: var(--text-secondary); margin-bottom: 5px;">Editor</h5>
            <h1 style="margin: 0;">
                <i class="fa-brands fa-<?php echo strtolower($language_name); ?>" style="color: var(--primary-color);"></i> 
                <?php echo htmlspecialchars($language_name); ?> Roadmap
            </h1>
        </div>
        <a href="index.php" class="button" style="background: var(--background-color); color: var(--text-color); border: 1px solid var(--border-color);">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="card" style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 20px;">
            <h4 style="color: #dc3545; margin-top: 0;">Error</h4>
            <?php foreach ($errors as $error) echo "<p style='margin:0'>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
        <div class="card" style="background: rgba(46, 204, 113, 0.1); border-left: 4px solid #2ecc71; padding: 15px; margin-bottom: 20px;">
            <p style="margin: 0; color: #2ecc71; font-weight: bold;"><i class="fa-solid fa-check"></i> <?php echo $success_msg; ?></p>
        </div>
    <?php endif; ?>

    <div class="card">
        <form action="" method="post">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <label for="content" style="font-weight: 600;">JSON Configuration</label>
                <span id="json-status" style="font-size: 0.9rem; padding: 5px 10px; border-radius: 4px; background: #333; color: #aaa;">
                    Waiting for input...
                </span>
            </div>

            <textarea id="content" name="content" spellcheck="false"
                style="width: 100%; height: 500px; font-family: 'Consolas', 'Monaco', monospace; 
                       background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; 
                       border: 1px solid #444; line-height: 1.5; tab-size: 4; resize: vertical;"
            ><?php echo htmlspecialchars($roadmap_content_json); ?></textarea>

            <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                <p style="font-size: 0.85rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-circle-info"></i> Ensure brackets <code>{}</code> and <code>[]</code> match correctly.
                </p>
                <button type="submit" id="save-btn" class="button">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const textarea = document.getElementById('content');
    const statusLabel = document.getElementById('json-status');
    const saveBtn = document.getElementById('save-btn');

    function validateJSON() {
        const text = textarea.value;
        try {
            if (text.trim() === "") {
                statusLabel.innerHTML = "Empty";
                statusLabel.style.background = "#333";
                statusLabel.style.color = "#aaa";
                return;
            }
            JSON.parse(text);
            // Valid
            statusLabel.innerHTML = '<i class="fa-solid fa-check"></i> Valid JSON';
            statusLabel.style.background = "rgba(46, 204, 113, 0.2)";
            statusLabel.style.color = "#2ecc71";
            saveBtn.disabled = false;
            saveBtn.style.opacity = "1";
            saveBtn.style.cursor = "pointer";
            textarea.style.borderColor = "#2ecc71";
        } catch (e) {
            // Invalid
            statusLabel.innerHTML = '<i class="fa-solid fa-times"></i> Invalid Syntax';
            statusLabel.style.background = "rgba(220, 53, 69, 0.2)";
            statusLabel.style.color = "#dc3545";
            // Optional: Disable save to prevent errors
            // saveBtn.disabled = true;
            // saveBtn.style.opacity = "0.6";
            // saveBtn.style.cursor = "not-allowed";
            textarea.style.borderColor = "#dc3545";
        }
    }

    // Run on load and on input
    textarea.addEventListener('input', validateJSON);
    validateJSON(); // Run once on page load
</script>

<?php require '../includes/footer.php'; ?>