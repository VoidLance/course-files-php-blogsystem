<?php
// Author Profile Page - showcase their work!
require_once dirname(__FILE__) . '/../bootstrap.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$user_id) {
    header("Location: /BlogSystem/public/index.php");
    exit();
}

// Get the author
$author = $userObj->getUserById($user_id);

if (!$author) {
    header("HTTP/1.0 404 Not Found");
    echo "Author not found";
    exit();
}

// Get their published posts
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$posts = $postObj->getPostsByAuthor($user_id, 'published', $per_page, ($page - 1) * $per_page);
$total_posts = $postObj->getAuthorPostCount($user_id);
$paginate = Helper::paginate($total_posts, $per_page, $page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($author['username']); ?> - BlogSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/BlogSystem/public/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BlogSystem/public/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/BlogSystem/public/index.php">📝 BlogSystem</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/BlogSystem/public/index.php">Home</a>
                <a class="nav-link" href="/BlogSystem/public/search.php">Search</a>
            </div>
        </div>
    </nav>

    <!-- Author header -->
    <div class="bg-light py-5 mb-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px; font-size: 3em; margin: 0 auto;">
                        👤
                    </div>
                </div>
                <div class="col-md-10">
                    <h1><?php echo htmlspecialchars($author['first_name'] . ' ' . $author['last_name']); ?></h1>
                    <p class="text-muted">@<?php echo htmlspecialchars($author['username']); ?></p>
                    <?php if ($author['bio']): ?>
                        <p><?php echo htmlspecialchars($author['bio']); ?></p>
                    <?php endif; ?>
                    <p class="small text-muted">Member since <?php echo Helper::formatDate($author['created_at']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <main class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="mb-4">📝 Posts by <?php echo htmlspecialchars($author['username']); ?> (<?php echo $total_posts; ?>)</h2>

                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="card mb-4">
                                <?php if ($post['featured_image']): ?>
                                    <img src="/BlogSystem/public/uploads/posts/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="/BlogSystem/public/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h5>
                                    <p class="text-muted small">
                                        Published <?php echo Helper::formatDate($post['published_at']); ?> | 
                                        <?php echo $post['view_count']; ?> views
                                    </p>
                                    <p class="card-text">
                                        <?php echo Helper::excerpt($post['summary'] ?? $post['content'], 200); ?>
                                    </p>
                                    <a href="/BlogSystem/public/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-primary btn-sm">
                                        Read More →
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Pagination -->
                        <?php if ($paginate['total_pages'] > 1): ?>
                            <nav>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?id=<?php echo $user_id; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $paginate['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?id=<?php echo $user_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $paginate['total_pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?id=<?php echo $user_id; ?>&page=<?php echo $page + 1; ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            This author hasn't published any posts yet. Check back later!
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">📊 Author Stats</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <strong>Total Posts:</strong> <?php echo $total_posts; ?>
                            </p>
                            <p class="mb-2">
                                <strong>Member Since:</strong> <?php echo Helper::formatDate($author['created_at']); ?>
                            </p>
                            <?php if ($author['last_login']): ?>
                                <p class="mb-0">
                                    <strong>Last Active:</strong> <?php echo Helper::formatDate($author['last_login']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <p class="mb-0">&copy; 2024 BlogSystem</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
