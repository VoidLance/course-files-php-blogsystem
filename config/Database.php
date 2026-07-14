<?php
// Database configuration - where all the magic happens (and occasionally breaks)
class Database {
    private $host = 'localhost';
    private $db_name = 'blog_system';
    private $db_user = 'blog_user';
    private $db_pass = 'BlogPass2024';
    private $conn;

    // Fire up that database connection - connect() to the database or connect() to your feelings
    public function connect() {
        if (!extension_loaded('mysqli')) {
            throw new RuntimeException(
                'PHP extension "mysqli" is not enabled. Enable mysqli (and usually pdo_mysql) in your php.ini, then restart your local PHP server.'
            );
        }

        $this->conn = new mysqli(
            $this->host,
            $this->db_user,
            $this->db_pass,
            $this->db_name
        );

        // Scream if something went wrong
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        // Set UTF8 encoding so emojis don't become question marks (sad question marks)
        $this->conn->set_charset("utf8");

        return $this->conn;
    }

    // Get the connection object because we need it everywhere
    public function getConnection() {
        return $this->conn;
    }

    // Close the connection like a gentleman
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
