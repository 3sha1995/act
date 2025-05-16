<?php
require_once __DIR__ . '/../db_connection.php';

try {
    $pdo = getPDOConnection();
    
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // First, check if we can connect to the database
    echo "Database connection successful<br>";
    
    // Drop the existing table
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS `guidance_about`");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Dropped existing guidance_about table<br>";
    
    // Create the table with proper structure
    $sql = "CREATE TABLE `guidance_about` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ontop_title` varchar(255) NOT NULL DEFAULT 'ABOUT US',
        `main_title` varchar(255) NOT NULL DEFAULT 'GUIDANCE OFFICE',
        `image_path` varchar(255) DEFAULT '../imgs/cte.jpg',
        `description` text,
        `is_visible` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql);
    echo "Created guidance_about table<br>";
    
    // Verify table was created
    $result = $pdo->query("SHOW CREATE TABLE guidance_about");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    echo "<pre>Created table structure:\n" . $row['Create Table'] . "</pre><br>";
    
    // Insert default record
    $sql = "INSERT INTO `guidance_about` 
            (`ontop_title`, `main_title`, `description`, `is_visible`) 
            VALUES 
            ('ABOUT US', 'GUIDANCE OFFICE', 'Welcome to the Guidance Office section. Please update this content.', 1)";
    $pdo->exec($sql);
    echo "Inserted default record<br>";
    
    // Verify the record was inserted
    $result = $pdo->query("SELECT * FROM guidance_about WHERE id = 1");
    $record = $result->fetch(PDO::FETCH_ASSOC);
    echo "<pre>Inserted record:\n" . print_r($record, true) . "</pre>";
    
    echo "<br>Table recreated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "SQL State: " . $e->errorInfo[0] . "<br>";
    echo "Error Code: " . $e->errorInfo[1] . "<br>";
    echo "Message: " . $e->errorInfo[2] . "<br>";
    error_log("Database error in recreate_table.php: " . $e->getMessage());
}
?> 