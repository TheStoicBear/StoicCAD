<?php
    // // Set headers and session configurations
    // header("Access-Control-Allow-Origin: *");
    // header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    // header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

    // $secure = true;
    // $httponly = true;
    // $samesite = 'None';
    // $lifetime = 600;

    // if (PHP_VERSION_ID < 70300) {
    //     session_set_cookie_params($lifetime, '/; samesite='.$samesite, $_SERVER['HTTP_HOST'], $secure, $httponly);
    // } else {
    //     session_set_cookie_params([
    //         'lifetime' => $lifetime,
    //         'path' => '/',
    //         'domain' => $_SERVER['HTTP_HOST'],
    //         'secure' => $secure,
    //         'httponly' => $httponly,
    //         'samesite' => $samesite
    //     ]);
    // }
    session_start();
    require_once 'config/db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number, super FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit;
    }

    require_once 'config/dept_style_config.php';

    $search_query = '';
    $results = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_query'])) {
        $search_query = trim($_POST['search_query']);
        $search_stmt = $conn->prepare("SELECT * FROM nd_characters WHERE firstname LIKE :query OR lastname LIKE :query OR dob LIKE :query OR gender LIKE :query OR mugshot LIKE :query");
        $search_stmt->execute([':query' => "%$search_query%"]);
        $results = $search_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $key => $person) {
            $char_id = $person['charid'];

            // Fetch arrest records
            $arrest_stmt = $conn->prepare("SELECT * FROM arrests WHERE char_id = ?");
            $arrest_stmt->execute([$char_id]);
            $results[$key]['arrests'] = $arrest_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch ticket records
            $ticket_stmt = $conn->prepare("SELECT * FROM tickets WHERE char_id = ?");
            $ticket_stmt->execute([$char_id]);
            $results[$key]['tickets'] = $ticket_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch report records
            $report_stmt = $conn->prepare("SELECT * FROM reports WHERE char_id = ?");
            $report_stmt->execute([$char_id]);
            $results[$key]['reports'] = $report_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Search and fetch results
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_query'])) {
        $search_query = trim($_POST['search_query']);
        $search_stmt = $conn->prepare("SELECT * FROM nd_characters WHERE firstname LIKE :query OR lastname LIKE :query OR dob LIKE :query OR gender LIKE :query OR mugshot LIKE :query");
        $search_stmt->execute([':query' => "%$search_query%"]);
        $results = $search_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Default positions for the full name
    $fullNameTop = '180px';  // Change this in PHP as needed
    $fullNameLeft = '45%';  // Change this in PHP as needed
    $firstNameTop = '70px';
    $firstNameLeft = '-10%';

    $lastNameTop = '70px';
    $lastNameLeft = '10%';

    $dobTop = '127px';
    $dobLeft = '-10%';

    $genderTop = '127px';
    $genderLeft = '5%';

    $driversLicenseTop = '180px';
    $driversLicenseLeft = '30%';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Searches - MDT</title>
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
            transition: margin-left 0.3s ease-out;
            margin-left: 256px; /* Initial margin to accommodate sidebar */
        }
        .full-width {
            margin-left: 0; /* Full width when sidebar is hidden */
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
        }
        .modal-content {
            position: relative;
            background-color: #fefefe00;
            margin: 15% auto;
            padding: 20px;
            width: 80%;
            max-width: 500px;
            height: 250px; /* Adjust based on your background image size */
        }
        .close {
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 25px;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .idcard-image {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .idcard-image:hover {
            transform: scale(1.05);
        }
        .content-container {
            padding-top: 100px; /* Added top padding to move content down */
        }
        .modal-data {
            position: absolute;
            width: 100%;
            text-align: center;
            font-size: 14px; /* Adjusted font size for better fit */
            font-weight: bold;
            color: #fff; /* Black color for visibility on a light background */
        }
        .modal-dataN {
            position: absolute;
            width: 100%;
            text-align: center;
            font-size: 22px; /* Adjusted font size for better fit */
            font-weight: bold;
            color: #fff; /* Black color for visibility on a light background */
        }
        #mugshotImage {
            position: absolute;
            top: 55px;      /* Adjusted for vertical alignment */
            left: 40px;     /* Closer to the left edge of the modal */
            width: 100px;   /* Size as per your specification */
            height: 100px;  /* Size as per your specification */
            border-radius: 50%;
            transform: translate(0, 0); /* Removed the centering transform if not needed */
        }
        .signature {
            font-family: 'Great Vibes', cursive;
            font-size: 24px;
            color: #fff; /* Adjust as needed */
            position: absolute;
            left: 50%; /* Center horizontally */
            top: 150px; /* Position where it fits on your background */
            transform: translateX(-50%); /* Center align */
        }
    </style>
</head>
<body class="font-sans antialiased text-white">
    <div class="flex min-h-screen">
        <!-- Toggle Button -->
        <button onclick="toggleSidebar()" class="sidebar-button text-white text-xl bg-gray-800 px-4 py-2 rounded">&#9776;</button>
        
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
                <a href="map.php" class="block py-2.5 px-4 rounded hover:bg-blue-600"><i the="fas fa-map-marked-alt mr-2"></i>Map</a>
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
        <div id="mainContent" class="flex-1 flex flex-col ml-64 p-10 content">
            <header class="mb-5">
                <h1 class="font-bold text-3xl mb-4">Police Search Database</h1>
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                    <form method="post" class="space-y-4">
                        <div>
                            <label for="search_query" class="block">Search for an individual:</label>
                            <input type="text" id="search_query" name="search_query" value="<?php echo htmlspecialchars($search_query); ?>" required class="w-full h-10 px-3 rounded bg-gray-700 focus:bg-gray-600 outline-none">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600 focus:outline-none">Search</button>
                    </form>
                </div>
                <div id="idModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <img src="https://raw.githubusercontent.com/jonassvensson4/jsfour-idcard/master/html/assets/images/idcard.png" style="width: 100%; height: auto; position: relative;">
                    <img id="mugshotImage" src="" alt="Mugshot">
                    <div id="fullName" class="modal-data signature" style="top: <?php echo $fullNameTop; ?>; left: <?php echo $fullNameLeft; ?>;">Full Name:</div>

                    <div id="firstName" class="modal-dataN" style="top: <?php echo $firstNameTop; ?>; left: <?php echo $firstNameLeft; ?>;">First Name:</div>
                    <div id="lastName" class="modal-dataN" style="top: <?php echo $lastNameTop; ?>; left: <?php echo $lastNameLeft; ?>;">Last Name:</div>
                    <div id="dob" class="modal-data" style="top: <?php echo $dobTop; ?>; left: <?php echo $dobLeft; ?>;">DOB:</div>
                    <div id="gender" class="modal-data" style="top: <?php echo $genderTop; ?>; left: <?php echo $genderLeft; ?>;">Gender:</div>
                    <div id="driverslicense" class="modal-data" style="top: <?php echo $driversLicenseTop; ?>; left: <?php echo $driversLicenseLeft; ?>;">DL Status:</div>            
                </div>
            </div>
                <?php if (!empty($message)): ?>
                    <div class="mt-4 bg-gray-800 p-4 rounded-lg shadow-lg"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if (!empty($results)): ?>
                <div class="bg-gray-800 mt-4 p-6 rounded-lg shadow-lg space-y-4">
                    <h2 class="text-xl font-semibold">Search Results</h2>
                    <?php foreach ($results as $row): ?>
                        <div class="bg-gray-700 p-4 rounded-lg flex items-center">
                            <div class="idcard-image" onclick="showModal('<?php echo htmlspecialchars($row['firstname']); ?>', '<?php echo htmlspecialchars($row['lastname']); ?>', '<?php echo htmlspecialchars($row['dob']); ?>', '<?php echo htmlspecialchars($row['gender']); ?>', '<?php echo htmlspecialchars($row['driverslicense']); ?>', '<?php echo htmlspecialchars($row['mugshot']); ?>')">
                                <img src="https://raw.githubusercontent.com/jonassvensson4/jsfour-idcard/master/html/assets/images/idcard.png" alt="ID Card" style="width: 100px;">
                            </div>
                            <div class="ml-4">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></p>
                                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($row['dob']); ?></p>
                                <p><strong>Gender:</strong> <?php echo htmlspecialchars($row['gender']); ?></p>
                                <p><strong>DL Status:</strong> <?php echo htmlspecialchars($row['driverslicense']); ?></p>
                            </div>
                            <div>
                                <a href="tickets.php" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600">Tickets</a>
                                <a href="add/add_ticket.php?char_id=<?php echo $row['charid']; ?>" class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-600">Add Ticket</a>
                                <a href="add/add_arrest.php?char_id=<?php echo $row['charid']; ?>" class="px-4 py-2 bg-red-500 rounded hover:bg-red-600">Add Arrest</a>
                                <a href="arrests.php" class="px-4 py-2 bg-red-500 rounded hover:bg-red-600">Arrests</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            var mainContent = document.getElementById("mainContent");
            sidebar.classList.toggle("hidden-sidebar");
            mainContent.classList.toggle("full-width");
        }

        function showModal(firstName, lastName, dob, gender, driverslicense, mugshot) {
            document.getElementById("driverslicense").textContent = 'DL Status: ' + driverslicense;
            var fullName = firstName + " " + lastName; // Combine names to create full name
            document.getElementById("fullName").textContent = fullName;
            document.getElementById("firstName").textContent = firstName;
            document.getElementById("lastName").textContent = lastName;
            document.getElementById("dob").textContent = dob;
            document.getElementById("gender").textContent = gender;
            document.getElementById("mugshotImage").src = 'data:image/png;base64,' + mugshot.replace(/^data:image\/(png|jpg);base64,/, '');

            document.getElementById("idModal").style.display = "block";
        }

        document.addEventListener('DOMContentLoaded', function () {
            const closeBtn = document.querySelector('.close');
            closeBtn.onclick = function() {
                document.getElementById("idModal").style.display = "none";
            };

            window.onclick = function(event) {
                var modal = document.getElementById("idModal");
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            };

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
