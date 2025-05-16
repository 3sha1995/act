<?php
require_once __DIR__ . '/../db_connection.php';

class ActivitiesCMS {
    private $pdo;
    private $upload_dir = '../../uploads/activities/';

    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
        
        // Create health_activities table if it doesn't exist
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS health_activities (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            section_title varchar(255) NOT NULL DEFAULT 'ACTIVITIES CALENDAR',
            event_title varchar(255) NOT NULL,
            event_description text NOT NULL,
            event_date date NOT NULL,
            event_time varchar(50) NOT NULL,
            event_location varchar(255) NOT NULL,
            event_image varchar(255) DEFAULT NULL,
            is_visible tinyint(1) NOT NULL DEFAULT 1,
            created_at timestamp NULL DEFAULT current_timestamp(),
            updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            image_path varchar(255) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        
        // Create settings table if it doesn't exist
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS health_activities_settings (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            section_title varchar(255) NOT NULL,
            is_visible tinyint(1) DEFAULT 1,
            created_at timestamp NULL DEFAULT current_timestamp(),
            updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
        )");
        
        // Insert default settings if not exists
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM health_activities_settings");
        if ($stmt->fetchColumn() == 0) {
            $this->pdo->exec("INSERT INTO health_activities_settings (section_title, is_visible) VALUES ('ACTIVITIES CALENDAR', 1)");
        }
    }

    public function getSection() {
        $stmt = $this->pdo->query("SELECT * FROM health_activities_settings WHERE id = 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSection($title, $is_visible) {
        $stmt = $this->pdo->prepare("UPDATE health_activities_settings SET section_title = ?, is_visible = ? WHERE id = 1");
        $stmt->execute([$title, $is_visible]);
    }

    private function handleImageUpload($image_file) {
        if ($image_file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($image_file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $filepath = $this->upload_dir . $filename;
            
            if (move_uploaded_file($image_file['tmp_name'], $filepath)) {
                return 'uploads/activities/' . $filename;
            }
        }
        return null;
    }

    public function fetchAll() {
        $stmt = $this->pdo->query("SELECT * FROM health_activities ORDER BY event_date DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addActivity($data) {
        // Handle image upload or URL
        if (isset($_FILES['event_image_file']) && $_FILES['event_image_file']['size'] > 0) {
            $data['event_image'] = $this->handleImageUpload($_FILES['event_image_file']);
            $data['image_path'] = $data['event_image']; // Store the same path in image_path
        } elseif (isset($_POST['event_image_url']) && !empty($_POST['event_image_url'])) {
            $data['event_image'] = $_POST['event_image_url'];
            $data['image_path'] = $_POST['event_image_url'];
        }

        $sql = "INSERT INTO health_activities (
                event_title, event_description, event_date, 
                event_time, event_location, event_image, 
                is_visible, image_path
            ) VALUES (
                :event_title, :event_description, :event_date, 
                :event_time, :event_location, :event_image, 
                :is_visible, :image_path
            )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':event_title' => $data['event_title'],
            ':event_description' => $data['event_description'],
            ':event_date' => $data['event_date'],
            ':event_time' => $data['event_time'],
            ':event_location' => $data['event_location'],
            ':event_image' => $data['event_image'] ?? null,
            ':is_visible' => $data['is_visible'],
            ':image_path' => $data['image_path'] ?? null
        ]);
    }

    public function updateActivity($id, $data) {
        // Handle image upload or URL
        if (isset($_FILES['event_image_file']) && $_FILES['event_image_file']['size'] > 0) {
            $data['event_image'] = $this->handleImageUpload($_FILES['event_image_file']);
            $data['image_path'] = $data['event_image'];
        } elseif (isset($_POST['event_image_url']) && !empty($_POST['event_image_url'])) {
            $data['event_image'] = $_POST['event_image_url'];
            $data['image_path'] = $_POST['event_image_url'];
        }

        $sql = "UPDATE health_activities SET 
                event_title = :event_title,
                event_description = :event_description,
                event_date = :event_date,
                event_time = :event_time,
                event_location = :event_location,
                is_visible = :is_visible";

        if (isset($data['event_image'])) {
            $sql .= ", event_image = :event_image, image_path = :image_path";
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        
        $params = [
            ':event_title' => $data['event_title'],
            ':event_description' => $data['event_description'],
            ':event_date' => $data['event_date'],
            ':event_time' => $data['event_time'],
            ':event_location' => $data['event_location'],
            ':is_visible' => $data['is_visible'],
            ':id' => $id
        ];

        if (isset($data['event_image'])) {
            $params[':event_image'] = $data['event_image'];
            $params[':image_path'] = $data['image_path'];
        }

        $stmt->execute($params);
    }

    public function deleteActivity($id) {
        // Get the image path before deleting
        $stmt = $this->pdo->prepare("SELECT event_image FROM health_activities WHERE id = ?");
        $stmt->execute([$id]);
        $activity = $stmt->fetch();

        // Delete the activity
        $stmt = $this->pdo->prepare("DELETE FROM health_activities WHERE id = ?");
        $stmt->execute([$id]);

        // Delete the image file if it exists and is a local file
        if ($activity && $activity['event_image'] && !str_starts_with($activity['event_image'], 'http')) {
            $filepath = '../../' . $activity['event_image'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    public function toggleVisibility($id, $visibility) {
        $stmt = $this->pdo->prepare("UPDATE health_activities SET is_visible = ? WHERE id = ?");
        $stmt->execute([$visibility, $id]);
    }
}

$cms = new ActivitiesCMS($pdo);

// Handle section update
if (isset($_POST['update_section'])) {
    $cms->updateSection(
        $_POST['section_title'],
        isset($_POST['section_visible']) ? 1 : 0
    );
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get current section data
$section = $cms->getSection() ?: ['section_title' => 'ACTIVITIES CALENDAR', 'is_visible' => 1];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add']) || isset($_POST['update'])) {
        $data = [
            'event_title' => $_POST['event_title'],
            'event_description' => $_POST['event_description'],
            'event_date' => $_POST['event_date'],
            'event_time' => $_POST['event_time'],
            'event_location' => $_POST['event_location'],
            'is_visible' => isset($_POST['is_visible']) ? 1 : 0
        ];

        if (isset($_POST['add'])) {
            $cms->addActivity($data);
        } else {
            $cms->updateActivity($_POST['id'], $data);
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_GET['delete'])) {
        $cms->deleteActivity($_GET['delete']);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_GET['toggle'])) {
        $cms->toggleVisibility($_GET['toggle'], $_GET['v']);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

$activities = $cms->fetchAll();
?>
