<?php


include_once 'config/db.php';
include_once 'config/config.php';
session_start();

$clientId = CLIENT_ID;
$clientSecret = CLIENT_SECRET;
$redirectUri = REDIRECTURI;
$tokenURL = 'https://discord.com/api/oauth2/token';
$apiURLBase = 'https://discord.com/api/users/@me';

function apiRequest($url, $postFields = NULL, $accessToken = NULL) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    if ($postFields) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        curl_setopt($ch, CURLOPT_POST, TRUE);
    }

    $headers = ['Accept: application/json'];
    if ($accessToken) {
        $headers[] = 'Authorization: Bearer ' . $accessToken;
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);

    if (!$response) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    curl_close($ch);
    return json_decode($response);
}

// CSRF protection
if (!isset($_GET['state']) || $_SESSION['oauth2state'] !== $_GET['state']) {
    header('Location: error.php?message=Invalid_state');
    exit;
}
unset($_SESSION['oauth2state']);

// Exchange code for an access token
try {
    $tokenResponse = apiRequest($tokenURL, [
        "grant_type" => "authorization_code",
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'code' => $_GET['code']
    ]);

    if (!isset($tokenResponse->access_token)) {
        throw new Exception('Access token not received.');
    }
    $accessToken = $tokenResponse->access_token;

    // Fetch user details with access token
    $user = apiRequest($apiURLBase, NULL, $accessToken);

    // Check and register or log in user
    $stmt = $conn->prepare("SELECT id FROM users WHERE discord_id = ?");
    $stmt->execute([$user->id]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $_SESSION['user_id'] = $existingUser['id'];
    } else {
        $avatarUrl = isset($user->avatar) ? "https://cdn.discordapp.com/avatars/{$user->id}/{$user->avatar}.png" : null;
        $stmt = $conn->prepare("INSERT INTO users (discord_id, username, email, avatar_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user->id, $user->username, $user->email, $avatarUrl]);
        $_SESSION['user_id'] = $conn->lastInsertId();
    }

    header('Location: dashboard.php');
    exit();
} catch (Exception $e) {
    // Handle exceptions by redirecting to an error page or logging
    header('Location: error.php?message=' . urlencode($e->getMessage()));
    exit;
}
?>
