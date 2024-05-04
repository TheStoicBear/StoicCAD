<?php


require 'config/db.php';  // This will use the connection setup from db.php
include_once 'config/config.php';
$clientId = CLIENT_ID;
$redirectUri = REDIRECTURI;

if (!isset($_SESSION['oauth2state'])) {
    $_SESSION['oauth2state'] = hash('sha256', microtime(TRUE).rand().$_SERVER['REMOTE_ADDR']);
}

$authorizeURL = "https://discord.com/api/oauth2/authorize?client_id={$clientId}&response_type=code&redirect_uri=" . urlencode($redirectUri) . "&scope=identify%20email&state=" . $_SESSION['oauth2state'];


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the database query
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Check if user exists and password is correct
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['dept'] = $user['dept'];
        $_SESSION['rank'] = $user['rank'];
        $_SESSION['badge_number'] = $user['badge_number'];

        // Redirect to the dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid login credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Police Dashboard Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body class="bg-gray-800 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8 space-y-6 bg-gray-700 rounded-lg shadow-lg text-white">
        <h1 class="text-center text-3xl font-bold">Police Portal Login</h1>
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-bold mb-2">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required class="w-full p-3 rounded bg-gray-600 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required class="w-full p-3 rounded bg-gray-600 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Sign In</button>
        </form>
        <div class="text-center mt-4">
            <a href="<?php echo $authorizeURL ?>" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded">Login with Discord</a>
        </div>
    </div>
</body>
</html>
