<?php
session_start();

// 1. All session variables-ah clear panradhu
$_SESSION = array();

// 2. Session cookie-ah delete panradhu (Extra security)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Session-ah destroy panradhu
session_destroy();

// 4. Thirumba main login (index.php) page-ku redirect panradhu
header("Location: index.php");
exit();
?>