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

    public function __construct($pdo) {
        $this->pdo = $pdo;
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
            $stmt = $this->pdo->prepare("SELECT id FROM af_page_contact WHERE contact_type = ?");
            $stmt->execute([$contact_type]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing entry
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

    // Delete contact entry
    public function deleteContact($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM af_page_contact WHERE id = ?");
            return $stmt->execute([$id]);
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
        $result = $cms->addOrUpdateContact(
            $_POST['contact_type'],
            $_POST['label'],
            $_POST['value'],
            $_POST['contact_type'] === 'facebook' ? $_POST['display_text'] : null,
            $_POST['icon_path'],
            isset($_POST['is_visible']) ? 1 : 0
        );
        $message = $result ? 
            '<div style="color: green; margin: 10px 0;">Contact information updated successfully!</div>' : 
            '<div style="color: red; margin: 10px 0;">Error updating contact information.</div>';
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
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .form-container {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .display-text-group {
            display: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f5f5f5;
        }
        .button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .button-primary {
            background: #4CAF50;
            color: white;
        }
        .button-danger {
            background: #f44336;
            color: white;
        }
        .button:hover {
            opacity: 0.9;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .section-settings {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
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
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #4CAF50;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body>
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
            <form method="POST">
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
                    <label>Icon Path:</label>
                    <input type="text" name="icon_path" required placeholder="Path to icon image">
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
            <form method="POST" id="editContactForm">
                <input type="hidden" name="contact_type" id="edit_contact_type">
                <input type="hidden" name="id" id="edit_id">
                
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
                    <label>Icon Path:</label>
                    <input type="text" name="icon_path" id="edit_icon_path" required placeholder="Path to icon image">
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
                <th>Icon Path</th>
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
                <td><?= htmlspecialchars($contact['icon_path']) ?></td>
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
            document.getElementById('edit_icon_path').value = contact.icon_path;
            document.getElementById('edit_is_visible').checked = contact.is_visible == 1;

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
    </script>
</body>
</html>
