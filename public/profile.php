<?php
// User Profile Page - here's where you flex your blog stats
require_once dirname(__FILE__) . '/../bootstrap.php';

// Require login
AuthMiddleware::checkAuth();

$user_id = $_SESSION['user_id'];
$user = $userObj->getUserById($user_id);
$posts = $postObj->getPostsByAuthor($user_id, 'published', 100);
$post_count = count($posts);

$errors = [];
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Helper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token';
    } else {
        $first_name = Helper::sanitizeInput($_POST['first_name'] ?? '');
        $last_name = Helper::sanitizeInput($_POST['last_name'] ?? '');
        $bio = Helper::sanitizeInput($_POST['bio'] ?? '');

        if ($userObj->updateProfile($user_id, $first_name, $last_name, $bio)) {
            $success = 'Profile updated successfully!';
            // Refresh user data
            $user = $userObj->getUserById($user_id);
            Helper::logActivity($conn, $user_id, 'UPDATE_PROFILE', 'User updated their profile');
        } else {
            $errors[] = 'Failed to update profile';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - BlogSystem</title>
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
                <?php if (Helper::isAdmin()): ?>
                    <a class="nav-link" href="/BlogSystem/public/admin/dashboard.php">Admin</a>
                <?php endif; ?>
                <a class="nav-link" href="/BlogSystem/public/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h2 class="mb-0">👤 My Profile</h2>
                        </div>
                        <div class="card-body">
                            <!-- Display messages -->
                            <?php foreach ($errors as $error): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <?php echo htmlspecialchars($success); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Profile form -->
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo Helper::generateCsrfToken(); ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                        <small class="text-muted">Cannot be changed</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                        <small class="text-muted">Cannot be changed</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                    <small class="text-muted">Tell other users about yourself!</small>
                                </div>

                                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                            </form>
                        </div>
                    </div>

                    <!-- User's posts section -->
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">📝 My Posts (<?php echo $post_count; ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($post_count > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($posts as $post): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-1">
                                                    <a href="/BlogSystem/public/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" 
                                                       class="text-decoration-none">
                                                        <?php echo htmlspecialchars($post['title']); ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted"><?php echo Helper::formatDate($post['created_at']); ?></small>
                                            </div>
                                            <p class="mb-2 text-muted"><?php echo Helper::excerpt($post['summary'] ?? $post['content'], 100); ?></p>
                                            <a href="/BlogSystem/public/edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="/BlogSystem/public/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-sm btn-primary">View</a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">You haven't published any posts yet. <a href="/BlogSystem/public/create-post.php">Write your first post!</a></p>
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
