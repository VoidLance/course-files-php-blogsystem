<?php
// Delete Post Handler - permanently remove a post (no undo!)
require_once dirname(__FILE__) . '/../bootstrap.php';

AuthMiddleware::checkAuth();

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$post_id) {
    header("Location: /BlogSystem/public/index.php");
    exit();
}

// Get the post first
$post = $postObj->getPostById($post_id);

if (!$post) {
    header("Location: /BlogSystem/public/index.php");
    exit();
}

// Check ownership (author or admin only)
if ($post['author_id'] !== $_SESSION['user_id'] && !Helper::isAdmin()) {
    header("Location: /BlogSystem/public/access-denied.php");
    exit();
}

// Confirm deletion if parameter present
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    // Delete the post (cascade delete will handle comments)
    if ($postObj->deletePost($post_id)) {
        Helper::logActivity($conn, $_SESSION['user_id'], 'DELETE_POST', "Deleted post: " . $post['title']);
        header("Location: /BlogSystem/public/index.php?deleted=true");
        exit();
    }
}

// If not confirmed yet, redirect back with confirmation
// In a real app, you'd show a modal or page asking for confirmation
header("Location: /BlogSystem/public/post.php?slug=" . $post['slug'] . "&confirm_delete=true");
exit();
?>
