<?php
// Auth middleware file. Straightforward on purpose, because beginner code should be readable.
// Middleware - gatekeepers for your routes!
class AuthMiddleware {

    // Check if user is authenticated - guard those pages!
    public static function checkAuth() {
        // Start session if not already started (annoying but necessary)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Redirect if not logged in
        if (!isset($_SESSION['user_id'])) {
            header("Location: /public/login.php");
            exit();
        }
    }

    // Check if user is admin - only the chosen few get past
    public static function checkAdmin() {
        self::checkAuth();

        if ($_SESSION['role'] !== 'admin') {
            header("Location: /public/access-denied.php");
            exit();
        }
    }

    // Check if user owns the resource - authorization for your own stuff
    public static function checkOwnership($resource_user_id) {
        self::checkAuth();

        // Allow if user is admin or owns the resource
        if ($_SESSION['user_id'] !== $resource_user_id && $_SESSION['role'] !== 'admin') {
            header("Location: /public/access-denied.php");
            exit();
        }
    }

    // CSRF protection middleware - validate those tokens!
    public static function validateCsrfToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !Helper::verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }
        }
    }

    // Log user activity - track what they're doing
    public static function logActivity($db, $action, $description = '') {
        if (isset($_SESSION['user_id'])) {
            Helper::logActivity($db, $_SESSION['user_id'], $action, $description);
        }
    }
}

// Start session with secure settings - because session cookies matter
if (session_status() === PHP_SESSION_NONE) {
    // Make session cookie HTTP-only (no JavaScript access!)
    session_set_cookie_params([
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'httponly' => true,
        'secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'), // HTTPS only in production
        'samesite' => 'Strict'
    ]);

    session_start();

    // Regenerate session ID on login (security best practice)
    if (!isset($_SESSION['_regenerated'])) {
        session_regenerate_id(true);
        $_SESSION['_regenerated'] = true;
    }
}
?>
