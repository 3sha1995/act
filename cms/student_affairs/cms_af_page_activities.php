<?php
require_once __DIR__ . '/../db_connection.php';

class ActivitiesCMS {
    private $pdo;
    private $upload_dir = '../../uploads/activities/';

    public function __construct($pdo) {
        $this->pdo = $pdo;
        // Create upload directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
        
        // Create necessary database tables if they don't exist
        $this->ensureTablesExist();
    }
    
    private function ensureTablesExist() {
        try {
            // Create settings table
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS af_page_activities_settings (
                id INT PRIMARY KEY DEFAULT 1,
                section_title VARCHAR(255) DEFAULT 'ACTIVITIES CALENDAR',
                is_visible TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            
            // Insert default settings if the table is empty
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_activities_settings");
            if ($stmt->fetchColumn() == 0) {
                $this->pdo->exec("INSERT INTO af_page_activities_settings (id, section_title, is_visible) 
                                VALUES (1, 'ACTIVITIES CALENDAR', 1)");
            }
            
            // Create activities table
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS af_page_activities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                section_title VARCHAR(255),
                event_title VARCHAR(255) NOT NULL,
                event_description TEXT,
                event_date DATE,
                event_time TIME,
                event_location VARCHAR(255),
                event_image VARCHAR(255),
                is_visible TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            
        } catch (PDOException $e) {
            error_log("Error ensuring tables exist: " . $e->getMessage());
        }
    }

    public function getSection() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_page_activities_settings WHERE id = 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Return default values if no record found
            if (!$result) {
                return [
                    'section_title' => 'ACTIVITIES CALENDAR',
                    'is_visible' => 1
                ];
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting activities section: " . $e->getMessage());
            
            // Return default values if there was an error
            return [
                'section_title' => 'ACTIVITIES CALENDAR',
                'is_visible' => 1
            ];
        }
    }

    public function updateSection($title, $is_visible) {
        // Check if section exists
        $stmt = $this->pdo->prepare("SELECT * FROM af_page_activities_settings WHERE id = 1");
        $stmt->execute();
        
        if ($stmt->fetch()) {
            // Update existing section
            $stmt = $this->pdo->prepare("UPDATE af_page_activities_settings SET section_title = ?, is_visible = ? WHERE id = 1");
            $stmt->execute([$title, $is_visible]);
        } else {
            // Insert new section
            $stmt = $this->pdo->prepare("INSERT INTO af_page_activities_settings (section_title, is_visible) VALUES (?, ?)");
            $stmt->execute([$title, $is_visible]);
        }
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
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_page_activities ORDER BY event_date DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching activities: " . $e->getMessage());
            return []; // Return empty array if there's an error
        }
    }

