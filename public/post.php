<?php
// Post view page - read your favorite blog posts here!
require_once dirname(__FILE__) . '/../bootstrap.php';

// Get the slug from URL
$slug = isset($_GET['slug']) ? Helper::sanitizeInput($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: /BlogSystem/public/index.php");
    exit();
}

// Retrieve the post
$post = $postObj->getPostBySlug($slug);

if (!$post) {
    header("HTTP/1.0 404 Not Found");
    echo "Post not found";
    exit();
}

// Get comments for this post
$comments = $commentObj->getPostComments($post['id']);
$comment_count = $commentObj->getApprovedCommentCount($post['id']);

// Handle comment submission
$comment_errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    // Require login to comment
    if (!Helper::isLoggedIn()) {
        $comment_errors[] = 'You must be logged in to comment';
    } else {
        // Verify CSRF token
        if (!Helper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $comment_errors[] = 'Invalid security token';
        } else {
            $content = Helper::sanitizeInput($_POST['content'] ?? '');

            if (empty($content)) {
                $comment_errors[] = 'Comment cannot be empty';
            } else {
                $result = $commentObj->addComment($post['id'], $_SESSION['user_id'], $content);
                if ($result['success']) {
                    echo "<div class='alert alert-success'>Comment submitted and awaiting moderation</div>";
                } else {
                    $comment_errors[] = $result['message'];
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - BlogSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/BlogSystem/public/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BlogSystem/public/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/BlogSystem/public/index.php">📝 BlogSystem</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/BlogSystem/public/index.php">Home</a>
                    </li>
                    <?php if (Helper::isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/BlogSystem/public/profile.php">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/BlogSystem/public/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/BlogSystem/public/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Featured image -->
                    <?php if ($post['featured_image']): ?>
                        <img src="/BlogSystem/public/uploads/posts/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                             class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php endif; ?>

                    <!-- Post title -->
                    <h1 class="mb-2"><?php echo htmlspecialchars($post['title']); ?></h1>

                    <!-- Post metadata (who, when, stats) -->
                    <div class="mb-4 text-muted">
                        <p class="mb-2">
                            By <strong>
                                <a href="/BlogSystem/public/author.php?id=<?php echo $post['author_id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?>
                                </a>
                            </strong>
                            on <?php echo Helper::formatDate($post['published_at']); ?>
                        </p>
                        <p class="mb-0">
                            👁️ <?php echo $post['view_count']; ?> views | 
                            💬 <?php echo $comment_count; ?> comments
                        </p>
                    </div>

                    <!-- Categories -->
                    <?php if (!empty($post['categories'])): ?>
                        <div class="mb-3">
                            <?php foreach ($post['categories'] as $cat): ?>
                                <a href="/BlogSystem/public/category.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>" 
                                   class="badge bg-primary text-decoration-none">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <hr>

                    <!-- Post content -->
                    <div class="post-content mb-5">
                        <?php echo $post['content']; ?>
                    </div>

                    <hr>

                    <!-- Edit/Delete buttons for post author or admin -->
                    <?php if (Helper::isLoggedIn() && ($post['author_id'] === $_SESSION['user_id'] || Helper::isAdmin())): ?>
                        <div class="mb-4">
                            <a href="/BlogSystem/public/edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-warning btn-sm">
                                ✏️ Edit Post
                            </a>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                🗑️ Delete Post
                            </button>
                        </div>

                        <!-- Delete confirmation modal -->
                        <div class="modal fade" id="deleteModal">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Delete</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure? This cannot be undone!
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <a href="/BlogSystem/public/delete-post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger">
                                            Yes, Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Comments section -->
                    <section class="mb-5">
                        <h2 class="mb-4">💬 Comments (<?php echo $comment_count; ?>)</h2>

                        <!-- Display comment errors -->
                        <?php foreach ($comment_errors as $error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>

                        <!-- Comment form -->
                        <?php if (Helper::isLoggedIn()): ?>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Leave a Comment</h5>
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo Helper::generateCsrfToken(); ?>">
                                        <input type="hidden" name="add_comment" value="1">

                                        <div class="mb-3">
                                            <textarea class="form-control" name="content" rows="4" 
                                                      placeholder="Share your thoughts..." required></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Post Comment</button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <a href="/BlogSystem/public/login.php">Login</a> to post a comment
                            </div>
                        <?php endif; ?>

                        <!-- Display comments -->
                        <?php if (count($comments) > 0): ?>
                            <div class="comments-list">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                                <small class="text-muted">
                                                    <?php echo Helper::formatDate($comment['created_at']); ?>
                                                    <?php if ($comment['is_edited']): ?>
                                                        <em>(edited)</em>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No comments yet. Be the first!</p>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">&copy; 2024 BlogSystem</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
