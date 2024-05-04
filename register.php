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

include_once 'config/db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username, $email, $password]);

    header("Location: login.php");
}
?>
<html>
<body>
<form method="post">
    Username: <input type="text" name="username" required><br>
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Register</button>
</form>
</body>
</html>
