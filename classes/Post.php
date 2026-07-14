<?php
// Post class - the main event, where your words become immortal (or forgotten)
class Post {
    private $conn;
    private $table = 'posts';
    private $pc_table = 'post_categories';

    public $id;
    public $title;
    public $slug;
    public $content;
    public $summary;
    public $featured_image;
    public $author_id;
    public $status;
    public $created_at;

    // Constructor - pass the database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new post - the birth of a masterpiece
    public function createPost($title, $content, $summary, $author_id, $status = 'draft', $featured_image = null) {
        // Generate a slug from the title (for nice URLs instead of post?id=123)
        $slug = $this->generateSlug($title);

        // Check if slug already exists (append random numbers if it does)
        while ($this->slugExists($slug)) {
            $slug = $this->generateSlug($title) . '-' . rand(100, 999);
        }

        // Set published_at if status is published
        $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;

        $query = "INSERT INTO {$this->table} (title, slug, content, summary, featured_image, author_id, status, published_at)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return array('success' => false, 'message' => $this->conn->error);
        }

        // Bind those parameters like a pro
        $stmt->bind_param('ssssssss', $title, $slug, $content, $summary, $featured_image, $author_id, $status, $published_at);

        if ($stmt->execute()) {
            // Return the new post ID for reference
            return array('success' => true, 'message' => 'Post created successfully!', 'post_id' => $this->conn->insert_id);
        } else {
            return array('success' => false, 'message' => $stmt->error);
        }
    }

    // Get post by ID - retrieve a specific post
    public function getPostById($id) {
        $query = "SELECT p.*, u.username, u.first_name, u.last_name FROM {$this->table} p
                  JOIN users u ON p.author_id = u.id
                  WHERE p.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $post = $stmt->get_result()->fetch_assoc();

        if ($post) {
            // Get categories for this post
            $post['categories'] = $this->getPostCategories($id);
        }

        return $post;
    }

    // Get post by slug - used for pretty URLs
    public function getPostBySlug($slug) {
        $query = "SELECT p.*, u.username, u.first_name, u.last_name FROM {$this->table} p
                  JOIN users u ON p.author_id = u.id
                  WHERE p.slug = ? AND p.status = 'published'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $post = $stmt->get_result()->fetch_assoc();

        if ($post) {
            $post['categories'] = $this->getPostCategories($post['id']);
            // Increment view count (people love stats!)
            $this->incrementViewCount($post['id']);
        }

        return $post;
    }

    // Get all published posts - what the world sees
    public function getPublishedPosts($limit = 10, $offset = 0) {
        $query = "SELECT p.id, p.title, p.slug, p.summary, p.featured_image, p.author_id, p.view_count, p.created_at, p.published_at,
                         u.username, u.first_name, u.last_name FROM {$this->table} p
                  JOIN users u ON p.author_id = u.id
                  WHERE p.status = 'published'
                  ORDER BY p.published_at DESC
                  LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get posts by author - show off their work
    public function getPostsByAuthor($author_id, $status = 'published', $limit = 10, $offset = 0) {
        $query = "SELECT p.id, p.title, p.slug, p.summary, p.featured_image, p.status, p.view_count, p.created_at, p.published_at
                  FROM {$this->table} p
                  WHERE p.author_id = ? AND p.status = ?
                  ORDER BY p.created_at DESC
                  LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('isii', $author_id, $status, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get posts by category - filter by tags
    public function getPostsByCategory($category_id, $limit = 10, $offset = 0) {
        $query = "SELECT p.id, p.title, p.slug, p.summary, p.featured_image, p.view_count, p.created_at, p.published_at,
                         u.username FROM {$this->table} p
                  JOIN {$this->pc_table} pc ON p.id = pc.post_id
                  JOIN users u ON p.author_id = u.id
                  WHERE pc.category_id = ? AND p.status = 'published'
                  ORDER BY p.published_at DESC
                  LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iii', $category_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Search posts - find that one post you can't remember
    public function searchPosts($search_term, $limit = 10, $offset = 0) {
        $search_term = '%' . $search_term . '%';

        $query = "SELECT p.id, p.title, p.slug, p.summary, p.featured_image, p.view_count, p.created_at,
                         u.username FROM {$this->table} p
                  JOIN users u ON p.author_id = u.id
                  WHERE p.status = 'published' AND (p.title LIKE ? OR p.content LIKE ? OR u.username LIKE ?)
                  ORDER BY p.published_at DESC
                  LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sssii', $search_term, $search_term, $search_term, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Update post - change your mind, it's allowed
    public function updatePost($id, $title, $content, $summary, $featured_image = null) {
        $query = "UPDATE {$this->table} SET title = ?, content = ?, summary = ?, featured_image = COALESCE(?, featured_image)
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssssi', $title, $content, $summary, $featured_image, $id);

        return $stmt->execute();
    }

    // Publish post - make it visible to the world
    public function publishPost($id) {
        $query = "UPDATE {$this->table} SET status = 'published', published_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // Delete post - the undo button we all wish had
    public function deletePost($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // Add category to post - tag your work
    public function addCategory($post_id, $category_id) {
        $query = "INSERT INTO {$this->pc_table} (post_id, category_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE post_id=post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $post_id, $category_id);
        return $stmt->execute();
    }

    // Remove category from post - untag yourself
    public function removeCategory($post_id, $category_id) {
        $query = "DELETE FROM {$this->pc_table} WHERE post_id = ? AND category_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $post_id, $category_id);
        return $stmt->execute();
    }

    // Get categories for a post - what's this post about?
    private function getPostCategories($post_id) {
        $query = "SELECT c.id, c.name, c.slug FROM categories c
                  JOIN {$this->pc_table} pc ON c.id = pc.category_id
                  WHERE pc.post_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $post_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Generate slug from title - make URLs pretty
    private function generateSlug($title) {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    // Check if slug exists - avoid duplicates
    private function slugExists($slug) {
        $query = "SELECT id FROM {$this->table} WHERE slug = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Increment view count - people love popularity metrics
    private function incrementViewCount($id) {
        $query = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    // Count published posts - for analytics
    public function getTotalPublishedPosts() {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'published'";
        $result = $this->conn->query($query);
        return $result->fetch_assoc()['count'];
    }

    // Count posts by author
    public function getAuthorPostCount($author_id) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE author_id = ? AND status = 'published'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $author_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }
}
?>
