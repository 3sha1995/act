<?php
require_once __DIR__ . '/../db_connection.php';
require_once 'includes/editor.php';

class ProcessInfoCMS {
    private $conn;

    public function __construct($pdo) {
        $this->conn = $pdo;
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS af_page_process_info (
                id INT AUTO_INCREMENT PRIMARY KEY,
                section_title VARCHAR(255) DEFAULT 'Health Services Information',
                section_description TEXT,
                is_visible TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $this->conn->exec($sql);

            // Insert default record if table is empty
            $stmt = $this->conn->query("SELECT COUNT(*) FROM af_page_process_info");
            if ($stmt->fetchColumn() == 0) {
                $sql = "INSERT INTO af_page_process_info (section_title, section_description, is_visible) 
                        VALUES ('Health Services Information', 
                                'Learn about our health services application process and access important forms and resources.',
                                1)";
                $this->conn->exec($sql);
            }
        } catch (PDOException $e) {
            error_log("Error creating process info table: " . $e->getMessage());
            throw $e;
        }
    }

    public function getInfo() {
        $stmt = $this->conn->prepare("SELECT * FROM af_page_process_info LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateInfo($title, $description, $is_visible) {
        $stmt = $this->conn->prepare("UPDATE af_page_process_info 
            SET section_title = :title, section_description = :description, is_visible = :visible, updated_at = NOW() 
            WHERE id = 1");
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':visible' => $is_visible
        ]);
    }
}

// Initialize class
$cms = new ProcessInfoCMS($pdo);

// Update form handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['section_title'];
    $desc = $_POST['section_description'];
    $visible = isset($_POST['is_visible']) ? 1 : 0;

    if ($cms->updateInfo($title, $desc, $visible)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=1");
    }
    exit;
}

// Fetch current info
$info = $cms->getInfo();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php editorStyles(); ?>
</head>
<body class="container py-5">
    <div class="container">
        <h2 class="mb-4">Edit Process Information Section</h2>
        
    <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Section updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Failed to update section. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
    <?php endif; ?>

    <form method="POST">
            <div class="form-group">
                <label for="section_title" class="form-label">Section Title:</label>
                <input type="text" class="form-control" id="section_title" name="section_title" 
                       value="<?= htmlspecialchars($info['section_title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="section_description" class="form-label">Description:</label>
                <?= renderEditor('description', $info['section_description'] ?? '', 'description') ?>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="is_visible" name="is_visible" 
                       <?= $info['is_visible'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_visible">Show this section on the website</label>
            </div>

            <button type="submit" class="btn btn-primary">Update Section</button>
        </form>
    </div>

    <?php editorScripts(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
