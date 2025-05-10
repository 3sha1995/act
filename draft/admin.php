<?php
// File: /cms/admin.php

require_once 'PageContent.php';

$pageContent = new PageContent();
$entries = $pageContent->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Page Content</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        a.button { padding: 5px 10px; background-color: #007BFF; color: #fff; text-decoration: none; border-radius: 3px; }
        a.button.delete { background-color: #dc3545; }
    </style>
</head>
<body>

<h1>Manage Page Content</h1>

<a href="add_edit_form.php" class="button">+ Add New</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Page Name</th>
            <th>Section Name</th>
            <th>Title</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($entries as $entry): ?>
        <tr>
            <td><?php echo htmlspecialchars($entry['id']); ?></td>
            <td><?php echo htmlspecialchars($entry['page_name']); ?></td>
            <td><?php echo htmlspecialchars($entry['section_name']); ?></td>
            <td><?php echo htmlspecialchars($entry['title']); ?></td>
            <td>
                <a href="add_edit_form.php?id=<?php echo $entry['id']; ?>" class="button">Edit</a>
                <a href="#" class="button delete" onclick="deleteEntry(<?php echo $entry['id']; ?>)">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
function deleteEntry(id) {
    if (confirm('Are you sure you want to delete this entry?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax/delete_content.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.status == 200) {
                alert('Deleted successfully!');
                location.reload();
            } else {
                alert('Delete failed!');
            }
        };
        xhr.send('id=' + id);
    }
}
</script>

</body>
</html>
