<?php
    session_start();
    require_once 'config/db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Fetch detailed user information including dept, rank, and badge number
    $stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number, super FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$user) {
        echo "User not found.";
        exit;
    }
    // Redirection logic based on the department
    if ($user['dept'] === 'CIV') {
        header("Location: general_dashboard.php"); // Redirect to general_dashboard.php if department is CIV
        exit();
    }
    // If department is not CIV, continue on dashboard.php
    require_once 'config/dept_style_config.php'; // Include the department style configurations


    // Update incident status
    if (isset($_POST['update_incident_status'], $_POST['incident_id'], $_POST['status'])) {
        $allowedStatuses = ['Open', 'Closed', 'On Scene', 'Enroute'];
        $status = $_POST['status'];
        $incident_id = (int)$_POST['incident_id'];
        
        if (in_array($status, $allowedStatuses)) {
            $stmt = $conn->prepare("UPDATE incidents SET status = ? WHERE id = ?");
            $stmt->execute([$status, $incident_id]);
        } else {
            // Handle invalid status
            echo "Invalid status update attempted.";
        }
    }


    // Update report status
    if (isset($_POST['update_report_status'])) {
        $stmt = $conn->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
        $stmt->execute([$_POST['status'], $_POST['report_id']]);
    }

    // Fetch incidents and reports
    $incidents_stmt = $conn->prepare("SELECT * FROM incidents ORDER BY created_at DESC");
    $incidents_stmt->execute();
    $incidents = $incidents_stmt->fetchAll(PDO::FETCH_ASSOC);


    $reports_stmt = $conn->prepare("SELECT * FROM reports ORDER BY report_date DESC");
    $reports_stmt->execute();
    $reports = $reports_stmt->fetchAll(PDO::FETCH_ASSOC);


    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: login.php");
        exit();
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" report_content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MDT</title>
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
        .dropdown-menu {
            display: none;
            position: absolute;
            left: 0;
            z-index: 1000;
            width: 100%;
            background: #4b5563; /* Matching Tailwind's gray-700 */
            border-radius: 0 0 0.5rem 0.5rem;
        }
        .sidebar {
            transition: transform 0.3s ease-out;
            transform: translateX(0);
            z-index: 10;
        }
        .hidden-sidebar {
            transform: translateX(-100%);
        }
        .sidebar-button {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 20;
        }
        .content {
            transition: margin-left 0.9s ease-out;
            margin-right: 120px; /* match sidebar width when visible */
        }
        .full-width {
            margin-left: 0; /* full width when sidebar is hidden */
        }
    </style>
