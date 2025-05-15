    </div><!-- Close container -->

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <script>
    // Handle sidebar responsiveness
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.cms-sidebar');
        const container = document.querySelector('.container');
        
        function handleResize() {
            if (window.innerWidth <= 768) {
                sidebar.style.width = '60px';
                container.style.marginLeft = '60px';
            } else {
                sidebar.style.width = '280px';
                container.style.marginLeft = '280px';
            }
        }

        // Initial check
        handleResize();

        // Listen for window resize
        window.addEventListener('resize', handleResize);

        // Handle rich text editor
        if (document.querySelector('.editor')) {
            const editor = document.querySelector('.editor');
            
            // Update toolbar state when selection changes
            editor.addEventListener('keyup', updateToolbarState);
            editor.addEventListener('mouseup', updateToolbarState);
            
            // Sync editor content to hidden input before form submission
            const form = editor.closest('form');
            if (form) {
                form.addEventListener('submit', function() {
                    const hiddenInput = this.querySelector('input[type="hidden"]');
                    if (hiddenInput) {
                        hiddenInput.value = editor.innerHTML;
                    }
                });
            }
        }
    });

    // Rich text editor functions
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

    // Image preview function
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                preview.classList.add('current-image-preview');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Tab switching function
    function switchTab(button, tabId) {
        const container = button.closest('.tab-container');
        if (container) {
            container.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            container.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            button.classList.add('active');
            const tabContent = document.getElementById(tabId);
            if (tabContent) {
                tabContent.classList.add('active');
            }
        }
    }

    // Mobile sidebar toggle
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.cms-sidebar');
        
        if (toggleButton && sidebar) {
            toggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(e.target) && 
                    !toggleButton.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            });
        }

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Confirmation dialog for delete actions
    function confirmDelete(message = 'Are you sure you want to delete this item?') {
        return confirm(message);
    }
    </script>
</body>
</html> 