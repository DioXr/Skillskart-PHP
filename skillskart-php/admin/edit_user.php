<?php
require '../core/auth_admin.php';
require '../core/db_connect.php';

if ($_SESSION['assigned_language'] !== null) { die("Access Denied."); }

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) { header("Location: manage_users.php"); exit(); }

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subscription = $_POST['subscription'];
    $sql = "UPDATE users SET subscription = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $subscription, $user_id);
    if ($stmt->execute()) {
        header("Location: manage_users.php");
        exit();
    }
    $stmt->close();
}

// Fetch user data
$user_sql = "SELECT email, subscription FROM users WHERE id = ?";
$stmt_user = $conn->prepare($user_sql);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

if (!$user) { die("User not found."); }

$page_title = "Edit User";
require '../includes/header.php';
?>
<h2>Edit User: <?php echo htmlspecialchars($user['email']); ?></h2>
<a href="manage_users.php"> &larr; Back to User Management</a>

<form method="post" style="margin-top: 20px;">
    <div class="form-group">
        <label for="subscription">Subscription Status:</label>
        <select name="subscription" id="subscription">
            <option value="free" <?php echo $user['subscription'] === 'free' ? 'selected' : ''; ?>>Free</option>
            <option value="premium" <?php echo $user['subscription'] === 'premium' ? 'selected' : ''; ?>>Premium</option>
        </select>
    </div>
    <button type="submit">Update User</button>
</form>

<?php 
$conn->close();
require '../includes/footer.php'; 
?>