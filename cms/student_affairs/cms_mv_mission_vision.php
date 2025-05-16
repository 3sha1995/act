<?php
require_once '../db_connection.php';

class MissionVisionCMS {
    private $pdo;
    private $upload_dir;
    private $last_error;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->upload_dir = '../../uploads/mission_vision/';
        $this->last_error = '';
        
        // Create uploads directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            if (!mkdir($this->upload_dir, 0777, true)) {
                $this->last_error = "Failed to create upload directory: " . $this->upload_dir;
                error_log($this->last_error);
            }
        }
        
        $this->ensureTablesExist();
    }

    public function getLastError() {
        return $this->last_error;
    }

    private function ensureTablesExist() {
        try {
            // Create mission vision table if it doesn't exist
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS af_page_mv (
                id INT AUTO_INCREMENT PRIMARY KEY,
                section_title VARCHAR(255) DEFAULT 'Mission & Vision',
                mission_title VARCHAR(255) DEFAULT 'Our Mission',
                mission_image_url VARCHAR(255) DEFAULT 'https://via.placeholder.com/600x400',
                mission_description TEXT,
                mission_show_more_text VARCHAR(255),
                vision_title VARCHAR(255) DEFAULT 'Our Vision',
                vision_image_url VARCHAR(255) DEFAULT 'https://via.placeholder.com/600x400',
                vision_description TEXT,
                vision_show_more_text VARCHAR(255),
                is_visible TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            
            // Insert default data if table is empty
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_mv");
            if ($stmt->fetchColumn() == 0) {
                $this->pdo->exec("INSERT INTO af_page_mv (
                    section_title, mission_title, mission_image_url, mission_description,
                    vision_title, vision_image_url, vision_description, is_visible
                ) VALUES (
                    'Mission & Vision', 
                    'Our Mission', 
                    'https://via.placeholder.com/600x400',
                    'Our mission is to support students in their academic and personal development.',
                    'Our Vision',
                    'https://via.placeholder.com/600x400',
                    'Our vision is to create a thriving community of empowered students.',
                    1
                )");
            }
        } catch (PDOException $e) {
            $this->last_error = "Error ensuring tables exist: " . $e->getMessage();
            error_log($this->last_error);
        }
    }

    // Fetch current MV record
    public function getMV() {
        try {
            $sql = "SELECT * FROM af_page_mv LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // Return default values if no record found
                return [
                    'id' => 1,
                    'section_title' => 'Mission & Vision',
                    'mission_title' => 'Our Mission',
                    'mission_image_url' => 'https://via.placeholder.com/600x400',
                    'mission_description' => 'Our mission is to support students in their academic and personal development.',
                    'mission_show_more_text' => '',
                    'vision_title' => 'Our Vision',
                    'vision_image_url' => 'https://via.placeholder.com/600x400',
                    'vision_description' => 'Our vision is to create a thriving community of empowered students.',
                    'vision_show_more_text' => '',
                    'is_visible' => 1
                ];
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->last_error = "Error fetching mission vision data: " . $e->getMessage();
            error_log($this->last_error);
            
            // Return default values on error
            return [
                'id' => 1,
                'section_title' => 'Mission & Vision',
                'mission_title' => 'Our Mission',
                'mission_image_url' => 'https://via.placeholder.com/600x400',
                'mission_description' => 'Our mission is to support students in their academic and personal development.',
                'mission_show_more_text' => '',
                'vision_title' => 'Our Vision',
                'vision_image_url' => 'https://via.placeholder.com/600x400',
                'vision_description' => 'Our vision is to create a thriving community of empowered students.',
                'vision_show_more_text' => '',
                'is_visible' => 1
            ];
        }
    }

    // Process image upload
    public function uploadImage($file) {
        if (empty($file['name'])) {
            return '';
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $this->last_error = "Invalid file type. Allowed types: jpg, jpeg, png, gif, webp";
            throw new Exception($this->last_error);
        }
        
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $this->upload_dir . $new_filename;
        
        // Check if upload directory is writable
        if (!is_writable(dirname($upload_path))) {
            $this->last_error = "Upload directory is not writable: " . dirname($upload_path);
            throw new Exception($this->last_error);
        }
        
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            $this->last_error = "Failed to upload file: " . (error_get_last() ? error_get_last()['message'] : 'Unknown error');
            throw new Exception($this->last_error);
        }
        
        return 'uploads/mission_vision/' . $new_filename;
    }

    // Update MV record
    public function updateMV($data) {
        try {
            // Debug info
            error_log("Updating MV data: " . print_r($data, true));
            
            // First, check if updated_at column exists, if not, add it
            try {
                $checkColumn = $this->pdo->query("SHOW COLUMNS FROM af_page_mv LIKE 'updated_at'");
                if ($checkColumn->rowCount() === 0) {
                    // Column doesn't exist, add it
                    error_log("Adding missing updated_at column to af_page_mv table");
                    $this->pdo->exec("ALTER TABLE af_page_mv ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                }
            } catch (PDOException $e) {
                error_log("Error checking/adding updated_at column: " . $e->getMessage());
                // Continue with update without the updated_at column
            }
            
            // Remove the updated_at from the SQL if we couldn't add it
            $sql = "UPDATE af_page_mv SET 
                section_title = :section_title,
                mission_title = :mission_title,
                mission_image_url = :mission_image_url,
                mission_description = :mission_description,
                mission_show_more_text = :mission_show_more_text,
                vision_title = :vision_title,
                vision_image_url = :vision_image_url,
                vision_description = :vision_description,
                vision_show_more_text = :vision_show_more_text,
                is_visible = :is_visible";
                
            // Only include updated_at if we know the column exists
            try {
                $checkColumn = $this->pdo->query("SHOW COLUMNS FROM af_page_mv LIKE 'updated_at'");
                if ($checkColumn->rowCount() > 0) {
                    $sql .= ", updated_at = CURRENT_TIMESTAMP";
                }
            } catch (PDOException $e) {
                // Skip adding updated_at if we can't check
            }
            
            $sql .= " WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $params = [
                ':section_title' => $data['section_title'],
                ':mission_title' => $data['mission_title'],
                ':mission_image_url' => $data['mission_image_url'],
                ':mission_description' => $data['mission_description'],
                ':mission_show_more_text' => $data['mission_show_more_text'] ?? '',
                ':vision_title' => $data['vision_title'],
                ':vision_image_url' => $data['vision_image_url'],
                ':vision_description' => $data['vision_description'],
                ':vision_show_more_text' => $data['vision_show_more_text'] ?? '',
                ':is_visible' => isset($data['is_visible']) ? 1 : 0,
                ':id' => $data['id']
            ];
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $error_info = $stmt->errorInfo();
                $this->last_error = "Database error: " . ($error_info[2] ?? 'Unknown error');
                error_log($this->last_error);
                return false;
            }
            
            // If no rows were affected but no errors occurred, it's because no changes were made
            if ($stmt->rowCount() === 0) {
                error_log("No rows affected by update, but this might be normal if no changes were made.");
            }
            
            return true;
        } catch (PDOException $e) {
            $this->last_error = "Error updating mission vision data: " . $e->getMessage();
            error_log($this->last_error);
            return false;
        }
    }
}

