<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy session
session_destroy();

// Delete cookies
if (isset($_COOKIE["username"])) {
    setcookie("username", "", time() - 3600, "/");
}
if (isset($_COOKIE["password"])) {
    setcookie("password", "", time() - 3600, "/");
}

header("Location: login.php");
exit();
?>