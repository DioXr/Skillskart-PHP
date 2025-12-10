<?php
require '../core/auth_admin.php'; 
require '../core/db_connect.php';

// --- 1. ROBUST ID CHECK ---
$topic_id = 0;
if (isset($_GET['id'])) { $topic_id = (int)$_GET['id']; } 
elseif (isset($_GET['topic_id'])) { $topic_id = (int)$_GET['topic_id']; }

if ($topic_id <= 0) { 
    die("<h3 style='color:red; padding:20px;'>Error: Missing Topic ID.</h3>"); 
}

// --- 2. FETCH DETAILS ---
$sql_topic = "SELECT t.title, m.language_id, m.title as module_title, l.name as lang_name 
              FROM topics t 
              JOIN modules m ON t.module_id = m.id 
              JOIN languages l ON m.language_id = l.id
              WHERE t.id = ?";
$stmt_topic = $conn->prepare($sql_topic);
$stmt_topic->bind_param("i", $topic_id);
$stmt_topic->execute();
$topic_details = $stmt_topic->get_result()->fetch_assoc();
$stmt_topic->close();

if (!$topic_details) { die("Topic not found."); }

$language_id = $topic_details['language_id'];
$topic_title = $topic_details['title'];

// Permission Check
$is_super_admin = ($_SESSION['assigned_language'] === null);
if (!$is_super_admin && $_SESSION['assigned_language'] !== $language_id) {
    die("Access Denied.");
}

// --- 3. HANDLE ADD BLOCK ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_block'])) {
    $type = $_POST['type'];
    $value = $_POST['value'];

    $sql_order = "SELECT MAX(order_index) as max_order FROM content_blocks WHERE topic_id = ?";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("i", $topic_id);
    $stmt_order->execute();
    $max_order = $stmt_order->get_result()->fetch_assoc()['max_order'];
    $order_index = $max_order + 1;
    $stmt_order->close();

    $sql_insert = "INSERT INTO content_blocks (topic_id, type, value, order_index) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("issi", $topic_id, $type, $value, $order_index);
    $stmt_insert->execute();
    $stmt_insert->close();
    
    header("Location: edit_content.php?id=" . $topic_id);
    exit();
}

// --- 4. HANDLE UPDATE BLOCK (New Feature) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_block'])) {
    $block_id = (int)$_POST['block_id'];
    $value = $_POST['value'];
    
    $sql_update = "UPDATE content_blocks SET value = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $value, $block_id);
    $stmt_update->execute();
    $stmt_update->close();
    
    header("Location: edit_content.php?id=" . $topic_id . "&status=updated");
    exit();
}

// --- 5. HANDLE REORDER (New Feature) ---
if (isset($_GET['action']) && isset($_GET['block_id'])) {
    $block_id = (int)$_GET['block_id'];
    $action = $_GET['action']; // 'up' or 'down'
    
    // Find current block
    $curr = $conn->query("SELECT order_index FROM content_blocks WHERE id=$block_id")->fetch_assoc();
    $current_order = $curr['order_index'];
    
    if ($action === 'up') {
        $swap_sql = "SELECT id, order_index FROM content_blocks WHERE topic_id=$topic_id AND order_index < $current_order ORDER BY order_index DESC LIMIT 1";
    } else {
        $swap_sql = "SELECT id, order_index FROM content_blocks WHERE topic_id=$topic_id AND order_index > $current_order ORDER BY order_index ASC LIMIT 1";
    }
    
    $swap = $conn->query($swap_sql)->fetch_assoc();
    
    if ($swap) {
        $swap_id = $swap['id'];
        $swap_order = $swap['order_index'];
        
        // Swap orders
        $conn->query("UPDATE content_blocks SET order_index=$swap_order WHERE id=$block_id");
        $conn->query("UPDATE content_blocks SET order_index=$current_order WHERE id=$swap_id");
    }
    
    header("Location: edit_content.php?id=" . $topic_id);
    exit();
}

