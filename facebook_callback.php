<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/oauth_config.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    unset($_SESSION['oauth_state']);
    redirect('/kinmel/login.php?oauth_error=1');
}
unset($_SESSION['oauth_state']);

if (!isset($_GET['code'])) {
    redirect('/kinmel/login.php?oauth_error=1');
}

// ---------------- Exchange the code for an access token ----------------
$token_params = http_build_query([
    'client_id'     => FACEBOOK_APP_ID,
    'client_secret' => FACEBOOK_APP_SECRET,
    'redirect_uri'  => FACEBOOK_REDIRECT_URI,
    'code'          => $_GET['code'],
]);

$token_response = file_get_contents(
    'https://graph.facebook.com/v19.0/oauth/access_token?' . $token_params,
    false,
    stream_context_create(['http' => ['ignore_errors' => true]])
);

$token_data = json_decode($token_response, true);
if (!isset($token_data['access_token'])) {
    redirect('/kinmel/login.php?oauth_error=1');
}

// ---------------- Fetch the user's Facebook profile ----------------
$profile_params = http_build_query([
    'fields'       => 'id,first_name,last_name,email',
    'access_token' => $token_data['access_token'],
]);

$profile_response = file_get_contents(
    'https://graph.facebook.com/me?' . $profile_params,
    false,
    stream_context_create(['http' => ['ignore_errors' => true]])
);

$profile = json_decode($profile_response, true);
if (empty($profile['email'])) {
    // Facebook accounts without a verified email can't be matched to your users table
    redirect('/kinmel/login.php?oauth_error=noemail');
}

$user = findOrCreateOAuthUser(
    $conn,
    'facebook',
    $profile['id'],
    $profile['email'],
    $profile['first_name'] ?? 'Facebook',
    $profile['last_name'] ?? 'User'
);

$_SESSION['user_id'] = $user['id'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['role'] = $user['role'];

redirect($user['role'] === 'admin' ? '/kinmel/admin/index.php' : '/kinmel/home.php');
?>
