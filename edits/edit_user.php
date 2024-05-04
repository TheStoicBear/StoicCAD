<?php
session_start();
require_once '../db.php'; // Ensure this file contains your PDO connection logic

// Only allow access if the user is an admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_GET['user_id'] ?? null;
if (!$userId) {
    die("User ID not specified.");
}

// Prepare and execute the statement to fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bindParam(1, $userId);  // PDO uses bindParam() and 1-indexed positions
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Setup data for the template
$currentData = $user;
$fields = [
    'username' => $user['username'],
    'email' => $user['email'],
    'dept' => $user['dept'] ?? '',
    'rank' => $user['rank'] ?? '',
    'badge_number' => $user['badge_number'] ?? ''
];
$type = 'Users';

// Include the generic edit template
include 'edit_template.php';
