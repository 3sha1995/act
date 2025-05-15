<?php
require_once __DIR__ . '/../db_connection.php';

try {
    $pdo = getPDOConnection();
    echo "Database connection successful!<br>";

    // Test MV table
    $stmt = $pdo->query("SHOW TABLES LIKE 'af_page_mv'");
    if ($stmt->rowCount() > 0) {
        echo "af_page_mv table exists<br>";
        // Show table structure
        $stmt = $pdo->query("DESCRIBE af_page_mv");
        echo "<pre>af_page_mv structure:\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
    } else {
        echo "Creating af_page_mv table...<br>";
        $sql = "CREATE TABLE af_page_mv (
            id INT PRIMARY KEY AUTO_INCREMENT,
            section_title VARCHAR(255) NOT NULL,
            is_visible TINYINT(1) DEFAULT 1
        )";
        $pdo->exec($sql);
        echo "af_page_mv table created!<br>";
    }

    // Test Mission table
    $stmt = $pdo->query("SHOW TABLES LIKE 'af_page_mission'");
    if ($stmt->rowCount() > 0) {
        echo "af_page_mission table exists<br>";
        // Show table structure
        $stmt = $pdo->query("DESCRIBE af_page_mission");
        echo "<pre>af_page_mission structure:\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
    } else {
        echo "Creating af_page_mission table...<br>";
        $sql = "CREATE TABLE af_page_mission (
            id INT PRIMARY KEY AUTO_INCREMENT,
            section_title VARCHAR(255) NOT NULL,
            image_url VARCHAR(255),
            description TEXT,
            show_more_text VARCHAR(255)
        )";
        $pdo->exec($sql);
        echo "af_page_mission table created!<br>";
    }

    // Test Vision table
    $stmt = $pdo->query("SHOW TABLES LIKE 'af_page_vision'");
    if ($stmt->rowCount() > 0) {
        echo "af_page_vision table exists<br>";
        // Show table structure
        $stmt = $pdo->query("DESCRIBE af_page_vision");
        echo "<pre>af_page_vision structure:\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
    } else {
        echo "Creating af_page_vision table...<br>";
        $sql = "CREATE TABLE af_page_vision (
            id INT PRIMARY KEY AUTO_INCREMENT,
            section_title VARCHAR(255) NOT NULL,
            image_url VARCHAR(255),
            description TEXT,
            show_more_text VARCHAR(255)
        )";
        $pdo->exec($sql);
        echo "af_page_vision table created!<br>";
    }

    // Show current data
    echo "<h3>Current Data:</h3>";
    
    echo "<h4>MV Data:</h4>";
    $stmt = $pdo->query("SELECT * FROM af_page_mv");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "<h4>Mission Data:</h4>";
    $stmt = $pdo->query("SELECT * FROM af_page_mission");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "<h4>Vision Data:</h4>";
    $stmt = $pdo->query("SELECT * FROM af_page_vision");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 