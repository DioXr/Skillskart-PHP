<?php
require 'core/db_connect.php';

// Get the search query from the URL, if it exists
$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';

$language_results = [];
$topic_results = [];

if (!empty($search_query)) {
    // --- Search for matching languages ---
    $sql_languages = "SELECT id, name, description FROM languages WHERE name LIKE ? OR description LIKE ?";
    $stmt_languages = $conn->prepare($sql_languages);
    $search_term = "%{$search_query}%"; // Add wildcards for LIKE search
    $stmt_languages->bind_param("ss", $search_term, $search_term);
    $stmt_languages->execute();
    $result_languages = $stmt_languages->get_result();
    while ($row = $result_languages->fetch_assoc()) {
        $language_results[] = $row;
    }
    $stmt_languages->close();

    // --- Search for matching topics ---
    $sql_topics = "SELECT slug, title FROM topics WHERE title LIKE ?";
    $stmt_topics = $conn->prepare($sql_topics);
    $stmt_topics->bind_param("s", $search_term);
    $stmt_topics->execute();
    $result_topics = $stmt_topics->get_result();
    while ($row = $result_topics->fetch_assoc()) {
        $topic_results[] = $row;
    }
    $stmt_topics->close();
}

$page_title = "Search Results";
require 'includes/header.php';
?>

<h2>Search Results for: "<?php echo htmlspecialchars($search_query); ?>"</h2>

<hr>

<h3>Matching Languages</h3>
<?php if (!empty($language_results)): ?>
    <ul>
        <?php foreach ($language_results as $lang): ?>
            <li>
                <a href="roadmap.php?id=<?php echo htmlspecialchars($lang['id']); ?>">
                    <strong><?php echo htmlspecialchars($lang['name']); ?></strong> - <?php echo htmlspecialchars($lang['description']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No matching languages found.</p>
<?php endif; ?>

<hr>

<h3>Matching Topics</h3>
<?php if (!empty($topic_results)): ?>
    <ul>
        <?php foreach ($topic_results as $topic): ?>
            <li>
                <a href="topic.php?slug=<?php echo htmlspecialchars($topic['slug']); ?>">
                    <strong><?php echo htmlspecialchars($topic['title']); ?></strong>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No matching topics found.</p>
<?php endif; ?>


<?php
$conn->close();
require 'includes/footer.php';
?>