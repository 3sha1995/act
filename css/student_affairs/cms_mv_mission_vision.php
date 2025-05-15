<?php
require_once __DIR__ . '/../db_connection.php';

class MissionVisionCMS {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS af_page_mv (
                id INT AUTO_INCREMENT PRIMARY KEY,
                section_title VARCHAR(255) NOT NULL,
                mission_title VARCHAR(255) NOT NULL,
                mission_image_url VARCHAR(255) NOT NULL,
                mission_description TEXT NOT NULL,
                mission_show_more_text VARCHAR(255),
                vision_title VARCHAR(255) NOT NULL,
                vision_image_url VARCHAR(255) NOT NULL,
                vision_description TEXT NOT NULL,
                vision_show_more_text VARCHAR(255),
                is_visible TINYINT(1) DEFAULT 1
            )";
            $this->pdo->exec($sql);

            // Check if default record exists
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_mv");
            if ($stmt->fetchColumn() == 0) {
                $this->createDefaultContent();
            }
} catch (PDOException $e) {
            error_log("Error ensuring table exists: " . $e->getMessage());
        }
    }

    private function createDefaultContent() {
        try {
            $sql = "INSERT INTO af_page_mv (
                section_title, 
                mission_title, 
                mission_image_url, 
                mission_description,
                mission_show_more_text,
                vision_title,
                vision_image_url,
                vision_description,
                vision_show_more_text,
                is_visible
            ) VALUES (
                'Mission & Vision',
                'Our Mission',
                '../imgs/cte.jpg',
                'Default mission content. Please update this in the CMS.',
                'Read More',
                'Our Vision',
                '../imgs/cte.jpg',
                'Default vision content. Please update this in the CMS.',
                'Read More',
                1
            )";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating default content: " . $e->getMessage());
        }
    }

    public function getMV() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_page_mv WHERE id = 1");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting MV content: " . $e->getMessage());
            return null;
        }
    }

    public function updateMV($data) {
        try {
            $stmt = $this->pdo->prepare("UPDATE af_page_mv SET 
                section_title = ?,
                mission_title = ?,
                mission_image_url = ?,
                mission_description = ?,
                mission_show_more_text = ?,
                vision_title = ?,
                vision_image_url = ?,
                vision_description = ?,
                vision_show_more_text = ?,
                is_visible = ?
                WHERE id = 1");

            return $stmt->execute([
                $data['section_title'],
                $data['mission_title'],
                $data['mission_image_url'],
                $data['mission_description'],
                $data['mission_show_more_text'],
                $data['vision_title'],
                $data['vision_image_url'],
                $data['vision_description'],
                $data['vision_show_more_text'],
                $data['is_visible']
            ]);
        } catch (PDOException $e) {
            error_log("Error updating MV content: " . $e->getMessage());
            return false;
        }
    }
}

// Initialize CMS and handle form submission
$cms = new MissionVisionCMS(getPDOConnection());
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'section_title' => $_POST['section_title'] ?? '',
        'mission_title' => $_POST['mission_title'] ?? '',
        'mission_image_url' => $_POST['mission_image_url'] ?? $_POST['current_mission_image'] ?? '',
        'mission_description' => $_POST['mission_description'] ?? '',
        'mission_show_more_text' => $_POST['mission_show_more'] ?? '',
        'vision_title' => $_POST['vision_title'] ?? '',
        'vision_image_url' => $_POST['vision_image_url'] ?? $_POST['current_vision_image'] ?? '',
        'vision_description' => $_POST['vision_description'] ?? '',
        'vision_show_more_text' => $_POST['vision_show_more'] ?? '',
        'is_visible' => isset($_POST['is_visible']) ? 1 : 0
    ];

    // Handle mission image upload
    if (!empty($_FILES['mission_image']['name'])) {
        $upload_dir = '../../uploads/';
        $file_extension = strtolower(pathinfo($_FILES['mission_image']['name'], PATHINFO_EXTENSION));
        $new_filename = 'mission_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['mission_image']['tmp_name'], $upload_path)) {
            $data['mission_image_url'] = 'uploads/' . $new_filename;
        }
    }

    // Handle vision image upload
    if (!empty($_FILES['vision_image']['name'])) {
        $upload_dir = '../../uploads/';
        $file_extension = strtolower(pathinfo($_FILES['vision_image']['name'], PATHINFO_EXTENSION));
        $new_filename = 'vision_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['vision_image']['tmp_name'], $upload_path)) {
            $data['vision_image_url'] = 'uploads/' . $new_filename;
        }
    }

    if ($cms->updateMV($data)) {
        $message = '<div class="alert alert-success">Content updated successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating content. Please try again.</div>';
    }
}

