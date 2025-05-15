<?php
// cms.php

require_once __DIR__ . '/../../cms/db_connection.php'; // Include the database connection

class PageCMS {
    private $pdo;
    protected $upload_dir;

    public function __construct() {
        $this->pdo = getPDOConnection();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->upload_dir = '../../uploads/';
        // Create uploads directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    // Save Mission Section
    public function saveMission($title, $image_url, $description, $show_more_text) {
        try {
            // Check if a record exists
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_mission");
            $exists = $stmt->fetchColumn() > 0;

            if ($exists) {
                $stmt = $this->pdo->prepare("UPDATE af_page_mission SET 
                    section_title = ?, 
                    image_url = ?, 
                    description = ?, 
                    show_more_text = ? 
                    WHERE id = (SELECT id FROM af_page_mission LIMIT 1)");
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO af_page_mission 
                    (section_title, image_url, description, show_more_text) 
                    VALUES (?, ?, ?, ?)");
            }
            
            $success = $stmt->execute([$title, $image_url, $description, $show_more_text]);
            if (!$success) {
                error_log("Database error in saveMission: " . implode(", ", $stmt->errorInfo()));
            }
            return $success;
        } catch (PDOException $e) {
            error_log("Error saving mission: " . $e->getMessage());
            return false;
        }
    }

    // Save Vision Section
    public function saveVision($title, $image_url, $description, $show_more_text) {
        try {
            // Check if a record exists
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_vision");
            $exists = $stmt->fetchColumn() > 0;

            if ($exists) {
                $stmt = $this->pdo->prepare("UPDATE af_page_vision SET 
                    section_title = ?, 
                    image_url = ?, 
                    description = ?, 
                    show_more_text = ? 
                    WHERE id = (SELECT id FROM af_page_vision LIMIT 1)");
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO af_page_vision 
                    (section_title, image_url, description, show_more_text) 
                    VALUES (?, ?, ?, ?)");
            }
            
            $success = $stmt->execute([$title, $image_url, $description, $show_more_text]);
            if (!$success) {
                error_log("Database error in saveVision: " . implode(", ", $stmt->errorInfo()));
            }
            return $success;
        } catch (PDOException $e) {
            error_log("Error saving vision: " . $e->getMessage());
            return false;
        }
    }

    // Save MV Section (Main section visibility)
    public function saveMV($section_title, $is_visible = 1) {
        try {
            // Check if a record exists
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_mv");
            $exists = $stmt->fetchColumn() > 0;

            if ($exists) {
                $stmt = $this->pdo->prepare("UPDATE af_page_mv SET 
                    section_title = ?, 
                    is_visible = ? 
                    WHERE id = (SELECT id FROM af_page_mv LIMIT 1)");
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO af_page_mv 
                    (section_title, is_visible) 
                    VALUES (?, ?)");
            }
            
            $success = $stmt->execute([$section_title, $is_visible]);
            if (!$success) {
                error_log("Database error in saveMV: " . implode(", ", $stmt->errorInfo()));
            }
            return $success;
        } catch (PDOException $e) {
            error_log("Error saving MV: " . $e->getMessage());
            return false;
        }
    }

    // Get Mission data
    public function getMission() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_page_mission LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && !empty($result['image_url'])) {
                // Adjust image path for display in CMS
                if (!filter_var($result['image_url'], FILTER_VALIDATE_URL) && strpos($result['image_url'], '../') !== 0) {
                    $result['display_image_url'] = '../../' . $result['image_url'];
                } else {
                    $result['display_image_url'] = $result['image_url'];
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting mission: " . $e->getMessage());
            return false;
        }
    }

    // Get Vision data
    public function getVision() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_page_vision LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && !empty($result['image_url'])) {
                // Adjust image path for display in CMS
                if (!filter_var($result['image_url'], FILTER_VALIDATE_URL) && strpos($result['image_url'], '../') !== 0) {
                    $result['display_image_url'] = '../../' . $result['image_url'];
                } else {
                    $result['display_image_url'] = $result['image_url'];
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting vision: " . $e->getMessage());
            return false;
        }
    }

    // Get MV data
    public function getMV() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_page_mv LIMIT 1");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting MV: " . $e->getMessage());
            return false;
        }
    }

    // Upload image
    protected function uploadImage($inputName) {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $file = $_FILES[$inputName];
        $fileName = $file['name'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Generate unique filename
        $newFileName = uniqid() . '.' . $fileType;
        $uploadFile = $this->upload_dir . $newFileName;
        
        // Check if it's an image
        if (!getimagesize($file['tmp_name'])) {
            error_log("File is not an image: " . $fileName);
            return false;
        }
        
        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            return 'uploads/' . $newFileName; // Return relative path for database storage
        }
        
        error_log("Failed to move uploaded file: " . $fileName);
        return false;
    }
}

