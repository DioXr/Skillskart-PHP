<?php
require '../core/auth_admin.php';
require '../core/db_connect.php';

// Security Check
if ($_SESSION['assigned_language'] !== null) {
    die("Access Denied: Only Super Admins can manage admin accounts.");
}

$errors = [];

// --- 1. FETCH LANGUAGES (For Dropdown & Mapping) ---
$sql_languages = "SELECT id, name FROM languages";
$languages = $conn->query($sql_languages)->fetch_all(MYSQLI_ASSOC);

// Create a lookup array: [id => name] to display text instead of numbers later
$lang_map = [];
foreach ($languages as $l) {
    $lang_map[$l['id']] = $l['name'];
}

// --- 2. HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_admin'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $assigned_language_id = !empty($_POST['assigned_language_id']) ? trim($_POST['assigned_language_id']) : null;

    if (empty($username) || empty($password)) {
        $errors[] = "Username and password are required.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO admins (username, password, assigned_language_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $hashed_password, $assigned_language_id);

        if ($stmt->execute()) {
            header("Location: manage_admins.php?status=created");
            exit();
        } else {
            $errors[] = "Error: Username might already be taken.";
        }
        $stmt->close();
    }
}

// --- 3. FETCH ADMINS ---
$sql_admins = "SELECT id, username, assigned_language_id FROM admins ORDER BY id ASC";
$admins = $conn->query($sql_admins)->fetch_all(MYSQLI_ASSOC);

$page_title = "Manage Admins";
require '../includes/header.php';
?>

<div class="container" style="text-align: left;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h5 style="text-transform: uppercase; color: var(--text-secondary); margin-bottom: 5px;">System Security</h5>
            <h1 style="margin: 0;">Manage Admins</h1>
        </div>
        <a href="index.php" class="button" style="background: var(--background-color); border: 1px solid var(--border-color); color: var(--text-color); width: auto;"> 
            &larr; Dashboard
        </a>
    </div>

    <div class="dashboard-grid">
        
        <div style="align-self: start; position: sticky; top: 100px;">
            <div class="card">
                <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary-color);">
                    <i class="fa-solid fa-user-plus"></i> Create Admin
                </h3>
                
                <?php if (!empty($errors)): ?>
                    <div style="background: rgba(220, 53, 69, 0.1); color: #dc3545; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem;">
                        <?php foreach ($errors as $error) echo "<p style='margin:0'>$error</p>"; ?>
                    </div>
                <?php endif; ?>

                <form action="manage_admins.php" method="post">
                    <input type="hidden" name="create_admin" value="1">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" required placeholder="e.g. python_expert">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" required placeholder="Strong password">
                    </div>
                    
                    <div class="form-group">
                        <label for="assigned_language_id">Role / Assignment</label>
                        <select name="assigned_language_id">
                            <option value="">★ Super Admin (Full Access)</option>
                            <optgroup label="Language Expert (Restricted)">
                                <?php foreach($languages as $language): ?>
                                    <option value="<?php echo htmlspecialchars($language['id']); ?>">
                                        <?php echo htmlspecialchars($language['name']); ?> Manager
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                        <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 5px;">
                            Super Admins can manage everything. Experts can only edit their specific language.
                        </p>
                    </div>
                    
                    <button type="submit" class="button" style="width: 100%;">Create Account</button>
                </form>
            </div>
        </div>

        <div>
            <h3 style="margin-top: 0; margin-bottom: 20px;">Existing Accounts</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
        <?php foreach($admins as $admin): ?>
            <div class="card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-color);">
                
                <div style="padding: 15px 25px; display: flex; align-items: center;">
                    
                    <div style="flex: 0 0 auto;">
                        <div style="width: 45px; height: 45px; background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: var(--text-color);">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                    </div>

                    <div style="flex: 1; text-align: center; padding: 0 15px;">
                        <h4 style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($admin['username']); ?></h4>
                        
                        <div style="margin-top: 6px;">
                            <?php if ($admin['assigned_language_id']): ?>
                                <span style="font-size: 0.8rem; background: rgba(108, 99, 255, 0.1); color: var(--primary-color); padding: 3px 10px; border-radius: 20px; font-weight: 600;">
                                    <?php echo isset($lang_map[$admin['assigned_language_id']]) ? htmlspecialchars($lang_map[$admin['assigned_language_id']]) : 'Unknown'; ?> Expert
                                </span>
                            <?php else: ?>
                                <span style="font-size: 0.8rem; background: rgba(255, 215, 0, 0.1); color: #ffd700; padding: 3px 10px; border-radius: 20px; font-weight: 600;">
                                    <i class="fa-solid fa-crown"></i> Super Admin
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="flex: 0 0 auto; display: flex; gap: 8px;">
                        <a href="edit_admin.php?id=<?php echo $admin['id']; ?>" class="button" style="width: auto; background: transparent; border: 1px solid var(--text-secondary); color: var(--text-secondary); padding: 8px 12px;">
                            <i class="fa-solid fa-pen"></i>
                        </a>

                        <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                            <button class="button" disabled style="width: auto; background: transparent; border: 1px solid var(--border-color); color: var(--text-muted); padding: 8px 12px; cursor: not-allowed;">
                                <i class="fa-solid fa-user-lock"></i>
                            </button>
                        <?php else: ?>
                            <a href="delete_admin.php?id=<?php echo $admin['id']; ?>" onclick="return confirm('Delete this admin?');" class="button" style="width: auto; background: var(--danger-color); border: none; padding: 8px 12px; color: white;">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
        </div>

    </div>
</div>

<?php
$conn->close();
require '../includes/footer.php';
?>