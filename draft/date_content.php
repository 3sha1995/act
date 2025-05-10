<?php
// File: /cms/ajax/delete_content.php

require_once '../PageContent.php';

$pageContent = new PageContent();

// Delete
if (!empty($_POST['id'])) {
    $pageContent->delete($_POST['id']);
    echo 'success';
} else {
    http_response_code(400);
    echo 'ID required';
}
