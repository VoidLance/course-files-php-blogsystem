<?php
// Helper file. Straightforward on purpose, because beginner code should be readable.
// Helper functions - little utilities that make life easier
class Helper {
    
    // Generate CSRF token - protect forms from cross-site attacks
    public static function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Verify CSRF token - check that the form came from your site
    public static function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Sanitize input - remove nasty stuff
    public static function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    // Validate email - is it actually an email?
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Validate password - must be strong enough
    public static function validatePassword($password) {
        // At least 8 chars, 1 uppercase, 1 number, 1 special char (basic requirements)
        return strlen($password) >= 8 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }

    // Generate filename for uploads - avoid duplicates
    public static function generateFileName($original_filename) {
        $ext = pathinfo($original_filename, PATHINFO_EXTENSION);
        return time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    }

    // Handle file upload - securely save files
    public static function uploadFile($file, $upload_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 5242880) {
        // Check file errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array('success' => false, 'message' => 'File upload error');
        }

        // Get file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($ext, $allowed_types)) {
            return array('success' => false, 'message' => 'File type not allowed');
        }

        // Validate file size
        if ($file['size'] > $max_size) {
            return array('success' => false, 'message' => 'File too large');
        }

        // Generate new filename
        $new_filename = self::generateFileName($file['name']);
        $target_path = $upload_dir . $new_filename;

        // Make sure directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return array('success' => true, 'message' => 'File uploaded', 'filename' => $new_filename);
        } else {
            return array('success' => false, 'message' => 'Failed to save file');
        }
    }

    // Resize image - create thumbnail
    public static function resizeImage($source, $destination, $width, $height) {
        $info = getimagesize($source);
        $mime = $info['mime'];

        // Load image based on type
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($source);
                break;
            default:
                return false;
        }

        // Get original dimensions
        $orig_width = imagesx($image);
        $orig_height = imagesy($image);

        // Calculate new dimensions (maintain aspect ratio)
        $ratio = $orig_width / $orig_height;
        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        // Create new image
        $resized = imagecreatetruecolor($width, $height);

        // Preserve transparency for PNG/GIF
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagecolortransparent($resized, imagecolorallocatealpha($resized, 0, 0, 0, 127));
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        // Resize
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

        // Save
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($resized, $destination, 90);
                break;
            case 'image/png':
                imagepng($resized, $destination);
                break;
            case 'image/gif':
                imagegif($resized, $destination);
                break;
        }

        imagedestroy($image);
        imagedestroy($resized);
        return true;
    }

    // Paginate results - slice and dice your data
    public static function paginate($total_items, $items_per_page, $current_page) {
        $total_pages = ceil($total_items / $items_per_page);
        $offset = ($current_page - 1) * $items_per_page;

        return array(
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'current_page' => $current_page,
            'offset' => $offset,
            'items_per_page' => $items_per_page
        );
    }

    // Format date - make dates readable
    public static function formatDate($date) {
        return date('M d, Y', strtotime($date));
    }

    // Format date with time
    public static function formatDateTime($date) {
        return date('M d, Y @ g:i A', strtotime($date));
    }

    // Truncate text - make previews
    public static function truncateText($text, $length = 150) {
        if (strlen($text) > $length) {
            return substr($text, 0, $length) . '...';
        }
        return $text;
    }

    // Strip HTML tags and truncate - for summaries
    public static function excerpt($text, $length = 150) {
        $text = strip_tags($text);
        return self::truncateText($text, $length);
    }

    // Log activity - keep records for auditing
    public static function logActivity($db, $user_id, $action, $description = '', $ip = '') {
        if (!$ip) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        $query = "INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bind_param('isss', $user_id, $action, $description, $ip);
        $stmt->execute();
    }

    // Send secure headers - protect against common attacks
    public static function setSecureHeaders() {
        // Prevent clickjacking
        header("X-Frame-Options: SAMEORIGIN");
        // Prevent MIME sniffing
        header("X-Content-Type-Options: nosniff");
        // Enable XSS protection
        header("X-XSS-Protection: 1; mode=block");
        // Content Security Policy (basic)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'");
    }

    // Check if user is admin - role-based access
    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    // Check if user is logged in - authorization check
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Redirect if not logged in - protect pages
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header("Location: /login.php");
            exit();
        }
    }

    // Redirect if not admin - protect admin areas
    public static function requireAdmin() {
        if (!self::isAdmin()) {
            header("Location: /access-denied.php");
            exit();
        }
    }
}
?>
