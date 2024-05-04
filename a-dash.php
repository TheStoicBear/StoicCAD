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


// Handle POST requests for updates and deletes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        switch ($action) {
            case 'delete_user':
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                break;
            case 'edit_user':
                header("Location: edits/edit_user.php?user_id=" . $_POST['user_id']);
                break;
            case 'edit_incident':
                header("Location: edits/edit_incident.php?incident_id=" . $_POST['incident_id']);
                break;
            case 'edit_report':
                header("Location: edits/edit_report.php?report_id=" . $_POST['report_id']);
                break;
            case 'edit_ticket':
                header("Location: edits/edit_ticket.php?ticket_id=" . $_POST['ticket_id']);
                break;
            case 'edit_arrest':
                header("Location: edits/edit_arrest.php?arrest_id=" . $_POST['arrest_id']);
                break;
        }
        exit();
    }
}

// Fetch all data to display
$users = $conn->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
$incidents = $conn->query("SELECT * FROM incidents")->fetchAll(PDO::FETCH_ASSOC);
$reports = $conn->query("SELECT * FROM reports")->fetchAll(PDO::FETCH_ASSOC);
$tickets = $conn->query("SELECT * FROM tickets")->fetchAll(PDO::FETCH_ASSOC);
$arrests = $conn->query("SELECT * FROM arrests")->fetchAll(PDO::FETCH_ASSOC);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MDT</title>
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
</style>
        
</head>
<body class="font-sans antialiased text-white">
    <div class="flex min-h-screen">

    <body class="font-sans antialiased text-white">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="bg-gray-800 w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 overflow-y-auto">
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
        <div class="flex-1 flex flex-col ml-64 p-10">
            <header class="mb-5">
                <h1>Admin Dashboard</h1>
            </header>

            <!-- Users Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold">Users</h2>
                <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    ID
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Username
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Avatar
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Department
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Rank
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Badge Number
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr class="bg-gray-800 hover:bg-gray-700">
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo $user['id']; ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Avatar" class="h-8 w-8 rounded-full">
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($user['dept']); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($user['rank']); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($user['badge_number']); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <a href="edits/edit_user.php?user_id=<?php echo $user['id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                    <form method="post" action="" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="action" value="delete_user" class="text-red-500 hover:text-red-400 ml-4">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Incidents Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold">Incidents</h2>
                <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    ID
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Title
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Description
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidents as $incident): ?>
                            <tr class="bg-gray-800 hover:bg-gray-700">
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo $incident['id']; ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($incident['title']); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($incident['description']); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <?php echo htmlspecialchars($incident['status']); ?>
                                </td>
                                <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                    <a href="edits/edit_incident.php?incident_id=<?php echo $incident['id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                    <form method="post" action="" style="display:inline;">
                                        <input type="hidden" name="incident_id" value="<?php echo $incident['id']; ?>">
                                        <button type="submit" name="action" value="delete_incident" class="text-red-500 hover:text-red-400 ml-4">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
                <!-- Reports Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold">Reports</h2>
                    <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Author
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Content
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                <tr class="bg-gray-800 hover:bg-gray-700">
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo $report['report_id']; ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($report['author']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($report['report_content']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($report['status']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <a href="edits/edit_report.php?report_id=<?php echo $report['report_id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                            <button type="submit" name="action" value="delete_report" class="text-red-500 hover:text-red-400 ml-4">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tickets Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold">Tickets</h2>
                    <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Violation
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Fine Amount
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Issued By
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                <tr class="bg-gray-800 hover:bg-gray-700">
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo $ticket['ticket_id']; ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($ticket['violation']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($ticket['fine_amount']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($ticket['issued_by']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <a href="edits/edit_ticket.php?ticket_id=<?php echo $ticket['ticket_id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>">
                                            <button type="submit" name="action" value="delete_ticket" class="text-red-500 hover:text-red-400 ml-4">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    </div>
                <!-- Arrests Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold">Arrests</h2>
                    <div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg mt-6">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Charges
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Bail Amount
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Arrested By
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-500 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($arrests as $arrest): ?>
                                <tr class="bg-gray-800 hover:bg-gray-700">
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo $arrest['arrest_id']; ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($arrest['charges']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($arrest['bail_amount']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <?php echo htmlspecialchars($arrest['officer_name']); ?>
                                    </td>
                                    <td class="px-5 py-2 border-b border-gray-700 text-sm">
                                        <a href="edits/edit_arrest.php?arrest_id=<?php echo $arrest['arrest_id']; ?>" class="text-blue-500 hover:text-blue-400">Edit</a>
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="arrest_id" value="<?php echo $arrest['arrest_id']; ?>">
                                            <button type="submit" name="action" value="delete_arrest" class="text-red-500 hover:text-red-400 ml-4">Delete</button>
                                        </form>
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
</body>
</html>