// Init
$cms = new MissionVisionCMS($pdo);
$mvData = $cms->getMV();
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $mission_image_url = $mvData['mission_image_url'];
        $vision_image_url = $mvData['vision_image_url'];
        
        // Process mission image upload if provided
        if (!empty($_FILES['mission_image']['name'])) {
            $mission_image_url = $cms->uploadImage($_FILES['mission_image']);
            error_log("New mission image uploaded: $mission_image_url");
        } else if (isset($_POST['mission_image_existing'])) {
            $mission_image_url = $_POST['mission_image_existing'];
            error_log("Using existing mission image: $mission_image_url");
        }
        
        // Process vision image upload if provided
        if (!empty($_FILES['vision_image']['name'])) {
            $vision_image_url = $cms->uploadImage($_FILES['vision_image']);
            error_log("New vision image uploaded: $vision_image_url");
        } else if (isset($_POST['vision_image_existing'])) {
            $vision_image_url = $_POST['vision_image_existing'];
            error_log("Using existing vision image: $vision_image_url");
        }
        
        $updateData = [
            'id' => $_POST['id'],
            'section_title' => $_POST['section_title'],
            'mission_title' => $_POST['mission_title'],
            'mission_image_url' => $mission_image_url,
            'mission_description' => $_POST['mission_description'],
            'mission_show_more_text' => $_POST['mission_show_more_text'],
            'vision_title' => $_POST['vision_title'],
            'vision_image_url' => $vision_image_url,
            'vision_description' => $_POST['vision_description'],
            'vision_show_more_text' => $_POST['vision_show_more_text'],
            'is_visible' => isset($_POST['is_visible']) ? 1 : 0
        ];

        if ($cms->updateMV($updateData)) {
            $message = "<p class='success'>Updated successfully!</p>";
            $mvData = $cms->getMV(); // Refresh data
        } else {
            $error = $cms->getLastError();
            $message = "<p class='error'>Update failed. " . ($error ? "Error: $error" : "") . "</p>";
            error_log("Mission Vision update failed: " . $error);
        }
    } catch (Exception $e) {
        $message = "<p class='error'>Error: " . $e->getMessage() . "</p>";
        error_log("Exception in Mission Vision update: " . $e->getMessage());
    }
}

