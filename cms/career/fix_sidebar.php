<?php
/**
 * Student Affairs Sidebar Fix Script
 * 
 * This script adds the Student Affairs sidebar to all CMS pages
 * and updates their CSS to properly display with the sidebar.
 */

// List of all CMS pages to update
$pages = [
    'cms_af_page_contact.php',
    // We already fixed officer page manually
    // 'cms_af_page_officer.php',
    'cms_af_page_info.php',
    'cms_af_page_facilities.php',
    'cms_af_page_activities.php',
    'cms_af_page_process.php',
    'cms_af_page_obj_funct.php',
    'cms_mv_mission_vision.php'
];

// Counter for successful updates
$updated = 0;
$errors = [];

foreach ($pages as $page) {
    echo "Processing $page...\n";
    
    // Only process if file exists
    if (!file_exists($page)) {
        echo "  - File doesn't exist, skipping\n";
        $errors[] = "$page - File doesn't exist";
        continue;
    }
    
    try {
        // Read file content
        $content = file_get_contents($page);
        if ($content === false) {
            throw new Exception("Failed to read file");
        }
        
        // Skip if already has the sidebar
        if (strpos($content, "include 'student_affairs_sidebar.php'") !== false) {
            echo "  - Already includes sidebar, skipping\n";
            continue;
        }
        
        // Find the <body> tag, considering different formats like <body> or <body class="...">
        if (preg_match('/<body[^>]*>/', $content, $bodyTagMatches)) {
            $bodyTag = $bodyTagMatches[0];
            $bodyTagPos = strpos($content, $bodyTag);
            
            echo "  - Found body tag: $bodyTag\n";
            
            // Add margin-left to body style
            $bodyStylePattern = '/body\s*{([^}]*)}/';
            if (preg_match($bodyStylePattern, $content, $matches)) {
                $oldBodyStyle = $matches[0];
                $bodyStyleContent = $matches[1];
                
                // Check if margin-left is already set
                if (strpos($bodyStyleContent, 'margin-left') === false) {
                    $newBodyStyle = str_replace(
                        'body {',
                        'body {
            margin-left: 250px; /* Make room for the sidebar */
            transition: margin-left 0.3s ease;',
                        $oldBodyStyle
                    );
                    $content = str_replace($oldBodyStyle, $newBodyStyle, $content);
                }
            }
            
            // Add responsive styles if not already present
            if (strpos($content, '@media (max-width: 768px)') === false) {
                $styleEndPos = strpos($content, '</style>');
                if ($styleEndPos !== false) {
                    $responsiveStyles = '
        /* Mobile styles */
        @media (max-width: 768px) {
            body {
                margin-left: 0 !important;
            }
            body.sidebar-open {
                margin-left: 250px !important;
            }
        }
        ';
                    $content = substr_replace($content, $responsiveStyles . '</style>', $styleEndPos, strlen('</style>'));
                }
            }
            
            // Insert the sidebar include right after the body tag
            $insertPos = $bodyTagPos + strlen($bodyTag);
            $sidebarInclude = "\n<?php include 'student_affairs_sidebar.php'; ?>\n\n<div class=\"content-wrapper\" style=\"max-width: 1200px; margin: 20px auto; padding: 20px;\">\n";
            $content = substr_replace($content, $sidebarInclude, $insertPos, 0);
            
            // Find end of body and add closing div
            $closeBodyPos = strpos($content, '</body>');
            if ($closeBodyPos === false) {
                throw new Exception("Could not find </body> tag");
            }
            $content = substr_replace($content, "\n</div>\n", $closeBodyPos, 0);
            
            // Write modified content back to the file
            if (file_put_contents($page, $content) === false) {
                throw new Exception("Failed to write modified content");
            }
            
            echo "  - Successfully updated\n";
            $updated++;
        } else {
            throw new Exception("Could not find body tag");
        }
    } catch (Exception $e) {
        echo "  - Error: " . $e->getMessage() . "\n";
        $errors[] = "$page - " . $e->getMessage();
    }
}

echo "\nCompleted! Updated $updated out of " . count($pages) . " files.\n";

if (count($errors) > 0) {
    echo "\nThe following files had errors:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

echo "\nPlease test each page to ensure the sidebar is displaying correctly.\n"; 