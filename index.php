<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, skip the splash and go straight to the right place
if (isLoggedIn()) {
    redirect(isAdmin() ? '/kinmel/admin/index.php' : '/kinmel/home.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KinMel</title>
    <link rel="stylesheet" href="/kinmel/css/style.css">
</head>
<body class="splash-body">

<div class="splash-screen" id="splashScreen">
    <img src="/kinmel/images/logo-full.png" alt="KinMel" class="splash-logo">

    <div class="splash-bar-track">
        <div class="splash-bar-fill" id="splashBarFill"></div>
    </div>
    <div class="splash-percent" id="splashPercent">0%</div>

    <p class="splash-credit">Developed by: Sobit Shrestha &amp; Pukar Gurung</p>
</div>

<script>
    let progress = 0;
    const fill = document.getElementById('splashBarFill');
    const percentText = document.getElementById('splashPercent');

    const loadingInterval = setInterval(function () {
        progress += Math.floor(Math.random() * 8) + 4; // increase by 4-11 each tick
        if (progress >= 100) {
            progress = 100;
            clearInterval(loadingInterval);
            setTimeout(function () {
                window.location.href = '/kinmel/login.php';
            }, 400);
        }
        fill.style.width = progress + '%';
        percentText.textContent = progress + '%';
    }, 150);
</script>

</body>
</html>
