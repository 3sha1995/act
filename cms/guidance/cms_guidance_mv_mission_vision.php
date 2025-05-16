<?php
require_once '../db_connection.php';

class MissionVisionCMS {
    private $pdo;
    private static $defaultValues = [
        'id' => 1,
        'section_title' => 'MISSION AND VISION',
        'mission_title' => 'MISSION',
        'mission_image_url' => '../imgs/default.jpg',
        'mission_description' => 'Default mission description',
        'mission_show_more_text' => 'SHOW MORE',
        'vision_title' => 'VISION',
        'vision_image_url' => '../imgs/default.jpg',
        'vision_description' => 'Default vision description',
        'vision_show_more_text' => 'SHOW MORE',
        'is_visible' => 1
    ];

    public function __construct($pdo) {
        if (!$pdo) {
            throw new Exception("Database connection failed");
        }
        $this->pdo = $pdo;
        $this->ensureTableExists();
    }

    public static function getDefaultValues() {
        return self::$defaultValues;
    }

    private function ensureTableExists() {
        try {
            // Drop the table if it exists to ensure clean creation
            $this->pdo->exec("DROP TABLE IF EXISTS guidance_mv");
            
            $sql = "CREATE TABLE guidance_mv (
                id int(11) NOT NULL AUTO_INCREMENT,
                section_title varchar(255) NOT NULL DEFAULT 'MISSION AND VISION',
                mission_title varchar(255) NOT NULL DEFAULT 'MISSION',
                mission_image_url varchar(255) DEFAULT '../imgs/default.jpg',
                mission_description text NOT NULL DEFAULT 'Default mission description',
                mission_show_more_text varchar(255) DEFAULT 'SHOW MORE',
                vision_title varchar(255) NOT NULL DEFAULT 'VISION',
                vision_image_url varchar(255) DEFAULT '../imgs/default.jpg',
                vision_description text NOT NULL DEFAULT 'Default vision description',
                vision_show_more_text varchar(255) DEFAULT 'SHOW MORE',
                is_visible tinyint(1) NOT NULL DEFAULT 1,
                created_at timestamp NULL DEFAULT current_timestamp(),
                updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            
            $this->pdo->exec($sql);
            
            // Insert default record
            $sql = "INSERT INTO guidance_mv (
                id, section_title, mission_title, mission_image_url, 
                mission_description, mission_show_more_text, vision_title, 
                vision_image_url, vision_description, vision_show_more_text, 
                is_visible
            ) VALUES (
                1, 'MISSION AND VISION', 'MISSION', '../imgs/default.jpg',
                'Default mission description', 'SHOW MORE', 'VISION',
                '../imgs/default.jpg', 'Default vision description', 'SHOW MORE',
                1
            )";
            
            $this->pdo->exec($sql);
            
        } catch (Exception $e) {
            error_log("Error in ensureTableExists: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMV() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM guidance_mv WHERE id = 1");
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute select query: " . implode(", ", $stmt->errorInfo()));
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                return self::$defaultValues;
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in getMV: " . $e->getMessage());
            return self::$defaultValues;
        }
    }

    public function updateMV($data) {
        try {
            // Validate required fields
            $requiredFields = ['section_title', 'mission_title', 'vision_title', 'mission_description', 'vision_description'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Required field missing: " . $field);
                }
            }

            // Handle file uploads
            $uploadDir = '../uploads/guidance/';
            $webPath = 'uploads/guidance/';
            
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Failed to create upload directory");
                }
            }

            // Process mission image upload
            if (isset($_FILES['mission_image']) && $_FILES['mission_image']['error'] === UPLOAD_ERR_OK) {
                $missionImageInfo = pathinfo($_FILES['mission_image']['name']);
                $missionImageExt = strtolower($missionImageInfo['extension']);
                
                // Only allow image files
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($missionImageExt, $allowedExtensions)) {
                    throw new Exception('Invalid file type for mission image. Allowed types: ' . implode(', ', $allowedExtensions));
                }
                
                $missionImageName = 'mission_' . time() . '.' . $missionImageExt;
                $missionImagePath = $uploadDir . $missionImageName;
                
                if (move_uploaded_file($_FILES['mission_image']['tmp_name'], $missionImagePath)) {
                    $data['mission_image_url'] = $webPath . $missionImageName;
                    error_log("Mission image uploaded successfully: " . $data['mission_image_url']);
                } else {
                    throw new Exception('Failed to upload mission image');
                }
            }

            // Process vision image upload
            if (isset($_FILES['vision_image']) && $_FILES['vision_image']['error'] === UPLOAD_ERR_OK) {
                $visionImageInfo = pathinfo($_FILES['vision_image']['name']);
                $visionImageExt = strtolower($visionImageInfo['extension']);
                
                // Only allow image files
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($visionImageExt, $allowedExtensions)) {
                    throw new Exception('Invalid file type for vision image. Allowed types: ' . implode(', ', $allowedExtensions));
                }
                
                $visionImageName = 'vision_' . time() . '.' . $visionImageExt;
                $visionImagePath = $uploadDir . $visionImageName;
                
                if (move_uploaded_file($_FILES['vision_image']['tmp_name'], $visionImagePath)) {
                    $data['vision_image_url'] = $webPath . $visionImageName;
                    error_log("Vision image uploaded successfully: " . $data['vision_image_url']);
                } else {
                    throw new Exception('Failed to upload vision image');
                }
            }

            // Debug log
            error_log("Updating MV with data: " . print_r($data, true));

            $sql = "UPDATE guidance_mv SET 
                section_title = :section_title,
                mission_title = :mission_title,
                mission_image_url = :mission_image_url,
                mission_description = :mission_description,
                mission_show_more_text = :mission_show_more_text,
                vision_title = :vision_title,
                vision_image_url = :vision_image_url,
                vision_description = :vision_description,
                vision_show_more_text = :vision_show_more_text,
                is_visible = :is_visible
                WHERE id = 1";

            $stmt = $this->pdo->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare update statement: " . implode(", ", $this->pdo->errorInfo()));
            }

            // Keep existing image URLs if no new images were uploaded
            if (!isset($data['mission_image_url'])) {
                $currentData = $this->getMV();
                $data['mission_image_url'] = $currentData['mission_image_url'];
            }
            if (!isset($data['vision_image_url'])) {
                $currentData = $currentData ?? $this->getMV();
                $data['vision_image_url'] = $currentData['vision_image_url'];
            }

            $params = [
                ':section_title' => $data['section_title'],
                ':mission_title' => $data['mission_title'],
                ':mission_image_url' => $data['mission_image_url'],
                ':mission_description' => $data['mission_description'],
                ':mission_show_more_text' => $data['mission_show_more_text'] ?: 'SHOW MORE',
                ':vision_title' => $data['vision_title'],
                ':vision_image_url' => $data['vision_image_url'],
                ':vision_description' => $data['vision_description'],
                ':vision_show_more_text' => $data['vision_show_more_text'] ?: 'SHOW MORE',
                ':is_visible' => isset($data['is_visible']) ? 1 : 0
            ];

            // Debug log
            error_log("Executing update with params: " . print_r($params, true));

            if (!$stmt->execute($params)) {
                throw new Exception("Failed to execute update: " . implode(", ", $stmt->errorInfo()));
            }

            return true;
        } catch (Exception $e) {
            error_log("Error in updateMV: " . $e->getMessage());
            throw $e;
        }
    }
}

