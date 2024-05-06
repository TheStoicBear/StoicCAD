<?php

// Set CORS headers
header("Access-Control-Allow-Origin: *"); // Allows all domains
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allowed methods
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Allowed headers

$secure = true; 
$httponly = true;
$samesite = 'none';
$lifetime = 600; // Corrected variable name here

if (PHP_VERSION_ID < 70300) {
    // Correctly reference $lifetime instead of $maxlifetime
    session_set_cookie_params($lifetime, '/; samesite='.$samesite, $_SERVER['HTTP_HOST'], $secure, $httponly);
} else {
    session_set_cookie_params([
        'lifetime' => $lifetime, // Corrected variable name here
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}

$servername = '164.152.122.155';
$username = 'website';
$password = "NclckXUX2NDs";
$dbname = "ksrp";


// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare an INSERT statement
$sql = "INSERT INTO incidents (title, description, reported_by) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

// Bind parameters
$title = $_POST['title'];
$description = $_POST['description'];
$reported_by = 1; // This should be a valid ID from the `users` table.

$stmt->bind_param("ssi", $title, $description, $reported_by);

// Execute the statement
if (!$stmt->execute()) {
    die('Execute error: ' . $stmt->error);
} else {
    echo "New incident reported successfully";
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
