<?php
// Comment class - where people debate your blog posts (civilly, we hope)
class Comment {
    private $conn;
    private $table = 'comments';

    public $id;
    public $post_id;
    public $user_id;
    public $content;
    public $status;
    public $created_at;

    // Constructor - pass the database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Add comment - let the discussion begin!
    public function addComment($post_id, $user_id, $content, $parent_id = null) {
        // Sanitize the content (remove HTML tags because we're not monsters)
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        $query = "INSERT INTO {$this->table} (post_id, user_id, parent_comment_id, content, status)
                  VALUES (?, ?, ?, ?, 'pending')";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return array('success' => false, 'message' => $this->conn->error);
        }

        // Bind parameters - make sure parent_id can be NULL
        $stmt->bind_param('iiis', $post_id, $user_id, $parent_id, $content);

        if ($stmt->execute()) {
            return array('success' => true, 'message' => 'Comment submitted! Waiting for moderation.', 'comment_id' => $this->conn->insert_id);
        } else {
            return array('success' => false, 'message' => $stmt->error);
        }
    }

    // Get approved comments for a post - show the good stuff
    public function getPostComments($post_id, $limit = 20, $offset = 0) {
        $query = "SELECT c.id, c.user_id, c.content, c.parent_comment_id, c.created_at, c.is_edited,
                         u.username, u.profile_image FROM {$this->table} c
                  JOIN users u ON c.user_id = u.id
                  WHERE c.post_id = ? AND c.status = 'approved'
                  ORDER BY c.parent_comment_id ASC, c.created_at ASC
                  LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iii', $post_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get comment by ID - retrieve a specific comment
    public function getCommentById($id) {
        $query = "SELECT c.*, u.username FROM {$this->table} c
                  JOIN users u ON c.user_id = u.id
                  WHERE c.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Get pending comments - for moderation purposes
    public function getPendingComments($limit = 20, $offset = 0) {
        $query = "SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username, p.title FROM {$this->table} c
                  JOIN users u ON c.user_id = u.id
                  JOIN posts p ON c.post_id = p.id
                  WHERE c.status = 'pending'
                  ORDER BY c.created_at ASC
                  LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Approve comment - let it go live!
    public function approveComment($id) {
        $query = "UPDATE {$this->table} SET status = 'approved' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // Reject comment - send it to the shadow realm
    public function rejectComment($id) {
        $query = "UPDATE {$this->table} SET status = 'rejected' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // Mark as spam - for when things get nasty
    public function markAsSpam($id) {
        $query = "UPDATE {$this->table} SET status = 'spam' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // Edit comment - fix typos and embarrassing mistakes
    public function editComment($id, $content, $user_id) {
        // Sanitize content again (because you can never be too safe)
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        // Make sure the user owns the comment (authorization check!)
        $query = "UPDATE {$this->table} SET content = ?, is_edited = TRUE, updated_at = NOW()
                  WHERE id = ? AND user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sii', $content, $id, $user_id);
        return $stmt->execute();
    }

    // Delete comment - when you really regret saying something
    public function deleteComment($id, $user_id = null) {
        // If user_id provided, ensure they own the comment
        if ($user_id) {
            $query = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ii', $id, $user_id);
        } else {
            // Admin can delete any comment
            $query = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $id);
        }

        return $stmt->execute();
    }

    // Get comment count for a post - how much engagement?
    public function getApprovedCommentCount($post_id) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE post_id = ? AND status = 'approved'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $post_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }

    // Get total pending comments - for admin dashboard
    public function getPendingCommentCount() {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'pending'";
        $result = $this->conn->query($query);
        return $result->fetch_assoc()['count'];
    }

    // Get replies to a comment - threaded conversations!
    public function getCommentReplies($parent_id) {
        $query = "SELECT c.id, c.user_id, c.content, c.created_at, u.username FROM {$this->table} c
                  JOIN users u ON c.user_id = u.id
                  WHERE c.parent_comment_id = ? AND c.status = 'approved'
                  ORDER BY c.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $parent_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
