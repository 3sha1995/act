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

        /* Image preview styles */
        .image-preview {
            max-width: 200px;
            margin: 10px 0;
            display: none;
        }

        .current-image-container {
            margin: 15px 0;
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

        .current-image-label {
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
        <div class="mb-3">
            <input type="file" id="image_file" name="image_file" accept="image/*" onchange="previewImage(this)">
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($content['image_path'] ?? '') ?>">
            <img id="image-preview" class="image-preview" alt="Preview">
        </div>

        <div class="current-image-container">
            <h3><i class="fas fa-image"></i> Current Image</h3>
            <?php if (!empty($content['image_path'])): ?>
                <label class="current-image-label">Current image path: <?= htmlspecialchars($content['image_path']) ?></label>
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

        <input type="submit" value="Update Content">
    </form>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Rich text editor functions
        function execCommand(command) {
            document.getElementById('description').focus();
            document.execCommand(command, false, null);
            updateToolbarState();
        }

        function execCommandWithArg(command, arg) {
            document.getElementById('description').focus();
            document.execCommand(command, false, arg);
            updateToolbarState();
        }

        function createLink() {
            const url = prompt('Enter URL:', 'http://');
            if (url) {
                document.getElementById('description').focus();
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

        // Form submission handler
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (this.querySelector('#description')) {
                    document.getElementById('description_input').value = 
                        document.getElementById('description').innerHTML;
                }
                
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    </script>
</body>
</html>
