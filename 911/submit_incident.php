<?php

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Session security settings
$secure = true; 
$httponly = true;
$samesite = 'none';
$lifetime = 600;

// Adjust session cookie parameters based on PHP version
if (PHP_VERSION_ID < 70300) {
    session_set_cookie_params($lifetime, '/; samesite='.$samesite, $_SERVER['HTTP_HOST'], $secure, $httponly);
} else {
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}

// Include the database connection from db.php
require_once '../config/db.php';

// Prepare an INSERT statement using PDO
$sql = "INSERT INTO incidents (title, description, reported_by) VALUES (:title, :description, :reported_by)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Prepare error: ' . $conn->errorInfo()[2]);
}

// Bind parameters using PDO
$title = $_POST['title'] ?? 'Default Title';
$description = $_POST['description'] ?? 'No description provided';
$reported_by = 1; // Ensure you validate and sanitize this

$stmt->bindParam(':title', $title);
$stmt->bindParam(':description', $description);
$stmt->bindParam(':reported_by', $reported_by);

// Execute the statement
if (!$stmt->execute()) {
    die('Execute error: ' . $stmt->errorInfo()[2]);
} else {
    echo "New incident reported successfully";
}

// No need to explicitly close statement or connection, as PDO handles this at script end
?>
