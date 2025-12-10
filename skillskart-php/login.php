<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'core/db_connect.php';

$errors = [];
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Both fields are required.";
    } else {
        $sql = "SELECT id, password, subscription FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                
                // --- SET SESSION VARIABLES ---
                $_SESSION['student_id'] = $user['id']; 
                $_SESSION['user_id']    = $user['id']; 
                $_SESSION['subscription'] = $user['subscription']; 
                $_SESSION['role'] = 'student'; 
                
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Invalid email or password.";
        }
        $stmt->close();
    }
    $conn->close();
}

$page_title = "Login";
require 'includes/header.php';
?>

<div class="card" style="max-width: 550px; margin: 60px auto; padding: 40px; border-top: 4px solid var(--primary-color); box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="margin: 0; font-size: 2rem;">Welcome Back</h2>
        <p style="color: var(--text-secondary); margin-top: 5px;">Login to continue learning</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="errors" style="background: rgba(220, 53, 69, 0.1); color: #dc3545; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid rgba(220, 53, 69, 0.2);">
            <?php foreach ($errors as $error): ?>
                <p style="margin: 0;"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="post">
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <div style="position: relative;">
                <i class="fa-solid fa-envelope" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required 
                       style="padding-left: 40px; width: 100%;" placeholder="you@example.com">
            </div>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <div style="position: relative;">
                <i class="fa-solid fa-lock" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                <input type="password" id="password" name="password" required 
                       style="padding-left: 40px; width: 100%;" placeholder="••••••••">
            </div>
        </div>
        
        <button type="submit" class="button" style="width: 100%; font-size: 1rem; padding: 12px; margin-top: 10px;">
            Login <i class="fa-solid fa-arrow-right-to-bracket"></i>
        </button>

    </form>

    <p style="text-align: center; margin-top: 25px; color: var(--text-secondary); font-size: 0.9rem;">
        Don't have an account? <a href="register.php" style="font-weight: 600;">Register here</a>
    </p>

</div>

<?php require 'includes/footer.php'; ?>