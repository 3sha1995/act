<?php
// File: /cms/ajax/delete_content.php

require_once '../PageContent.php';
require_once '../helpers/TemplateGenerator.php';

$pageContent = new PageContent();

// Delete entry
if (!empty($_POST['id'])) {
    $pageContent->delete($_POST['id']);
    // Regenerate template_views.php after delete
    TemplateGenerator::regenerate();
    echo 'success';
} else {
    http_response_code(400);
    echo 'ID required';
}
