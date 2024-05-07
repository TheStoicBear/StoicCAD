<?php
session_start();
require_once '..config/db.php'; // Ensure this file contains your PDO connection logic

if (!isset($_GET['arrest_id']) || !is_numeric($_GET['arrest_id'])) {
    echo "Invalid arrest ID.";
    exit;
}

$arrest_id = $_GET['arrest_id'];
$stmt = $conn->prepare("SELECT * FROM arrests WHERE arrest_id = ?");
$stmt->bindParam(1, $arrest_id, PDO::PARAM_INT);
$stmt->execute();
$currentData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentData) {
    echo "No arrest found with this ID.";
    exit;
}

$type = 'arrest';
$fields = [
    'officer_name' => $currentData['officer_name'],
    'arrest_date' => $currentData['arrest_date']->format('Y-m-d H:i:s'),
    'charges' => $currentData['charges'],
    'bail_amount' => $currentData['bail_amount']
];

include 'edit_template.php';
?>
