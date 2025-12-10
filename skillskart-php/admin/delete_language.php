<?php
session_start();

// Protect the page: Only logged-in admins can access this.
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require '../core/db_connect.php';

// Check if a language ID was provided in the URL
if (isset($_GET['id'])) {
    $language_id = $_GET['id'];

    // Use a prepared statement to prevent SQL Injection
    $sql = "DELETE FROM languages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $language_id);

    // Execute the query
    $stmt->execute();
    $stmt->close();
}

// Redirect the admin back to the dashboard
header("Location: index.php");
exit();
?>