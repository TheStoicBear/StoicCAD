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


    // Check if the user is a superuser
    if ($user['super'] != 1) {
        echo "Access denied. This area is restricted to super users only.";
        exit;
    }

    // Fetch all users in the same department as the logged-in super user
    $deptUsersStmt = $conn->prepare("SELECT id, username, email, avatar_url, dept, rank, badge_number, super FROM users WHERE dept = ?");
    $deptUsersStmt->execute([$user['dept']]);
    $deptUsers = $deptUsersStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - MDT</title>
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

        <!-- Main Content -->
        <!-- Main Content -->
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
            <header class="mb-5">
                <h1>Super Admin Dashboard</h1>
            </header>

            <!-- Users Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold">Department Users</h2>
                <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">ID</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Username</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Email</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Avatar</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Rank</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Badge Number</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Super</th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deptUsers as $deptUser): ?>
                            <tr class="bg-gray-800 hover:bg-gray-700">
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo $deptUser['id']; ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo htmlspecialchars($deptUser['username']); ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo htmlspecialchars($deptUser['email']); ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <img src="<?php echo htmlspecialchars($deptUser['avatar_url']); ?>" alt="Avatar" class="h-8 w-8 rounded-full">
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo htmlspecialchars($deptUser['rank']); ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo htmlspecialchars($deptUser['badge_number']); ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm"><?php echo $deptUser['super'] ? 'Yes' : 'No'; ?></td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <a href="edit_user.php?user_id=<?php echo $deptUser['id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
    </script>
</body>
</html>
