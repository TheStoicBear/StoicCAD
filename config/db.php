<?php


$host = 'YOUR_HOST_IP';
$db_name = 'YOUR_HOST_DB';
$username = 'YOUR_HOST_USERNAME';
$password = 'YOUR_HOST_PASSWORD';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
}
?>
