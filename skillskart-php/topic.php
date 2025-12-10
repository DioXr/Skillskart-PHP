<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'core/db_connect.php';

$topic_slug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (empty($topic_slug)) { header("Location: index.php"); exit(); }

// 1. Fetch details
$sql = "SELECT t.id, t.title, t.is_premium, m.title as module_title, m.id as module_id, m.language_id, l.name as language_name
        FROM topics t
        JOIN modules m ON t.module_id = m.id
        JOIN languages l ON m.language_id = l.id
        WHERE t.slug = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $topic_slug);
$stmt->execute();
$topic_details = $stmt->get_result()->fetch_assoc();

if (!$topic_details) { die("Topic not found."); }

// --- SIDEBAR VARIABLES ---
$numeric_topic_id = $topic_details['id'];
$language_id = $topic_details['language_id'];
$language_name = $topic_details['language_name'];
$stmt->close();

// 2. Auth Check (Robust: Checks both session types)
$user_id = null;
if (isset($_SESSION['student_id'])) {
    $user_id = $_SESSION['student_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

// 3. Check Progress
$is_completed = false;
if ($user_id) {
    $sql_progress = "SELECT id FROM user_progress WHERE user_id = ? AND topic_id = ?";
    $stmt = $conn->prepare($sql_progress);
    $stmt->bind_param("ii", $user_id, $numeric_topic_id);
    $stmt->execute();
    $is_completed = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

// 4. Check Subscription (THE FIX IS HERE)
$user_subscription = 'free';

// Check Database
if ($user_id) {
    $sql_user = "SELECT subscription FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user) { 
        $user_subscription = $user['subscription']; 
    }
    $stmt->close();
}

// --- CRITICAL FIX: Normalize the text ---
// This converts "Premium", "PREMIUM", or "premium " to just "premium"
$user_subscription = strtolower(trim($user_subscription));

$page_title = $topic_details['title'];
require 'includes/header.php';
?>

<div class="roadmap-layout">
    <?php require 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        
        <?php if ($topic_details['is_premium'] == 1 && $user_subscription !== 'premium'): ?>
            <div class="paywall-box">
                <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 20px;">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h2>Premium Content</h2>
                <p style="max-width: 500px; margin: 0 auto 20px auto; color: var(--text-secondary);">
                    This topic is part of our comprehensive <strong><?php echo htmlspecialchars($language_name); ?></strong> pro curriculum.
                </p>
                <a href='pricing.php' class="button" style="display: inline-block; width: auto; padding: 12px 30px;">
                    <i class="fa-solid fa-crown"></i> Upgrade to Premium
                </a>
            </div>
        
        <?php else: ?>
            <div class="content-box">
                <div class="topic-header">
                    <div class="breadcrumb">
                        <span><?php echo htmlspecialchars($language_name); ?></span>
                        <i class="fa-solid fa-chevron-right"></i>
                        <span><?php echo htmlspecialchars($topic_details['module_title']); ?></span>
                    </div>
                    
                    <div class="title-row">
                        <h1><?php echo htmlspecialchars($topic_details['title']); ?></h1>
                        <?php if ($user_id): ?>
                            <div class="completion-wrapper">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="progress-checkbox" data-topic-id="<?php echo $numeric_topic_id; ?>" <?php echo $is_completed ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                                <span class="toggle-label">Mark Complete</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <hr>
                
                <div class="topic-body">
                    <?php
                    $sql_content = "SELECT type, value FROM content_blocks WHERE topic_id = ? ORDER BY order_index";
                    $stmt = $conn->prepare($sql_content);
                    $stmt->bind_param("i", $numeric_topic_id);
                    $stmt->execute();
                    $result_content = $stmt->get_result();

                    while ($block = $result_content->fetch_assoc()) {
                        $type = $block['type'];
                        $value = $block['value'];
                        switch ($type) {
                            case 'paragraph': echo "<p>" . nl2br(htmlspecialchars($value)) . "</p>"; break;
                            case 'heading': echo "<h3>" . htmlspecialchars($value) . "</h3>"; break;
                            case 'code': 
                                echo "<div class='code-block-wrapper'><button class='copy-code-btn' onclick='copyCode(this)'>Copy</button><pre class='code-block'><code class='language-javascript'>" . htmlspecialchars($value) . "</code></pre></div>"; 
                                break;
                            case 'list':
                                $list_items = json_decode($value, true);
                                if (is_array($list_items)) {
                                    echo "<ul>";
                                    foreach ($list_items as $item) { echo "<li>" . htmlspecialchars($item) . "</li>"; }
                                    echo "</ul>";
                                }
                                break;
                        }
                    }
                    ?>
                </div>

                <?php if ($user_subscription === 'premium'): ?>
                    <div class="module-item" style="margin-top: 50px; border-left: 4px solid var(--primary-color);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin-bottom: 5px;"><i class="fa-solid fa-user-graduate"></i> Need Expert Help?</h4>
                                <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 0;">Request custom notes from a mentor.</p>
                            </div>
                            <div>
                                <?php if (isset($_GET['status']) && $_GET['status'] === 'requested'): ?>
                                    <button class="button" disabled style="background: #2ecc71; cursor: default;"><i class="fa-solid fa-check"></i> Request Sent</button>
                                <?php else: ?>
                                    <a href="core/request_notes.php?topic_id=<?php echo $numeric_topic_id; ?>" class="button" style="background: var(--surface-color); color: var(--text-color); border: 1px solid var(--border-color);">Request Notes</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    const checkbox = document.getElementById('progress-checkbox');
    if(checkbox) {
        checkbox.addEventListener('change', function() {
            const topicId = this.getAttribute('data-topic-id');
            const isCompleted = this.checked;

            fetch('core/ajax_progress.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `topic_id=${topicId}&completed=${isCompleted}`
            })
            .then(res => res.text()) 
            .then(text => {
                // DEBUG: Log to console (F12) instead of annoying Alert
                console.log("Server response:", text); 
                
                // SILENT SUCCESS: 
                // We assume it worked. We do NOT alert the user.
                // We only revert the toggle if we specifically detect a "Fatal" error.
                if (text.includes('error_login') || text.includes('Database error')) {
                    alert('Login expired or Database error.');
                    this.checked = !isCompleted;
                } else {
                    console.log('Progress visual updated.');
                }
            })
            .catch(err => {
                console.error(err);
                // Only alert on total network failure
                // alert('Network error'); 
            });
        });
    }

    function copyCode(btn) {
        const codeBlock = btn.nextElementSibling.querySelector('code');
        navigator.clipboard.writeText(codeBlock.innerText).then(() => {
            btn.innerText = 'Copied!';
            setTimeout(() => { btn.innerText = 'Copy'; }, 2000);
        });
    }
</script>

<?php
$conn->close();
require 'includes/footer.php';
?>