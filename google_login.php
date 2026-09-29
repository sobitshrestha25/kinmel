<?php
require_once __DIR__ . '/config/oauth_config.php';
require_once __DIR__ . '/includes/functions.php';
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$params = http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'state'         => $state,
    'prompt'        => 'select_account',
]);
header("Location: https://accounts.google.com/o/oauth2/v2/auth?" . $params);
exit();
?>
