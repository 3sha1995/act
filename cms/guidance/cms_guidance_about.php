<?php
require_once __DIR__ . '/../db_connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$error_message = null;
$success_message = null;
$content = null;

class CMS_AboutSection {
    private $pdo;
    protected $upload_dir;

    public function __construct() {
        try {
            // Get database connection
            $this->pdo = getPDOConnection();
            if (!$this->pdo) {
                throw new PDOException("Failed to get database connection");
            }
            
            // Set error mode to throw exceptions
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create uploads directory if it doesn't exist
            $this->upload_dir = '../../uploads/';
            if (!file_exists($this->upload_dir)) {
                if (!mkdir($this->upload_dir, 0777, true)) {
                    throw new Exception("Failed to create upload directory");
                }
            }
        } catch (PDOException $e) {
            error_log("Database connection error in CMS_AboutSection constructor: " . $e->getMessage());
            throw new Exception("Failed to connect to database: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Initialization error in CMS_AboutSection constructor: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUploadDir() {
        return $this->upload_dir;
    }

    public function getContent() {
        try {
            // Create table if it doesn't exist
            $sql = "CREATE TABLE IF NOT EXISTS guidance_about (
                id int(11) NOT NULL AUTO_INCREMENT,
                ontop_title varchar(255) NOT NULL DEFAULT 'ABOUT US',
                main_title varchar(255) NOT NULL DEFAULT 'GUIDANCE OFFICE',
                image_path varchar(255) DEFAULT '../imgs/cte.jpg',
                description text,
                is_visible tinyint(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            $this->pdo->exec($sql);

            // Check if record exists
            $stmt = $this->pdo->prepare("SELECT * FROM guidance_about WHERE id = 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                // If no record exists, create one with default values
                $this->createDefaultContent();
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            return $result ?: [
                'ontop_title' => 'ABOUT US',
                'main_title' => 'GUIDANCE OFFICE',
                'image_path' => '../imgs/cte.jpg',
                'description' => 'Default content. Please update this in the CMS.',
                'is_visible' => 1
            ];
        } catch (PDOException $e) {
            error_log("Database error in getContent: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    private function createDefaultContent() {
        try {
            $this->pdo->beginTransaction();

            // Check if record already exists
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM guidance_about WHERE id = 1");
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                $this->pdo->commit();
                return;
            }

            // Insert default content
            $stmt = $this->pdo->prepare("INSERT INTO guidance_about 
                (id, ontop_title, main_title, image_path, description, is_visible) 
                VALUES (1, :ontop_title, :main_title, :image_path, :description, :is_visible)");
            
            $result = $stmt->execute([
                ':ontop_title' => 'ABOUT US',
                ':main_title' => 'GUIDANCE OFFICE',
                ':image_path' => '../imgs/cte.jpg',
                ':description' => 'Default content. Please update this in the CMS.',
                ':is_visible' => 1
            ]);

            if (!$result) {
                throw new PDOException("Failed to insert default content");
            }

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error creating default content: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateContent($ontop_title, $main_title, $image_path, $description, $is_visible) {
        try {
            $this->pdo->beginTransaction();

            // Log the values being updated
            error_log("Updating guidance_about with values:");
            error_log("ontop_title: " . $ontop_title);
            error_log("main_title: " . $main_title);
            error_log("image_path: " . $image_path);
            error_log("description length: " . strlen($description));
            error_log("is_visible: " . $is_visible);

            // Validate input
            if (empty($ontop_title) || empty($main_title)) {
                throw new Exception("Title fields cannot be empty");
            }

            // Check if record exists
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM guidance_about WHERE id = 1");
            $checkStmt->execute();
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                $stmt = $this->pdo->prepare("UPDATE guidance_about SET 
                    ontop_title = :ontop_title, 
                    main_title = :main_title, 
                    image_path = :image_path, 
                    description = :description, 
                    is_visible = :is_visible 
                    WHERE id = 1");
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO guidance_about 
                    (id, ontop_title, main_title, image_path, description, is_visible) 
                    VALUES (1, :ontop_title, :main_title, :image_path, :description, :is_visible)");
            }
            
            $params = [
                ':ontop_title' => $ontop_title,
                ':main_title' => $main_title,
                ':image_path' => $image_path,
                ':description' => $description,
                ':is_visible' => $is_visible
            ];

            error_log("Executing query with parameters: " . print_r($params, true));
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                throw new PDOException("Failed to update content: " . implode(", ", $stmt->errorInfo()));
            }
            
            $this->pdo->commit();
            error_log("Update successful");
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Database error in updateContent: " . $e->getMessage());
            error_log("SQL State: " . $e->errorInfo[0]);
            error_log("Error Code: " . $e->errorInfo[1]);
            error_log("Error Message: " . $e->errorInfo[2]);
            throw new Exception("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            if ($this->pdo && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error in updateContent: " . $e->getMessage());
            throw $e;
        }
    }

    // Add this new function to get formatted content for the view
    public static function getAboutContent() {
        try {
            $pdo = getPDOConnection();
            $stmt = $pdo->prepare("SELECT * FROM `guidance_about` WHERE `id` = 1");
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

// Main script
try {
    $cmsAbout = new CMS_AboutSection();
    $content = $cmsAbout->getContent();

    // Handling the POST request for updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Validate required fields
            if (empty($_POST['ontop_title']) || empty($_POST['main_title'])) {
                throw new Exception("Title fields are required");
            }

            $ontop_title = trim($_POST['ontop_title']);
            $main_title = trim($_POST['main_title']);
            $description = isset($_POST['description_input']) ? trim($_POST['description_input']) : '';
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            
            // Handle image upload or URL
            $image_path = '';
            if (!empty($_FILES['image_file']['name'])) {
                $file_extension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
                
                if (!in_array($file_extension, $allowed_extensions)) {
                    throw new Exception("Invalid file type. Allowed types: jpg, jpeg, png, gif");
                }

                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $cmsAbout->getUploadDir() . $new_filename;
                
                if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_path)) {
                    throw new Exception("Error uploading file: " . error_get_last()['message']);
                }
                
                $image_path = 'uploads/' . $new_filename;
                error_log("Image uploaded successfully to: " . $upload_path);
            } elseif (!empty($_POST['image_url'])) {
                $image_path = $_POST['image_url'];
            } elseif (!empty($_POST['existing_image'])) {
                $image_path = $_POST['existing_image'];
            }

            error_log("Form data received:");
            error_log("ontop_title: " . $ontop_title);
            error_log("main_title: " . $main_title);
            error_log("description length: " . strlen($description));
            error_log("is_visible: " . $is_visible);
            error_log("image_path: " . $image_path);

            $cmsAbout->updateContent($ontop_title, $main_title, $image_path, $description, $is_visible);
            $success_message = "Content updated successfully! Visibility is " . ($is_visible ? "enabled" : "disabled");
            
            // Refresh content after update
            $content = $cmsAbout->getContent();
        } catch (Exception $e) {
            error_log("Error in form processing: " . $e->getMessage());
            $error_message = $e->getMessage();
        }
    }
} catch (Exception $e) {
    error_log("Critical error: " . $e->getMessage());
    $error_message = "A critical error occurred: " . $e->getMessage();
    $content = [
        'ontop_title' => '',
        'main_title' => '',
        'image_path' => '',
        'description' => '',
        'is_visible' => 1
    ];
}
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

        /* Alert styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        /* Debug info styles */
        .debug-info {
            background: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <h1>About Section CMS</h1>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if (isset($_ENV['DEBUG']) && $_ENV['DEBUG']): ?>
        <div class="debug-info">
            <strong>Debug Information:</strong>
            <pre><?php print_r($content); ?></pre>
        </div>
    <?php endif; ?>

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
            <div class="editor" id="description" contenteditable="true"><?= htmlspecialchars($content['description'] ?? '') ?></div>
            <input type="hidden" name="description_input" id="description_input">
        </div>

        <label for="is_visible">Visible:</label>
        <input type="checkbox" id="is_visible" name="is_visible" <?= ($content['is_visible'] ?? false) ? 'checked' : '' ?>><br><br>

        <input type="submit" value="Update Content">
    </form>

    <script>
        // Form submission handler
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const editor = document.getElementById('description');
            const hiddenInput = document.getElementById('description_input');

            // Update hidden input with editor content before form submission
            form.addEventListener('submit', function(e) {
                hiddenInput.value = editor.innerHTML;
                console.log('Description content being submitted:', hiddenInput.value);
            });

            // Initialize editor content if exists
            if (editor.innerHTML.trim() === '') {
                editor.innerHTML = '<?= addslashes($content['description'] ?? '') ?>';
            }
        });

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
    </script>
</body>
</html>
