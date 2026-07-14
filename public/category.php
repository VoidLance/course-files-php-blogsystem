<?php
// Category page - view all posts in a category
require_once dirname(__FILE__) . '/../bootstrap.php';

$slug = isset($_GET['slug']) ? Helper::sanitizeInput($_GET['slug']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;

if (empty($slug)) {
    header("Location: /BlogSystem/public/index.php");
    exit();
}

// Get category
$category = $categoryObj->getCategoryBySlug($slug);

if (!$category) {
    header("HTTP/1.0 404 Not Found");
    echo "Category not found";
    exit();
}

// Get posts in category
$posts = $postObj->getPostsByCategory($category['id'], $per_page, ($page - 1) * $per_page);

// Count total posts (for pagination)
$total = $categoryObj->getPostCountByCategory($category['id']);
$paginate = Helper::paginate($total, $per_page, $page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> - BlogSystem</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="/BlogSystem/public/search.php">Search</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <main class="py-5">
        <div class="container">
            <!-- Category header -->
            <div class="mb-5">
                <h1><?php echo htmlspecialchars($category['name']); ?></h1>
                <p class="lead text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
            </div>

            <!-- Posts in category -->
            <div class="row">
                <div class="col-lg-8">
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
                                        By <strong><?php echo htmlspecialchars($post['username']); ?></strong> on <?php echo Helper::formatDate($post['created_at']); ?>
                                    </p>
                                    <p class="card-text">
                                        <?php echo Helper::excerpt($post['summary'] ?? $post['content'], 200); ?>
                                    </p>
                                    <a href="/BlogSystem/public/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-primary btn-sm">Read More →</a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Pagination -->
                        <?php if ($paginate['total_pages'] > 1): ?>
                            <nav>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?slug=<?php echo htmlspecialchars($slug); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $paginate['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?slug=<?php echo htmlspecialchars($slug); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $paginate['total_pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?slug=<?php echo htmlspecialchars($slug); ?>&page=<?php echo $page + 1; ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No posts in this category yet.</div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">📁 Other Categories</h5>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($categoryObj->getCategoriesWithCounts() as $cat): ?>
                                    <li class="list-group-item">
                                        <a href="/BlogSystem/public/category.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </a>
                                        <span class="badge bg-secondary float-end"><?php echo $cat['post_count']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
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
