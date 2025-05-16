<?php
require_once __DIR__ . '/../db_connection.php';

try {
    $pdo = getPDOConnection();
    
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Check if table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'guidance_about'")->rowCount() > 0;
    echo "Table exists: " . ($tableExists ? "Yes" : "No") . "<br>";
    
    if ($tableExists) {
        // Show table structure
        $result = $pdo->query("SHOW CREATE TABLE guidance_about");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        echo "<pre>Table structure:\n" . $row['Create Table'] . "</pre><br>";
        
        // Show table contents
        $result = $pdo->query("SELECT * FROM guidance_about");
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>Table contents:\n" . print_r($rows, true) . "</pre>";
    } else {
        echo "Table does not exist. Will create it now.<br>";
        
        // Create the table
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
        echo "Table created successfully.<br>";
        
        // Insert default record
        $sql = "INSERT INTO `guidance_about` 
                (`ontop_title`, `main_title`, `description`, `is_visible`) 
                VALUES 
                ('ABOUT US', 'GUIDANCE OFFICE', 'Welcome to the Guidance Office section. Please update this content.', 1)";
        $pdo->exec($sql);
        echo "Default record inserted.<br>";
        
        // Verify creation
        $result = $pdo->query("SHOW CREATE TABLE guidance_about");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        echo "<pre>Created table structure:\n" . $row['Create Table'] . "</pre>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    error_log("Database error in check_table.php: " . $e->getMessage());
}
?> 