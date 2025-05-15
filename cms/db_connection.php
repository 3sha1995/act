<?php
require_once __DIR__ . '/config.php';

function getPDOConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME,
                DB_USERNAME,
                DB_PASSWORD,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch (PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check the configuration.");
        }
    }
    
    return $pdo;
}

// Function to check if tables exist and create them if they don't
function initializeDatabase() {
    try {
        $pdo = getPDOConnection();
        
        // Check if af_page table exists
        $tableExists = $pdo->query("SHOW TABLES LIKE 'af_page'")->rowCount() > 0;
        
        if (!$tableExists) {
            // Create af_page table
            $sql = "CREATE TABLE IF NOT EXISTS `af_page` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `ontop_title` varchar(255) NOT NULL DEFAULT 'ABOUT US',
                `main_title` varchar(255) NOT NULL DEFAULT 'STUDENT AFFAIRS',
                `image_path` varchar(255) DEFAULT '../imgs/cte.jpg',
                `description` text,
                `is_visible` tinyint(1) NOT NULL DEFAULT '1',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            $pdo->exec($sql);
            
            // Insert default record
            $sql = "INSERT INTO `af_page` 
                   (`ontop_title`, `main_title`, `description`, `is_visible`) 
                   VALUES 
                   ('ABOUT US', 'STUDENT AFFAIRS', 'Welcome to the Student Affairs section. Please update this content.', 1)";
            $pdo->exec($sql);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Database initialization failed: " . $e->getMessage());
        return false;
    }
}

// Initialize the database when this file is included
initializeDatabase();
?>
