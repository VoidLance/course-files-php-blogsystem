<?php
// Main entry point - the home page where visitors first land
require_once dirname(__FILE__) . '/../bootstrap.php';

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;

// Get total posts for pagination math
$total_posts = $postObj->getTotalPublishedPosts();
$paginate = Helper::paginate($total_posts, $per_page, $page);

// Get recent posts for this page
$posts = $postObj->getPublishedPosts($per_page, $paginate['offset']);

// Get categories for the sidebar
$categories = $categoryObj->getCategoriesWithCounts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blog - Welcome to the Chronicles of My Thoughts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/BlogSystem/public/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BlogSystem/public/css/style.css">
</head>
<body>
    <!-- Navigation bar - where users explore the site -->
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
                    <?php if (Helper::isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/BlogSystem/public/create-post.php">Create Post</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/BlogSystem/public/profile.php">Profile</a>
                        </li>
                        <?php if (Helper::isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/BlogSystem/public/admin/dashboard.php">Admin Panel</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/BlogSystem/public/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/BlogSystem/public/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/BlogSystem/public/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main content area - where the magic happens -->
    <main class="py-5">
        <div class="container">
            <div class="row">
                <!-- Blog posts column - the main event -->
                <div class="col-md-8">
                    <h1 class="mb-4">Latest Posts</h1>

                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="card mb-4">
                                <?php if ($post['featured_image']): ?>
                                    <img src="/BlogSystem/public/uploads/posts/<?php echo htmlspecialchars($post['featured_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="/BlogSystem/public/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h5>
                                    <p class="text-muted small">
                                        By <strong><?php echo htmlspecialchars($post['username']); ?></strong> 
                                        on <?php echo Helper::formatDate($post['published_at']); ?>
                                        | <?php echo $post['view_count']; ?> views
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

                        <!-- Pagination links - navigate through posts -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $paginate['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $paginate['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>No posts published yet. Check back soon!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar - extra navigation and filters -->
                <div class="col-md-4">
                    <!-- Categories widget - browse by topic -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">📁 Categories</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($categories as $cat): ?>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <a href="/BlogSystem/public/category.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </a>
                                        <span class="badge bg-secondary"><?php echo $cat['post_count']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- About widget - tell visitors about yourself -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">👋 About This Blog</h5>
                        </div>
                        <div class="card-body">
                            <p>Welcome to my blog! Here I share my thoughts, experiences, and random musings about life, tech, and everything in between.</p>
                            <p class="mb-0">Feel free to browse around, read some posts, and leave comments!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer - the bottom of the page -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2024 BlogSystem. Built with ❤️ and PHP.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