// Debug - check if we can connect to DB
try {
    $test = $pdo->query("SELECT 1");
    error_log("Database connection test: " . ($test ? "SUCCESS" : "FAILED"));
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mission & Vision Management</title>
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
            background-color: #ffffff;
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
        
        .section {
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
        
        input[type="text"], textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: #fff;
        }
        
        input[type="text"]:focus, textarea:focus {
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
        
        textarea {
            height: 150px;
            line-height: 1.6;
            resize: vertical;
        }
        
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: #3182ce;
            color: white;
            margin-top: 15px;
        }
        
        button:hover {
            background-color: #2c5282;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.13);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            position: relative;
        }

        .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .alert-danger {
            background-color: #fed7d7;
            color: #822727;
            border: 1px solid #feb2b2;
        }
        
        .success {
            color: #22543d;
            background-color: #c6f6d5;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #9ae6b4;
            margin: 20px 0;
            font-weight: 500;
        }
        
        .error {
            color: #822727;
            background-color: #fed7d7;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #feb2b2;
            margin: 20px 0;
            font-weight: 500;
        }

        h1, h2, h3 {
            color: #2c5282;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 700;
        }

        h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 30px;
        }

        h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 25px;
            color: #2c5282;
        }

        /* Switch toggle */
        .switch-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.95rem;
            margin-bottom: 20px;
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
        
        .image-preview-container {
            margin: 15px 0;
            padding: 15px;
            background: #ebf8ff;
            border: 1px solid #bee3f8;
            border-radius: 8px;
        }
        
        .current-image {
            max-width: 100%;
            height: auto;
            max-height: 300px;
            border-radius: 8px;
            margin: 10px 0;
            border: 1px solid #bee3f8;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
        }
        
        .image-preview {
            max-width: 100%;
            height: auto;
            max-height: 200px;
            border-radius: 8px;
            margin: 10px 0;
            border: 1px solid #bee3f8;
            display: none;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
        }
    </style>
</head>

<body>
<?php include 'student_affairs_sidebar.php'; ?>

