<?php
// Category class - organize your content into bite-sized chunks
class Category {
    private $conn;
    private $table = 'categories';

    public $id;
    public $name;
    public $slug;
    public $description;
    public $created_by;

    // Constructor - pass the database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create category - organize! organize! organize!
    public function createCategory($name, $description, $created_by) {
        // Generate slug from name (keep URLs clean)
        $slug = $this->generateSlug($name);

        // Make sure slug is unique (add numbers if needed)
        $count = 1;
        $original_slug = $slug;
        while ($this->slugExists($slug)) {
            $slug = $original_slug . '-' . $count;
            $count++;
        }

        $query = "INSERT INTO {$this->table} (name, slug, description, created_by)
                  VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return array('success' => false, 'message' => $this->conn->error);
        }

        $stmt->bind_param('sssi', $name, $slug, $description, $created_by);

        if ($stmt->execute()) {
            return array('success' => true, 'message' => 'Category created!', 'category_id' => $this->conn->insert_id);
        } else {
            return array('success' => false, 'message' => $stmt->error);
        }
    }

    // Get all categories - show 'em all
    public function getAllCategories() {
        $query = "SELECT id, name, slug, description FROM {$this->table} ORDER BY name ASC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get category by ID - retrieve a specific one
    public function getCategoryById($id) {
        $query = "SELECT id, name, slug, description FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Get category by slug - for pretty URLs
    public function getCategoryBySlug($slug) {
        $query = "SELECT id, name, slug, description FROM {$this->table} WHERE slug = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update category - fix those typos!
    public function updateCategory($id, $name, $description) {
        $query = "UPDATE {$this->table} SET name = ?, description = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssi', $name, $description, $id);
        return $stmt->execute();
    }

    // Delete category - be careful with this one!
    public function deleteCategory($id) {
        // This will cascade delete due to foreign key constraints
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // Get post count for category - how popular is it?
    public function getPostCountByCategory($category_id) {
        $query = "SELECT COUNT(*) as count FROM post_categories WHERE category_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $category_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }

    // Get categories with post counts - for display
    public function getCategoriesWithCounts() {
        $query = "SELECT c.id, c.name, c.slug, c.description, COUNT(pc.post_id) as post_count
                  FROM {$this->table} c
                  LEFT JOIN post_categories pc ON c.id = pc.category_id
                  GROUP BY c.id
                  ORDER BY c.name ASC";

        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Check if category name exists - avoid duplicates
    public function categoryExists($name) {
        $query = "SELECT id FROM {$this->table} WHERE name = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $name);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Generate slug from name - make URLs pretty
    private function generateSlug($name) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    // Check if slug exists - avoid duplicate URLs
    private function slugExists($slug) {
        $query = "SELECT id FROM {$this->table} WHERE slug = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
?>
