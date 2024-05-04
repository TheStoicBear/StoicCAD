<?php
$secure = true; 
$httponly = true;
$samesite = 'none';
$lifetime = 600;  // This is the variable that should be used for session cookie lifetime

if (PHP_VERSION_ID < 70300) {
    // Corrected to use $lifetime instead of $maxlifetime
    session_set_cookie_params($lifetime, '/; samesite='.$samesite, $_SERVER['HTTP_HOST'], $secure, $httponly);
} else {
    // Corrected to use $lifetime instead of $maxlifetime in the array
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}

session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch detailed user information including dept, rank, and badge number
$stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

require_once 'config/dept_style_config.php'; // Include the department style configurations



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $reported_by = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO incidents (title, description, reported_by) VALUES (?, ?, ?)");
    $stmt->execute([$title, $description, $reported_by]);
}

$incidents_stmt = $conn->prepare("SELECT * FROM incidents ORDER BY created_at DESC");
$incidents_stmt->execute();
$incidents = $incidents_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidents</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-image: url('<?php echo $backgroundImage; ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .top-nav {
            background: #2d3748; /* Dark gray background */
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-item {
            color: #cbd5e0; /* Light gray text */
            text-decoration: none;
            margin-right: 10px;
            display: inline-block;
            padding: 10px 15px;
        }
        .nav-item:hover {
            background-color: #4a5568; /* Lighter dark gray on hover */
        }
        .user-info {
            color: #cbd5e0;
            font-size: 0.875rem; /* Smaller font size for user info */
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-100">
    <div class="top-nav">
        <div>
            <a href="dashboard.php" class="nav-item"><i class="fas fa-home mr-2"></i>Dashboard</a>
            <a href="incidents.php" class="nav-item"><i class="fas fa-exclamation-triangle mr-2"></i>Incidents</a>
            <a href="reports.php" class="nav-item"><i class="fas fa-file-alt mr-2"></i>Reports</a>
            <a href="map.php" class="nav-item"><i class="fas fa-map-marked-alt mr-2"></i>Map</a>
        </div>
        <div>
            <span class="user-info"><?php echo htmlspecialchars($user['username']) . ' (' . htmlspecialchars($user['rank']) . ')'; ?></span>
            <form method="post" action="logout.php" style="display:inline;">
                <button type="submit" name="logout" class="nav-item">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </button>
            </form>
        </div>
    </div>
    <div class="p-10">
        <h1 class="font-bold text-3xl mb-6">Incidents</h1>
        <div class="bg-gray-900 mt-6 p-6 rounded-lg shadow-md overflow-hidden">
            <h2 class="text-xl font-semibold mb-4">Web Content</h2>
            <iframe src="https://shawn1-wxg9gm.users.cfx.re/webmap/" style="width:100%; height:500px; border:none;"></iframe>
        </div>
    </div>
    <script>
        // Your JavaScript remains the same or as needed
    </script>
</body>
</html>
