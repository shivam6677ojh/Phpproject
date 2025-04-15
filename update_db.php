<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host;dbname=career_explorer", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Update admin_users table
    $sql = "ALTER TABLE admin_users CHANGE password password_hash VARCHAR(255) NOT NULL";
    $pdo->exec($sql);

    echo "Database updated successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 