<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '..config/db.php'; // Ensure this file contains your PDO connection logic

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare an INSERT statement
$sql = "INSERT INTO incidents (title, description, reported_by, status) VALUES (?, ?, ?, 'Open')";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

// Bind parameters
$title = $_POST['title'];
$description = $_POST['description'];
$reported_by = $_POST['reported_by']; // Assumed to be set correctly elsewhere; consider security implications

$stmt->bind_param("ssi", $title, $description, $reported_by);

// Execute the statement
if (!$stmt->execute()) {
    die('Execute error: ' . $stmt->error);
} else {
    echo "New incident reported successfully with status 'Open'";
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
