<?php
require_once __DIR__ . '/../db_connection.php'; // Connect to database (PDO in $pdo)
require_once 'includes/editor.php';

class ObjectiveFunctionCMS {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTablesExist();
    }

    private function ensureTablesExist() {
        try {
            // Create objectives table if it doesn't exist
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS af_page_obj (
                id INT PRIMARY KEY DEFAULT 1,
                section_title VARCHAR(255) DEFAULT 'OUR OBJECTIVES',
                description TEXT,
                is_visible TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");

            // Create functions table if it doesn't exist
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS af_page_funct (
                id INT PRIMARY KEY DEFAULT 1,
                section_title VARCHAR(255) DEFAULT 'KEY FUNCTIONS',
                description TEXT,
                is_visible TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");

            // Insert default row in objectives table if empty
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_obj");
            if ($stmt->fetchColumn() == 0) {
                $this->pdo->exec("INSERT INTO af_page_obj (id, section_title, description, is_visible) 
                                VALUES (1, 'OUR OBJECTIVES', '', 0)");
            }

            // Insert default row in functions table if empty
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_funct");
            if ($stmt->fetchColumn() == 0) {
                $this->pdo->exec("INSERT INTO af_page_funct (id, section_title, description, is_visible) 
                                VALUES (1, 'KEY FUNCTIONS', '', 0)");
            }
        } catch (PDOException $e) {
            error_log("Error ensuring tables exist: " . $e->getMessage());
        }
    }

    // Get section by table
    public function getSection($table) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM $table WHERE id = 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Return default structure if no data found
            if (!$result) {
                return [
                    'section_title' => '',
                    'description' => '',
                    'is_visible' => 0
                ];
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error fetching section from $table: " . $e->getMessage());
            return [
                'section_title' => '',
                'description' => '',
                'is_visible' => 0
            ];
        }
    }

    // Update section
    public function updateSection($table, $title, $description, $visible) {
        try {
            // Check if record exists
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM $table");
            $checkStmt->execute();
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                // Update existing record
                $stmt = $this->pdo->prepare("UPDATE $table SET section_title = ?, description = ?, is_visible = ?, updated_at = NOW() WHERE id = 1");
            } else {
                // Insert new record
                $stmt = $this->pdo->prepare("INSERT INTO $table (id, section_title, description, is_visible, created_at, updated_at) VALUES (1, ?, ?, ?, NOW(), NOW())");
            }
            
            return $stmt->execute([$title, $description, $visible]);
        } catch (PDOException $e) {
            error_log("Error updating section in $table: " . $e->getMessage());
            return false;
        }
    }
}

// Initialize
$cms = new ObjectiveFunctionCMS($pdo);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_obj'])) {
        $cms->updateSection('af_page_obj', $_POST['obj_title'], $_POST['obj_desc'], isset($_POST['obj_visible']) ? 1 : 0);
    }

    if (isset($_POST['update_funct'])) {
        $cms->updateSection('af_page_funct', $_POST['funct_title'], $_POST['funct_desc'], isset($_POST['funct_visible']) ? 1 : 0);
    }
}

// Fetch existing content
$objData = $cms->getSection('af_page_obj');
$functData = $cms->getSection('af_page_funct');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objectives & Functions Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php editorStyles(); ?>
</head>
<body class="container py-5">

<h2>Manage Objectives Section</h2>
<form method="POST">
    <label>Section Title:</label>
    <input type="text" name="obj_title" value="<?= htmlspecialchars($objData['section_title']) ?>">

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <?= renderEditor('description', $objData['description'] ?? '', 'description') ?>
    </div>

    <label><input type="checkbox" name="obj_visible" <?= $objData['is_visible'] ? 'checked' : '' ?>> Show this section</label><br>

    <button type="submit" name="update_obj">Update Objectives</button>
</form>

<h2>Manage Functions Section</h2>
<form method="POST">
    <label>Section Title:</label>
    <input type="text" name="funct_title" value="<?= htmlspecialchars($functData['section_title']) ?>">

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <?= renderEditor('description', $functData['description'] ?? '', 'description') ?>
    </div>

    <label><input type="checkbox" name="funct_visible" <?= $functData['is_visible'] ? 'checked' : '' ?>> Show this section</label><br>

    <button type="submit" name="update_funct">Update Functions</button>
</form>

<?php editorScripts(); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
