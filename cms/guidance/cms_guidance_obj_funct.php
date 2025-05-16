<?php
require_once '../db_connection.php'; // Connect to database (PDO in $pdo)

class ObjectiveFunctionCMS {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTablesExist();
    }

    private function ensureTablesExist() {
        try {
            // Create objectives table if it doesn't exist
            $sql = "CREATE TABLE IF NOT EXISTS guidance_obj (
                    id INT PRIMARY KEY DEFAULT 1,
                    section_title VARCHAR(255) DEFAULT 'OUR OBJECTIVES',
                    description TEXT NOT NULL,
                    is_visible TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
            $this->pdo->exec($sql);
            error_log("Successfully checked/created guidance_obj table");

            // Create functions table if it doesn't exist
            $sql = "CREATE TABLE IF NOT EXISTS guidance_funct (
                    id INT PRIMARY KEY DEFAULT 1,
                    section_title VARCHAR(255) DEFAULT 'KEY FUNCTIONS',
                    description TEXT NOT NULL,
                    is_visible TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
            $this->pdo->exec($sql);
            error_log("Successfully checked/created guidance_funct table");

            // Insert default row in objectives table if empty
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM guidance_obj");
            if ($stmt->fetchColumn() == 0) {
                $this->pdo->exec("INSERT INTO guidance_obj (id, section_title, description, is_visible) 
                                VALUES (1, 'OUR OBJECTIVES', 'Default objectives description', 1)");
                error_log("Inserted default row into guidance_obj table");
            }

            // Insert default row in functions table if empty
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM guidance_funct");
            if ($stmt->fetchColumn() == 0) {
                $this->pdo->exec("INSERT INTO guidance_funct (id, section_title, description, is_visible) 
                                VALUES (1, 'KEY FUNCTIONS', 'Default functions description', 1)");
                error_log("Inserted default row into guidance_funct table");
            }
        } catch (PDOException $e) {
            error_log("Error ensuring tables exist: " . $e->getMessage());
            error_log("SQL State: " . $e->errorInfo[0]);
            error_log("Error Code: " . $e->errorInfo[1]);
            error_log("Error Message: " . $e->errorInfo[2]);
            throw $e;
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
                    'is_visible' => 1
                ];
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error fetching section from $table: " . $e->getMessage());
            return [
                'section_title' => '',
                'description' => '',
                'is_visible' => 1
            ];
        }
    }

    // Update section with better error handling
    public function updateSection($table, $title, $description, $visible) {
        try {
            error_log("Attempting to update $table with title: $title, visible: $visible");
            
            // Validate table name to prevent SQL injection
            if (!in_array($table, ['guidance_obj', 'guidance_funct'])) {
                throw new Exception("Invalid table name");
            }

            // Check if record exists
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM $table WHERE id = 1");
            $checkStmt->execute();
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                // Update existing record
                $stmt = $this->pdo->prepare("UPDATE $table 
                                           SET section_title = :title, 
                                               description = :description, 
                                               is_visible = :visible,
                                               updated_at = CURRENT_TIMESTAMP 
                                           WHERE id = 1");
            } else {
                // Insert new record
                $stmt = $this->pdo->prepare("INSERT INTO $table 
                                           (id, section_title, description, is_visible) 
                                           VALUES (1, :title, :description, :visible)");
            }
            
            $result = $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':visible' => $visible
            ]);

            if (!$result) {
                error_log("Failed to update $table. PDO error info: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            error_log("Successfully updated $table");
            return true;
        } catch (PDOException $e) {
            error_log("Database error updating section in $table: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
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
        $cms->updateSection('guidance_obj', $_POST['obj_title'], $_POST['obj_desc'], isset($_POST['obj_visible']) ? 1 : 0);
    }

    if (isset($_POST['update_funct'])) {
        $cms->updateSection('guidance_funct', $_POST['funct_title'], $_POST['funct_desc'], isset($_POST['funct_visible']) ? 1 : 0);
    }
}

// Fetch existing content
$objData = $cms->getSection('guidance_obj');
$functData = $cms->getSection('guidance_funct');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Objectives and Functions CMS</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        h2 { color: #B32134; }
        form { background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input[type="text"] { width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px; }
        input[type="checkbox"] { margin-right: 10px; }
        button { background: #B32134; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        
        /* Editor styles from cms_af_page.php */
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

        .editor {
            min-height: 200px;
            padding: 20px;
            background: white;
            border: none;
            outline: none;
            font-size: 16px;
            line-height: 1.6;
        }

        .font-size-select {
            padding: 6px 12px;
            border: 1px solid #d1d1d1;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            color: #444;
            font-size: 14px;
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
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<h2>Manage Objectives Section</h2>
<form method="POST">
    <label>Section Title:</label>
    <input type="text" name="obj_title" value="<?= htmlspecialchars($objData['section_title']) ?>">

    <label>Description:</label>
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
        <div class="editor" id="obj_desc" contenteditable="true"><?= $objData['description'] ?></div>
        <input type="hidden" name="obj_desc" id="obj_desc_input">
    </div>

    <label><input type="checkbox" name="obj_visible" <?= $objData['is_visible'] ? 'checked' : '' ?>> Show this section</label><br>

    <button type="submit" name="update_obj">Update Objectives</button>
</form>

<h2>Manage Functions Section</h2>
<form method="POST">
    <label>Section Title:</label>
    <input type="text" name="funct_title" value="<?= htmlspecialchars($functData['section_title']) ?>">

    <label>Description:</label>
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
        <div class="editor" id="funct_desc" contenteditable="true"><?= $functData['description'] ?></div>
        <input type="hidden" name="funct_desc" id="funct_desc_input">
    </div>

    <label><input type="checkbox" name="funct_visible" <?= $functData['is_visible'] ? 'checked' : '' ?>> Show this section</label><br>

    <button type="submit" name="update_funct">Update Functions</button>
</form>

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
            editor.addEventListener('keyup', updateToolbarState);
            editor.addEventListener('mouseup', updateToolbarState);
        });
        
        // Sync editor content to hidden input before form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const editors = this.querySelectorAll('.editor');
                editors.forEach(editor => {
                    const hiddenInput = editor.nextElementSibling;
                    if (hiddenInput && hiddenInput.type === 'hidden') {
                        hiddenInput.value = editor.innerHTML;
                    }
                });
            });
        });
    });
</script>

</body>
</html>
