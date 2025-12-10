<?php
// db_connect.php
$servername = "localhost";
$username = "root";     // Default XAMPP username
$password = "";         // Default XAMPP password is empty
$dbname = "skillskart_db";

// Create connection using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}?>