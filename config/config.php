<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Database credentials
define('DB_HOST', 'YOUR_HOST_IP'); // Database host (typically 'localhost')
define('DB_USERNAME', 'YOUR_HOST_UERNAME'); // Database username (adjust as per your environment)
define('DB_PASSWORD', 'YOUR_HOST_PASSWORD'); // Database password (adjust as per your environment)
define('DB_NAME', 'YOUR_HOST_DB'); // Database name
define('CLIENT_ID', 'YOUR_DSICORD_CLIENT_ID');
define('CLIENT_SECRET', 'YOUR_DSICORD_CLIENT_SECRET');
define('REDIRECTURI', 'https://YOUR_DOMAIN_HERE/process-oauth.php');

// Establish PDO database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $conn = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
} catch (PDOException $e) {
    die("Could not connect to the database " . DB_NAME . ": " . $e->getMessage());
}


// Function to check if user is logged in
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
}
?>
