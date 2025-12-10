<?php
require '../core/auth_admin.php'; 
require '../core/db_connect.php';

// --- HANDLE FORM SUBMISSION (Fulfill) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $req_id = (int)$_POST['request_id'];
    $notes = $_POST['notes'];
    
    // File Upload Logic (Optional)
    $file_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        $upload_dir = '../uploads/notes/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['attachment']['name']);
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $filename)) {
            $file_path = 'uploads/notes/' . $filename;
        }
    }

    $sql = "UPDATE note_requests SET status = 'fulfilled', response_notes = ?, response_file_path = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $notes, $file_path, $req_id);
    $stmt->execute();
    
    header("Location: view_requests.php?msg=fulfilled");
    exit();
}

// Fetch Pending Requests
$sql = "SELECT nr.*, u.email, t.title as topic_title, l.name as language_name
        FROM note_requests nr
        JOIN users u ON nr.user_id = u.id
        JOIN topics t ON nr.topic_id = t.id
        JOIN modules m ON t.module_id = m.id
        JOIN languages l ON m.language_id = l.id
        WHERE nr.status = 'pending'
        ORDER BY nr.created_at DESC";
$requests = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

$page_title = "Manage Requests";
require '../includes/header.php';
?>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1>Pending Requests</h1>
        <a href="index.php" class="button" style="background:var(--background-color); color:var(--text-color); border:1px solid var(--border-color);">Back to Dashboard</a>
    </div>

    <?php if (count($requests) > 0): ?>
        <div class="card-grid">
            <?php foreach ($requests as $req): ?>
                <div class="card" style="text-align:left;">
                    <div style="margin-bottom:15px;">
                        <span class="badge" style="background:var(--primary-color); color:white;"><?php echo htmlspecialchars($req['language_name']); ?></span>
                        <h3 style="margin-top:10px;"><?php echo htmlspecialchars($req['topic_title']); ?></h3>
                    </div>
                    <p style="color:var(--text-secondary); margin-bottom:5px;"><strong>Student:</strong> <?php echo htmlspecialchars($req['email']); ?></p>
                    <p style="color:var(--text-secondary); margin-bottom:20px;"><small><?php echo date('M d, Y', strtotime($req['created_at'])); ?></small></p>
                    
                    <button onclick="openModal(<?php echo $req['id']; ?>)" class="button" style="width:100%;">
                        <i class="fa-solid fa-paper-plane"></i> Fulfill Request
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-check-circle"></i>
            <p>All caught up! No pending requests.</p>
        </div>
    <?php endif; ?>
</div>

<div id="fulfillModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000; justify-content:center; align-items:center;">
    <div style="background:var(--surface-color); padding:30px; border-radius:12px; width:500px; max-width:90%; border:1px solid var(--border-color);">
        <h2 style="margin-top:0;">Send Notes</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="request_id" id="modalRequestId">
            
            <div class="form-group">
                <label>Text Response</label>
                <textarea name="notes" rows="5" required style="width:100%;"></textarea>
            </div>
            
            <div class="form-group">
                <label>Attach File (PDF/Doc)</label>
                <input type="file" name="attachment">
            </div>
            
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" onclick="document.getElementById('fulfillModal').style.display='none'" style="background:transparent; border:1px solid var(--border-color); color:var(--text-color);">Cancel</button>
                <button type="submit">Send</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById('modalRequestId').value = id;
    document.getElementById('fulfillModal').style.display = 'flex';
}
</script>

<?php require '../includes/footer.php'; ?>