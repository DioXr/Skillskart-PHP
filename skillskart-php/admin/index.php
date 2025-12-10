<?php
// Ensure admin is logged in
require '../core/auth_admin.php'; 
require '../core/db_connect.php';

// Determine Role
// If 'assigned_language' is NULL, they are Super Admin
$assigned_language = isset($_SESSION['assigned_language']) ? $_SESSION['assigned_language'] : null;
$is_super_admin = ($assigned_language === null);

// --- 1. FETCH LANGUAGES ---
if ($is_super_admin) {
    // Super Admin sees ALL languages
    $sql = "SELECT * FROM languages ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
} else {
    // Regular Admin sees ONLY their assigned language
    $sql = "SELECT * FROM languages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assigned_language);
}
$stmt->execute();
$languages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- 2. FETCH PENDING REQUESTS COUNT (For the Alert Widget) ---
// If regular admin, only count requests for THEIR language
if ($is_super_admin) {
    $sql_req = "SELECT COUNT(*) as count FROM note_requests WHERE status = 'pending'";
    $stmt_req = $conn->prepare($sql_req);
} else {
    $sql_req = "SELECT COUNT(*) as count FROM note_requests nr 
                JOIN topics t ON nr.topic_id = t.id 
                JOIN modules m ON t.module_id = m.id 
                WHERE nr.status = 'pending' AND m.language_id = ?";
    $stmt_req = $conn->prepare($sql_req);
    $stmt_req->bind_param("i", $assigned_language);
}
$stmt_req->execute();
$pending_count = $stmt_req->get_result()->fetch_assoc()['count'];
$stmt_req->close();

$page_title = "Admin Dashboard";
require '../includes/header.php';
?>

<div class="container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h5 style="text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-bottom: 5px;">Overview</h5>
            <h1>Dashboard</h1>
        </div>
        
        <?php if ($is_super_admin): ?>
            <a href="add_language.php" class="button" style="width: auto;">
                <i class="fa-solid fa-plus"></i> Add New Language
            </a>
        <?php endif; ?>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 40px;">
        <div class="card" style="text-align: left; padding: 20px; border-left: 4px solid var(--primary-color);">
            <h5 style="color: var(--text-secondary); margin-bottom: 5px;">Total Languages</h5>
            <h2 style="margin: 0;"><?php echo count($languages); ?></h2>
        </div>
        <div class="card" style="text-align: left; padding: 20px; border-left: 4px solid #2ecc71;">
            <h5 style="color: var(--text-secondary); margin-bottom: 5px;">Your Role</h5>
            <h4 style="margin: 0; font-weight: 600;">
                <?php echo $is_super_admin ? 'Super Admin' : 'Language Admin'; ?>
            </h4>
        </div>
    </div>

    <?php if ($pending_count > 0): ?>
    <div class="card" style="margin-bottom: 40px; border-left: 4px solid #ff9f43; display: flex; justify-content: space-between; align-items: center;">
        <div style="text-align: left;">
            <h3 style="margin: 0; color: #ff9f43;"><i class="fa-solid fa-bell"></i> Action Required</h3>
            <p style="margin: 5px 0 0 0; color: var(--text-secondary);">
                You have <strong><?php echo $pending_count; ?></strong> pending note request(s) from students.
            </p>
        </div>
        <a href="view_requests.php" class="button" style="background: #ff9f43; color: white; width: auto;">
            View Requests
        </a>
    </div>
    <?php endif; ?>

    <h3 class="section-title">Managed Languages</h3>
    
    <?php if (count($languages) > 0): ?>
        <div class="card-grid">
            <?php foreach ($languages as $lang): ?>
                <div class="card language-card" style="display: flex; flex-direction: column; height: 100%;">
                    <div style="margin-bottom: 20px;">
                        <i class="fa-brands fa-<?php echo strtolower($lang['name']); ?>" style="font-size: 3rem; color: var(--primary-color);"></i>
                    </div>
                    
                    <h2 style="margin-bottom: 10px;"><?php echo htmlspecialchars($lang['name']); ?></h2>
                    <p style="color: var(--text-secondary); flex-grow: 1;">
                        <?php echo htmlspecialchars($lang['description']); ?>
                    </p>

                    <div style="margin-top: 20px; display: grid; gap: 10px;">
                        <a href="manage_roadmap.php?id=<?php echo $lang['id']; ?>" class="button">
                            <i class="fa-solid fa-layer-group"></i> Manage Modules
                        </a>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <a href="edit_language.php?id=<?php echo $lang['id']; ?>" class="button" style="background: var(--background-color); color: var(--text-color); border: 1px solid var(--border-color);">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                            
                            <?php if ($is_super_admin): ?>
                                <a href="delete_language.php?id=<?php echo $lang['id']; ?>" class="button" style="background: var(--danger-color); color: white;" onclick="return confirm('Are you sure? This will delete all modules and topics inside!');">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </a>
                            <?php else: ?>
                                <button class="button" style="background: var(--background-color); opacity: 0.5; cursor: not-allowed;" disabled>
                                    <i class="fa-solid fa-lock"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>No languages found assigned to your account.</p>
        </div>
    <?php endif; ?>

</div>

<?php require '../includes/footer.php'; ?>