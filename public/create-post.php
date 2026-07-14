<?php
// Create post page - birth a new blog post!
require_once dirname(__FILE__) . '/../bootstrap.php';

// Require login
AuthMiddleware::checkAuth();

$errors = [];
$success = '';
$categories = $categoryObj->getAllCategories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Helper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token';
    } else {
        // Get and sanitize inputs
        $title = Helper::sanitizeInput($_POST['title'] ?? '');
        $content = $_POST['content'] ?? ''; // Don't sanitize HTML content!
        $summary = Helper::sanitizeInput($_POST['summary'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $featured_image = null;

        // Validation
        if (empty($title)) {
            $errors[] = 'Title is required';
        } elseif (empty($content)) {
            $errors[] = 'Content is required';
        } elseif ($status !== 'draft' && $status !== 'published') {
            $errors[] = 'Invalid status';
        } else {
            // Handle file upload if provided
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload_dir = BASE_PATH . '/BlogSystem/public/uploads/posts/';
                $upload_result = Helper::uploadFile($_FILES['featured_image'], $upload_dir);

                if (!$upload_result['success']) {
                    $errors[] = $upload_result['message'];
                } else {
                    $featured_image = $upload_result['filename'];

                    // Create thumbnail (optional but cool!)
                    $thumb_dir = BASE_PATH . '/BlogSystem/public/uploads/thumbnails/';
                    if (!is_dir($thumb_dir)) {
                        mkdir($thumb_dir, 0755, true);
                    }
                    Helper::resizeImage(
                        $upload_dir . $featured_image,
                        $thumb_dir . 'thumb_' . $featured_image,
                        200,
                        150
                    );
                }
            }

            // If no errors, create the post
            if (empty($errors)) {
                $result = $postObj->createPost($title, $content, $summary, $_SESSION['user_id'], $status, $featured_image);

                if ($result['success']) {
                    $post_id = $result['post_id'];

                    // Add selected categories
                    $selected_categories = $_POST['categories'] ?? [];
                    foreach ($selected_categories as $cat_id) {
                        $postObj->addCategory($post_id, (int)$cat_id);
                    }

                    // Log activity
                    Helper::logActivity($conn, $_SESSION['user_id'], 'CREATE_POST', "Created post: $title");

                    $success = 'Post created successfully!';

                    // Redirect after 2 seconds
                    header("Refresh: 2; url=/post.php?slug=" . $postObj->getPostById($post_id)['slug']);
                } else {
                    $errors[] = $result['message'];
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
    <title>Create Post - BlogSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/BlogSystem/public/css/bootstrap.min.css" rel="stylesheet">
    <!-- TinyMCE rich text editor (optional - remove if not needed) -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
    <link rel="stylesheet" href="/BlogSystem/public/css/style.css">
    <script>
        // Initialize TinyMCE for rich text editing (because plain text is so boring)
        tinymce.init({
            selector: '#content',
            height: 400,
            plugins: 'link image code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | link image | code'
        });
    </script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/BlogSystem/public/index.php">📝 BlogSystem</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/BlogSystem/public/index.php">Home</a>
                <a class="nav-link" href="/BlogSystem/public/profile.php">Profile</a>
                <a class="nav-link" href="/BlogSystem/public/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h2 class="mb-0">✍️ Create New Post</h2>
                        </div>
                        <div class="card-body">
                            <!-- Display errors -->
                            <?php foreach ($errors as $error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>

                            <!-- Display success -->
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Post creation form -->
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo Helper::generateCsrfToken(); ?>">

                                <!-- Title field -->
                                <div class="mb-3">
                                    <label for="title" class="form-label">Post Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>

                                <!-- Summary/Excerpt field -->
                                <div class="mb-3">
                                    <label for="summary" class="form-label">Summary (short preview)</label>
                                    <textarea class="form-control" id="summary" name="summary" rows="2" 
                                              placeholder="Brief description of your post..."></textarea>
                                </div>

                                <!-- Featured image upload -->
                                <div class="mb-3">
                                    <label for="featured_image" class="form-label">Featured Image</label>
                                    <input type="file" class="form-control" id="featured_image" name="featured_image" 
                                           accept="image/*">
                                    <small class="text-muted">Max 5MB, formats: JPG, PNG, GIF</small>
                                </div>

                                <!-- Content editor (rich text with TinyMCE!) -->
                                <div class="mb-3">
                                    <label for="content" class="form-label">Content *</label>
                                    <textarea class="form-control" id="content" name="content" required></textarea>
                                </div>

                                <!-- Categories selection -->
                                <div class="mb-3">
                                    <label for="categories" class="form-label">Categories</label>
                                    <div class="form-check">
                                        <?php foreach ($categories as $cat): ?>
                                            <div>
                                                <input type="checkbox" class="form-check-input" id="cat_<?php echo $cat['id']; ?>" 
                                                       name="categories[]" value="<?php echo $cat['id']; ?>">
                                                <label class="form-check-label" for="cat_<?php echo $cat['id']; ?>">
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Status selection -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="draft">Draft (Private)</option>
                                        <option value="published">Publish Now</option>
                                    </select>
                                    <small class="text-muted">Choose Draft to save for later, Publish to go live immediately</small>
                                </div>

                                <!-- Submit buttons -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">📝 Create Post</button>
                                    <a href="/BlogSystem/public/index.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
