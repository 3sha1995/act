<?php
// Database connection
require_once 'cms/db_connection.php';

// Script to fix health_services table and insert test data
try {
    $pdo = getPDOConnection();
    echo "<h1>Health Services Table Fix</h1>";
    
    // First check if table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'health_services'")->fetchAll();
    if (count($tables) == 0) {
        echo "<p>Table health_services does not exist. Creating it...</p>";
        
        // Create the table
        $pdo->exec("CREATE TABLE `health_services` (
            `id` int(11) NOT NULL,
            `section_title` varchar(255) NOT NULL DEFAULT 'Our Services',
            `section_description` text NOT NULL DEFAULT 'We provide a range of healthcare services to support the well-being of our university community.',
            `icon_class` varchar(255) NOT NULL,
            `service_title` varchar(255) NOT NULL,
            `service_description` text NOT NULL,
            `is_visible` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        
        // Set primary key and auto increment
        $pdo->exec("ALTER TABLE `health_services` ADD PRIMARY KEY (`id`)");
        $pdo->exec("ALTER TABLE `health_services` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");
        
        echo "<p>Table created successfully.</p>";
    } else {
        echo "<p>Table health_services already exists.</p>";
    }
    
    // Insert test data
    echo "<h2>Adding Test Services</h2>";
    
    // Check if data already exists
    $count = $pdo->query("SELECT COUNT(*) FROM health_services")->fetchColumn();
    echo "<p>Current number of services: $count</p>";
    
    if ($count == 0) {
        // Insert sample services
        $services = [
            [
                'section_title' => 'Our Healthcare Services',
                'section_description' => 'WMSU Health Services provides comprehensive healthcare support for students and staff.',
                'icon_class' => 'fas fa-heartbeat',
                'service_title' => 'Medical Consultation',
                'service_description' => 'Professional medical consultation with our qualified healthcare providers.',
                'is_visible' => 1
            ],
            [
                'section_title' => 'Our Healthcare Services',
                'section_description' => 'WMSU Health Services provides comprehensive healthcare support for students and staff.',
                'icon_class' => 'fas fa-pills',
                'service_title' => 'Pharmacy Services',
                'service_description' => 'Access to essential medications and pharmaceutical advice.',
                'is_visible' => 1
            ],
            [
                'section_title' => 'Our Healthcare Services',
                'section_description' => 'WMSU Health Services provides comprehensive healthcare support for students and staff.',
                'icon_class' => 'fas fa-user-md',
                'service_title' => 'Specialized Care',
                'service_description' => 'Specialized healthcare services for various medical needs.',
                'is_visible' => 1
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO health_services (section_title, section_description, icon_class, service_title, service_description, is_visible)
                              VALUES (:section_title, :section_description, :icon_class, :service_title, :service_description, :is_visible)");
        
        foreach ($services as $service) {
            $stmt->execute($service);
            echo "<p>Added service: {$service['service_title']}</p>";
        }
        
        echo "<p>Test data inserted successfully.</p>";
    } else {
        echo "<p>Table already has data. Skipping test data insertion.</p>";
    }
    
    // Display current data
    echo "<h2>Current Data in Table</h2>";
    $data = $pdo->query("SELECT * FROM health_services")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    
    echo "<p>Done! Now check your health page to see if services are displayed.</p>";
    echo "<p><a href='page/health.php'>Go to Health Services Page</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 