// Initialize and handle form
try {
    if (!isset($pdo)) {
        throw new Exception("Database connection not available");
    }
    
    $cms = new MissionVisionCMS($pdo);
    $mvData = $cms->getMV();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Collect form data
        $updateData = [
            'section_title' => $_POST['section_title'] ?? '',
            'mission_title' => $_POST['mission_title'] ?? '',
            'mission_image_url' => $mvData['mission_image_url'], // Keep existing image if no new upload
            'mission_description' => $_POST['mission_description'] ?? '',
            'mission_show_more_text' => $_POST['mission_show_more_text'] ?? '',
            'vision_title' => $_POST['vision_title'] ?? '',
            'vision_image_url' => $mvData['vision_image_url'], // Keep existing image if no new upload
            'vision_description' => $_POST['vision_description'] ?? '',
            'vision_show_more_text' => $_POST['vision_show_more_text'] ?? '',
            'is_visible' => isset($_POST['is_visible']) ? 1 : 0
        ];

        try {
            if ($cms->updateMV($updateData)) {
                echo "<div class='success-message'>Updated successfully!</div>";
                $mvData = $cms->getMV(); // Refresh data
            }
        } catch (Exception $e) {
            error_log("Update failed: " . $e->getMessage());
            echo "<div class='error-message'>Update failed: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
} catch (Exception $e) {
    error_log("CMS Error: " . $e->getMessage());
    echo "<div class='error-message'>An error occurred: " . htmlspecialchars($e->getMessage()) . "</div>";
    $mvData = MissionVisionCMS::getDefaultValues();
}
?>

<!-- CMS FORM -->
<h2>Manage Mission and Vision Section</h2>
<form method="POST" class="cms-form" enctype="multipart/form-data">
    <div class="form-group">
        <label>Section Title</label>
        <input type="text" name="section_title" value="<?= htmlspecialchars($mvData['section_title']) ?>" required>
    </div>

    <fieldset>
        <legend>Mission</legend>
        <div class="form-group">
            <label>Mission Title</label>
            <input type="text" name="mission_title" value="<?= htmlspecialchars($mvData['mission_title']) ?>" required>
        </div>

        <div class="form-group">
            <label>Mission Image</label>
            <div class="image-upload-container">
                <?php if (!empty($mvData['mission_image_url'])): ?>
                    <div class="current-image">
                        <img src="<?= htmlspecialchars('../' . $mvData['mission_image_url']) ?>" alt="Current Mission Image" style="max-width: 200px;">
                        <p>Current Image</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="mission_image" accept="image/*" class="image-upload">
                <p class="file-info">Recommended size: 800x600 pixels. Max file size: 2MB</p>
            </div>
        </div>

        <div class="form-group">
            <label>Mission Description</label>
            <textarea name="mission_description" required><?= htmlspecialchars($mvData['mission_description']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Mission Show More Text</label>
            <input type="text" name="mission_show_more_text" value="<?= htmlspecialchars($mvData['mission_show_more_text']) ?>">
        </div>
    </fieldset>

    <fieldset>
        <legend>Vision</legend>
        <div class="form-group">
            <label>Vision Title</label>
            <input type="text" name="vision_title" value="<?= htmlspecialchars($mvData['vision_title']) ?>" required>
        </div>

        <div class="form-group">
            <label>Vision Image</label>
            <div class="image-upload-container">
                <?php if (!empty($mvData['vision_image_url'])): ?>
                    <div class="current-image">
                        <img src="<?= htmlspecialchars('../' . $mvData['vision_image_url']) ?>" alt="Current Vision Image" style="max-width: 200px;">
                        <p>Current Image</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="vision_image" accept="image/*" class="image-upload">
                <p class="file-info">Recommended size: 800x600 pixels. Max file size: 2MB</p>
            </div>
        </div>

        <div class="form-group">
            <label>Vision Description</label>
            <textarea name="vision_description" required><?= htmlspecialchars($mvData['vision_description']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Vision Show More Text</label>
            <input type="text" name="vision_show_more_text" value="<?= htmlspecialchars($mvData['vision_show_more_text']) ?>">
        </div>
    </fieldset>

    <div class="form-group">
        <label class="checkbox-label">
            <input type="checkbox" name="is_visible" <?= $mvData['is_visible'] ? 'checked' : '' ?>> Show this section
        </label>
    </div>

    <button type="submit" class="submit-button">Update</button>
</form>

<style>
.cms-form {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

input[type="text"], textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}

textarea {
    min-height: 100px;
}

fieldset {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

legend {
    font-weight: bold;
    padding: 0 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
}

.submit-button {
    background: #B32134;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.submit-button:hover {
    background: #8e1a29;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.image-upload-container {
    margin-top: 10px;
}

.current-image {
    margin-bottom: 10px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    display: inline-block;
}

.current-image img {
    display: block;
    margin-bottom: 5px;
}

.current-image p {
    margin: 0;
    font-size: 0.9em;
    color: #666;
}

.image-upload {
    margin-top: 10px;
}

.file-info {
    margin-top: 5px;
    font-size: 0.9em;
    color: #666;
}
</style>
