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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - Process</title>
    <?php include 'includes/header.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container { max-width: 800px; margin-top: 30px; }
        .form-group { margin-bottom: 20px; }

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

        .editor-toolbar button i {
            font-size: 14px;
        }

        .editor {
            min-height: 300px;
            padding: 20px;
            background: white;
            border: none;
            outline: none;
            font-size: 16px;
            line-height: 1.6;
        }

        .editor:focus {
            outline: none;
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

        /* Font size select styles */
        .font-size-select {
            padding: 6px 12px;
            border: 1px solid #d1d1d1;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            color: #444;
            font-size: 14px;
        }

        .font-size-select:hover {
            background: #e9ecef;
            border-color: #bbb;
        }

        .font-size-select:focus {
            outline: none;
            border-color: #0056b3;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .toolbar-group {
                padding: 5px;
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
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
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Process Management</h1>
            </div>

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

            <form method="POST" enctype="multipart/form-data">
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

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="is_visible" name="is_visible" 
                           <?= $info['is_visible'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_visible">Show this section on the website</label>
                </div>

                <button type="submit" class="btn btn-primary">Update Section</button>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        });
    </script>
</body>
</html>
