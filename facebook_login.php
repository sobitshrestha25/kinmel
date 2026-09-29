<?php
require_once __DIR__ . '/config/oauth_config.php';
require_once __DIR__ . '/includes/functions.php';

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = http_build_query([
    'client_id'    => FACEBOOK_APP_ID,
    'redirect_uri' => FACEBOOK_REDIRECT_URI,
    'state'        => $state,
    'scope'        => 'email,public_profile',
]);

header("Location: https://www.facebook.com/v19.0/dialog/oauth?" . $params);
exit();
?>