<div class="content-wrapper">
    <h1>Mission & Vision Management</h1>
    
    <?php if (!empty($message)): ?>
        <div class="<?= strpos($message, 'success') !== false ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <!-- CMS FORM -->
    <div class="section">
        <h2>Manage Mission and Vision Section</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($mvData['id']) ?>">
        
            <div class="form-group">
                <label>Section Title</label>
                <input type="text" name="section_title" value="<?= htmlspecialchars($mvData['section_title']) ?>" required>
            </div>
        
            <h3>Mission</h3>
            <div class="form-group">
                <label>Mission Title</label>
                <input type="text" name="mission_title" value="<?= htmlspecialchars($mvData['mission_title']) ?>" required>
            </div>
        
            <div class="form-group">
                <label>Current Mission Image</label>
                <div class="image-preview-container">
                    <input type="hidden" name="mission_image_existing" value="<?= htmlspecialchars($mvData['mission_image_url']) ?>">
                    <?php if (!empty($mvData['mission_image_url']) && $mvData['mission_image_url'] != 'https://via.placeholder.com/600x400'): ?>
                        <?php 
                            $imagePath = $mvData['mission_image_url'];
                            if (strpos($imagePath, 'http') !== 0) {
                                $imagePath = '../../' . $imagePath;
                            }
                        ?>
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="Mission Image" class="current-image">
                        <p>Current image: <?= htmlspecialchars($mvData['mission_image_url']) ?></p>
                    <?php else: ?>
                        <img src="https://via.placeholder.com/600x400" alt="Default Mission Image" class="current-image">
                        <p>Using default placeholder image</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Upload New Mission Image</label>
                <input type="file" name="mission_image" accept="image/*" onchange="previewImage(this, 'mission-preview')">
                <img id="mission-preview" class="image-preview" alt="Mission Preview">
                <p class="help-text">Leave empty to keep current image. Accepted formats: JPG, PNG, GIF, WEBP</p>
            </div>
        
            <div class="form-group">
                <label>Mission Description</label>
                <textarea name="mission_description" required><?= htmlspecialchars($mvData['mission_description']) ?></textarea>
            </div>
        
            <div class="form-group">
                <label>Mission Show More Text</label>
                <input type="text" name="mission_show_more_text" value="<?= htmlspecialchars($mvData['mission_show_more_text'] ?? '') ?>">
            </div>
        
            <h3>Vision</h3>
            <div class="form-group">
                <label>Vision Title</label>
                <input type="text" name="vision_title" value="<?= htmlspecialchars($mvData['vision_title']) ?>" required>
            </div>
        
            <div class="form-group">
                <label>Current Vision Image</label>
                <div class="image-preview-container">
                    <input type="hidden" name="vision_image_existing" value="<?= htmlspecialchars($mvData['vision_image_url']) ?>">
                    <?php if (!empty($mvData['vision_image_url']) && $mvData['vision_image_url'] != 'https://via.placeholder.com/600x400'): ?>
                        <?php 
                            $imagePath = $mvData['vision_image_url'];
                            if (strpos($imagePath, 'http') !== 0) {
                                $imagePath = '../../' . $imagePath;
                            }
                        ?>
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="Vision Image" class="current-image">
                        <p>Current image: <?= htmlspecialchars($mvData['vision_image_url']) ?></p>
                    <?php else: ?>
                        <img src="https://via.placeholder.com/600x400" alt="Default Vision Image" class="current-image">
                        <p>Using default placeholder image</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Upload New Vision Image</label>
                <input type="file" name="vision_image" accept="image/*" onchange="previewImage(this, 'vision-preview')">
                <img id="vision-preview" class="image-preview" alt="Vision Preview">
                <p class="help-text">Leave empty to keep current image. Accepted formats: JPG, PNG, GIF, WEBP</p>
            </div>
        
            <div class="form-group">
                <label>Vision Description</label>
                <textarea name="vision_description" required><?= htmlspecialchars($mvData['vision_description']) ?></textarea>
            </div>
        
            <div class="form-group">
                <label>Vision Show More Text</label>
                <input type="text" name="vision_show_more_text" value="<?= htmlspecialchars($mvData['vision_show_more_text'] ?? '') ?>">
            </div>
        
            <div class="form-group">
                <label class="switch-label">
                    Visible
                    <label class="switch">
                        <input type="checkbox" name="is_visible" <?= $mvData['is_visible'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>
        
            <button type="submit">Update</button>
        </form>
    </div>
</div>

<!-- Include the sidebar persistence script -->
<script src="student_affairs_sidebar.js"></script>

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
        } else {
            preview.style.display = 'none';
        }
    }
</script>

</body>
</html>
