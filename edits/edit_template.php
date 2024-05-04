<?php
if (!isset($currentData)) {
    die("Data not loaded properly.");
}

require_once '../config/db.php';
// If department is not CIV, continue on dashboard.php
require_once '../config/dept_style_config.php'; // Include the department style configurations
// Fetch detailed user information including dept, rank, and badge number
$stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number, super FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../db.php';  // Ensure your database connection file is correct
    $errors = [];
    $updateValues = [];

    // Prepare values for updating
    foreach ($fields as $field => $oldValue) {
        if (isset($_POST[$field])) {
            $updateValues[$field] = $_POST[$field];
        } else {
            $errors[] = "Missing value for field $field";
        }
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE {$type} SET " . join(', ', array_map(fn($field) => "$field = :$field", array_keys($updateValues))) . " WHERE id = :id";
            $stmt = $conn->prepare($sql);
            foreach ($updateValues as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            $stmt->bindValue(":id", $currentData['id']);
            $stmt->execute();
            echo "<p>Record updated successfully.</p>";
        } catch (PDOException $e) {
            echo "<p>Error updating record: " . $e->getMessage() . "</p>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<p>Error: $error</p>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Record</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-image: url('<?php echo $backgroundImage; ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            left: 0;
            z-index: 1000;
            width: 100%;
            background: #4b5563; /* Matching Tailwind's gray-700 */
            border-radius: 0 0 0.5rem 0.5rem;
        }
        form { 
            max-width: 600px; 
            margin: 20px auto; 
            padding: 20px; 
            background: #333; 
            border-radius: 8px;
        }
        label { display: block; margin-top: 10px; }
        input, select, textarea { width: 100%; padding: 10px; margin-top: 5px; }
        button { padding: 10px 20px; color: #fff; background-color: #007BFF; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body class="font-sans antialiased text-white">
    <div class="flex min-h-screen">
    <form action="" method="post">
        <h2>Edit <?php echo $type ?? 'Record'; ?></h2>
        <?php foreach ($fields as $field => $value): ?>
            <label for="<?php echo $field; ?>"><?php echo ucfirst($field); ?>:</label>
            <?php if ($field === 'description' || $field === 'content'): ?>
                <textarea id="<?php echo $field; ?>" name="<?php echo $field; ?>"><?php echo htmlspecialchars($value); ?></textarea>
            <?php else: ?>
                <input type="<?php echo $field === 'email' ? 'email' : 'text'; ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo htmlspecialchars($value); ?>">
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit">Update</button>
    </form>
 </div>
</body>
</html>
