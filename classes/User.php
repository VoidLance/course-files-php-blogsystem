<?php
// User class - where we handle all that human stuff (authentication, profiles, etc)
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $bio;
    public $profile_image;
    public $role;
    public $created_at;

    // Constructor - set up the database connection (MUST be passed in)
    public function __construct($db) {
        $this->conn = $db;
    }

    // Register a new user - the beginning of their blogging journey
    public function register($username, $email, $password, $first_name, $last_name) {
        // Hash the password because storing plain text is for villains
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Generate a unique email verification token
        $email_token = bin2hex(random_bytes(32));

        // Insert the new user into the database
        $query = "INSERT INTO {$this->table} (username, email, password, first_name, last_name, email_token)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return array('success' => false, 'message' => $this->conn->error);
        }

        // Bind those parameters like your life depends on it (it kinda does)
        $stmt->bind_param('ssssss', $username, $email, $hashed_password, $first_name, $last_name, $email_token);

        if ($stmt->execute()) {
            // Send verification email (mock for now - replace with real email sending)
            $this->sendVerificationEmail($email, $email_token);
            return array('success' => true, 'message' => 'Registration successful! Check your email to verify.');
        } else {
            return array('success' => false, 'message' => $stmt->error);
        }
    }

    // Login user - authenticate and start a session
    public function login($email, $password) {
        // Find the user by email first
        $query = "SELECT id, username, email, password, role, is_verified FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password (compare hashes, don't compare plain text!)
            if (password_verify($password, $user['password'])) {
                if (!$user['is_verified']) {
                    return array('success' => false, 'message' => 'Please verify your email first');
                }

                // Password correct! Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Update last login timestamp (helpful for analytics)
                $this->updateLastLogin($user['id']);

                return array('success' => true, 'message' => 'Login successful!');
            } else {
                return array('success' => false, 'message' => 'Invalid credentials');
            }
        } else {
            return array('success' => false, 'message' => 'User not found');
        }
    }

    // Get user by ID - retrieve a user's data when we need it
    public function getUserById($id) {
        $query = "SELECT id, username, email, first_name, last_name, bio, profile_image, role, created_at FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Check if email exists - avoid duplicates like the plague
    public function emailExists($email) {
        $query = "SELECT id FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Check if username exists - usernames gotta be unique too
    public function usernameExists($username) {
        $query = "SELECT id FROM {$this->table} WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Update user profile - let users be themselves
    public function updateProfile($id, $first_name, $last_name, $bio) {
        $query = "UPDATE {$this->table} SET first_name = ?, last_name = ?, bio = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sssi', $first_name, $last_name, $bio, $id);
        return $stmt->execute();
    }

    // Send verification email - MOCK VERSION (use PHPMailer or similar in production!)
    private function sendVerificationEmail($email, $token) {
        // In a real app, you'd use PHPMailer or similar
        // For now, just log it
        error_log("Email verification link: http://localhost:8000/verify.php?token=$token");
        return true;
    }

    // Verify email using token - click the link in your email!
    public function verifyEmail($token) {
        $query = "UPDATE {$this->table} SET is_verified = TRUE, email_token = NULL WHERE email_token = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $token);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    // Update last login - track when users visited
    private function updateLastLogin($id) {
        $query = "UPDATE {$this->table} SET last_login = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    // Get all users - admin only
    public function getAllUsers($limit = 20, $offset = 0) {
        $query = "SELECT id, username, email, first_name, last_name, role, created_at FROM {$this->table} 
                  ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Count total users - for pagination math
    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->conn->query($query);
        return $result->fetch_assoc()['count'];
    }

    // Delete user - the nuclear option
    public function deleteUser($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
?>