// Initialize CMS
$pageCMS = new PageCMS();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = '';
    error_log("Form submitted via POST");
    
    if (isset($_POST['save_mission'])) {
        error_log("Mission form submitted");
        error_log("POST data: " . print_r($_POST, true));
        
        $image_url = '';
        
        // Handle file upload
        if (!empty($_FILES['mission_image']['name'])) {
            error_log("Mission image upload attempted");
            $image_url = $pageCMS->uploadImage('mission_image');
            if ($image_url === false) {
                error_log("Image upload failed, using current image");
                $image_url = $_POST['current_mission_image'];
            } else {
                error_log("Image uploaded successfully: " . $image_url);
            }
        } 
        // Handle image URL
        else if (!empty($_POST['mission_image_url'])) {
            error_log("Using provided image URL");
            $image_url = $_POST['mission_image_url'];
        }
        // Keep existing image
        else {
            error_log("Keeping existing image");
            $image_url = $_POST['current_mission_image'];
        }

        // Get the description from the hidden input
        $description = $_POST['mission_description_input'] ?? $_POST['mission_description'] ?? '';
        error_log("Description content: " . substr($description, 0, 100) . "...");

        if ($pageCMS->saveMission(
            $_POST['mission_title'],
            $image_url,
            $description,
            $_POST['show_more_text']
        )) {
            error_log("Mission saved successfully");
            $message = "Mission section updated successfully!";
            // Refresh the page to show updated content
            header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
            exit;
        } else {
            error_log("Error saving mission");
            $message = "Error updating mission section.";
        }
    }
    
    if (isset($_POST['save_vision'])) {
        error_log("Vision form submitted");
        error_log("POST data: " . print_r($_POST, true));
        
        $image_url = '';
        
        // Handle file upload
        if (!empty($_FILES['vision_image']['name'])) {
            error_log("Vision image upload attempted");
            $image_url = $pageCMS->uploadImage('vision_image');
            if ($image_url === false) {
                error_log("Image upload failed, using current image");
                $image_url = $_POST['current_vision_image'];
            } else {
                error_log("Image uploaded successfully: " . $image_url);
            }
        } 
        // Handle image URL
        else if (!empty($_POST['vision_image_url'])) {
            error_log("Using provided image URL");
            $image_url = $_POST['vision_image_url'];
        }
        // Keep existing image
        else {
            error_log("Keeping existing image");
            $image_url = $_POST['current_vision_image'];
        }

        // Get the description from the hidden input
        $description = $_POST['vision_description_input'] ?? $_POST['vision_description'] ?? '';
        error_log("Description content: " . substr($description, 0, 100) . "...");

        if ($pageCMS->saveVision(
            $_POST['vision_title'],
            $image_url,
            $description,
            $_POST['show_more_text']
        )) {
            error_log("Vision saved successfully");
            $message = "Vision section updated successfully!";
            // Refresh the page to show updated content
            header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
            exit;
        } else {
            error_log("Error saving vision");
            $message = "Error updating vision section.";
        }
    }
    
    if (isset($_POST['save_mv'])) {
        error_log("MV form submitted");
        error_log("POST data: " . print_r($_POST, true));
        
        if ($pageCMS->saveMV(
            $_POST['section_title'],
            isset($_POST['is_visible']) ? 1 : 0
        )) {
            error_log("MV saved successfully");
            $message = "Mission & Vision section updated successfully!";
            // Refresh the page to show updated content
            header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
            exit;
        } else {
            error_log("Error saving MV");
            $message = "Error updating Mission & Vision section.";
        }
    }
}

