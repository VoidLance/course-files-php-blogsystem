<?php
// Bootstrap file - initialize the entire application (the master key to everything!)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path for easier file includes
define('BASE_PATH', dirname(__FILE__));

// Include configuration files
require_once BASE_PATH . '/config/Database.php';

// Include all classes (the building blocks)
require_once BASE_PATH . '/classes/User.php';
require_once BASE_PATH . '/classes/Post.php';
require_once BASE_PATH . '/classes/Comment.php';
require_once BASE_PATH . '/classes/Category.php';

// Include helpers and middleware
require_once BASE_PATH . '/helpers/Helper.php';
require_once BASE_PATH . '/middleware/AuthMiddleware.php';

// Initialize database connection (connect to the mothership)
$db = new Database();
$conn = $db->connect();

// Set secure headers to protect against attacks
Helper::setSecureHeaders();

// Initialize class instances for global use
$userObj = new User($conn);
$postObj = new Post($conn);
$commentObj = new Comment($conn);
$categoryObj = new Category($conn);

// Set error handler (catch those errors before they escape!)
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $error_log = BASE_PATH . '/logs/error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $error_message = "[$timestamp] Error [$errno]: $errstr in $errfile on line $errline\n";
    error_log($error_message, 3, $error_log);
});
?>
