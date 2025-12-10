<?php
require '../core/auth_admin.php'; 
require '../core/db_connect.php';

// Security Check: Super Admins only
if ($_SESSION['assigned_language'] !== null) {
    die("Access Denied: Only Super Admins can manage admin roles.");
}

$admin_id_to_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($admin_id_to_edit <= 0) { 
    header("Location: manage_admins.php"); 
    exit(); 
}

$errors = [];

// --- 1. HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assigned_language_id = !empty($_POST['assigned_language_id']) ? trim($_POST['assigned_language_id']) : null;

    // Safety Check: Prevent a Super Admin from accidentally demoting themselves
    if ($admin_id_to_edit == $_SESSION['admin_id'] && $assigned_language_id !== null) {
        $errors[] = "Security Warning: You cannot demote your own Super Admin account.";
    } else {
        $sql = "UPDATE admins SET assigned_language_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $assigned_language_id, $admin_id_to_edit);
        
        if ($stmt->execute()) {
            header("Location: manage_admins.php?status=updated");
            exit();
        } else {
            $errors[] = "Database Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// --- 2. FETCH DATA ---
// Use prepared statement for security
$stmt = $conn->prepare("SELECT id, username, assigned_language_id FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id_to_edit);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$admin) { die("Admin user not found."); }

// Fetch languages for dropdown
$languages = $conn->query("SELECT id, name FROM languages ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$page_title = "Edit Admin Role";
require '../includes/header.php';
?>

<div class="container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h5 style="text-transform: uppercase; color: var(--text-secondary); margin-bottom: 5px;">User Management</h5>
            <h1 style="margin: 0;">Edit Role</h1>
        </div>
        <a href="manage_admins.php" class="button" style="background: var(--background-color); border: 1px solid var(--border-color); color: var(--text-color); width: auto;"> 
            &larr; Back to List
        </a>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto; padding: 40px;">
        
        <h3 style="margin-top: 0; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-user-gear" style="color: var(--primary-color);"></i> 
            Update Permissions
        </h3>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(220, 53, 69, 0.1); color: #dc3545; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem;">
                <?php foreach ($errors as $error) echo "<p style='margin:0'><i class='fa-solid fa-triangle-exclamation'></i> $error</p>"; ?>
            </div>
        <?php endif; ?>

        <form action="edit_admin.php?id=<?php echo $admin_id_to_edit; ?>" method="post">
            
            <div class="form-group">
                <label style="color: var(--text-secondary);">Username</label>
                <input type="text" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled 
                       style="background: var(--background-color); color: var(--text-color); border-color: transparent; cursor: not-allowed; font-weight: bold;">
            </div>

            <div class="form-group">
                <label for="assigned_language_id">Role Assignment</label>
                <select name="assigned_language_id" style="width: 100%; padding: 10px;">
                    <option value="" <?php echo $admin['assigned_language_id'] === null ? 'selected' : ''; ?>>
                        ★ Super Admin (Full Access)
                    </option>
                    
                    <optgroup label="Language Expert (Restricted)">
                        <?php foreach($languages as $language): ?>
                            <option value="<?php echo htmlspecialchars($language['id']); ?>" <?php echo $admin['assigned_language_id'] == $language['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($language['name']); ?> Manager
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 8px;">
                    <i class="fa-solid fa-circle-info"></i> Changing this will immediately affect what this admin can access.
                </p>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="button" style="flex: 2;">
                    Update Role
                </button>
                
                <a href="manage_admins.php" class="button" style="flex: 1; background: transparent; border: 1px solid var(--border-color); color: var(--text-color); text-align: center;">
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