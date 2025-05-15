<?php
// Standardized editor component for CMS pages
function renderEditor($editorId, $content = '', $hiddenInputName = '') {
    ob_start();
    ?>
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
        <div class="editor" id="<?= $editorId ?>" contenteditable="true"><?= $content ?></div>
        <?php if ($hiddenInputName): ?>
            <input type="hidden" name="<?= $hiddenInputName ?>" id="<?= $editorId ?>_input">
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Editor styles
function editorStyles() {
    ?>
    <style>
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
    <?php
}

// Editor scripts
function editorScripts() {
    ?>
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
                // Update toolbar state when selection changes
                editor.addEventListener('keyup', updateToolbarState);
                editor.addEventListener('mouseup', updateToolbarState);
            });
            
            // Sync editor content to hidden input before form submission
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const editors = this.querySelectorAll('.editor');
                    editors.forEach(editor => {
                        const hiddenInput = document.getElementById(editor.id + '_input');
                        if (hiddenInput) {
                            hiddenInput.value = editor.innerHTML;
                        }
                    });
                });
            });
        });
    </script>
    <?php
}
?> 