<?php
// Edit Post Page - fix those typos and change your mind!
require_once dirname(__FILE__) . '/../bootstrap.php';

AuthMiddleware::checkAuth();

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$post_id) {
    header("Location: /BlogSystem/public/index.php");
    exit();
}

// Get the post
$post = $postObj->getPostById($post_id);

if (!$post) {
    header("HTTP/1.0 404 Not Found");
    echo "Post not found";
    exit();
}

// Check ownership (author or admin can edit)
if ($post['author_id'] !== $_SESSION['user_id'] && !Helper::isAdmin()) {
    header("Location: /BlogSystem/public/access-denied.php");
    exit();
}

$errors = [];
$success = '';
$categories = $categoryObj->getAllCategories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Helper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token';
    } else {
        $title = Helper::sanitizeInput($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $summary = Helper::sanitizeInput($_POST['summary'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        if (empty($title) || empty($content)) {
            $errors[] = 'Title and content are required';
        } else {
            // Handle featured image if uploaded
            $featured_image = null;
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload_dir = BASE_PATH . '/BlogSystem/public/uploads/posts/';
                $upload_result = Helper::uploadFile($_FILES['featured_image'], $upload_dir);
                if (!$upload_result['success']) {
                    $errors[] = $upload_result['message'];
                } else {
                    $featured_image = $upload_result['filename'];
                }
            }

            if (empty($errors)) {
                // Update the post
                if ($postObj->updatePost($post_id, $title, $content, $summary, $featured_image)) {
                    // Update status if changed
                    if ($post['status'] !== $status) {
                        $status_query = "UPDATE posts SET status = ? WHERE id = ?";
                        $stmt = $conn->prepare($status_query);
                        $stmt->bind_param('si', $status, $post_id);
                        $stmt->execute();
                    }

                    // Update categories
                    $old_categories = $post['categories'] ?? [];
                    $new_categories = $_POST['categories'] ?? [];

                    // Remove categories not in new list
                    foreach ($old_categories as $old_cat) {
                        if (!in_array($old_cat['id'], $new_categories)) {
                            $postObj->removeCategory($post_id, $old_cat['id']);
                        }
                    }

                    // Add new categories
                    foreach ($new_categories as $new_cat) {
                        $postObj->addCategory($post_id, (int)$new_cat);
                    }

                    $success = 'Post updated successfully!';
                    Helper::logActivity($conn, $_SESSION['user_id'], 'EDIT_POST', "Edited post: $title");

                    // Refresh post data
                    $post = $postObj->getPostById($post_id);
                } else {
                    $errors[] = 'Failed to update post';
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
    <title>Edit Post - BlogSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/BlogSystem/public/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
    <link rel="stylesheet" href="/BlogSystem/public/css/style.css">
    <script>
        // Initialize TinyMCE for rich text editing
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
                <a class="nav-link" href="/BlogSystem/public/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>">Back to Post</a>
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
                        <div class="card-header bg-warning text-dark">
                            <h2 class="mb-0">✏️ Edit Post</h2>
                        </div>
                        <div class="card-body">
                            <!-- Display errors -->
                            <?php foreach ($errors as $error): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>

                            <!-- Display success -->
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Edit form -->
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo Helper::generateCsrfToken(); ?>">

                                <!-- Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label">Post Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($post['title']); ?>" required>
                                </div>

                                <!-- Summary -->
                                <div class="mb-3">
                                    <label for="summary" class="form-label">Summary</label>
                                    <textarea class="form-control" id="summary" name="summary" rows="2"><?php echo htmlspecialchars($post['summary'] ?? ''); ?></textarea>
                                </div>

                                <!-- Featured image -->
                                <div class="mb-3">
                                    <label for="featured_image" class="form-label">Featured Image</label>
                                    <?php if ($post['featured_image']): ?>
                                        <div class="mb-2">
                                            <img src="/BlogSystem/public/uploads/posts/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                                 class="img-thumbnail" style="max-height: 200px;" alt="Featured image">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                </div>

                                <!-- Content -->
                                <div class="mb-3">
                                    <label for="content" class="form-label">Content *</label>
                                    <textarea class="form-control" id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                                </div>

                                <!-- Categories -->
                                <div class="mb-3">
                                    <label class="form-label">Categories</label>
                                    <?php foreach ($categories as $cat): ?>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="cat_<?php echo $cat['id']; ?>" 
                                                   name="categories[]" value="<?php echo $cat['id']; ?>"
                                                   <?php echo in_array($cat['id'], array_column($post['categories'], 'id')) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="cat_<?php echo $cat['id']; ?>">
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Status -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                        <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                        <option value="archived" <?php echo $post['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning">💾 Save Changes</button>
                                    <a href="/BlogSystem/public/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-secondary">Cancel</a>
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
