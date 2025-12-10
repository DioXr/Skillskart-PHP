<?php
if (!isset($conn) || !isset($language_id)) {
    die("Required variables are not set for the sidebar.");
}

$sql_sidebar = "SELECT m.title as module_title, t.slug as topic_slug, t.title as topic_title
                FROM modules m
                JOIN topics t ON m.id = t.module_id
                WHERE m.language_id = ?
                ORDER BY m.order_index, t.id";

$stmt_sidebar = $conn->prepare($sql_sidebar);
$stmt_sidebar->bind_param("s", $language_id);
$stmt_sidebar->execute();
$result_sidebar = $stmt_sidebar->get_result();

$sidebar_roadmap = [];
while ($row = $result_sidebar->fetch_assoc()) {
    $sidebar_roadmap[$row['module_title']][] = [
        'slug' => $row['topic_slug'],
        'title' => $row['topic_title']
    ];
}
$stmt_sidebar->close();
?>

<aside class="sidebar">
    <h2 style="text-transform: capitalize"><?php echo htmlspecialchars($language_name ?? 'Roadmap'); ?></h2>
    <a href="roadmap.php?id=<?php echo htmlspecialchars($language_id); ?>" class="sidebar-link">Roadmap Overview</a>
    <hr>
    <?php foreach ($sidebar_roadmap as $module_title => $topics): ?>
        <h4><?php echo htmlspecialchars($module_title); ?></h4>
        <?php foreach ($topics as $topic): ?>
            <a href="topic.php?slug=<?php echo htmlspecialchars($topic['slug']); ?>" class="sidebar-link">
                <?php echo htmlspecialchars($topic['title']); ?>
            </a>
        <?php endforeach; ?>
    <?php endforeach; ?>
</aside>