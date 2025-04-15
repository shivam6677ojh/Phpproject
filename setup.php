<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";

try {
    // Connect to MySQL without selecting a database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read and execute the schema file
    $sql = file_get_contents('database/schema.sql');
    $pdo->exec($sql);

    echo "Database and tables created successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 