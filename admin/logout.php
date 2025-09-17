<?php
session_start();

// Clear all session variables
$_SESSION = [];

// If the session uses cookies, remove the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally destroy the session on the server
session_destroy();

// Redirect to login or home page
header('Location: login.php');
exit;
