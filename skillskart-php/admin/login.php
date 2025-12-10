<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Clear any existing user session
if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start(); // Start a fresh session for the admin
}

require '../core/db_connect.php';

$errors = [];
$username = '';

if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if (empty($username) || empty($password)) {
        $errors[] = "Both fields are required.";
    } else {
        $sql = "SELECT id, password, assigned_language_id FROM admins WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $username;
                $_SESSION['assigned_language'] = $admin['assigned_language_id'];
                header("Location: index.php");
                exit();
            }
        }
        $errors[] = "Invalid username or password.";
        $stmt->close();
    }
    $conn->close();
}

$page_title = "Admin Login";
require '../includes/header.php';
?>

<form action="login.php" method="post">
    <h2>Admin Login</h2>
    <?php if (!empty($errors)): ?>
        <div class="errors" style="color: red; margin-bottom: 15px;"><p><?php echo implode('<br>', $errors); ?></p></div>
    <?php endif; ?>
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
    </div>
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Login</button>
</form>

<?php require '../includes/footer.php'; ?>