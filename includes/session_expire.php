<?php
// Set session timeout duration (5 minutes)
$timeout_duration = 300;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?timeout=1");
    exit();
}

$_SESSION['last_activity'] = time(); // Update last activity time
?>