</head>
<body class="font-sans antialiased text-white">
    <div class="flex min-h-screen">
        <!-- Toggle Button -->
        <button onclick="toggleSidebar()" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded">&#9776; Toggle</button>
        
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-800 w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 overflow-y-auto sidebar">
            <div class="text-center">
                <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="User Avatar" class="h-20 w-20 rounded-full mx-auto">
                <h2 class="mt-4 mb-2 font-semibold"><?php echo htmlspecialchars($user['username']); ?></h2>
                <p><?php echo htmlspecialchars($user['dept']); ?>, <?php echo htmlspecialchars($user['rank']); ?><br>Badge #<?php echo htmlspecialchars($user['badge_number']); ?></p>
            </div>
            <nav>
                <a href="dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-home mr-2"></i>Dashboard</a>
                <a href="incidents.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-exclamation-triangle mr-2"></i>Incidents</a>
                <a href="reports.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-file-alt mr-2"></i>Reports</a>
                <a href="map.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-map-marked-alt mr-2"></i>Map</a>
                <!-- Dropdown for Searches -->
                <div class="relative dropdown">
                    <a href="#" class="block py-2.5 px-4 rounded hover:bg-blue-600 cursor-pointer"><i class="fas fa-search mr-2"></i>Searches <i class="fa fa-caret-down"></i></a>
                    <div class="dropdown-menu">
                        <a href="people_search.php" class="block py-2 px-4 text-sm text-white hover:bg-gray-600">People</a>
                        <a href="vehicle_search.php" class="block py-2 px-4 text-sm text-white hover:bg-gray-600">Vehicles</a>
                    </div>
                </div>

                <a href="settings.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-cog mr-2"></i>Settings</a>
                <?php if ($user['rank'] == 'Admin'): ?>
                    <a href="a-dash.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Admin Dashboard</a>
                <?php endif; ?>

                <?php if ($user['super'] == 1): ?>
                    <a href="super-dashboard.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i class="fas fa-user-shield mr-2"></i>Supervisor Dashboard</a>
                <?php endif; ?>
                
                <form method="post" action="logout.php" class="mt-5">
                    <button type="submit" name="logout" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </nav>
        </div>
        <div>
        <!-- Main Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
            <header class="mb-5">
                <h1 class="font-bold text-3xl mb-2">Dashboard</h1>
            <div class="bg-gray-900 p-6 rounded-lg shadow-md">
                <h2 class="text-xl mb-2">Incidents</h2>
                <?php foreach ($incidents as $incident): ?>
                    <div class="bg-gray-800 p-4 rounded mb-2">
                        <p><strong><?php echo htmlspecialchars($incident['title']); ?></strong>: <?php echo htmlspecialchars($incident['description']); ?>
                        - <em>Status: <?php echo htmlspecialchars($incident['status']); ?></em></p>
                        <form method="post">
                            <input type="hidden" name="incident_id" value="<?php echo $incident['id']; ?>">
                            <select name="status" class="bg-gray-700 text-white p-2 rounded">
                                <option value="Open" <?= $incident['status'] == 'Open' ? 'selected' : '' ?>>Open</option>
                                <option value="Closed" <?= $incident['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                                <option value="On Scene" <?= $incident['status'] == 'On Scene' ? 'selected' : '' ?>>On Scene</option>
                                <option value="Enroute" <?= $incident['status'] == 'Enroute' ? 'selected' : '' ?>>Enroute</option>
                            </select>
                            <button type="submit" name="update_incident_status" class="ml-2 px-3 py-1 bg-blue-500 rounded hover:bg-blue-700">Update Status</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-gray-900 mt-5 p-5 rounded-lg shadow-lg">
                <h2 class="text-xl mb-2">Reports</h2>
                <?php foreach ($reports as $report): ?>
                    <div class="bg-gray-800 p-4 rounded mb-2">
                        <p><strong><?php echo htmlspecialchars($report['author']); ?></strong>: <?php echo htmlspecialchars($report['report_content']); ?>
                        - <em>Status: <?php echo htmlspecialchars($report['status']); ?></em></p>
                        <form method="post">
                            <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                            <select name="status" class="bg-gray-700 text-white p-2 rounded">
                                <option value="Open" <?= $report['status'] == 'Open' ? 'selected' : '' ?>>Open</option>
                                <option value="Closed" <?= $report['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                                <option value="On Scene" <?= $report['status'] == 'On Scene' ? 'selected' : '' ?>>On Scene</option>
                                <option value="Enroute" <?= $report['status'] == 'Enroute' ? 'selected' : '' ?>>Enroute</option>
                            </select>
                            <button type="submit" name="update_report_status" class="ml-2 px-3 py-1 bg-blue-500 rounded hover:bg-blue-700">Update Status</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    </div>
    </div>
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            var mainContent = document.getElementById("mainContent");
            sidebar.classList.toggle("hidden-sidebar");
            mainContent.classList.toggle("full-width");
        }
        // JavaScript to handle dropdown behavior
        document.addEventListener('DOMContentLoaded', function () {
            const dropdown = document.querySelector('.dropdown');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            dropdown.addEventListener('click', function (event) {
                event.stopPropagation();
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            });

            window.addEventListener('click', function () {
                if (dropdownMenu.style.display === 'block') {
                    dropdownMenu.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
