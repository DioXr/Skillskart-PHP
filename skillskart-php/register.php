<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'core/db_connect.php';

$errors = [];
$email = '';

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    // Check if user already exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "A user with this email already exists.";
    }
    $stmt->close();

    // If there are no errors, proceed with registration
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert (Default subscription is 'free')
        $sql = "INSERT INTO users (email, password, subscription) VALUES (?, ?, 'free')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $hashed_password);

        if ($stmt->execute()) {
            // --- THE FIX: Set session variables immediately ---
            $new_user_id = $stmt->insert_id;
            
            $_SESSION['student_id'] = $new_user_id; 
            $_SESSION['user_id']    = $new_user_id; 
            $_SESSION['role']       = 'student';
            $_SESSION['subscription'] = 'free'; 
            
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Error: Could not register user.";
        }
        $stmt->close();
    }
    $conn->close();
}

$page_title = "Register";
require 'includes/header.php';
?>

<div style="max-width: 500px; margin: 50px auto;">
    
    <div class="card" style="padding: 40px; border-top: 4px solid var(--primary-color); box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="margin: 0; font-size: 2rem;">Create Account</h2>
            <p style="color: var(--text-secondary); margin-top: 5px;">Join us and start learning today</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="errors" style="background: rgba(220, 53, 69, 0.1); color: #dc3545; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid rgba(220, 53, 69, 0.2);">
                <?php foreach ($errors as $error): ?>
                    <p style="margin: 0;"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post">
            
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
                           style="padding-left: 40px; width: 100%;" placeholder="Min 6 characters">
                </div>
            </div>
            
            <button type="submit" class="button" style="width: 100%; font-size: 1rem; padding: 12px; margin-top: 10px;">
                Register <i class="fa-solid fa-user-plus"></i>
            </button>
            
        </form>

        <p style="text-align: center; margin-top: 25px; color: var(--text-secondary); font-size: 0.9rem;">
            Already have an account? <a href="login.php" style="font-weight: 600; color: var(--primary-color);">Login here</a>
        </p>

    </div>
</div>

<?php require 'includes/footer.php'; ?>