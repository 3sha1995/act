<?php
/**
 * Student Affairs Sidebar Persistence Script Fixer
 * 
 * This script adds the student_affairs_persistent.js reference to all CMS pages
 * in the student_affairs directory to ensure consistent sidebar visibility
 */

// List of pages to update
$pages = [
    'cms_af_page_contact.php',
    'cms_af_page_officer.php',
    'cms_af_page_info.php',
    'cms_af_page_facilities.php',
    'cms_af_page_activities.php',
    'cms_af_page_process.php',
    'cms_af_page_obj_funct.php',
    'cms_af_page_services.php',
    'cms_mv_mission_vision.php'
];

$updated = 0;
$errors = [];

foreach ($pages as $page) {
    echo "Processing $page...\n";
    
    // Skip if file doesn't exist
    if (!file_exists($page)) {
        echo "  - File doesn't exist, skipping\n";
        $errors[] = "$page - File not found";
        continue;
    }
    
    // Read file content
    $content = file_get_contents($page);
    if ($content === false) {
        echo "  - Failed to read file, skipping\n";
        $errors[] = "$page - Failed to read file";
        continue;
    }
    
    // Check if it already includes the persistence script
    if (strpos($content, 'student_affairs_persistent.js') !== false) {
        echo "  - Already includes persistent.js, skipping\n";
        continue;
    }
    
    // Find the closing body tag
    $bodyEnd = strpos($content, '</body>');
    if ($bodyEnd === false) {
        echo "  - Couldn't find </body> tag, skipping\n";
        $errors[] = "$page - No </body> tag found";
        continue;
    }
    
    // Add the script tag before the closing body tag
    $scriptTag = "\n    <!-- Include the sidebar persistence script -->\n    <script src=\"student_affairs_persistent.js\"></script>\n";
    
    // If the page already has student_affairs_sidebar.js, add the persistent script right after it
    $sidebarJs = strpos($content, 'student_affairs_sidebar.js');
    if ($sidebarJs !== false) {
        // Find the end of that script tag
        $sidebarJsEnd = strpos($content, '</script>', $sidebarJs);
        if ($sidebarJsEnd !== false) {
            $insertPos = $sidebarJsEnd + 9; // Length of </script>
            $content = substr_replace($content, $scriptTag, $insertPos, 0);
        } else {
            // If we can't find the end of the sidebar script tag, just add it before </body>
            $content = substr_replace($content, $scriptTag, $bodyEnd, 0);
        }
    } else {
        // No sidebar.js found, add it before </body>
        $content = substr_replace($content, $scriptTag, $bodyEnd, 0);
    }
    
    // Write the updated content back to the file
    if (file_put_contents($page, $content) === false) {
        echo "  - Failed to write updated file\n";
        $errors[] = "$page - Failed to write updated file";
        continue;
    }
    
    echo "  - Successfully added persistent.js script\n";
    $updated++;
}

echo "\nCompleted! Added persistence script to $updated out of " . count($pages) . " files.\n";

if (count($errors) > 0) {
    echo "\nErrors encountered:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

echo "\nPlease test all pages to ensure the sidebar stays visible throughout navigation.\n";
?> 