<?php
// File: /cms/ajax/save_content.php

require_once '../PageContent.php';
require_once '../helpers/TemplateGenerator.php';

$pageContent = new PageContent();

// Collect POST data
$data = [
    'page_name' => $_POST['page_name'] ?? '',
    'section_name' => $_POST['section_name'] ?? '',
    'title' => $_POST['title'] ?? '',
    'subtitle' => $_POST['subtitle'] ?? '',
    'image_path' => $_POST['image_path'] ?? '',
    'icon_class' => $_POST['icon_class'] ?? '',
    'description' => $_POST['description'] ?? '',
    'extra_description' => $_POST['extra_description'] ?? '',
    'officer_name' => $_POST['officer_name'] ?? '',
    'officer_position' => $_POST['officer_position'] ?? '',
    'clinic_process_steps' => $_POST['clinic_process_steps'] ?? '',
    'clinic_downloadable_forms' => $_POST['clinic_downloadable_forms'] ?? '',
    'contact_phone' => $_POST['contact_phone'] ?? '',
    'contact_email' => $_POST['contact_email'] ?? '',
    'contact_location' => $_POST['contact_location'] ?? '',
    'facebook_link' => $_POST['facebook_link'] ?? '',
];

// Save entry
if (!empty($_POST['id'])) {
    $pageContent->update($_POST['id'], $data);
} else {
    $pageContent->create($data);
}

// Regenerate template_views.php after saving
TemplateGenerator::regenerate();

echo 'success';
