<?php
require 'core/db_connect.php';

$language_id = isset($_GET['id']) ? $_GET['id'] : '';
if (empty($language_id)) { 
    // Redirect or show a nicer error
    header("Location: index.php"); 
    exit(); 
}

// Fetch the full roadmap structure
$sql = "SELECT l.name as language_name, m.id as module_id, m.title as module_title, t.slug as topic_slug, t.title as topic_title
        FROM languages l
        LEFT JOIN modules m ON l.id = m.language_id
        LEFT JOIN topics t ON m.id = t.module_id
        WHERE l.id = ?
        ORDER BY m.order_index, t.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $language_id);
$stmt->execute();
$result = $stmt->get_result();

$roadmap = [];
$language_name = '';

// Group data by Module
while ($row = $result->fetch_assoc()) {
    $language_name = $row['language_name'];
    if ($row['module_title']) {
        $roadmap[$row['module_title']][] = $row;
    }
}
$stmt->close();

if (empty($language_name)) { die("Language not found."); }

$page_title = $language_name . " Roadmap";
require 'includes/header.php';
?>

<script type="module">
    import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
    mermaid.initialize({ 
        startOnLoad: true, 
        theme: 'base',
        themeVariables: {
            fontFamily: 'Poppins',
            primaryColor: '#6C63FF',
            primaryTextColor: '#fff',
            lineColor: '#555'
        }
    });
</script>

<div class="container">
    <div class="roadmap-layout">
        
        <?php require 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            
            <div class="content-box">
                
                <div class="roadmap-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px;">
                    <div>
                        <h5 style="text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-bottom: 5px;">Learning Path</h5>
                        <h1 style="margin: 0; font-size: 2.2rem;">
                            <i class="fa-brands fa-<?php echo strtolower($language_id); ?>" style="color: var(--primary-color);"></i> 
                            <?php echo htmlspecialchars($language_name); ?>
                        </h1>
                    </div>
                    <div>
                        <a href="index.php" class="button" style="background: var(--background-color); color: var(--text-color); border: 1px solid var(--border-color);">
                            <i class="fa-solid fa-arrow-left"></i> Back to Courses
                        </a>
                    </div>
                </div>

                <div class="mermaid" style="display: flex; justify-content: center; overflow-x: auto;">
                    graph TD;
                    
                    %% Global Graph Styles
                    classDef moduleNode fill:#6C63FF,stroke:none,color:#fff,rx:5,ry:5,font-weight:bold;
                    classDef topicNode fill:#2d2d2d,stroke:#555,color:#eee,rx:5,ry:5;
                    classDef topicHover fill:#fff,stroke:#6C63FF,color:#000;
                    
                    <?php
                    $last_module_id = 'start';
                    $module_counter = 1;

                    // 1. Create the Start Node
                    echo "start((Start)) --> M1;\n";
                    echo "class start moduleNode;\n";

                    foreach ($roadmap as $module_title => $topics) {
                        $module_node_id = 'M' . $module_counter++;
                        
                        // Link previous module to this module
                        if ($last_module_id !== 'start') {
                            echo "{$last_module_id} --> {$module_node_id};\n";
                        }

                        // Define Module Node
                        echo "{$module_node_id}[\"{$module_title}\"];\n";
                        echo "class {$module_node_id} moduleNode;\n";
                        
                        // Define Topic Nodes
                        foreach($topics as $topic) {
                            if (!empty($topic['topic_slug'])) {
                                $topic_node_id = 'T' . preg_replace('/[^a-zA-Z0-9_-]/', '', $topic['topic_slug']);
                                
                                // Link Module -> Topic
                                echo "{$module_node_id} --> {$topic_node_id}(\"{$topic['topic_title']}\");\n";
                                
                                // Style Topic
                                echo "class {$topic_node_id} topicNode;\n";
                                
                                // Add Click Event
                                echo "click {$topic_node_id} \"topic.php?slug={$topic['topic_slug']}\" \"Start Learning\";\n";
                            }
                        }
                        
                        $last_module_id = $module_node_id;
                    }
                    ?>
                </div>
                
                <div style="text-align: center; margin-top: 30px; color: var(--text-secondary); font-size: 0.9rem;">
                    <i class="fa-solid fa-computer-mouse"></i> Click on a topic to start learning
                </div>

            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require 'includes/footer.php';
?>