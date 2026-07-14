<?php
// Search page - find that one post you half-remember
require_once dirname(__FILE__) . '/../bootstrap.php';

$query = isset($_GET['q']) ? Helper::sanitizeInput($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$results = [];
$total_results = 0;

// Perform search if query provided
if (!empty($query) && strlen($query) >= 2) {
    // Log search activity (useful for analytics)
    Helper::logActivity($conn, $_SESSION['user_id'] ?? null, 'SEARCH', "Searched for: $query");

    // Search posts
    $results = $postObj->searchPosts($query, $per_page, ($page - 1) * $per_page);
    
    // Count total results (approximate count from search)
    $search_query = '%' . $query . '%';
    $count_query = "SELECT COUNT(*) as count FROM posts p
                    WHERE p.status = 'published' 
                    AND (p.title LIKE ? OR p.content LIKE ?)";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param('ss', $search_query, $search_query);
    $stmt->execute();
    $total_results = $stmt->get_result()->fetch_assoc()['count'];
}

$paginate = Helper::paginate($total_results, $per_page, $page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - BlogSystem</title>
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

    <!-- Search page -->
    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8">
                    <h1 class="mb-4 text-center">🔍 Search Blog Posts</h1>

                    <!-- Search form -->
                    <form method="GET" class="mb-4">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" name="q" placeholder="Search posts..." 
                                   value="<?php echo htmlspecialchars($query); ?>">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>

                    <!-- Search results or prompt -->
                    <?php if (!empty($query)): ?>
                        <h4 class="mb-3">
                            <?php if ($total_results > 0): ?>
                                Found <?php echo $total_results; ?> result<?php echo $total_results !== 1 ? 's' : ''; ?> for "<?php echo htmlspecialchars($query); ?>"
                            <?php else: ?>
                                No results found for "<?php echo htmlspecialchars($query); ?>"
                            <?php endif; ?>
                        </h4>

                        <!-- Display results -->
                        <?php foreach ($results as $post): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="/BlogSystem/public/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h5>
                                    <p class="text-muted small mb-2">
                                        By <?php echo htmlspecialchars($post['username']); ?> | 
                                        <?php echo Helper::formatDate($post['created_at']); ?>
                                    </p>
                                    <p class="card-text">
                                        <?php echo Helper::excerpt($post['summary'] ?? $post['content'], 250); ?>
                                    </p>
                                    <a href="/BlogSystem/public/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-sm btn-primary">
                                        Read More →
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Pagination -->
                        <?php if ($paginate['total_pages'] > 1): ?>
                            <nav aria-label="Search results pagination">
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $paginate['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $paginate['total_pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Enter a search term (minimum 2 characters) to find posts!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2024 BlogSystem</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
