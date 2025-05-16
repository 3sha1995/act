<?php
// Database connection
require_once 'cms/db_connection.php';

// Debug script to check database content
try {
    $pdo = getPDOConnection();
    echo "<h1>Database Connection Test</h1>";
    echo "<p>Connection successful!</p>";
    
    // Check if table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'health_services'")->fetchAll();
    echo "<h2>Table Check</h2>";
    if (count($tables) > 0) {
        echo "<p>Table health_services exists.</p>";
    } else {
        echo "<p>Table health_services does NOT exist!</p>";
    }
    
    // Check table structure
    echo "<h2>Table Structure</h2>";
    $columns = $pdo->query("SHOW COLUMNS FROM health_services")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Check content
    echo "<h2>Table Content</h2>";
    $data = $pdo->query("SELECT * FROM health_services")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<h1>Database Error</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 