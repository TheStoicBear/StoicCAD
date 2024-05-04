<?php
session_start();
require_once '../db.php';

if (!isset($_GET['report_id']) || !is_numeric($_GET['report_id'])) {
    echo "Invalid report ID.";
    exit;
}

$report_id = $_GET['report_id'];
$stmt = $conn->prepare("SELECT * FROM reports WHERE report_id = ?");
$stmt->bindParam(1, $report_id, PDO::PARAM_INT);
$stmt->execute();
$currentData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentData) {
    echo "No report found with this ID.";
    exit;
}

$type = 'report';
$fields = [
    'author' => $currentData['author'],
    'report_content' => $currentData['report_content'],
    'status' => $currentData['status']
];

include 'edit_template.php';
?>