// --- 6. FETCH BLOCKS ---
$sql_fetch = "SELECT id, type, value, order_index FROM content_blocks WHERE topic_id = ? ORDER BY order_index ASC";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $topic_id);
$stmt_fetch->execute();
$content_blocks = $stmt_fetch->get_result();
$stmt_fetch->close();

$page_title = "Edit Content";
require '../includes/header.php';
?>

<div class="container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px;">
        <div>
            <h5 style="text-transform: uppercase; color: var(--text-secondary); margin-bottom: 5px; font-size: 0.9rem;">
                <?php echo htmlspecialchars($topic_details['lang_name']); ?> <span style="opacity:0.5">/</span> <?php echo htmlspecialchars($topic_details['module_title']); ?>
            </h5>
            <h1 style="margin: 0;"><?php echo htmlspecialchars($topic_title); ?></h1>
        </div>
        <a href="manage_roadmap.php?id=<?php echo htmlspecialchars($language_id); ?>" class="button" style="background: var(--background-color); border: 1px solid var(--border-color); color: var(--text-color); width: auto;"> 
            &larr; Back to Roadmap
        </a>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: 1fr 2fr; gap: 40px; align-items: start;">
        
        <div style="position: sticky; top: 20px;">
            <div class="card" style="border-top: 4px solid var(--primary-color);">
                <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary-color);">
                    <i class="fa-solid fa-square-plus"></i> Add Block
                </h3>
                
                <form action="edit_content.php?id=<?php echo $topic_id; ?>" method="post">
                    <input type="hidden" name="add_block" value="1">
                    <div class="form-group">
                        <label for="type">Block Type</label>
                        <select name="type" id="contentTypeSelector" onchange="updatePlaceholder()" style="cursor: pointer;">
                            <option value="paragraph">Paragraph (Text)</option>
                            <option value="heading">Sub-Heading</option>
                            <option value="code">Code Block</option>
                            <option value="list">List (JSON Array)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="value">Content</label>
                        <textarea name="value" id="contentValue" rows="12" required 
                            style="width: 100%; font-family: 'Consolas', monospace; line-height: 1.5; font-size: 0.95rem; background: var(--background-color); border: 1px solid var(--border-color); color: var(--text-color); padding: 15px; border-radius: 6px;"
                            placeholder="Enter your text here..."></textarea>
                    </div>
                    
                    <button type="submit" class="button" style="width: 100%;">Insert Block</button>
                </form>
            </div>
        </div>

        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0;">Live Preview</h3>
                <span style="font-size: 0.9rem; color: var(--text-secondary);">
                    <?php echo $content_blocks->num_rows; ?> blocks total
                </span>
            </div>
            
            <?php if ($content_blocks->num_rows > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php while($block = $content_blocks->fetch_assoc()): ?>
                        
                        <div class="card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-color);">
                            
                            <div style="background: rgba(255,255,255,0.03); padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color);">
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <span style="font-size: 0.75rem; background: var(--border-color); padding: 2px 8px; border-radius: 4px; color: var(--text-secondary); font-weight: bold;">
                                        #<?php echo $block['order_index']; ?>
                                    </span>
                                    <span style="font-size: 0.85rem; text-transform: uppercase; font-weight: 600; color: var(--text-secondary);">
                                        <?php echo ucfirst($block['type']); ?>
                                    </span>
                                </div>
                                
                                <div style="display: flex; gap: 5px;">
                                    <a href="edit_content.php?id=<?php echo $topic_id; ?>&action=up&block_id=<?php echo $block['id']; ?>" class="button" title="Move Up" style="padding: 5px 8px; width: auto; font-size: 0.8rem; background: transparent; border: 1px solid var(--border-color); color: var(--text-secondary);">
                                        <i class="fa-solid fa-arrow-up"></i>
                                    </a>
                                    <a href="edit_content.php?id=<?php echo $topic_id; ?>&action=down&block_id=<?php echo $block['id']; ?>" class="button" title="Move Down" style="padding: 5px 8px; width: auto; font-size: 0.8rem; background: transparent; border: 1px solid var(--border-color); color: var(--text-secondary);">
                                        <i class="fa-solid fa-arrow-down"></i>
                                    </a>
                                    <button onclick="toggleEdit(<?php echo $block['id']; ?>)" class="button" title="Edit" style="padding: 5px 8px; width: auto; font-size: 0.8rem; background: transparent; border: 1px solid var(--primary-color); color: var(--primary-color);">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="delete_content_block.php?id=<?php echo $block['id']; ?>" onclick="return confirm('Delete this block?');" class="button" title="Delete" style="padding: 5px 8px; width: auto; font-size: 0.8rem; background: transparent; border: 1px solid var(--danger-color); color: var(--danger-color);">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </div>

                            <div id="view-<?php echo $block['id']; ?>" style="padding: 25px;">
                                <?php if ($block['type'] === 'code'): ?>
                                    <pre style="background: #151515; color: #d4d4d4; padding: 15px; border-radius: 6px; margin: 0; font-family: monospace; overflow-x: auto; border: 1px solid #333;"><?php echo htmlspecialchars($block['value']); ?></pre>
                                <?php elseif ($block['type'] === 'heading'): ?>
                                    <h3 style="margin: 0; color: var(--primary-color);"><?php echo htmlspecialchars($block['value']); ?></h3>
                                <?php elseif ($block['type'] === 'list'): ?>
                                    <?php 
                                        $listItems = json_decode($block['value']);
                                        if (is_array($listItems)) {
                                            echo "<ul style='margin: 0; padding-left: 20px;'>";
                                            foreach($listItems as $item) echo "<li>" . htmlspecialchars($item) . "</li>";
                                            echo "</ul>";
                                        } else {
                                            echo "<span style='color:red'>Invalid JSON List</span>";
                                        }
                                    ?>
                                <?php else: ?>
                                    <p style="margin: 0; line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($block['value']); ?></p>
                                <?php endif; ?>
                            </div>

                            <div id="edit-<?php echo $block['id']; ?>" style="display: none; padding: 20px; background: rgba(0,0,0,0.2);">
                                <form action="edit_content.php?id=<?php echo $topic_id; ?>" method="post">
                                    <input type="hidden" name="update_block" value="1">
                                    <input type="hidden" name="block_id" value="<?php echo $block['id']; ?>">
                                    
                                    <textarea name="value" rows="6" style="width: 100%; font-family: monospace; padding: 10px; background: var(--background-color); color: var(--text-color); border: 1px solid var(--border-color); border-radius: 4px;"><?php echo htmlspecialchars($block['value']); ?></textarea>
                                    
                                    <div style="margin-top: 10px; display: flex; gap: 10px; justify-content: flex-end;">
                                        <button type="button" onclick="toggleEdit(<?php echo $block['id']; ?>)" style="background: transparent; color: var(--text-secondary); border: 1px solid var(--border-color); padding: 5px 15px; cursor: pointer;">Cancel</button>
                                        <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;">Save</button>
                                    </div>
                                </form>
                            </div>

                        </div>

                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>This topic has no content yet.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
    function updatePlaceholder() {
        const type = document.getElementById('contentTypeSelector').value;
        const textarea = document.getElementById('contentValue');
        if (type === 'code') textarea.placeholder = "print('Hello World')";
        else if (type === 'list') textarea.placeholder = '["Item 1", "Item 2"]';
        else if (type === 'heading') textarea.placeholder = "Section Title";
        else textarea.placeholder = "Enter text here...";
    }

    function toggleEdit(id) {
        const viewDiv = document.getElementById('view-' + id);
        const editDiv = document.getElementById('edit-' + id);
        if (editDiv.style.display === 'none') {
            editDiv.style.display = 'block';
            viewDiv.style.display = 'none';
        } else {
            editDiv.style.display = 'none';
            viewDiv.style.display = 'block';
        }
    }
</script>

<?php 
$conn->close();
require '../includes/footer.php'; 
?>