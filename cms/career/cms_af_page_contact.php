<?php
require_once '../db_connection.php';

try {
    $pdo = getPDOConnection();

    // Create settings table for section configuration
    $createSettingsTableSQL = "CREATE TABLE IF NOT EXISTS `af_page_contact_settings` (
        `id` INT PRIMARY KEY DEFAULT 1,
        `section_title` VARCHAR(255) NOT NULL DEFAULT 'Get in Touch',
        `section_visible` TINYINT(1) NOT NULL DEFAULT 1,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($createSettingsTableSQL);

    // Create contacts table
    $createContactsTableSQL = "CREATE TABLE IF NOT EXISTS `af_page_contact` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `contact_type` ENUM('phone', 'email', 'location', 'facebook') NOT NULL,
        `label` VARCHAR(100) NOT NULL,
        `value` VARCHAR(255) NOT NULL,
        `display_text` VARCHAR(255),
        `icon_path` VARCHAR(255) NOT NULL,
        `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_contact_type` (`contact_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($createContactsTableSQL);

    // Insert default settings if not exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM af_page_contact_settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO af_page_contact_settings (section_title, section_visible) VALUES ('Get in Touch', 1)");
    }

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}

class ContactCMS {
    private $pdo;
    private $upload_dir;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->upload_dir = '../../uploads/icons/';
        
        // Create uploads directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    // Get section settings
    public function getSectionSettings() {
        try {
            $stmt = $this->pdo->query("SELECT section_title, section_visible FROM af_page_contact_settings WHERE id = 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: ['section_title' => 'Get in Touch', 'section_visible' => 1];
        } catch (PDOException $e) {
            error_log("Error getting section settings: " . $e->getMessage());
            return ['section_title' => 'Get in Touch', 'section_visible' => 1];
        }
    }

    // Update section settings
    public function updateSectionSettings($title, $visible) {
        try {
            $stmt = $this->pdo->prepare("UPDATE af_page_contact_settings SET section_title = ?, section_visible = ? WHERE id = 1");
            return $stmt->execute([$title, $visible]);
        } catch (PDOException $e) {
            error_log("Error updating section settings: " . $e->getMessage());
            return false;
        }
    }

    // Fetch all contact entries
    public function getAllContacts() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_page_contact ORDER BY contact_type ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting contacts: " . $e->getMessage());
            return [];
        }
    }

    // Add or update contact entry
    public function addOrUpdateContact($contact_type, $label, $value, $display_text, $icon_path, $is_visible) {
        try {
            // Check if entry exists for this contact type
            $stmt = $this->pdo->prepare("SELECT id, icon_path FROM af_page_contact WHERE contact_type = ?");
            $stmt->execute([$contact_type]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing entry
                // If no new icon was uploaded, keep the existing one
                if (empty($icon_path)) {
                    $icon_path = $existing['icon_path'];
                }
                
                $stmt = $this->pdo->prepare("UPDATE af_page_contact SET label = ?, value = ?, display_text = ?, icon_path = ?, is_visible = ? WHERE contact_type = ?");
                return $stmt->execute([$label, $value, $display_text, $icon_path, $is_visible, $contact_type]);
            } else {
                // Insert new entry
                $stmt = $this->pdo->prepare("INSERT INTO af_page_contact (contact_type, label, value, display_text, icon_path, is_visible) VALUES (?, ?, ?, ?, ?, ?)");
                return $stmt->execute([$contact_type, $label, $value, $display_text, $icon_path, $is_visible]);
            }
        } catch (PDOException $e) {
            error_log("Error in addOrUpdateContact: " . $e->getMessage());
            return false;
        }
    }

    // Process icon upload
    public function processIconUpload($file) {
        if (empty($file['name'])) {
            return '';
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'svg');
        
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception("Invalid file type. Allowed types: jpg, jpeg, png, gif, svg");
        }
        
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $this->upload_dir . $new_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception("Failed to upload file: " . error_get_last()['message']);
        }
        
        return 'uploads/icons/' . $new_filename;
    }

    // Delete contact entry
    public function deleteContact($id) {
        try {
            // Get the icon path to delete the file
            $stmt = $this->pdo->prepare("SELECT icon_path FROM af_page_contact WHERE id = ?");
            $stmt->execute([$id]);
            $contact = $stmt->fetch();
            
            // Delete the contact from database
            $stmt = $this->pdo->prepare("DELETE FROM af_page_contact WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            // Delete the icon file if exists
            if ($result && $contact && !empty($contact['icon_path'])) {
                $filepath = '../../' . $contact['icon_path'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error deleting contact: " . $e->getMessage());
            return false;
        }
    }

    // Get contact types that don't have entries yet
    public function getAvailableContactTypes() {
        try {
            $stmt = $this->pdo->query("SELECT contact_type FROM af_page_contact");
            $existingTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $allTypes = ['phone', 'email', 'location', 'facebook'];
            return array_diff($allTypes, $existingTypes);
        } catch (PDOException $e) {
            error_log("Error getting available contact types: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize
$cms = new ContactCMS($pdo);
$message = '';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_section'])) {
        $result = $cms->updateSectionSettings(
            $_POST['section_title'],
            isset($_POST['section_visible']) ? 1 : 0
        );
        $message = $result ? 
            '<div style="color: green; margin: 10px 0;">Section settings updated successfully!</div>' : 
            '<div style="color: red; margin: 10px 0;">Error updating section settings.</div>';
    }
    elseif (isset($_POST['add']) || isset($_POST['update'])) {
        try {
            // Process the icon upload
            $icon_path = '';
            if (!empty($_FILES['icon_file']['name'])) {
                $icon_path = $cms->processIconUpload($_FILES['icon_file']);
            } else if (isset($_POST['existing_icon'])) {
                $icon_path = $_POST['existing_icon'];
            }
            
            $result = $cms->addOrUpdateContact(
                $_POST['contact_type'],
                $_POST['label'],
                $_POST['value'],
                $_POST['contact_type'] === 'facebook' ? $_POST['display_text'] : null,
                $icon_path,
                isset($_POST['is_visible']) ? 1 : 0
            );
            $message = $result ? 
                '<div style="color: green; margin: 10px 0;">Contact information updated successfully!</div>' : 
                '<div style="color: red; margin: 10px 0;">Error updating contact information.</div>';
        } catch (Exception $e) {
            $message = '<div style="color: red; margin: 10px 0;">Error: ' . $e->getMessage() . '</div>';
        }
    } elseif (isset($_POST['delete'])) {
        $result = $cms->deleteContact($_POST['id']);
        $message = $result ? 
            '<div style="color: green; margin: 10px 0;">Contact deleted successfully!</div>' : 
            '<div style="color: red; margin: 10px 0;">Error deleting contact.</div>';
    }
}

$contacts = $cms->getAllContacts();
$availableTypes = $cms->getAvailableContactTypes();
$sectionSettings = $cms->getSectionSettings();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact CMS</title>
    <link rel="stylesheet" href="student_affairs_sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin-left: 250px;
            padding: 0 20px;
            transition: all 0.3s ease;
            background-color: #f0f4f8;
            color: #1a365d;
            line-height: 1.6;
        }

        .content-wrapper {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.1);
        }

        @media (max-width: 768px) {
            body {
                margin-left: 0;
                padding: 0 15px;
            }
            body.sidebar-open {
                margin-left: 250px;
            }
        }

        .form-container {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.95rem;
        }

        input[type="text"], select {
            width: 100%;
            padding: 12px;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        input[type="text"]:focus, select:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            background: #fff;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            cursor: pointer;
        }

        input[type="file"]:hover {
            border-color: #93c5fd;
        }

        .image-preview {
            max-width: 100px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }

        .current-icon {
            margin: 15px 0;
            padding: 15px;
            background: #ebf8ff;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .current-icon img {
            max-width: 50px;
            border-radius: 4px;
            border: 1px solid #bee3f8;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 25px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }

        th, td {
            padding: 16px;
            border: 1px solid #bee3f8;
            text-align: left;
        }

        td img {
            max-width: 40px;
            border-radius: 4px;
        }

        th {
            background: #ebf8ff;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f0f5ff;
        }

        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .button-primary {
            background: #3182ce;
            color: white;
        }

        .button-danger {
            background: #e53e3e;
            color: white;
        }

        .button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.13);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(26, 54, 93, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: #ffffff;
            margin: 5% auto;
            padding: 30px;
            border: none;
            width: 90%;
            max-width: 600px;
            border-radius: 16px;
            position: relative;
            box-shadow: 0 10px 25px rgba(49, 130, 206, 0.13);
            border: 1px solid #bee3f8;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: normal;
            cursor: pointer;
            color: #4a5568;
            transition: color 0.2s ease, background 0.2s;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close:hover {
            color: #2c5282;
            background-color: #ebf8ff;
        }

        .section-settings {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid #bee3f8;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e0;
            transition: .3s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
        }

        input:checked + .slider {
            background-color: #3182ce;
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #3182ce;
        }
    </style>
</head>
<body>

<?php include 'student_affairs_sidebar.php'; ?>

<div class="content-wrapper">
    <h2>Contact Information Management</h2>
    <?= $message ?>

    <!-- Section Settings -->
    <div class="section-settings">
        <h3>Section Settings</h3>
        <form method="POST" class="form-container">
            <div class="form-group">
                <label>Section Title:</label>
                <input type="text" name="section_title" value="<?= htmlspecialchars($sectionSettings['section_title']) ?>" required>
            </div>
            <div class="form-group">
                <label class="switch-label">
                    Section Visibility:
                    <label class="switch">
                        <input type="checkbox" name="section_visible" <?= $sectionSettings['section_visible'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>
            <button type="submit" name="update_section" class="button button-primary">Update Section Settings</button>
        </form>
    </div>

    <!-- Add Contact Button -->
    <div style="margin: 20px 0;">
        <button onclick="openAddModal()" class="button button-primary">
            <i class="fas fa-plus"></i> Add New Contact
        </button>
    </div>

    <!-- Add Contact Modal -->
    <div id="addContactModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h3>Add New Contact Information</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Contact Type:</label>
                    <select name="contact_type" required onchange="toggleDisplayText(this.value)">
                        <option value="">Select Type</option>
                        <?php foreach ($availableTypes as $type): ?>
                        <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Label:</label>
                    <input type="text" name="label" required placeholder="e.g., Phone Number, Email Address">
                </div>

                <div class="form-group">
                    <label>Value:</label>
                    <input type="text" name="value" required placeholder="Enter the actual contact information">
                </div>

                <div class="form-group display-text-group" id="displayTextGroup">
                    <label>Display Text (for Facebook):</label>
                    <input type="text" name="display_text" placeholder="e.g., Follow us on Facebook">
                </div>

                <div class="form-group">
                    <label>Icon:</label>
                    <input type="file" name="icon_file" accept="image/*" onchange="previewImage(this, 'add-icon-preview')" required>
                    <img id="add-icon-preview" class="image-preview" style="display: none;" alt="Icon Preview">
                    <p class="icon-help">Upload an icon image for this contact type (JPG, PNG, GIF, SVG)</p>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_visible" value="1" checked>
                        Visible on website
                    </label>
                </div>

                <button type="submit" name="add" class="button button-primary">Add Contact</button>
            </form>
        </div>
    </div>

    <!-- Edit Contact Modal -->
    <div id="editContactModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Edit Contact Information</h3>
            <form method="POST" id="editContactForm" enctype="multipart/form-data">
                <input type="hidden" name="contact_type" id="edit_contact_type">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="existing_icon" id="edit_existing_icon">
                
                <div class="form-group">
                    <label>Contact Type:</label>
                    <input type="text" id="edit_contact_type_display" disabled>
                </div>

                <div class="form-group">
                    <label>Label:</label>
                    <input type="text" name="label" id="edit_label" required placeholder="e.g., Phone Number, Email Address">
                </div>

                <div class="form-group">
                    <label>Value:</label>
                    <input type="text" name="value" id="edit_value" required placeholder="Enter the actual contact information">
                </div>

                <div class="form-group" id="edit_display_text_group">
                    <label>Display Text (for Facebook):</label>
                    <input type="text" name="display_text" id="edit_display_text" placeholder="e.g., Follow us on Facebook">
                </div>

                <div class="form-group">
                    <label>Current Icon:</label>
                    <div class="current-icon" id="current-icon-container">
                        <img id="current-icon-image" src="" alt="Current Icon">
                        <span id="current-icon-path"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Upload New Icon (optional):</label>
                    <input type="file" name="icon_file" accept="image/*" onchange="previewImage(this, 'edit-icon-preview')">
                    <img id="edit-icon-preview" class="image-preview" style="display: none;" alt="Icon Preview">
                    <p class="icon-help">Leave empty to keep current icon</p>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_visible" id="edit_is_visible" value="1">
                        Visible on website
                    </label>
                </div>

                <button type="submit" name="update" class="button button-primary">Update Contact</button>
            </form>
        </div>
    </div>

    <!-- Contacts Table -->
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Label</th>
                <th>Value</th>
                <th>Display Text</th>
                <th>Icon</th>
                <th>Visible</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contacts as $contact): ?>
            <tr>
                <td><?= ucfirst($contact['contact_type']) ?></td>
                <td><?= htmlspecialchars($contact['label']) ?></td>
                <td><?= htmlspecialchars($contact['value']) ?></td>
                <td>
                    <?php if ($contact['contact_type'] === 'facebook'): ?>
                        <?= htmlspecialchars($contact['display_text']) ?>
                    <?php else: ?>
                        <em>N/A</em>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($contact['icon_path'])): ?>
                        <img src="../../<?= htmlspecialchars($contact['icon_path']) ?>" alt="<?= ucfirst($contact['contact_type']) ?> Icon">
                    <?php else: ?>
                        <em>No icon</em>
                    <?php endif; ?>
                </td>
                <td><?= $contact['is_visible'] ? 'Yes' : 'No' ?></td>
                <td>
                    <button type="button" class="button button-primary" 
                            onclick="openEditModal(<?= htmlspecialchars(json_encode($contact)) ?>)">
                        Edit
                    </button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                        <button type="submit" name="delete" class="button button-danger" 
                                onclick="return confirm('Are you sure you want to delete this contact information?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openAddModal() {
            document.getElementById('addContactModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addContactModal').style.display = 'none';
        }

        function openEditModal(contact) {
            // Fill the edit form with contact data
            document.getElementById('edit_id').value = contact.id;
            document.getElementById('edit_contact_type').value = contact.contact_type;
            document.getElementById('edit_contact_type_display').value = contact.contact_type.charAt(0).toUpperCase() + contact.contact_type.slice(1);
            document.getElementById('edit_label').value = contact.label;
            document.getElementById('edit_value').value = contact.value;
            document.getElementById('edit_is_visible').checked = contact.is_visible == 1;
            document.getElementById('edit_existing_icon').value = contact.icon_path;

            // Display current icon
            const currentIconContainer = document.getElementById('current-icon-container');
            const currentIconImage = document.getElementById('current-icon-image');
            const currentIconPath = document.getElementById('current-icon-path');
            
            if (contact.icon_path) {
                currentIconImage.src = '../../' + contact.icon_path;
                currentIconPath.textContent = contact.icon_path;
                currentIconContainer.style.display = 'flex';
            } else {
                currentIconContainer.style.display = 'none';
            }

            // Handle display text field for Facebook
            const displayTextGroup = document.getElementById('edit_display_text_group');
            const displayTextInput = document.getElementById('edit_display_text');
            if (contact.contact_type === 'facebook') {
                displayTextGroup.style.display = 'block';
                displayTextInput.value = contact.display_text || '';
            } else {
                displayTextGroup.style.display = 'none';
                displayTextInput.value = '';
            }

            // Show the modal
            document.getElementById('editContactModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editContactModal').style.display = 'none';
        }

        function toggleDisplayText(contactType) {
            const displayTextGroup = document.getElementById('displayTextGroup');
            if (contactType === 'facebook') {
                displayTextGroup.style.display = 'block';
            } else {
                displayTextGroup.style.display = 'none';
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('addContactModal')) {
                closeAddModal();
            }
            if (event.target == document.getElementById('editContactModal')) {
                closeEditModal();
            }
        }

        // Initialize display text visibility
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('displayTextGroup').style.display = 'none';
        });
    </script>
    
    <!-- Include the sidebar persistence script -->
    <script src="student_affairs_sidebar.js"></script>
</div>
</body>
</html>
