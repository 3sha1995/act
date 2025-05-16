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
        
        // Drop and recreate activities table
        try {
            $this->pdo->exec("DROP TABLE IF EXISTS guidance_activities");
            $sql = "CREATE TABLE guidance_activities (
                id int(11) NOT NULL AUTO_INCREMENT,
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
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            $this->pdo->exec($sql);
            error_log("Successfully created guidance_activities table");

            // Drop and recreate settings table
            $this->pdo->exec("DROP TABLE IF EXISTS guidance_settings");
            $sql = "CREATE TABLE guidance_settings (
                id int(11) NOT NULL AUTO_INCREMENT,
                section_title varchar(255) NOT NULL DEFAULT 'ACTIVITIES CALENDAR',
                is_visible tinyint(1) DEFAULT 1,
                created_at timestamp NULL DEFAULT current_timestamp(),
                updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            $this->pdo->exec($sql);
            error_log("Successfully created guidance_settings table");

            // Insert default settings if not exists
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM guidance_settings");
            if ($stmt->fetchColumn() == 0) {
                $this->pdo->exec("INSERT INTO guidance_settings (section_title, is_visible) VALUES ('ACTIVITIES CALENDAR', 1)");
            }
        } catch (PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
        }
    }

    public function getSection() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM guidance_settings WHERE id = 1");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getSection: " . $e->getMessage());
            return false;
        }
    }

    public function updateSection($title, $is_visible) {
        try {
            // Check if section exists
            $stmt = $this->pdo->prepare("SELECT * FROM guidance_settings WHERE id = 1");
            $stmt->execute();
            
            if ($stmt->fetch()) {
                // Update existing section
                $stmt = $this->pdo->prepare("UPDATE guidance_settings SET section_title = ?, is_visible = ? WHERE id = 1");
                $stmt->execute([$title, $is_visible]);
            } else {
                // Insert new section
                $stmt = $this->pdo->prepare("INSERT INTO guidance_settings (section_title, is_visible) VALUES (?, ?)");
                $stmt->execute([$title, $is_visible]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error in updateSection: " . $e->getMessage());
            return false;
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
            $stmt = $this->pdo->query("SELECT * FROM guidance_activities ORDER BY event_date DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in fetchAll: " . $e->getMessage());
            return [];
        }
    }

    public function addActivity($data) {
        try {
            // Handle image upload or URL
            if (isset($_FILES['event_image_file']) && $_FILES['event_image_file']['size'] > 0) {
                $data['event_image'] = $this->handleImageUpload($_FILES['event_image_file']);
            } elseif (isset($_POST['event_image_url']) && !empty($_POST['event_image_url'])) {
                $data['event_image'] = $_POST['event_image_url'];
            } else {
                $data['event_image'] = null;
            }

            // Get section title from settings table
            $section = $this->getSection();
            $data['section_title'] = $section ? $section['section_title'] : 'ACTIVITIES CALENDAR';

            $sql = "INSERT INTO guidance_activities (
                    section_title,
                    event_title,
                    event_description,
                    event_date,
                    event_time,
                    event_location,
                    event_image,
                    is_visible
                ) VALUES (
                    :section_title,
                    :event_title,
                    :event_description,
                    :event_date,
                    :event_time,
                    :event_location,
                    :event_image,
                    :is_visible
                )";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return true;
        } catch (PDOException $e) {
            error_log("Error in addActivity: " . $e->getMessage());
            throw $e;
        }
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

        $sql = "UPDATE guidance_activities 
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
        $stmt = $this->pdo->prepare("SELECT event_image FROM guidance_activities WHERE id = ?");
        $stmt->execute([$id]);
        $activity = $stmt->fetch();

        // Delete the activity
        $stmt = $this->pdo->prepare("DELETE FROM guidance_activities WHERE id = ?");
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
        $stmt = $this->pdo->prepare("UPDATE guidance_activities SET is_visible = ? WHERE id = ?");
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
    <style>
        form, table { margin: 20px auto; width: 90%; }
        input, textarea { width: 100%; margin: 4px 0; }
        table { border-collapse: collapse; }
        th, td { border: 1px solid #aaa; padding: 8px; }
        .actions a { margin: 0 5px; }
        .section-header {
            background: #f5f5f5;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .section-title {
            font-size: 24px;
            margin: 0;
            display: inline-block;
        }
        .section-controls {
            float: right;
        }
        .visibility-status {
            color: #666;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <!-- Section Management -->
    <div class="section-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="section-title"><?= htmlspecialchars($section['section_title']) ?></h2>
            <div class="section-controls">
                <button onclick="showSectionModal()">Edit Section</button>
                <span class="visibility-status">
                    Status: <?= $section['is_visible'] ? 'Visible' : 'Hidden' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Section Edit Modal -->
    <div id="sectionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
        <div style="background: white; margin: 50px auto; padding: 20px; width: 80%; max-width: 500px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h3>Edit Section Settings</h3>
                <button onclick="hideSectionModal()" style="border: none; background: none; font-size: 20px;">&times;</button>
            </div>
            <form method="POST">
                <label>Section Title:</label>
                <input type="text" name="section_title" value="<?= htmlspecialchars($section['section_title']) ?>" required>
                
                <label>
                    <input type="checkbox" name="section_visible" <?= $section['is_visible'] ? 'checked' : '' ?>>
                    Show this section on the website
                </label>
                
                <button type="submit" name="update_section">Update Section</button>
            </form>
        </div>
    </div>

    <!-- Add Activity Button -->
    <div style="text-align: right; margin: 20px;">
        <button onclick="showAddModal()">Add New Activity</button>
    </div>

    <!-- Add Activity Modal -->
    <div id="addActivityModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
        <div style="background: white; margin: 50px auto; padding: 20px; width: 80%; max-width: 600px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h3>Add New Activity</h3>
                <button onclick="hideAddModal()" style="border: none; background: none; font-size: 20px;">&times;</button>
            </div>
            <form method="POST" id="addActivityForm" enctype="multipart/form-data">
                <label>Event Title:</label>
                <input type="text" name="event_title" required>
                
                <label>Event Description:</label>
                <textarea name="event_description" required></textarea>
                
                <label>Date:</label>
                <input type="date" name="event_date" required>
                
                <label>Time:</label>
                <input type="time" name="event_time" required>
                
                <label>Location:</label>
                <input type="text" name="event_location" required>
                
                <div style="margin: 10px 0;">
                    <label>Image:</label><br>
                    <input type="radio" name="image_type" value="file" checked onchange="toggleImageInput('add')"> Upload File
                    <input type="radio" name="image_type" value="url" onchange="toggleImageInput('add')"> Image URL
                    
                    <div id="add_file_input" style="margin-top: 5px;">
                        <input type="file" name="event_image_file" accept="image/*">
        </div>
                    <div id="add_url_input" style="margin-top: 5px; display: none;">
                        <input type="text" name="event_image_url" placeholder="Enter image URL">
        </div>
                    <div id="add_image_preview" style="margin-top: 10px;"></div>
                </div>
                
                <label><input type="checkbox" name="is_visible" checked> Visible</label><br><br>
                
                <button type="submit" name="add">Add Activity</button>
            </form>
        </div>
            </div>

    <h3 style="text-align:center;">All Activities</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Section Title</th>
            <th>Title</th>
            <th>Date</th>
                            <th>Time</th>
            <th>Location</th>
                            <th>Image</th>
            <th>Visible</th>
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
            <td><?= $a['is_visible'] ? 'Yes' : 'No' ?></td>
            <td class="actions">
                <button onclick='editActivity(<?= json_encode($a) ?>)'>Edit</button>
                <a href="?delete=<?= $a['id'] ?>" onclick="return confirm('Delete this activity?')">Delete</a>
                <a href="?toggle=<?= $a['id'] ?>&v=<?= $a['is_visible'] ? 0 : 1 ?>">
                    <?= $a['is_visible'] ? 'Hide' : 'Show' ?>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- Edit Activity Modal -->
    <div id="editActivityModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
        <div style="background: white; margin: 50px auto; padding: 20px; width: 80%; max-width: 600px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h3>Edit Activity</h3>
                <button onclick="hideEditModal()" style="border: none; background: none; font-size: 20px;">&times;</button>
        </div>
            <form method="POST" id="editActivityForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="edit_id">
                        
                <label>Event Title:</label>
                <input type="text" name="event_title" id="edit_event_title" required>
                
                <label>Event Description:</label>
                <textarea name="event_description" id="edit_event_description" required></textarea>
                
                <label>Date:</label>
                <input type="date" name="event_date" id="edit_event_date" required>
                
                <label>Time:</label>
                <input type="time" name="event_time" id="edit_event_time" required>
                
                <label>Location:</label>
                <input type="text" name="event_location" id="edit_event_location" required>
                
                <div style="margin: 10px 0;">
                    <label>Image:</label><br>
                    <input type="radio" name="edit_image_type" value="file" checked onchange="toggleImageInput('edit')"> Upload File
                    <input type="radio" name="edit_image_type" value="url" onchange="toggleImageInput('edit')"> Image URL
                    
                    <div id="edit_file_input" style="margin-top: 5px;">
                        <input type="file" name="event_image_file" id="edit_event_image_file" accept="image/*">
                            </div>
                    <div id="edit_url_input" style="margin-top: 5px; display: none;">
                        <input type="text" name="event_image_url" id="edit_event_image_url" placeholder="Enter image URL">
                            </div>
                    <div id="edit_image_preview" style="margin-top: 10px;"></div>
                        </div>

                <label><input type="checkbox" name="is_visible" id="edit_is_visible"> Visible</label><br><br>
                
                <button type="submit" name="update">Update Activity</button>
                    </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('addActivityModal').style.display = 'block';
        }

        function hideAddModal() {
            document.getElementById('addActivityModal').style.display = 'none';
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
                        if (activity.event_image.startsWith('http')) {
                    document.querySelector('input[name="edit_image_type"][value="url"]').checked = true;
                    document.getElementById('edit_event_image_url').value = activity.event_image;
                }
                const imageUrl = activity.event_image.startsWith('http') ? activity.event_image : '../../' + activity.event_image;
                document.getElementById('edit_image_preview').innerHTML = 
                    `<img src="${imageUrl}" alt="Preview" style="max-width: 200px; max-height: 200px;">`;
            }
            toggleImageInput('edit');
            
            // Show the edit modal
            document.getElementById('editActivityModal').style.display = 'block';
        }

        function hideEditModal() {
            document.getElementById('editActivityModal').style.display = 'none';
            resetForm('editActivityForm');
        }

        function toggleImageInput(type) {
            const radioValue = document.querySelector(`input[name="${type}_image_type"]:checked`).value;
            document.getElementById(`${type}_file_input`).style.display = radioValue === 'file' ? 'block' : 'none';
            document.getElementById(`${type}_url_input`).style.display = radioValue === 'url' ? 'block' : 'none';
        }

        function resetForm(formId) {
            document.getElementById(formId).reset();
            const type = formId === 'addActivityForm' ? 'add' : 'edit';
            document.getElementById(`${type}_image_preview`).innerHTML = '';
            toggleImageInput(type);
        }

        // Preview image when file is selected
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const type = this.form.id === 'addActivityForm' ? 'add' : 'edit';
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                        document.getElementById(`${type}_image_preview`).innerHTML = 
                            `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px;">`;
                };
                reader.readAsDataURL(this.files[0]);
            }
            });
        });

        // Preview image when URL is entered
        document.querySelectorAll('input[name$="image_url"]').forEach(input => {
            input.addEventListener('input', function() {
                const type = this.form.id === 'addActivityForm' ? 'add' : 'edit';
                if (this.value) {
                    document.getElementById(`${type}_image_preview`).innerHTML = 
                        `<img src="${this.value}" alt="Preview" style="max-width: 200px; max-height: 200px;" onerror="this.src='../../imgs/cte.jpg';">`;
                }
            });
        });

        // Add new section modal functions
        function showSectionModal() {
            document.getElementById('sectionModal').style.display = 'block';
        }

        function hideSectionModal() {
            document.getElementById('sectionModal').style.display = 'none';
        }

        // Update modal closing handler
        window.onclick = function(event) {
            if (event.target == document.getElementById('addActivityModal')) {
                hideAddModal();
            }
            if (event.target == document.getElementById('editActivityModal')) {
                hideEditModal();
            }
            if (event.target == document.getElementById('sectionModal')) {
                hideSectionModal();
            }
        }
    </script>
</body>
</html>
