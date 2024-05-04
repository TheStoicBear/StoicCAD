<?php
session_start();
require_once 'config/db.php'; // Ensure this file contains the correct database connection setup

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information
$stmt = $conn->prepare("SELECT username, avatar_url, dept, rank, badge_number FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}



// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
if (isset($_POST['submitApplication'])) {
    // Collecting form data
    $department = $_POST['department'];
    $fullName = $_POST['fullName'];
    $emailAddress = $_POST['emailAddress'];
    $discordName = $_POST['discordName'];
    $previousExperience = $_POST['previousExperience'];
    $motivation = $_POST['motivation'];

    // Prepare the data to send to Discord webhook
    $webhookurl = "https://discord.com/api/webhooks/1228466906277871758/CEOKB8sAAgbUm2BoxlWQrp5Jr1xqJe4X80z-XlUNhTzSLL4Vfvx3qwYDyOATl2QEL7CR";
    $json_data = json_encode([
        "content" => "",
        "embeds" => [
            [
                "title" => "New Application for $department",
                "description" => "**$fullName** has applied to join **$department**.",
                "color" => hexdec("3366ff"),
                "fields" => [
                    ["name" => "Full Name", "value" => $fullName, "inline" => true],
                    ["name" => "Email Address", "value" => $emailAddress, "inline" => true],
                    ["name" => "Discord Name", "value" => $discordName, "inline" => true],
                    ["name" => "Previous Experience", "value" => $previousExperience, "inline" => false],
                    ["name" => "Motivation", "value" => $motivation, "inline" => false]
                ]
            ]
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

    // Sending data to Discord via cURL
    $ch = curl_init($webhookurl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    // Check for errors
    if(curl_error($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        echo 'Application submitted successfully.';
    }
    curl_close($ch);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIV Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css">
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-gray-800 w-64 p-5 fixed inset-y-0 left-0">
            <div class="text-center">
                <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="User Avatar" class="h-20 w-20 rounded-full mx-auto">
                <h2 class="mt-4 mb-2 font-semibold"><?php echo htmlspecialchars($user['username']); ?></h2>
                <p>Dept: <?php echo htmlspecialchars($user['dept']); ?></p>
            </div>
            <nav class="mt-10">
                <a href="general_dashboard.php" class="block py-2.5 px-4 rounded bg-blue-600 hover:bg-blue-700"><i class="fas fa-home mr-2"></i>Home</a>
            </nav>
                <form method="post" action="logout.php" class="mt-5">
                    <button type="submit" name="logout" class="w-full py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
        </div>


            <!-- Content Area in the HTML File -->
            <div class="flex-1 ml-64 p-10">
                <h1 class="text-3xl font-bold">Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="mt-3">Apply to join one of our dedicated departments and serve your community.</p>

                <form action="" method="post" class="mt-8 bg-gray-800 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold mb-4 text-white">Department Application Form</h2>

                    <div class="mb-4">
                        <label for="department" class="block text-sm font-medium text-gray-300">Department:</label>
                        <select name="department" id="department" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none">
                            <option value="LSPD">Los Santos Police Department (LSPD)</option>
                            <option value="BCSO">Blaine County Sheriff's Office (BCSO)</option>
                            <option value="SASP">San Andreas State Police (SASP)</option>
                            <option value="SAFD">San Andreas Fire Department (SAFD)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="fullName" class="block text-sm font-medium text-gray-300">Full Name:</label>
                        <input type="text" id="fullName" name="fullName" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none" placeholder="John Doe">
                    </div>

                    <div class="mb-4">
                        <label for="emailAddress" class="block text-sm font-medium text-gray-300">Email Address:</label>
                        <input type="email" id="emailAddress" name="emailAddress" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none" placeholder="johndoe@example.com">
                    </div>

                    <div class="mb-4">
                        <label for="discordName" class="block text-sm font-medium text-gray-300">Discord Name:</label>
                        <input type="text" id="discordName" name="discordName" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none" placeholder="Your Discord username#0000">
                    </div>

                    <div class="mb-4">
                        <label for="previousExperience" class="block text-sm font-medium text-gray-300">Previous Experience:</label>
                        <textarea id="previousExperience" name="previousExperience" rows="4" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none" placeholder="Describe any previous related experience..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="motivation" class="block text-sm font-medium text-gray-300">Why do you want to join?</label>
                        <textarea id="motivation" name="motivation" rows="4" required class="mt-1 block w-full p-2 bg-gray-700 text-white border border-gray-600 rounded shadow-sm focus:outline-none" placeholder="Your motivation for joining the department..."></textarea>
                    </div>

                    <button type="submit" name="submitApplication" class="py-2 px-4 bg-blue-700 hover:bg-blue-800 rounded text-white font-bold">Submit Application</button>
                </form>
            </div>
            </div>
            </div>
    </div>
</body>
</html>
