<?php
// File: /cms/add_edit_form.php

require_once 'PageContent.php';

$pageContent = new PageContent();

// Check if editing existing entry
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$entry = $id ? $pageContent->getById($id) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $id ? 'Edit' : 'Add'; ?> Page Content</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 600px; }
        label { display: block; margin-top: 10px; }
        input[type="text"], textarea { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 15px; padding: 10px 15px; background-color: #007BFF; color: #fff; border: none; border-radius: 3px; cursor: pointer; }
        a { display: inline-block; margin-top: 15px; }
    </style>
</head>
<body>

<h1><?php echo $id ? 'Edit' : 'Add'; ?> Page Content</h1>

<form id="contentForm">
    <input type="hidden" name="id" value="<?php echo $id; ?>">

    <label>Page Name:</label>
    <input type="text" name="page_name" value="<?php echo htmlspecialchars($entry['page_name'] ?? ''); ?>" required>

    <label>Section Name:</label>
    <input type="text" name="section_name" value="<?php echo htmlspecialchars($entry['section_name'] ?? ''); ?>" required>

    <label>Title:</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($entry['title'] ?? ''); ?>">

    <label>Subtitle:</label>
    <input type="text" name="subtitle" value="<?php echo htmlspecialchars($entry['subtitle'] ?? ''); ?>">

    <label>Image Path:</label>
    <input type="text" name="image_path" value="<?php echo htmlspecialchars($entry['image_path'] ?? ''); ?>">

    <label>Icon Class:</label>
    <input type="text" name="icon_class" value="<?php echo htmlspecialchars($entry['icon_class'] ?? ''); ?>">

    <label>Description:</label>
    <textarea name="description"><?php echo htmlspecialchars($entry['description'] ?? ''); ?></textarea>

    <label>Extra Description:</label>
    <textarea name="extra_description"><?php echo htmlspecialchars($entry['extra_description'] ?? ''); ?></textarea>

    <label>Officer Name:</label>
    <input type="text" name="officer_name" value="<?php echo htmlspecialchars($entry['officer_name'] ?? ''); ?>">

    <label>Officer Position:</label>
    <input type="text" name="officer_position" value="<?php echo htmlspecialchars($entry['officer_position'] ?? ''); ?>">

    <label>Clinic Process Steps (JSON):</label>
    <textarea name="clinic_process_steps"><?php echo htmlspecialchars($entry['clinic_process_steps'] ?? ''); ?></textarea>

    <label>Clinic Downloadable Forms (JSON):</label>
    <textarea name="clinic_downloadable_forms"><?php echo htmlspecialchars($entry['clinic_downloadable_forms'] ?? ''); ?></textarea>

    <label>Contact Phone:</label>
    <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($entry['contact_phone'] ?? ''); ?>">

    <label>Contact Email:</label>
    <input type="text" name="contact_email" value="<?php echo htmlspecialchars($entry['contact_email'] ?? ''); ?>">

    <label>Contact Location:</label>
    <input type="text" name="contact_location" value="<?php echo htmlspecialchars($entry['contact_location'] ?? ''); ?>">

    <label>Facebook Link:</label>
    <input type="text" name="facebook_link" value="<?php echo htmlspecialchars($entry['facebook_link'] ?? ''); ?>">

    <button type="submit">Save</button>
</form>

<a href="admin.php">&laquo; Back to List</a>

<script>
document.getElementById('contentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax/save_content.php', true);
    xhr.onload = function() {
        if (this.status == 200) {
            alert('Saved successfully!');
            window.location.href = 'admin.php';
        } else {
            alert('Save failed!');
        }
    };
    xhr.send(formData);
});
</script>

</body>
</html>
