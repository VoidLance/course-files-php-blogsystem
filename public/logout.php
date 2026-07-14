<?php
// Logout page - see you later!
require_once dirname(__FILE__) . '/../bootstrap.php';

// Check if user is logged in
if (Helper::isLoggedIn()) {
    // Log the logout activity
    Helper::logActivity($conn, $_SESSION['user_id'], 'LOGOUT', 'User logged out');

    // Destroy the session (purge all data!)
    session_destroy();
}

// Redirect to home page
header("Location: /BlogSystem/public/index.php");
exit();
?>
