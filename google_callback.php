<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/oauth_config.php';
require_once __DIR__ . '/includes/functions.php';

// Verify the state token to protect against CSRF
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    unset($_SESSION['oauth_state']);
    redirect('/kinmel/login.php?oauth_error=1');
}
unset($_SESSION['oauth_state']);

if (!isset($_GET['code'])) {
    redirect('/kinmel/login.php?oauth_error=1');
}

// ---------------- Exchange the code for an access token ----------------
$token_response = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query([
            'code'          => $_GET['code'],
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]),
        'ignore_errors' => true,
    ],
]));

$token_data = json_decode($token_response, true);
if (!isset($token_data['access_token'])) {
    redirect('/kinmel/login.php?oauth_error=1');
}

// ---------------- Fetch the user's Google profile ----------------
$profile_response = file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo', false, stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer " . $token_data['access_token'] . "\r\n",
        'ignore_errors' => true,
    ],
]));

$profile = json_decode($profile_response, true);
if (empty($profile['email'])) {
    redirect('/kinmel/login.php?oauth_error=1');
}

$user = findOrCreateOAuthUser(
    $conn,
    'google',
    $profile['id'],
    $profile['email'],
    $profile['given_name'] ?? 'Google',
    $profile['family_name'] ?? 'User'
);

$_SESSION['user_id'] = $user['id'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['role'] = $user['role'];

redirect($user['role'] === 'admin' ? '/kinmel/admin/index.php' : '/kinmel/home.php');
?>
