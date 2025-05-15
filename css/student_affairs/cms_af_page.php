<?php
require_once __DIR__ . '/../db_connection.php';

class AboutSectionCMS {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS af_page (
                id INT AUTO_INCREMENT PRIMARY KEY,
                section_title VARCHAR(255) NOT NULL,
                about_title VARCHAR(255) NOT NULL,
                about_image_url VARCHAR(255) NOT NULL,
                about_description TEXT NOT NULL,
                about_show_more_text VARCHAR(255),
                is_visible TINYINT(1) DEFAULT 1
            )";
            $this->pdo->exec($sql);

            // Check if default record exists
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page");
            if ($stmt->fetchColumn() == 0) {
                $this->createDefaultContent();
            }
        } catch (PDOException $e) {
            error_log("Error ensuring table exists: " . $e->getMessage());
        }
    }

    private function createDefaultContent() {
        try {
            $sql = "INSERT INTO af_page (
                section_title, 
                about_title, 
                about_image_url, 
                about_description,
                about_show_more_text,
                is_visible
            ) VALUES (
                'About Section',
                'About Us',
                '../imgs/cte.jpg',
                'Default about content. Please update this in the CMS.',
                'Read More',
                1
            )";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating default content: " . $e->getMessage());
        }
    }

    public function getAboutSection() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_page WHERE id = 1");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting about section content: " . $e->getMessage());
            return null;
        }
    }

    public function updateAboutSection($data) {
        try {
            $stmt = $this->pdo->prepare("UPDATE af_page SET 
                section_title = ?,
                about_title = ?,
                about_image_url = ?,
                about_description = ?,
                about_show_more_text = ?,
                is_visible = ?
                WHERE id = 1");

            return $stmt->execute([
                $data['section_title'],
                $data['about_title'],
                $data['about_image_url'],
                $data['about_description'],
                $data['about_show_more_text'],
                $data['is_visible']
            ]);
        } catch (PDOException $e) {
            error_log("Error updating about section content: " . $e->getMessage());
            return false;
        }
    }
}

// Initialize CMS and handle form submission
$cms = new AboutSectionCMS(getPDOConnection());
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'section_title' => $_POST['section_title'] ?? '',
        'about_title' => $_POST['about_title'] ?? '',
        'about_image_url' => $_POST['about_image_url'] ?? $_POST['current_about_image'] ?? '',
        'about_description' => $_POST['about_description'] ?? '',
        'about_show_more_text' => $_POST['about_show_more'] ?? '',
        'is_visible' => isset($_POST['is_visible']) ? 1 : 0
    ];

    // Handle image upload
    if (!empty($_FILES['about_image']['name'])) {
        $upload_dir = '../../uploads/';
        $file_extension = strtolower(pathinfo($_FILES['about_image']['name'], PATHINFO_EXTENSION));
        $new_filename = 'about_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['about_image']['tmp_name'], $upload_path)) {
            $data['about_image_url'] = 'uploads/' . $new_filename;
        }
    }

    if ($cms->updateAboutSection($data)) {
        $message = '<div class="alert alert-success">Content updated successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating content. Please try again.</div>';
    }
}

// Get current content
$content = $cms->getAboutSection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - About Section</title>
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">About Section Management</h1>
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
                            <label class="switch">
                                <input type="checkbox" name="is_visible" <?= ($content['is_visible'] ?? 1) ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                            <label class="form-check-label ms-2">Show About Section</label>
                        </div>
                    </div>

                    <!-- About Content -->
                    <div class="mb-4">
                        <h2>About Content</h2>
                        <div class="mb-3">
                            <label for="about_title" class="form-label">About Title</label>
                            <input type="text" class="form-control" id="about_title" name="about_title" 
                                   value="<?= htmlspecialchars($content['about_title'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">About Image</label>
                            <div class="tab-container">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#about-file">
                                            <i class="fas fa-upload"></i> Upload File
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#about-url">
                                            <i class="fas fa-link"></i> Image URL
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content p-3 border border-top-0">
                                    <div id="about-file" class="tab-pane fade show active">
                                        <input type="file" class="form-control" id="about_image" name="about_image" 
                                               accept="image/*" onchange="previewImage(this, 'about-preview')">
                                    </div>
                                    <div id="about-url" class="tab-pane fade">
                                        <input type="url" class="form-control" id="about_image_url" name="about_image_url" 
                                               placeholder="Enter image URL">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="current_about_image" 
                                   value="<?= htmlspecialchars($content['about_image_url'] ?? '') ?>">
                            
                            <div class="preview-section">
                                <h3><i class="fas fa-image"></i> Current Image Preview</h3>
                                <div class="image-preview-container">
                                    <?php if (!empty($content['about_image_url'])): ?>
                                        <label class="image-preview-label">Current Image:</label>
                                        <img src="../../<?= htmlspecialchars($content['about_image_url']) ?>" 
                                             alt="Current about image" 
                                             class="current-image-preview"
                                             onerror="this.src='../../imgs/cte.jpg';">
                                    <?php else: ?>
                                        <div class="no-image-placeholder">
                                            <i class="fas fa-image"></i>
                                            <p>No image currently set</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div id="about-preview" class="image-preview mt-3" style="display: none;"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="about_description" class="form-label">About Description</label>
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
                                <div class="editor" id="editor" contenteditable="true"><?= $content['about_description'] ?? '' ?></div>
                                <input type="hidden" name="about_description" id="about_description_input">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="about_show_more" class="form-label">Show More Text</label>
                            <input type="text" class="form-control" id="about_show_more" name="about_show_more" 
                                   value="<?= htmlspecialchars($content['about_show_more_text'] ?? '') ?>" required>
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
