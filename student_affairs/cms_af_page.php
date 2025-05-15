<?php
require_once __DIR__ . '/../db_connection.php';

class CMS_AboutSection {
    private $pdo;
    protected $upload_dir;

    public function __construct() {
        $this->pdo = getPDOConnection();
        $this->upload_dir = '../../uploads/';
        // Create uploads directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    public function getUploadDir() {
        return $this->upload_dir;
    }

    public function getContent() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `af_page` WHERE `id` = 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                // If no record exists, create one with default values
                $this->createDefaultContent();
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Database error in getContent: " . $e->getMessage());
            return null;
        }
    }

    private function createDefaultContent() {
        try {
            $sql = "INSERT INTO `af_page` (`id`, `ontop_title`, `main_title`, `image_path`, `description`, `is_visible`) 
                    VALUES (1, 'ABOUT US', 'STUDENT AFFAIRS', '../imgs/cte.jpg', 
                    'Default content. Please update this in the CMS.', 1)";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating default content: " . $e->getMessage());
        }
    }

    public function updateContent($ontop_title, $main_title, $image_path, $description, $is_visible) {
        try {
            $stmt = $this->pdo->prepare("UPDATE `af_page` SET 
                `ontop_title` = ?, 
                `main_title` = ?, 
                `image_path` = ?, 
                `description` = ?, 
                `is_visible` = ? 
                WHERE `id` = 1");
            
            $result = $stmt->execute([$ontop_title, $main_title, $image_path, $description, $is_visible]);
            
            if (!$result) {
                error_log("Update failed: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
            return true;
        } catch (PDOException $e) {
            error_log("Database error in updateContent: " . $e->getMessage());
            return false;
        }
    }

    // Add this new function to get formatted content for the view
    public static function getAboutContent() {
        try {
            $pdo = getPDOConnection();
            $stmt = $pdo->prepare("SELECT * FROM `af_page` WHERE `id` = 1");
            $stmt->execute();
            $content = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$content) {
                return null;
            }

            // Format image path
            $imagePath = $content['image_path'];
            if (strpos($imagePath, 'uploads/') === 0) {
                $imagePath = '../' . $imagePath;
            }

            return [
                'is_visible' => $content['is_visible'],
                'ontop_title' => $content['ontop_title'],
                'main_title' => $content['main_title'],
                'image_path' => $imagePath,
                'description' => $content['description']
            ];
        } catch (PDOException $e) {
            error_log("Error getting about content: " . $e->getMessage());
            return null;
        }
    }
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - About Section</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .editor-container {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .editor-toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            padding: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .toolbar-group {
            display: flex;
            gap: 5px;
            padding: 0 10px;
            border-right: 1px solid #e0e0e0;
        }

        .toolbar-group:last-child {
            border-right: none;
        }

        .editor-toolbar button {
            background: white;
            border: 1px solid #d1d1d1;
            border-radius: 4px;
            padding: 6px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            color: #444;
            transition: all 0.2s ease;
        }

        .editor-toolbar button:hover {
            background: #e9ecef;
            border-color: #bbb;
        }

        .editor-toolbar button.active {
            background: #e9ecef;
            border-color: #0056b3;
            color: #0056b3;
        }

        .editor-toolbar button i {
            font-size: 14px;
        }

        .editor {
            min-height: 300px;
            padding: 20px;
            background: white;
            border: none;
            outline: none;
            font-size: 16px;
            line-height: 1.6;
        }

        .editor:focus {
            outline: none;
        }

        /* Tooltip styles */
        .tooltip {
            position: relative;
        }

        .tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 4px 8px;
            background: #333;
            color: white;
            font-size: 12px;
            border-radius: 4px;
            white-space: nowrap;
            z-index: 1000;
        }

        /* Color picker styles */
        .color-picker {
            position: relative;
            display: inline-block;
        }

        .color-picker input[type="color"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .toolbar-group {
                padding: 5px;
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
                width: 100%;
                justify-content: center;
            }

            .toolbar-group:last-child {
                border-bottom: none;
            }

            .editor-toolbar {
                flex-direction: column;
            }
        }

        /* Restore image input styles */
        .image-input-container {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 15px;
        }

        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }

        .tab-container {
            margin-bottom: 15px;
        }

        .tab-button {
            padding: 8px 15px;
            cursor: pointer;
            background: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 4px 4px 0 0;
        }

        .tab-button.active {
            background: #fff;
            border-bottom: none;
        }

        .tab-content {
            display: none;
            padding: 15px;
            border: 1px solid #ccc;
        }

        .tab-content.active {
            display: block;
        }

        .preview-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .current-image-preview {
            max-width: 300px;
            margin: 10px 0;
            border: 2px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .preview-buttons {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .preview-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .preview-button:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .preview-button i {
            font-size: 16px;
        }

        .image-preview-container {
            margin: 15px 0;
        }

        .image-preview-label {
            display: block;
            font-weight: 500;
            margin-bottom: 10px;
            color: #495057;
        }

        .no-image-placeholder {
            padding: 20px;
            background: #e9ecef;
            border: 2px dashed #ced4da;
            border-radius: 4px;
            text-align: center;
            color: #6c757d;
        }

        /* Modal styles */
        .preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            overflow-y: auto;
            padding: 20px;
        }

        .modal-content {
            position: relative;
            background: white;
            margin: 20px auto;
            padding: 20px;
            max-width: 1200px;
            border-radius: 8px;
            overflow: hidden;
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .close-modal:hover {
            color: #333;
        }

        /* Main page styles for preview */
        .student_affairs_about_section {
            padding: 60px 20px;
            background: #fff;
        }

        .student_affairs_about_container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .student_affairs_about_header {
            text-align: center;
            margin-bottom: 40px;
        }

        .student_affairs_about_ontop_title {
            color: #666;
            font-size: 1.2em;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .student_affairs_about_title {
            color: #333;
            font-size: 2.5em;
            margin: 10px 0;
            font-weight: bold;
        }

        .student_affair_about_divider {
            width: 50px;
            height: 3px;
            background: #007bff;
            margin: 15px auto;
        }

        .student_affairs_about_content {
            display: flex;
            gap: 40px;
            align-items: flex-start;
        }

        .student_affairs_about_image {
            flex: 1;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .student_affairs_about_image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .student_affairs_about_text {
            flex: 1;
            padding: 20px;
        }

        .student_affairs_about_description {
            line-height: 1.8;
            color: #444;
            font-size: 1.1em;
        }

        /* Responsive styles for preview */
        @media (max-width: 768px) {
            .student_affairs_about_content {
                flex-direction: column;
            }

            .student_affairs_about_image,
            .student_affairs_about_text {
                width: 100%;
            }
        }

        /* Add styles for the font size select */
        .font-size-select {
            padding: 6px 12px;
            border: 1px solid #d1d1d1;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            color: #444;
            font-size: 14px;
        }

        .font-size-select:hover {
            background: #e9ecef;
            border-color: #bbb;
        }

        .font-size-select:focus {
            outline: none;
            border-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>About Section CMS</h1>

    <?php
    $cmsAbout = new CMS_AboutSection();

    // Handling the POST request for updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ontop_title = $_POST['ontop_title'];
        $main_title = $_POST['main_title'];
        $description = $_POST['description'];
        $is_visible = isset($_POST['is_visible']) ? 1 : 0;
        
        // Handle image upload or URL
        $image_path = '';
        if (!empty($_FILES['image_file']['name'])) {
            $file_extension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
            
            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $cmsAbout->getUploadDir() . $new_filename;
                
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_path)) {
                    $image_path = 'uploads/' . $new_filename; // Store path relative to root
                    echo "<p style='color: green;'>Image uploaded successfully!</p>";
                } else {
                    echo "<p style='color: red;'>Error uploading file: " . error_get_last()['message'] . "</p>";
                }
            } else {
                echo "<p style='color: red;'>Invalid file type. Allowed types: jpg, jpeg, png, gif</p>";
            }
        } elseif (!empty($_POST['image_url'])) {
            $image_path = $_POST['image_url'];
        } elseif (!empty($_POST['existing_image'])) {
            $image_path = $_POST['existing_image'];
        }

        // Add debug information
        echo "<!-- Debug: is_visible value before update: " . $is_visible . " -->";

        $updated = $cmsAbout->updateContent($ontop_title, $main_title, $image_path, $description, $is_visible);
        
        if ($updated) {
            echo "<p style='color: green;'>Content updated successfully! Visibility is " . ($is_visible ? "enabled" : "disabled") . "</p>";
        } else {
            echo "<p style='color: red;'>Error updating content. Please check the error logs.</p>";
        }
    }

    // Fetching and displaying the updated content
    $content = $cmsAbout->getContent();
    if ($content === null) {
        echo "<p style='color: red;'>Error fetching content from database.</p>";
    } else {
        // Debug information
        echo "<!-- Debug Info -->";
        echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border: 1px solid #ddd; display: none;'>";
        echo "<strong>Image Path from DB:</strong> " . htmlspecialchars($content['image_path'] ?? 'Not set') . "<br>";
        echo "<strong>Upload Directory:</strong> " . htmlspecialchars($cmsAbout->getUploadDir()) . "<br>";
        echo "</div>";
    }

    // Add visibility status indicator
    if ($content !== null) {
        echo "<div class='visibility-status' style='margin: 10px 0; padding: 10px; background: #f8f9fa; border: 1px solid #ddd;'>";
        echo "Current visibility status: <strong>" . ($content['is_visible'] == 1 ? "Visible" : "Hidden") . "</strong>";
        echo "</div>";
    }
    ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="ontop_title">About Title:</label>
        <input type="text" id="ontop_title" name="ontop_title" value="<?= htmlspecialchars($content['ontop_title'] ?? '') ?>" required><br><br>

        <label for="main_title">Main Title:</label>
        <input type="text" id="main_title" name="main_title" value="<?= htmlspecialchars($content['main_title'] ?? '') ?>" required><br><br>

        <label>Image:</label>
        <div class="tab-container">
            <button type="button" class="tab-button active" onclick="switchTab('file')">Upload File</button>
            <button type="button" class="tab-button" onclick="switchTab('url')">Image URL</button>
        </div>

        <div id="file-tab" class="tab-content active">
            <input type="file" id="image_file" name="image_file" accept="image/*" onchange="previewImage(this)">
        </div>

        <div id="url-tab" class="tab-content">
            <input type="url" id="image_url" name="image_url" placeholder="Enter image URL">
        </div>

        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($content['image_path'] ?? '') ?>">
        <img id="image-preview" class="image-preview" alt="Preview">
        <?php if (!empty($content['image_path'])): ?>
            <p>Current image: <?= htmlspecialchars($content['image_path']) ?></p>
        <?php endif; ?>
        <br><br>

        <label for="description">Description:</label>
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
            <div class="editor" id="editor" contenteditable="true"><?= $content['description'] ?? '' ?></div>
        </div>
        <input type="hidden" name="description" id="description">

        <label for="is_visible">Visible:</label>
        <input type="checkbox" id="is_visible" name="is_visible" <?= ($content['is_visible'] ?? false) ? 'checked' : '' ?>><br><br>

        <div class="preview-buttons">
            <button type="button" class="preview-button" onclick="showPreview()">
                <i class="fas fa-eye"></i> Quick Preview
            </button>
        </div>

        <div class="preview-section">
            <h3><i class="fas fa-image"></i> Current Image Preview</h3>
            <div class="image-preview-container">
                <?php if (!empty($content['image_path'])): ?>
                    <label class="image-preview-label">Current Image:</label>
                    <?php
                    $imagePath = $content['image_path'];
                    // If path starts with ../, remove it to get correct path relative to CMS
                    if (strpos($imagePath, '../') === 0) {
                        $imagePath = substr($imagePath, 3);
                    }
                    // Construct the correct path
                    $displayPath = '../../' . $imagePath;
                    ?>
                    <img src="<?= htmlspecialchars($displayPath) ?>" 
                         alt="Current image" 
                         class="current-image-preview"
                         onerror="this.src='../../imgs/cte.jpg'; console.log('Failed to load image: ' + this.src);">
                <?php else: ?>
                    <div class="no-image-placeholder">
                        <i class="fas fa-image"></i>
                        <p>No image currently set</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <input type="submit" value="Update Content">
    </form>

    <div id="previewModal" class="preview-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closePreview()">&times;</span>
            <div id="previewContent"></div>
        </div>
    </div>

    <script>
        function execCommand(command) {
            document.execCommand(command, false, null);
            updateToolbarState();
        }

        function execCommandWithArg(command, arg) {
            document.execCommand(command, false, arg);
            updateToolbarState();
        }

        function createLink() {
            const url = prompt('Enter URL:', 'http://');
            if (url) {
                document.execCommand('createLink', false, url);
            }
            updateToolbarState();
        }

        function updateToolbarState() {
            const buttons = document.querySelectorAll('.editor-toolbar button');
            buttons.forEach(button => {
                const command = button.getAttribute('data-command');
                if (command && document.queryCommandState(command)) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }

        // Initialize editor
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('editor');
            
            // Update toolbar state when selection changes
            editor.addEventListener('keyup', updateToolbarState);
            editor.addEventListener('mouseup', updateToolbarState);
            
            // Sync editor content to hidden input before form submission
            document.querySelector('form').addEventListener('submit', function() {
                document.getElementById('description').value = editor.innerHTML;
            });
        });

        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content and activate button
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    preview.classList.add('current-image-preview'); // Add the same styling as current image
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function showPreview() {
            const modal = document.getElementById('previewModal');
            const previewContent = document.getElementById('previewContent');
            
            // Get current form values
            const ontopTitle = document.getElementById('ontop_title').value;
            const mainTitle = document.getElementById('main_title').value;
            const description = document.getElementById('editor').innerHTML;
            const imagePreview = document.getElementById('image-preview');
            const imagePath = imagePreview.style.display !== 'none' ? imagePreview.src : 
                             document.querySelector('.current-image-preview')?.src || '../imgs/cte.jpg';

            // Create preview HTML using the same structure and classes as the main page
            const previewHTML = `
                <section class="student_affairs_about_section">
                    <div class="student_affairs_about_container">
                        <div class="student_affairs_about_header">
                            <div class="student_affairs_about_ontop_title">${ontopTitle}</div>
                            <h2 class="student_affairs_about_title">${mainTitle}</h2>
                            <div class="student_affair_about_divider"></div>
                        </div>
                        
                        <div class="student_affairs_about_content">
                            <div class="student_affairs_about_image">
                                <img src="${imagePath}" alt="Preview Image">
                            </div>
                            <div class="student_affairs_about_text">
                                <div class="student_affairs_about_description">
                                    ${description}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            `;

            previewContent.innerHTML = previewHTML;
            modal.style.display = 'block';
        }

        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('previewModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