// Get current content
$content = $cms->getMV();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - Mission & Vision</title>
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Mission & Vision Management</h1>
            </div>
            <div class="card-body">
                <?= $message ?>

                <form method="POST" enctype="multipart/form-data">
                    <!-- Section Settings -->
                    <div class="mb-4">
                        <h2>Section Settings</h2>
                        <div class="mb-3">
                            <label for="section_title" class="form-label">Section Title</label>
                            <input type="text" class="form-control" id="section_title" name="section_title" 
                                   value="<?= htmlspecialchars($content['section_title'] ?? '') ?>" required>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="is_visible" name="is_visible" 
                                   <?= ($content['is_visible'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_visible">Show Mission & Vision Section</label>
                        </div>
                    </div>

                    <!-- Mission Section -->
                    <div class="mb-4">
    <h2>Mission</h2>
                        <div class="mb-3">
                            <label for="mission_title" class="form-label">Mission Title</label>
                            <input type="text" class="form-control" id="mission_title" name="mission_title" 
                                   value="<?= htmlspecialchars($content['mission_title'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mission Image</label>
                            <div class="tab-container">
                                <div class="nav nav-tabs">
                                    <button type="button" class="nav-link active" onclick="switchTab(this, 'mission-file')">Upload File</button>
                                    <button type="button" class="nav-link" onclick="switchTab(this, 'mission-url')">Image URL</button>
                                </div>

                                <div class="tab-content p-3 border border-top-0">
                                    <div id="mission-file" class="tab-content active">
                                        <input type="file" class="form-control" id="mission_image" name="mission_image" 
                                               accept="image/*" onchange="previewImage(this, 'mission-preview')">
                                    </div>
                                    <div id="mission-url" class="tab-content">
                                        <input type="url" class="form-control" id="mission_image_url" name="mission_image_url" 
                                               placeholder="Enter image URL">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="current_mission_image" 
                                   value="<?= htmlspecialchars($content['mission_image_url'] ?? '') ?>">
                            
                            <div class="preview-container">
                                <h6 class="mb-3">Current Image Preview</h6>
                                <?php if (!empty($content['mission_image_url'])): ?>
                                    <img src="../../<?= htmlspecialchars($content['mission_image_url']) ?>" 
                                         alt="Current mission image" class="image-preview"
                                         onerror="this.src='../../imgs/cte.jpg';">
                                <?php else: ?>
                                    <div class="no-image-placeholder">
                                        <i class="fas fa-image"></i>
                                        <p>No image currently set</p>
                                    </div>
        <?php endif; ?>
                                <img id="mission-preview" class="image-preview mt-3" style="display: none;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="mission_description" class="form-label">Mission Description</label>
                            <div class="editor-container">
                                <div class="editor-toolbar">
                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('bold')" class="tooltip" data-tooltip="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('italic')" class="tooltip" data-tooltip="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('underline')" class="tooltip" data-tooltip="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('strikeThrough')" class="tooltip" data-tooltip="Strike">
                                            <i class="fas fa-strikethrough"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <select onchange="execCommandWithArg('fontSize', this.value)" class="font-size-select tooltip" data-tooltip="Font Size">
                                            <option value="1">Very Small</option>
                                            <option value="2">Small</option>
                                            <option value="3">Normal</option>
                                            <option value="4">Large</option>
                                            <option value="5">Very Large</option>
                                            <option value="6">Extra Large</option>
                                            <option value="7">Huge</option>
                                        </select>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('justifyLeft')" class="tooltip" data-tooltip="Align Left">
                                            <i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyCenter')" class="tooltip" data-tooltip="Align Center">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyRight')" class="tooltip" data-tooltip="Align Right">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyFull')" class="tooltip" data-tooltip="Justify">
                                            <i class="fas fa-align-justify"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('insertUnorderedList')" class="tooltip" data-tooltip="Bullet List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('insertOrderedList')" class="tooltip" data-tooltip="Number List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('indent')" class="tooltip" data-tooltip="Indent">
                                            <i class="fas fa-indent"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('outdent')" class="tooltip" data-tooltip="Outdent">
                                            <i class="fas fa-outdent"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('removeFormat')" class="tooltip" data-tooltip="Clear Format">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                        <button type="button" onclick="createLink()" class="tooltip" data-tooltip="Insert Link">
                                            <i class="fas fa-link"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="editor" id="mission_description_editor" contenteditable="true">
                                    <?= $content['mission_description'] ?? '' ?>
                                </div>
                            </div>
                            <input type="hidden" name="mission_description" id="mission_description_input">
                        </div>

                        <div class="mb-3">
                            <label for="mission_show_more" class="form-label">Show More Text</label>
                            <input type="text" class="form-control" id="mission_show_more" name="mission_show_more" 
                                   value="<?= htmlspecialchars($content['mission_show_more_text'] ?? '') ?>" required>
                        </div>
                    </div>

                    <!-- Vision Section -->
                    <div class="mb-4">
    <h2>Vision</h2>
                        <div class="mb-3">
                            <label for="vision_title" class="form-label">Vision Title</label>
                            <input type="text" class="form-control" id="vision_title" name="vision_title" 
                                   value="<?= htmlspecialchars($content['vision_title'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Vision Image</label>
                            <div class="tab-container">
                                <div class="nav nav-tabs">
                                    <button type="button" class="nav-link active" onclick="switchTab(this, 'vision-file')">Upload File</button>
                                    <button type="button" class="nav-link" onclick="switchTab(this, 'vision-url')">Image URL</button>
                                </div>

                                <div class="tab-content p-3 border border-top-0">
                                    <div id="vision-file" class="tab-content active">
                                        <input type="file" class="form-control" id="vision_image" name="vision_image" 
                                               accept="image/*" onchange="previewImage(this, 'vision-preview')">
                                    </div>
                                    <div id="vision-url" class="tab-content">
                                        <input type="url" class="form-control" id="vision_image_url" name="vision_image_url" 
                                               placeholder="Enter image URL">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="current_vision_image" 
                                   value="<?= htmlspecialchars($content['vision_image_url'] ?? '') ?>">
                            
                            <div class="preview-container">
                                <h6 class="mb-3">Current Image Preview</h6>
                                <?php if (!empty($content['vision_image_url'])): ?>
                                    <img src="../../<?= htmlspecialchars($content['vision_image_url']) ?>" 
                                         alt="Current vision image" class="image-preview"
                                         onerror="this.src='../../imgs/cte.jpg';">
                                <?php else: ?>
                                    <div class="no-image-placeholder">
                                        <i class="fas fa-image"></i>
                                        <p>No image currently set</p>
                                    </div>
        <?php endif; ?>
                                <img id="vision-preview" class="image-preview mt-3" style="display: none;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vision_description" class="form-label">Vision Description</label>
                            <div class="editor-container">
                                <div class="editor-toolbar">
                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('bold')" class="tooltip" data-tooltip="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('italic')" class="tooltip" data-tooltip="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('underline')" class="tooltip" data-tooltip="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('strikeThrough')" class="tooltip" data-tooltip="Strike">
                                            <i class="fas fa-strikethrough"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <select onchange="execCommandWithArg('fontSize', this.value)" class="font-size-select tooltip" data-tooltip="Font Size">
                                            <option value="1">Very Small</option>
                                            <option value="2">Small</option>
                                            <option value="3">Normal</option>
                                            <option value="4">Large</option>
                                            <option value="5">Very Large</option>
                                            <option value="6">Extra Large</option>
                                            <option value="7">Huge</option>
                                        </select>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('justifyLeft')" class="tooltip" data-tooltip="Align Left">
                                            <i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyCenter')" class="tooltip" data-tooltip="Align Center">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyRight')" class="tooltip" data-tooltip="Align Right">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyFull')" class="tooltip" data-tooltip="Justify">
                                            <i class="fas fa-align-justify"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('insertUnorderedList')" class="tooltip" data-tooltip="Bullet List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('insertOrderedList')" class="tooltip" data-tooltip="Number List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('indent')" class="tooltip" data-tooltip="Indent">
                                            <i class="fas fa-indent"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('outdent')" class="tooltip" data-tooltip="Outdent">
                                            <i class="fas fa-outdent"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('removeFormat')" class="tooltip" data-tooltip="Clear Format">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                        <button type="button" onclick="createLink()" class="tooltip" data-tooltip="Insert Link">
                                            <i class="fas fa-link"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="editor" id="vision_description_editor" contenteditable="true">
                                    <?= $content['vision_description'] ?? '' ?>
                                </div>
                            </div>
                            <input type="hidden" name="vision_description" id="vision_description_input">
                        </div>

                        <div class="mb-3">
                            <label for="vision_show_more" class="form-label">Show More Text</label>
                            <input type="text" class="form-control" id="vision_show_more" name="vision_show_more" 
                                   value="<?= htmlspecialchars($content['vision_show_more_text'] ?? '') ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
    </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
