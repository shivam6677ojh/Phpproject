<?php
class Database {
    private $host = "localhost";
    private $db_name = "career_explorer";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }

    public function createTables() {
        try {
            $conn = $this->getConnection();
            
            // Create careers table
            $conn->exec("CREATE TABLE IF NOT EXISTS careers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                industry VARCHAR(100) NOT NULL,
                education_level VARCHAR(100) NOT NULL,
                salary_min INT NOT NULL,
                salary_max INT NOT NULL,
                image_url VARCHAR(255),
                skills_required TEXT,
                job_outlook TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Create admin_users table
            $conn->exec("CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Create messages table
            $conn->exec("CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                status ENUM('new', 'read', 'replied') DEFAULT 'new',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Create default admin user if not exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO admin_users (username, password, email) VALUES ('admin', :password, 'admin@example.com')");
                $stmt->execute([':password' => $hashed_password]);
            }

            return true;
        } catch(PDOException $e) {
            echo "Error creating tables: " . $e->getMessage();
            return false;
        }
    }
}
?> 