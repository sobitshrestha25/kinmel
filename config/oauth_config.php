<?php
/*
    OAuth Configuration
    -------------------------
    Fill these in with your own credentials from:
    - Google:   https://console.cloud.google.com/apis/credentials
    - Facebook: https://developers.facebook.com/apps/

    Redirect URIs to register with each provider (must match EXACTLY):
    - Google:   http://localhost/kinmel/google_callback.php
    - Facebook: http://localhost/kinmel/facebook_callback.php
*/

// ---------------- Google ----------------
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
define('GOOGLE_REDIRECT_URI', 'http://localhost/kinmel/google_callback.php');

// ---------------- Facebook ----------------
define('FACEBOOK_APP_ID', 'YOUR_FACEBOOK_APP_ID_HERE');
define('FACEBOOK_APP_SECRET', 'YOUR_FACEBOOK_APP_SECRET_HERE');
define('FACEBOOK_REDIRECT_URI', 'http://localhost/kinmel/facebook_callback.php');
?>
