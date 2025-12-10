<?php
// 1. Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'core/db_connect.php';

// 2. Auth Check (Using student_id/user_id logic)
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['student_id'];

// --- 3. Fetch User Info ---
$sql_user = "SELECT email, subscription FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

if (!$user) {
    header("Location: logout.php");
    exit();
}

// --- 4. Fetch Progress ---
$sql_progress = "SELECT l.name as language_name, t.title as topic_title
                 FROM user_progress up
                 JOIN topics t ON up.topic_id = t.id
                 JOIN modules m ON t.module_id = m.id
                 JOIN languages l ON m.language_id = l.id
                 WHERE up.user_id = ?
                 ORDER BY l.name, t.id";
$stmt = $conn->prepare($sql_progress);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$progress = [];
while ($row = $result->fetch_assoc()) {
    $progress[$row['language_name']][] = $row['topic_title'];
}
$stmt->close();

// --- 5. Fetch Notes ---
$sql_notes = "SELECT t.title as topic_title, nr.response_notes, nr.response_file_path, nr.created_at 
              FROM note_requests nr
              JOIN topics t ON nr.topic_id = t.id
              WHERE nr.user_id = ? AND nr.status = 'fulfilled'
              ORDER BY nr.created_at DESC";
$stmt = $conn->prepare($sql_notes);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_notes = $stmt->get_result();
$fulfilled_notes = $result_notes->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = "My Profile";
require 'includes/header.php';
?>

<div class="container">
    
    <div class="profile-header-card">
        <div class="profile-avatar">
            <i class="fa-solid fa-user-astronaut"></i>
        </div>
        <div class="profile-info">
            <h5 class="label">Student Account</h5>
            <h2 class="email"><?php echo htmlspecialchars($user['email']); ?></h2>
            <div class="status-row">
                <span class="badge badge-<?php echo strtolower($user['subscription']); ?>">
                    <i class="fa-solid fa-crown"></i> <?php echo ucfirst($user['subscription']); ?> Plan
                </span>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        
        <div class="dashboard-col">
            <h3 class="section-title"><i class="fa-solid fa-chart-line"></i> Learning Journey</h3>
            
            <?php if (!empty($progress)): ?>
                <div class="progress-list">
                    <?php foreach ($progress as $language_name => $topics): ?>
                        <div class="card progress-card">
                            <div class="card-header-flex">
                                <h4><i class="fa-brands fa-<?php echo strtolower($language_name); ?>"></i> <?php echo htmlspecialchars($language_name); ?></h4>
                                <span class="count-badge"><?php echo count($topics); ?> topics</span>
                            </div>
                            <ul class="topic-list-ui">
                                <?php foreach ($topics as $topic_title): ?>
                                    <li><i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($topic_title); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-book-open"></i>
                    <p>You haven't completed any topics yet.</p>
                    <a href="index.php" class="btn-register">Browse Roadmaps</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-col">
            <h3 class="section-title"><i class="fa-solid fa-file-signature"></i> Expert Notes</h3>
            
            <?php if (!empty($fulfilled_notes)): ?>
                <div class="notes-stack">
                    <?php foreach($fulfilled_notes as $note): ?>
                        <div class="card note-card">
                            <div class="note-header">
                                <h5><?php echo htmlspecialchars($note['topic_title']); ?></h5>
                                <span class="date-badge"><?php echo date('M d', strtotime($note['created_at'])); ?></span>
                            </div>
                            
                            <?php if (!empty($note['response_notes'])): ?>
                                <div class="note-body">
                                    <?php echo nl2br(htmlspecialchars($note['response_notes'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($note['response_file_path'])): ?>
                                <a href="/skillskart-php/<?php echo htmlspecialchars($note['response_file_path']); ?>" download class="download-btn">
                                    <i class="fa-solid fa-file-arrow-down"></i> Download File
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <p>No fulfilled note requests yet.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php
$conn->close();
require 'includes/footer.php';
?>