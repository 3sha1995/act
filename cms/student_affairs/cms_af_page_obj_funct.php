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
<html>
<head>
    <title>Objectives and Functions CMS</title>
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

        h1, h2 { 
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
        
        input[type="text"], textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: #fff;
            margin-bottom: 15px;
        }
        
        input[type="text"]:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
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
            background: #2c5282;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.13);
        }
        
        .editor-container {
            border: 1px solid #bee3f8;
            border-radius: 12px;
            overflow: hidden;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(49,130,206,0.07);
            background: #ffffff;
        }

        .editor-toolbar {
            background: linear-gradient(to right, #ebf8ff, #f0f7ff);
            border-bottom: 1px solid #bee3f8;
            padding: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .toolbar-group {
            display: flex;
            gap: 8px;
            padding: 0 12px;
            border-right: 1px solid #bee3f8;
        }

        .toolbar-group:last-child {
            border-right: none;
        }

        .editor-toolbar button {
            background: #ffffff;
            border: 1px solid #bee3f8;
            border-radius: 6px;
            padding: 8px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #3b82f6;
            transition: all 0.2s ease;
            font-weight: 500;
            margin-top: 0;
        }

        .editor-toolbar button:hover {
            background: #f0f5ff;
            border-color: #93c5fd;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(49,130,206,0.1);
        }

        .editor-toolbar button.active {
            background: #f0f5ff;
            border-color: #3182ce;
            color: #1d4ed8;
        }

        .editor {
            min-height: 300px;
            padding: 20px;
            background: #ffffff;
            border: none;
            outline: none;
            font-size: 16px;
            line-height: 1.6;
            color: #1e3a8a;
        }

        .editor:focus {
            outline: none;
            background: #fafbff;
        }

        /* Font size select styles */
        .font-size-select {
            padding: 8px 16px;
            border: 1px solid #bee3f8;
            border-radius: 6px;
            background: #ffffff;
            cursor: pointer;
            color: #3b82f6;
            font-size: 14px;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .font-size-select:hover {
            background: #f0f5ff;
            border-color: #93c5fd;
            transform: translateY(-1px);
        }

        .font-size-select:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
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
            padding: 6px 12px;
            background: #1e3a8a;
            color: white;
            font-size: 13px;
            font-weight: 500;
            border-radius: 6px;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(49,130,206,0.13);
            opacity: 0;
            animation: fadeIn 0.2s ease forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                bottom: 105%;
            }
        }
    </style>
</head>
<body>

<?php include 'student_affairs_sidebar.php'; ?>

<div class="content-wrapper">
    <h1>Objectives & Functions Management</h1>
    
    <div class="section">
    <h2>Manage Objectives Section</h2>
    <form method="POST">
            <div class="form-group">
        <label>Section Title:</label>
        <input type="text" name="obj_title" value="<?= htmlspecialchars($objData['section_title']) ?>">
            </div>

            <div class="form-group">
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
        </div>

            <div class="form-group">
                <label class="switch-label">
                    Show this section on the website
                    <label class="switch">
                        <input type="checkbox" name="obj_visible" <?= $objData['is_visible'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>

        <button type="submit" name="update_obj">Update Objectives</button>
    </form>
    </div>

    <div class="section">
    <h2>Manage Functions Section</h2>
    <form method="POST">
            <div class="form-group">
        <label>Section Title:</label>
        <input type="text" name="funct_title" value="<?= htmlspecialchars($functData['section_title']) ?>">
            </div>

            <div class="form-group">
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
        </div>

            <div class="form-group">
                <label class="switch-label">
                    Show this section on the website
                    <label class="switch">
                        <input type="checkbox" name="funct_visible" <?= $functData['is_visible'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>

        <button type="submit" name="update_funct">Update Functions</button>
    </form>
    </div>

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

</div>

<!-- Include the sidebar persistence script -->
<script src="student_affairs_persistent.js"></script>
</body>
</html>
