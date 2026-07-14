<?php
// Admin dashboard page. Same app, more buttons, slightly more danger.
// Admin dashboard - the nerve center of blog management!
require_once dirname(__FILE__) . '/../../bootstrap.php';

// Require admin access
AuthMiddleware::checkAdmin();

// Get statistics for the dashboard (the numbers that matter)
$query_total_users = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($query_total_users);
$total_users = $result->fetch_assoc()['count'];

$total_posts = $postObj->getTotalPublishedPosts();

$query_total_comments = "SELECT COUNT(*) as count FROM comments";
$result = $conn->query($query_total_comments);
$total_comments = $result->fetch_assoc()['count'];

$pending_comments = $commentObj->getPendingCommentCount();

// Get recent activity
$query_recent_posts = "SELECT id, title, slug, created_at, u.username FROM posts p 
                       JOIN users u ON p.author_id = u.id 
                       ORDER BY p.created_at DESC LIMIT 5";
$recent_posts = $conn->query($query_recent_posts)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BlogSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/BlogSystem/public/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BlogSystem/public/css/style.css">
    <style>
        /* Admin dashboard styling - because admins deserve nice things */
        .sidebar {
            background-color: #2c3e50;
            min-height: 100vh;
            padding: 20px 0;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            border-left: 3px solid transparent;
            transition: 0.3s;
        }
        .sidebar a:hover,
        .sidebar a.active {
            border-left-color: #3498db;
            background-color: rgba(52, 152, 219, 0.1);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card h3 {
            font-size: 2em;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar navigation -->
            <div class="col-md-3 sidebar">
                <div class="px-3 mb-4">
                    <h5 class="text-white">⚙️ Admin Panel</h5>
                    <small class="text-muted">Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?></small>
                </div>

                <nav>
                    <a href="/BlogSystem/public/admin/dashboard.php" class="active">📊 Dashboard</a>
                    <a href="/BlogSystem/public/admin/posts.php">📝 Manage Posts</a>
                    <a href="/BlogSystem/public/admin/categories.php">📁 Categories</a>
                    <a href="/BlogSystem/public/admin/comments.php">💬 Comments (<?php echo $pending_comments > 0 ? '<span class="badge bg-danger">' . $pending_comments . '</span>' : '0'; ?>)</a>
                    <a href="/BlogSystem/public/admin/users.php">👥 Users</a>
                    <a href="/BlogSystem/public/admin/settings.php">⚙️ Settings</a>
                    <hr class="bg-secondary">
                    <a href="/BlogSystem/public/index.php">← Back to Blog</a>
                    <a href="/BlogSystem/public/logout.php">🚪 Logout</a>
                </nav>
            </div>

            <!-- Main content area -->
            <div class="col-md-9 p-4">
                <h1 class="mb-4">📊 Dashboard Overview</h1>

                <!-- Statistics cards - the numbers game! -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="text-center">
                                <h3><?php echo $total_users; ?></h3>
                                <p class="mb-0">Total Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="text-center">
                                <h3><?php echo $total_posts; ?></h3>
                                <p class="mb-0">Published Posts</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="text-center">
                                <h3><?php echo $total_comments; ?></h3>
                                <p class="mb-0">Total Comments</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: #333;">
                            <div class="text-center">
                                <h3><?php echo $pending_comments; ?></h3>
                                <p class="mb-0">Pending Moderation</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick actions -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">⚡ Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="/BlogSystem/public/create-post.php" class="btn btn-primary btn-sm">+ New Post</a>
                        <a href="/BlogSystem/public/admin/categories.php" class="btn btn-success btn-sm">+ New Category</a>
                        <a href="/BlogSystem/public/admin/users.php" class="btn btn-info btn-sm">Manage Users</a>
                        <a href="/BlogSystem/public/admin/comments.php" class="btn btn-warning btn-sm">Review Comments</a>
                    </div>
                </div>

                <!-- Recent posts activity -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">📰 Recently Created Posts</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_posts as $rpost): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($rpost['title']); ?></td>
                                        <td><?php echo htmlspecialchars($rpost['username']); ?></td>
                                        <td><?php echo Helper::formatDate($rpost['created_at']); ?></td>
                                        <td>
                                            <a href="/BlogSystem/public/post.php?slug=<?php echo htmlspecialchars($rpost['slug']); ?>" 
                                               class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="/BlogSystem/public/edit-post.php?id=<?php echo $rpost['id']; ?>" 
                                               class="btn btn-sm btn-outline-secondary">Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- System info -->
                <div class="alert alert-info mt-4" role="alert">
                    <strong>💡 Tip:</strong> Use the sidebar navigation to manage posts, categories, comments, and users. 
                    Check the Comments section to moderate pending comments!
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
