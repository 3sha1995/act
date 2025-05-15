<?php
require_once '../db_connection.php';

class MissionVisionCMS {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Fetch current MV record
    public function getMV() {
        $sql = "SELECT * FROM af_page_mv LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update MV record
    public function updateMV($data) {
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
            is_visible = :is_visible,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':section_title' => $data['section_title'],
            ':mission_title' => $data['mission_title'],
            ':mission_image_url' => $data['mission_image_url'],
            ':mission_description' => $data['mission_description'],
            ':mission_show_more_text' => $data['mission_show_more_text'],
            ':vision_title' => $data['vision_title'],
            ':vision_image_url' => $data['vision_image_url'],
            ':vision_description' => $data['vision_description'],
            ':vision_show_more_text' => $data['vision_show_more_text'],
            ':is_visible' => isset($data['is_visible']) ? 1 : 0,
            ':id' => $data['id']
        ]);
    }
}

// Init
$cms = new MissionVisionCMS($pdo);
$mvData = $cms->getMV();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateData = [
        'id' => $_POST['id'],
        'section_title' => $_POST['section_title'],
        'mission_title' => $_POST['mission_title'],
        'mission_image_url' => $_POST['mission_image_url'],
        'mission_description' => $_POST['mission_description'],
        'mission_show_more_text' => $_POST['mission_show_more_text'],
        'vision_title' => $_POST['vision_title'],
        'vision_image_url' => $_POST['vision_image_url'],
        'vision_description' => $_POST['vision_description'],
        'vision_show_more_text' => $_POST['vision_show_more_text'],
        'is_visible' => isset($_POST['is_visible']) ? 1 : 0
    ];

    if ($cms->updateMV($updateData)) {
        echo "<p>Updated successfully!</p>";
        $mvData = $cms->getMV(); // Refresh data
    } else {
        echo "<p>Update failed.</p>";
    }
}
?>

<!-- CMS FORM -->
<h2>Manage Mission and Vision Section</h2>
<form method="POST">
    <input type="hidden" name="id" value="<?= $mvData['id'] ?>">

    <label>Section Title</label>
    <input type="text" name="section_title" value="<?= htmlspecialchars($mvData['section_title']) ?>" required>

    <h3>Mission</h3>
    <label>Mission Title</label>
    <input type="text" name="mission_title" value="<?= htmlspecialchars($mvData['mission_title']) ?>" required>

    <label>Mission Image URL</label>
    <input type="text" name="mission_image_url" value="<?= htmlspecialchars($mvData['mission_image_url']) ?>" required>

    <label>Mission Description</label>
    <textarea name="mission_description" required><?= htmlspecialchars($mvData['mission_description']) ?></textarea>

    <label>Mission Show More Text</label>
    <input type="text" name="mission_show_more_text" value="<?= htmlspecialchars($mvData['mission_show_more_text']) ?>">

    <h3>Vision</h3>
    <label>Vision Title</label>
    <input type="text" name="vision_title" value="<?= htmlspecialchars($mvData['vision_title']) ?>" required>

    <label>Vision Image URL</label>
    <input type="text" name="vision_image_url" value="<?= htmlspecialchars($mvData['vision_image_url']) ?>" required>

    <label>Vision Description</label>
    <textarea name="vision_description" required><?= htmlspecialchars($mvData['vision_description']) ?></textarea>

    <label>Vision Show More Text</label>
    <input type="text" name="vision_show_more_text" value="<?= htmlspecialchars($mvData['vision_show_more_text']) ?>">

    <label><input type="checkbox" name="is_visible" <?= $mvData['is_visible'] ? 'checked' : '' ?>> Visible</label><br><br>

    <button type="submit">Update</button>
    </form>
