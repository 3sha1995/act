<?php
require_once('../db_connection.php');

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
<html>
<head>
    <title>Process Info CMS</title>
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

        .container { 
            max-width: 800px; 
            margin-top: 30px; 
        }
        
        .form-group { 
            margin-bottom: 20px; 
        }

        .form-container {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }

        label, .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.95rem;
        }

        input[type="text"], textarea, select, .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        input[type="text"]:focus, textarea:focus, select:focus, .form-control:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        .btn, button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .btn-primary, .button-primary {
            background: #3182ce;
            color: white;
        }

        .btn-danger {
            background: #e53e3e;
            color: white;
        }

        .btn:hover, button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.13);
        }

        .btn-primary:hover {
            background: #2b6cb0;
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

        .btn-close {
            position: absolute;
            right: 15px;
            top: 15px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            color: inherit;
            background: transparent;
            border: none;
        }

        h1, h2, h3, h4, h5, h6 {
            color: #2c5282;
        }

        .section {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
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

        .editor-toolbar button i {
            font-size: 14px;
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

        /* Responsive design */
        @media (max-width: 768px) {
            .toolbar-group {
                padding: 5px;
                border-right: none;
                border-bottom: 1px solid #bee3f8;
                width: 100%;
                justify-content: center;
            }

            .toolbar-group:last-child {
                border-bottom: none;
            }

            .editor-toolbar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<?php include 'student_affairs_sidebar.php'; ?>

<div class="content-wrapper">
    <div class="section">
        <h2>Edit Process Information Section</h2>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">
                Section updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                Failed to update section. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <div class="form-group">
                <label for="section_title" class="form-label">Section Title:</label>
                <input type="text" class="form-control" id="section_title" name="section_title" 
                       value="<?= htmlspecialchars($info['section_title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="section_description" class="form-label">Description:</label>
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
                    <div class="editor" id="editor" contenteditable="true"><?= $info['section_description'] ?></div>
                    <input type="hidden" name="section_description" id="section_description">
                </div>
            </div>

            <div class="form-group">
                <label class="switch-label">
                    Show this section on the website
                    <label class="switch">
                        <input type="checkbox" id="is_visible" name="is_visible" <?= $info['is_visible'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>

            <button type="submit" class="btn btn-primary">Update Section</button>
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
            const editor = document.getElementById('editor');
            
            // Update toolbar state when selection changes
            editor.addEventListener('keyup', updateToolbarState);
            editor.addEventListener('mouseup', updateToolbarState);
            
            // Sync editor content to hidden input before form submission
            document.querySelector('form').addEventListener('submit', function() {
                document.getElementById('section_description').value = editor.innerHTML;
            });

            // Close alert messages
            document.querySelectorAll('.btn-close').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.parentElement.style.display = 'none';
                });
            });
        });
    </script>

    <!-- Include the sidebar persistence script -->
    <script src="student_affairs_persistent.js"></script>
</div>
</body>
</html>
