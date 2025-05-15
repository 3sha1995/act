<?php
require_once __DIR__ . '/db_connection.php';

try {
    $pdo = getPDOConnection();
    
    // Drop old tables
    $sql = "DROP TABLE IF EXISTS af_page_process";
    $pdo->exec($sql);
    
    $sql = "DROP TABLE IF EXISTS af_page_process_settings";
    $pdo->exec($sql);
    
    $sql = "DROP TABLE IF EXISTS af_page_download_settings";
    $pdo->exec($sql);
    
    $sql = "DROP TABLE IF EXISTS af_page_download";
    $pdo->exec($sql);
    
    $sql = "DROP TABLE IF EXISTS af_pagec_info";
    $pdo->exec($sql);
    
    // Create the tables with the exact structure provided
    
    // Create process steps table
    $sql = "CREATE TABLE IF NOT EXISTS af_page_process_steps (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        visibility TINYINT(1) NOT NULL DEFAULT 1  -- 1 = visible, 0 = hidden
    )";
    $pdo->exec($sql);
    
    // Add section_title column to process steps table
    $sql = "ALTER TABLE af_page_process_steps 
            ADD COLUMN IF NOT EXISTS section_title VARCHAR(255) AFTER id";
    $pdo->exec($sql);
    
    // Create downloadable table
    $sql = "CREATE TABLE IF NOT EXISTS af_page_downloadable (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        file_path VARCHAR(255),
        visibility TINYINT(1) NOT NULL DEFAULT 1  -- 1 = visible, 0 = hidden
    )";
    $pdo->exec($sql);
    
    // Add section_title column to downloadable table
    $sql = "ALTER TABLE af_page_downloadable 
            ADD COLUMN IF NOT EXISTS section_title VARCHAR(255) AFTER id";
    $pdo->exec($sql);
    
    // Create info header table
    $sql = "CREATE TABLE IF NOT EXISTS af_pagec_info_header (
        id INT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        visibility TINYINT(1) NOT NULL DEFAULT 1  -- 1 = visible, 0 = hidden
    )";
    $pdo->exec($sql);
    
    // Insert default content into info header if it doesn't exist
    $sql = "INSERT IGNORE INTO af_pagec_info_header (id, title, description, visibility) 
            VALUES (1, 'Student Services Information', 'Learn about our services and access important resources.', 1)";
    $pdo->exec($sql);
    
    // Set default section titles if they are null
    $sql = "UPDATE af_page_process_steps SET section_title = 'How to Apply for Our Services' WHERE section_title IS NULL";
    $pdo->exec($sql);
    
    $sql = "UPDATE af_page_downloadable SET section_title = 'Downloadable Forms' WHERE section_title IS NULL";
    $pdo->exec($sql);
    
    echo "Database setup completed successfully! Old tables have been dropped and new tables have been created with the exact structure specified.<br>";
    echo "<a href='student_affairs/cms_af_page_info.php'>Go to CMS</a>";
    
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?> 