<?php
// Admin comments page. Same app, more buttons, slightly more danger.
// Admin: Manage Comments - moderate those opinions!
require_once dirname(__FILE__) . '/../../bootstrap.php';

AuthMiddleware::checkAdmin();

$action = isset($_GET['action']) ? $_GET['action'] : 'pending';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;

// Handle comment approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : null;
    $approval_action = isset($_POST['action']) ? $_POST['action'] : null;

    if ($comment_id && $approval_action) {
        if ($approval_action === 'approve') {
            $commentObj->approveComment($comment_id);
            $message = 'Comment approved!';
        } elseif ($approval_action === 'reject') {
            $commentObj->rejectComment($comment_id);
            $message = 'Comment rejected';
        } elseif ($approval_action === 'spam') {
            $commentObj->markAsSpam($comment_id);
            $message = 'Comment marked as spam';
        } elseif ($approval_action === 'delete') {
            $commentObj->deleteComment($comment_id);
            $message = 'Comment deleted';
        }
    }
}

// Get comments based on filter
$offset = ($page - 1) * $per_page;
if ($action === 'pending') {
    $comments = $commentObj->getPendingComments($per_page, $offset);
    $total = $commentObj->getPendingCommentCount();
} else {
    // Get all comments (not just pending)
    $query = "SELECT c.id, c.post_id, c.user_id, c.content, c.status, c.created_at, u.username, p.title FROM comments c
              JOIN users u ON c.user_id = u.id
              JOIN posts p ON c.post_id = p.id
              WHERE c.status = ?
              ORDER BY c.created_at DESC
              LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sii', $action, $per_page, $offset);
    $stmt->execute();
    $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get total count
    $count_query = "SELECT COUNT(*) as count FROM comments WHERE status = ?";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param('s', $action);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['count'];
}

$paginate = Helper::paginate($total, $per_page, $page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/BlogSystem/public/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BlogSystem/public/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3" style="background-color: #2c3e50; min-height: 100vh; color: #ecf0f1; padding: 20px 0;">
                <div class="px-3 mb-4">
                    <h5 class="text-white">⚙️ Admin Panel</h5>
                </div>
                <nav>
                    <a href="/BlogSystem/public/admin/dashboard.php" style="color: #ecf0f1; text-decoration: none; padding: 12px 20px; display: block;">📊 Dashboard</a>
                    <a href="/BlogSystem/public/admin/comments.php" style="color: #ecf0f1; text-decoration: none; padding: 12px 20px; display: block; background-color: rgba(52, 152, 219, 0.1);">💬 Comments</a>
                    <a href="/BlogSystem/public/admin/posts.php" style="color: #ecf0f1; text-decoration: none; padding: 12px 20px; display: block;">📝 Posts</a>
                    <a href="/BlogSystem/public/index.php" style="color: #ecf0f1; text-decoration: none; padding: 12px 20px; display: block;">← Back to Blog</a>
                </nav>
            </div>

            <!-- Main content -->
            <div class="col-md-9 p-4">
                <h1 class="mb-4">💬 Manage Comments</h1>

                <?php if (isset($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filter tabs -->
                <div class="mb-4">
                    <a href="?action=pending" class="btn <?php echo $action === 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        ⏳ Pending (<?php echo $commentObj->getPendingCommentCount(); ?>)
                    </a>
                    <a href="?action=approved" class="btn <?php echo $action === 'approved' ? 'btn-success' : 'btn-outline-success'; ?>">
                        ✅ Approved
                    </a>
                    <a href="?action=rejected" class="btn <?php echo $action === 'rejected' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                        ❌ Rejected
                    </a>
                    <a href="?action=spam" class="btn <?php echo $action === 'spam' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                        🚫 Spam
                    </a>
                </div>

                <!-- Comments table -->
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Author</th>
                                    <th>Post</th>
                                    <th>Comment</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($comments) > 0): ?>
                                    <?php foreach ($comments as $comment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($comment['username']); ?></td>
                                            <td>
                                                <a href="/BlogSystem/public/post.php?id=<?php echo $comment['post_id']; ?>" 
                                                   class="text-decoration-none">
                                                    <?php echo htmlspecialchars(substr($comment['title'], 0, 30)); ?>...
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars(substr($comment['content'], 0, 50)); ?>...</td>
                                            <td><?php echo Helper::formatDate($comment['created_at']); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <?php if ($action !== 'approved'): ?>
                                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">✅</button>
                                                    <?php endif; ?>
                                                    <?php if ($action !== 'rejected'): ?>
                                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">❌</button>
                                                    <?php endif; ?>
                                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-dark">🗑️</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No comments in this category</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($paginate['total_pages'] > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?action=<?php echo $action; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $paginate['total_pages']; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?action=<?php echo $action; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $paginate['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?action=<?php echo $action; ?>&page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