    public function addActivity($data) {
        // Handle image upload or URL
        if (isset($_FILES['event_image_file']) && $_FILES['event_image_file']['size'] > 0) {
            $data['event_image'] = $this->handleImageUpload($_FILES['event_image_file']);
        } elseif (isset($_POST['event_image_url']) && !empty($_POST['event_image_url'])) {
            $data['event_image'] = $_POST['event_image_url'];
        }

        // Get section title from sections table
        $section = $this->getSection();
        $data['section_title'] = $section['section_title'];

        $sql = "INSERT INTO af_page_activities (section_title, event_title, event_description, event_date, event_time, event_location, event_image, is_visible)
                VALUES (:section_title, :event_title, :event_description, :event_date, :event_time, :event_location, :event_image, :is_visible)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    public function updateActivity($id, $data) {
        // Handle image upload or URL
        if (isset($_FILES['event_image_file']) && $_FILES['event_image_file']['size'] > 0) {
            $data['event_image'] = $this->handleImageUpload($_FILES['event_image_file']);
        } elseif (isset($_POST['event_image_url']) && !empty($_POST['event_image_url'])) {
            $data['event_image'] = $_POST['event_image_url'];
        }

        // Get section title from sections table
        $section = $this->getSection();
        $data['section_title'] = $section['section_title'];

        $sql = "UPDATE af_page_activities 
                SET section_title = :section_title, 
                event_title = :event_title,
                event_description = :event_description,
                event_date = :event_date,
                event_time = :event_time,
                    event_location = :event_location";

        if (isset($data['event_image'])) {
            $sql .= ", event_image = :event_image";
        }

        $sql .= ", is_visible = :is_visible WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
    }

    public function deleteActivity($id) {
        // Get the image path before deleting
        $stmt = $this->pdo->prepare("SELECT event_image FROM af_page_activities WHERE id = ?");
        $stmt->execute([$id]);
        $activity = $stmt->fetch();

        // Delete the activity
        $stmt = $this->pdo->prepare("DELETE FROM af_page_activities WHERE id = ?");
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
        $stmt = $this->pdo->prepare("UPDATE af_page_activities SET is_visible = ? WHERE id = ?");
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
    $data = [
        'section_title' => $_POST['section_title'],
        'event_title' => $_POST['event_title'],
        'event_description' => $_POST['event_description'],
        'event_date' => $_POST['event_date'],
        'event_time' => $_POST['event_time'],
        'event_location' => $_POST['event_location'],
        'is_visible' => isset($_POST['is_visible']) ? 1 : 0
    ];

    if (isset($_POST['add'])) {
        $cms->addActivity($data);
    } elseif (isset($_POST['update'])) {
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

$activities = $cms->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Affairs – Activities CMS</title>
    <link rel="stylesheet" href="student_affairs_sidebar.css">
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

        @media (max-width: 768px) {
            body {
                margin-left: 0;
                padding: 0 15px;
            }
            body.sidebar-open {
                margin-left: 250px;
            }
        }

        .content-wrapper {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.1);
        }

        form, table {
            margin: 25px auto;
            width: 95%;
        }

        input, textarea, select {
            width: 100%;
            margin: 8px 0;
            padding: 12px;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            transition: border-color 0.2s ease;
            background: #fff;
            font-size: 1rem;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }

        th, td {
            border: 1px solid #bee3f8;
            padding: 14px 18px;
            text-align: left;
        }

        th {
            background-color: #ebf8ff;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f0f5ff;
        }

        .section-header {
            background: linear-gradient(to right, #ebf8ff, #f0f7ff);
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #bee3f8;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
        }

        .section-title {
            font-size: 28px;
            margin: 0;
            display: inline-block;
            color: #2c5282;
            font-weight: 600;
        }

        .section-controls {
            float: right;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-controls button {
            padding: 8px 16px;
            background-color: #3182ce;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            font-weight: 500;
        }

        .section-controls button:hover {
            background-color: #2c5282;
        }

        .visibility-status {
            color: #2c5282;
            margin-left: 15px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .visibility-status::before {
            content: "•";
            font-size: 1.5em;
            color: #3182ce;
        }

        /* Section styles */
        .section {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }

        h2, h3 {
            color: #2c5282;
            font-weight: 600;
        }

        h2 {
            font-size: 1.75rem;
            margin-top: 0;
            margin-bottom: 1rem;
        }

        h3 {
            font-size: 1.25rem;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Button styles */
        button, .btn, .button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .button-primary, .btn-primary {
            background: #3182ce;
            color: white;
        }

        .button-danger, .btn-danger {
            background: #e53e3e;
            color: white;
        }

        .button:hover, .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.13);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(26, 54, 93, 0.5);
            backdrop-filter: blur(4px);
            overflow-y: auto;
            padding: 20px 0;
        }

        .modal-content {
            background-color: #ffffff;
            margin: 2% auto;
            padding: 30px;
            border: none;
            width: 90%;
            max-width: 600px;
            border-radius: 16px;
            position: relative;
            box-shadow: 0 10px 25px rgba(49, 130, 206, 0.13);
            border: 1px solid #bee3f8;
            max-height: 85vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px 30px;
            background: #ebf8ff;
            border-radius: 16px 16px 0 0;
            border-bottom: 1px solid #bee3f8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -30px -30px 20px;
        }

        .modal-header h3 {
            margin: 0;
            color: #2c5282;
            font-size: 1.5rem;
        }

        .modal-footer {
            padding: 20px 30px;
            background: #ebf8ff;
            border-radius: 0 0 16px 16px;
            border-top: 1px solid #bee3f8;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin: 20px -30px -30px;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: normal;
            cursor: pointer;
            color: #4a5568;
            transition: color 0.2s ease, background 0.2s;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close:hover {
            color: #2c5282;
            background-color: #ebf8ff;
        }

        /* Form group styles */
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

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
        }
        
        .status-active {
            background-color: #c6f6d5;
            color: #22543d;
        }
        
        .status-inactive {
            background-color: #fed7d7;
            color: #822727;
        }

        /* Switch toggle */
        .switch-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.95rem;
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

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions a, .actions button {
            margin: 0;
            color: #3182ce;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .actions a:hover, .actions button:hover {
            color: #2c5282;
        }

        .tab-container {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #bee3f8;
        }
        
        .tab-button {
            background: #f0f7ff;
            border: 1px solid #bee3f8;
            border-bottom: none;
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
            font-weight: 500;
            color: #4a5568;
            margin-right: 5px;
        }
        
        .tab-button.active {
            background: #ebf8ff;
            color: #2c5282;
            border-color: #93c5fd;
        }
        
        .tab-content {
            display: none;
            padding: 15px 0;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
<?php include 'student_affairs_sidebar.php'; ?>

<div class="content-wrapper">

    <!-- Section Management -->
    <div class="section">
        <div class="section-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="section-title"><?= htmlspecialchars($section['section_title']) ?></h2>
                <div class="section-controls">
                    <button onclick="showSectionModal()">Edit Section</button>
                    <span class="status-badge <?= $section['is_visible'] ? 'status-active' : 'status-inactive' ?>">
                        <?= $section['is_visible'] ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Section Edit Modal -->
        <div id="sectionModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Section Settings</h3>
                    <button onclick="hideSectionModal()" class="close">&times;</button>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label>Section Title:</label>
                        <input type="text" name="section_title" value="<?= htmlspecialchars($section['section_title']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="switch-label">
                            Show this section on the website
                            <label class="switch">
                                <input type="checkbox" name="section_visible" <?= $section['is_visible'] ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="submit" name="update_section" class="button button-primary">Update Section</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Activity Button -->
        <div style="text-align: right; margin: 20px;">
            <button onclick="showAddModal()" class="button button-primary">Add New Activity</button>
        </div>

        <!-- Add Activity Modal -->
        <div id="addActivityModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add New Activity</h3>
                    <button onclick="hideAddModal()" class="close">&times;</button>
                </div>
                <form method="POST" id="addActivityForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Event Title:</label>
                        <input type="text" name="event_title" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Event Description:</label>
                        <textarea name="event_description" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Date:</label>
                        <input type="date" name="event_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Time:</label>
                        <input type="time" name="event_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Location:</label>
                        <input type="text" name="event_location" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Image:</label>
                        <div class="tab-container">
                            <button type="button" class="tab-button active" onclick="switchImageTab('add', 'file')">Upload File</button>
                            <button type="button" class="tab-button" onclick="switchImageTab('add', 'url')">Image URL</button>
                        </div>
                        
                        <div id="add_file_input" class="tab-content active">
                            <input type="file" name="event_image_file" accept="image/*" onchange="previewImage(this, 'add')">
                        </div>
                        
                        <div id="add_url_input" class="tab-content">
                            <input type="text" name="event_image_url" placeholder="Enter image URL" onchange="previewImageUrl(this, 'add')">
                        </div>
                        
                        <div id="add_image_preview" class="mt-3"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="switch-label">
                            Visibility
                            <label class="switch">
                                <input type="checkbox" name="is_visible" checked>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="submit" name="add" class="button button-primary">Add Activity</button>
                    </div>
                </form>
            </div>
        </div>

        <h3>All Activities</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Section Title</th>
                <th>Title</th>
                <th>Date</th>
                <th>Time</th>
                <th>Location</th>
                <th>Image</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($activities as $a): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= htmlspecialchars($a['section_title']) ?></td>
                <td><?= htmlspecialchars($a['event_title']) ?></td>
                <td><?= $a['event_date'] ?></td>
                <td><?= $a['event_time'] ?></td>
                <td><?= htmlspecialchars($a['event_location']) ?></td>
                <td>
                    <?php if ($a['event_image']): ?>
                        <img src="<?= str_starts_with($a['event_image'], 'http') ? $a['event_image'] : '../../' . $a['event_image'] ?>" 
                             alt="Event Image" style="max-width: 100px; max-height: 100px;">
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge <?= $a['is_visible'] ? 'status-active' : 'status-inactive' ?>" 
                          style="cursor: pointer;" 
                          onclick="toggleVisibility(<?= $a['id'] ?>, <?= $a['is_visible'] ? 0 : 1 ?>)">
                        <?= $a['is_visible'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td class="actions">
                    <button onclick='editActivity(<?= json_encode($a) ?>)' class="button-primary">Edit</button>
                    <button onclick="deleteActivity(<?= $a['id'] ?>)" class="button-danger">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Edit Activity Modal -->
    <div id="editActivityModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Activity</h3>
                <button onclick="hideEditModal()" class="close">&times;</button>
            </div>
            <form method="POST" id="editActivityForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                        
                <div class="form-group">
                    <label>Event Title:</label>
                    <input type="text" name="event_title" id="edit_event_title" required>
                </div>
                
                <div class="form-group">
                    <label>Event Description:</label>
                    <textarea name="event_description" id="edit_event_description" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" name="event_date" id="edit_event_date" required>
                </div>
                
                <div class="form-group">
                    <label>Time:</label>
                    <input type="time" name="event_time" id="edit_event_time" required>
                </div>
                
                <div class="form-group">
                    <label>Location:</label>
                    <input type="text" name="event_location" id="edit_event_location" required>
                </div>
                
                <div class="form-group">
                    <label>Image:</label>
                    <div class="tab-container">
                        <button type="button" class="tab-button active" onclick="switchImageTab('edit', 'file')">Upload File</button>
                        <button type="button" class="tab-button" onclick="switchImageTab('edit', 'url')">Image URL</button>
                    </div>
                    
                    <div id="edit_file_input" class="tab-content active">
                        <input type="file" name="event_image_file" id="edit_event_image_file" accept="image/*" onchange="previewImage(this, 'edit')">
                    </div>
                    
                    <div id="edit_url_input" class="tab-content">
                        <input type="text" name="event_image_url" id="edit_event_image_url" placeholder="Enter image URL" onchange="previewImageUrl(this, 'edit')">
                    </div>
                    
                    <div id="edit_image_preview" class="mt-3"></div>
                </div>

                <div class="form-group">
                    <label class="switch-label">
                        Visibility
                        <label class="switch">
                            <input type="checkbox" name="is_visible" id="edit_is_visible">
                            <span class="slider"></span>
                        </label>
                    </label>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" name="update" class="button button-primary">Update Activity</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('addActivityModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hideAddModal() {
            document.getElementById('addActivityModal').style.display = 'none';
            document.body.style.overflow = '';
            resetForm('addActivityForm');
        }

        function editActivity(activity) {
            // Fill the edit form with activity data
            document.getElementById('edit_id').value = activity.id;
            document.getElementById('edit_event_title').value = activity.event_title;
            document.getElementById('edit_event_description').value = activity.event_description;
            document.getElementById('edit_event_date').value = activity.event_date;
            document.getElementById('edit_event_time').value = activity.event_time;
            document.getElementById('edit_event_location').value = activity.event_location;
            document.getElementById('edit_is_visible').checked = activity.is_visible == 1;

            // Handle image preview
            if (activity.event_image) {
                const imageUrl = activity.event_image.startsWith('http') ? activity.event_image : '../../' + activity.event_image;
                document.getElementById('edit_image_preview').innerHTML = 
                    `<img src="${imageUrl}" alt="Preview" style="max-width: 200px; max-height: 200px;">`;
                
                if (activity.event_image.startsWith('http')) {
                    switchImageTab('edit', 'url');
                    document.getElementById('edit_event_image_url').value = activity.event_image;
                } else {
                    switchImageTab('edit', 'file');
                }
            } else {
                document.getElementById('edit_image_preview').innerHTML = '';
            }
            
            // Show the edit modal
            document.getElementById('editActivityModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hideEditModal() {
            document.getElementById('editActivityModal').style.display = 'none';
            document.body.style.overflow = '';
            resetForm('editActivityForm');
        }

        function switchImageTab(form, type) {
            // Get all tab buttons and contents in the form
            const tabButtons = document.querySelectorAll(`#${form}ActivityModal .tab-button`);
            const tabContents = document.querySelectorAll(`#${form}ActivityModal .tab-content`);
            
            // Remove active class from all tabs
            tabButtons.forEach(button => button.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to the selected tab
            document.querySelector(`#${form}ActivityModal .tab-button:nth-child(${type === 'file' ? 1 : 2})`).classList.add('active');
            document.getElementById(`${form}_${type}_input`).classList.add('active');
        }

        function previewImage(input, form) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(`${form}_image_preview`).innerHTML = 
                        `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px;">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewImageUrl(input, form) {
            if (input.value) {
                document.getElementById(`${form}_image_preview`).innerHTML = 
                    `<img src="${input.value}" alt="Preview" style="max-width: 200px; max-height: 200px;" onerror="this.src='../../imgs/cte.jpg';">`;
            }
        }

        function resetForm(formId) {
            document.getElementById(formId).reset();
            const form = formId === 'addActivityForm' ? 'add' : 'edit';
            document.getElementById(`${form}_image_preview`).innerHTML = '';
            switchImageTab(form, 'file');
        }

        function toggleVisibility(id, isVisible) {
            if (confirm('Are you sure you want to change the visibility of this activity?')) {
                window.location.href = `?toggle=${id}&v=${isVisible}`;
            }
        }

        function deleteActivity(id) {
            if (confirm('Are you sure you want to delete this activity?')) {
                window.location.href = `?delete=${id}`;
            }
        }

        // Section modal functions
        function showSectionModal() {
            document.getElementById('sectionModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hideSectionModal() {
            document.getElementById('sectionModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        // Modal closing handler
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
    </script>
    
    <!-- Include the sidebar persistence script -->
    <script src="student_affairs_persistent.js"></script>
</div>
</body>
</html>