// Get current data
$mission = $pageCMS->getMission() ?: [];
$vision = $pageCMS->getVision() ?: [];
$mv = $pageCMS->getMV() ?: [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mission and Vision CMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Base styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Editor styles */
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

        /* Form styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Image preview */
        .image-preview-container {
            margin: 15px 0;
        }

        .image-preview {
            max-width: 300px;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none;
        }

        /* Tab system */
        .tab-container {
            margin: 20px 0;
        }

        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: -1px;
        }

        .tab-button {
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
            background: #f5f5f5;
            cursor: pointer;
        }

        .tab-button.active {
            background: white;
            border-bottom: 1px solid white;
        }

        .tab-content {
            display: none;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 4px 4px 4px;
        }

        .tab-content.active {
            display: block;
        }

        /* Submit button */
        .submit-btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .submit-btn:hover {
            background: #0056b3;
        }

        /* Message styles */
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Add these new styles */
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

        /* Font size select styles */
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
    </style>
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
            const editors = document.querySelectorAll('.editor');
            
            editors.forEach(editor => {
                // Update toolbar state when selection changes
                editor.addEventListener('keyup', updateToolbarState);
                editor.addEventListener('mouseup', updateToolbarState);
            });
            
            // Sync editor content to hidden input before form submission
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const editor = this.querySelector('.editor');
                    const hiddenInput = this.querySelector('input[type="hidden"]');
                    if (editor && hiddenInput) {
                        hiddenInput.value = editor.innerHTML;
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Mission and Vision CMS</h1>
        <?php if (isset($message)): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Mission & Vision Main Settings -->
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="section_title">Section Title:</label>
                <input type="text" id="section_title" name="section_title" value="<?php echo htmlspecialchars($mv['section_title'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_visible" <?php echo ($mv['is_visible'] ?? 1) ? 'checked' : ''; ?>>
                    Show Mission & Vision Section
                </label>
            </div>

            <button type="submit" name="save_mv" class="submit-btn">Save Main Settings</button>
        </form>

        <!-- Mission Section -->
        <div class="tab-container">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="switchTab(this, 'edit-mission')">Edit Mission</button>
                <button class="tab-button" onclick="switchTab(this, 'preview-mission')">Preview Mission</button>
            </div>

            <div id="edit-mission" class="tab-content active">
                <form method="POST" enctype="multipart/form-data" name="mission_form" onsubmit="return prepareSubmission(this);">
                    <div class="form-group">
                        <label for="mission_title">Mission Title:</label>
                        <input type="text" id="mission_title" name="mission_title" value="<?php echo htmlspecialchars($mission['section_title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Mission Image:</label>
                        <div class="tab-container">
                            <div class="tab-buttons">
                                <button type="button" class="tab-button active" onclick="switchTab(this, 'mission-file')">Upload File</button>
                                <button type="button" class="tab-button" onclick="switchTab(this, 'mission-url')">Image URL</button>
                            </div>

                            <div id="mission-file-tab" class="tab-content active">
                                <input type="file" id="mission_image" name="mission_image" accept="image/*" onchange="previewImage(this, 'mission-image-preview')">
                            </div>

                            <div id="mission-url-tab" class="tab-content">
                                <input type="url" id="mission_image_url" name="mission_image_url" placeholder="Enter image URL">
                            </div>

                            <input type="hidden" name="current_mission_image" value="<?php echo htmlspecialchars($mission['image_url'] ?? ''); ?>">
                            
                            <div class="preview-section">
                                <h3><i class="fas fa-image"></i> Current Image Preview</h3>
                                <div class="image-preview-container">
                                    <?php if (!empty($mission['image_url'])): ?>
                                        <label class="image-preview-label">Current Image:</label>
                                        <img src="<?php echo htmlspecialchars($mission['display_image_url']); ?>" 
                                             alt="Current mission image" 
                                             class="current-image-preview"
                                             onerror="this.src='../../imgs/cte.jpg';">
                                    <?php else: ?>
                                        <div class="no-image-placeholder">
                                            <i class="fas fa-image"></i>
                                            <p>No image currently set</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div id="mission-image-preview" class="image-preview"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mission_description">Mission Description:</label>
                        <div id="mission_description" class="editor" contenteditable="true"><?php echo $mission['description'] ?? ''; ?></div>
                        <input type="hidden" id="mission_description_input" name="mission_description_input">
                    </div>

                    <div class="form-group">
                        <label for="mission_show_more">Show More Text:</label>
                        <input type="text" id="mission_show_more" name="show_more_text" value="<?php echo htmlspecialchars($mission['show_more_text'] ?? ''); ?>" required>
                    </div>

                    <button type="submit" name="save_mission" class="submit-btn">Save Mission</button>
                </form>
            </div>

            <div id="preview-mission" class="tab-content">
                <h2 id="preview-mission-title"></h2>
                <div id="preview-mission-content"></div>
                <div id="preview-mission-image"></div>
                <div id="preview-mission-show-more"></div>
            </div>
        </div>

        <!-- Vision Section -->
        <div class="tab-container">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="switchTab(this, 'edit-vision')">Edit Vision</button>
                <button class="tab-button" onclick="switchTab(this, 'preview-vision')">Preview Vision</button>
            </div>

            <div id="edit-vision" class="tab-content active">
                <form method="POST" enctype="multipart/form-data" name="vision_form" onsubmit="return prepareSubmission(this);">
                    <div class="form-group">
                        <label for="vision_title">Vision Title:</label>
                        <input type="text" id="vision_title" name="vision_title" value="<?php echo htmlspecialchars($vision['section_title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Vision Image:</label>
                        <div class="tab-container">
                            <div class="tab-buttons">
                                <button type="button" class="tab-button active" onclick="switchTab(this, 'vision-file')">Upload File</button>
                                <button type="button" class="tab-button" onclick="switchTab(this, 'vision-url')">Image URL</button>
                            </div>

                            <div id="vision-file-tab" class="tab-content active">
                                <input type="file" id="vision_image" name="vision_image" accept="image/*" onchange="previewImage(this, 'vision-image-preview')">
                            </div>

                            <div id="vision-url-tab" class="tab-content">
                                <input type="url" id="vision_image_url" name="vision_image_url" placeholder="Enter image URL">
                            </div>

                            <input type="hidden" name="current_vision_image" value="<?php echo htmlspecialchars($vision['image_url'] ?? ''); ?>">
                            
                            <div class="preview-section">
                                <h3><i class="fas fa-image"></i> Current Image Preview</h3>
                                <div class="image-preview-container">
                                    <?php if (!empty($vision['image_url'])): ?>
                                        <label class="image-preview-label">Current Image:</label>
                                        <img src="<?php echo htmlspecialchars($vision['display_image_url']); ?>" 
                                             alt="Current vision image" 
                                             class="current-image-preview"
                                             onerror="this.src='../../imgs/cte-field.png';">
                                    <?php else: ?>
                                        <div class="no-image-placeholder">
                                            <i class="fas fa-image"></i>
                                            <p>No image currently set</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div id="vision-image-preview" class="image-preview"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="vision_description">Vision Description:</label>
                        <div id="vision_description" class="editor" contenteditable="true"><?php echo $vision['description'] ?? ''; ?></div>
                        <input type="hidden" id="vision_description_input" name="vision_description_input">
                    </div>

                    <div class="form-group">
                        <label for="vision_show_more">Show More Text:</label>
                        <input type="text" id="vision_show_more" name="show_more_text" value="<?php echo htmlspecialchars($vision['show_more_text'] ?? ''); ?>" required>
                    </div>

                    <button type="submit" name="save_vision" class="submit-btn">Save Vision</button>
                </form>
            </div>

            <div id="preview-vision" class="tab-content">
                <h2 id="preview-vision-title"></h2>
                <div id="preview-vision-content"></div>
                <div id="preview-vision-image"></div>
                <div id="preview-vision-show-more"></div>
            </div>
        </div>
    </div>

    <script>
        // Function to update hidden input before form submission
        function updateHiddenInput(editorId) {
            const editor = document.getElementById(editorId);
            const hiddenInput = document.getElementById(editorId + '_input');
            if (editor && hiddenInput) {
                hiddenInput.value = editor.innerHTML;
            }
        }

        // Function to handle image preview
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-image';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Function to switch between tabs
        function switchTab(button, tabId) {
            // Remove active class from all buttons in the same container
            const container = button.closest('.tab-container');
            container.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            container.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            document.getElementById(tabId + '-tab').classList.add('active');
        }

        // Rich text editor functions
        function execCommand(command) {
            document.execCommand(command, false, null);
        }

        function execCommandWithArg(command, arg) {
            document.execCommand(command, false, arg);
        }

        function createLink() {
            const url = prompt('Enter URL:', 'http://');
            if (url) {
                document.execCommand('createLink', false, url);
            }
        }

        // Function to handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            const missionForm = document.querySelector('form[name="mission_form"]');
            const visionForm = document.querySelector('form[name="vision_form"]');

            if (missionForm) {
                missionForm.addEventListener('submit', function(e) {
                    updateHiddenInput('mission_description');
                });
            }

            if (visionForm) {
                visionForm.addEventListener('submit', function(e) {
                    updateHiddenInput('vision_description');
                });
            }
        });

        function prepareSubmission(form) {
            // For Mission form
            if (form.querySelector('#mission_description')) {
                const missionEditor = document.getElementById('mission_description');
                const missionInput = document.getElementById('mission_description_input');
                missionInput.value = missionEditor.innerHTML;
            }
            
            // For Vision form
            if (form.querySelector('#vision_description')) {
                const visionEditor = document.getElementById('vision_description');
                const visionInput = document.getElementById('vision_description_input');
                visionInput.value = visionEditor.innerHTML;
            }
            
            return true;
        }
    </script>
</body>
</html>
