<?php
require '../core/auth_admin.php';
require '../core/db_connect.php';

$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($request_id <= 0) { header("Location: index.php"); exit(); }

// ... (A full security check would go here)

$errors = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $notes = trim($_POST['response_notes']);
    $file_path = null;

    // --- FILE UPLOAD LOGIC ---
    if (isset($_FILES['note_document']) && $_FILES['note_document']['error'] == 0) {
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_size = 5 * 1024 * 1024; // 5 MB

        if (in_array($_FILES['note_document']['type'], $allowed_types) && $_FILES['note_document']['size'] <= $max_size) {
            $file_extension = pathinfo($_FILES['note_document']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('note_', true) . '.' . $file_extension;
            $upload_dir = '../uploads/';
            
            if (move_uploaded_file($_FILES['note_document']['tmp_name'], $upload_dir . $new_filename)) {
                $file_path = 'uploads/' . $new_filename;
            } else {
                $errors[] = "Failed to move uploaded file.";
            }
        } else {
            $errors[] = "Invalid file type or size is too large (max 5MB). Allowed types: PDF, DOC, DOCX.";
        }
    }
    // --- END FILE UPLOAD LOGIC ---

    if (empty($errors)) {
        $sql = "UPDATE note_requests SET response_notes = ?, response_file_path = ?, status = 'fulfilled' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $notes, $file_path, $request_id);
        if ($stmt->execute()) {
            header("Location: view_requests.php?status=fulfilled");
            exit();
        } else {
            $errors[] = "Database error: Could not fulfill request.";
        }
    }
}

// ... (Fetch request details logic is the same)
$sql_req = "SELECT t.title as topic_title, u.email as user_email FROM note_requests nr JOIN topics t ON nr.topic_id = t.id JOIN users u ON nr.user_id = u.id WHERE nr.id = ?";
$stmt_req = $conn->prepare($sql_req);
$stmt_req->bind_param("i", $request_id);
$stmt_req->execute();
$request = $stmt_req->get_result()->fetch_assoc();
$stmt_req->close();
if (!$request) { die("Request not found."); }

$page_title = "Fulfill Request";
require '../includes/header.php';
?>
<h2>Fulfill Note Request</h2>
<a href="view_requests.php"> &larr; Back to Requests</a>

<?php if (!empty($errors)): ?>
    <div class="errors" style="color: red; margin-top: 15px;"><p><?php echo implode('<br>', $errors); ?></p></div>
<?php endif; ?>

<div style="margin-top: 20px; padding: 15px; border-radius: 8px;">
    <p><strong>User:</strong> <?php echo htmlspecialchars($request['user_email']); ?></p>
    <p><strong>Topic:</strong> <?php echo htmlspecialchars($request['topic_title']); ?></p>
</div>

<form action="fulfill_request.php?id=<?php echo $request_id; ?>" method="post" enctype="multipart/form-data" style="margin-top: 20px;">
    <div class="form-group">
        <label for="response_notes">Enter Notes / Video Link:</label><br>
        <textarea name="response_notes" id="response_notes" rows="10" style="width:100%"></textarea>
    </div>
    <div class="form-group">
        <label for="note_document">Upload Document (Optional, PDF/DOC/DOCX, max 5MB):</label><br>
        <input type="file" name="note_document" id="note_document">
    </div>
    <button type="submit">Fulfill Request</button>
</form>

<?php 
$conn->close();
require '../includes/footer.php'; 
?>