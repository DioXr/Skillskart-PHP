<?php
require 'db_connect.php';

// Get the search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]); // return empty if query is too short
    exit;
}

$results = [];

// 1. Search Languages
$sql_lang = "SELECT id, name FROM languages WHERE name LIKE ? LIMIT 3";
$stmt = $conn->prepare($sql_lang);
$term = "%" . $query . "%";
$stmt->bind_param("s", $term);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $results[] = [
        'category' => 'Language',
        'title' => $row['name'],
        'url' => 'roadmap.php?id=' . $row['id'] // Adjust based on your routing
    ];
}

// 2. Search Topics
$sql_topic = "SELECT slug, title FROM topics WHERE title LIKE ? LIMIT 5";
$stmt = $conn->prepare($sql_topic);
$stmt->bind_param("s", $term);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $results[] = [
        'category' => 'Topic',
        'title' => $row['title'],
        'url' => 'topic.php?slug=' . $row['slug']
    ];
}

header('Content-Type: application/json');
echo json_encode($results);
?>