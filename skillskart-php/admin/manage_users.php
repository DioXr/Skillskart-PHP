<?php
require '../core/auth_admin.php';
require '../core/db_connect.php';

// Super Admins only
if ($_SESSION['assigned_language'] !== null) {
    die("Access Denied: Only Super Admins can manage users.");
}

// Fetch all users (Removed 'created_at' to fix the error)
$sql = "SELECT id, email, subscription FROM users ORDER BY id DESC";
$result = $conn->query($sql);

$page_title = "Manage Users";
require '../includes/header.php';
?>

<div class="container" style="text-align: left;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
        <div>
            <h5 style="text-transform: uppercase; color: var(--text-secondary); margin-bottom: 5px;">Admin Control</h5>
            <h1 style="margin: 0;">Manage Users</h1>
        </div>
        
        <div style="display: flex; gap: 15px; align-items: center;">
            <div style="position: relative;">
                <i class="fa-solid fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                <input type="text" id="userSearch" placeholder="Search by email..." 
                       style="padding: 10px 15px 10px 40px; border-radius: 50px; background: var(--surface-color); border: 1px solid var(--border-color); color: var(--text-color); width: 250px;">
            </div>
            
            <a href="index.php" class="button" style="background: var(--background-color); border: 1px solid var(--border-color); color: var(--text-color); width: auto;"> 
                &larr; Dashboard
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 50px 2fr 1fr 1fr; padding: 10px 20px; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; border-bottom: 1px solid var(--border-color); margin-bottom: 10px;">
        <span>ID</span>
        <span>User Details</span>
        <span>Status</span>
        <span style="text-align: right;">Actions</span>
    </div>

    <div id="userList">
        <?php if ($result->num_rows > 0): ?>
            <?php while($user = $result->fetch_assoc()): ?>
                
                <div class="user-row" style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 10px; padding: 15px 20px; display: grid; grid-template-columns: 50px 2fr 1fr 1fr; align-items: center; transition: transform 0.2s;">
                    
                    <span style="color: var(--text-secondary);">#<?php echo $user['id']; ?></span>
                    
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 35px; height: 35px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <span class="user-email" style="font-weight: 500;"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>

                    <div>
                        <?php if ($user['subscription'] === 'premium'): ?>
                            <span style="background: rgba(255, 215, 0, 0.15); color: #ffd700; border: 1px solid rgba(255, 215, 0, 0.3); padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize;">
                                <i class="fa-solid fa-crown"></i> Premium
                            </span>
                        <?php else: ?>
                            <span style="background: rgba(108, 117, 125, 0.15); color: #aab2bd; border: 1px solid rgba(108, 117, 125, 0.3); padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize;">
                                Free Plan
                            </span>
                        <?php endif; ?>
                    </div>

                    <div style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="button" style="padding: 6px 12px; font-size: 0.8rem; width: auto;">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');" class="button" style="padding: 6px 12px; font-size: 0.8rem; width: auto; background: var(--danger-color); border: none;">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>

                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state" style="text-align: center; padding: 40px; border: 1px dashed var(--border-color); border-radius: 8px; color: var(--text-secondary);">
                <p>No users found in the database.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    document.getElementById('userSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('.user-row');

        rows.forEach(row => {
            let email = row.querySelector('.user-email').textContent.toLowerCase();
            if (email.includes(filter)) {
                row.style.display = "grid";
            } else {
                row.style.display = "none";
            }
        });
    });
</script>

<?php
$conn->close();
require '../includes/footer.php';
?>