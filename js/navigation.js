document.addEventListener('DOMContentLoaded', function() {
    // Get all nav items
    const navItems = document.querySelectorAll('.nav-item');
    
    // Function to close all dropdowns
    function closeAllDropdowns() {
        navItems.forEach(item => {
            const dropdown = item.querySelector('.dropdown-menu');
            const link = item.querySelector('.nav-link');
            dropdown.classList.remove('show');
            link.classList.remove('active');
        });
    }

    // Add click event listeners to nav links
    navItems.forEach(item => {
        const link = item.querySelector('.nav-link');
        const dropdown = item.querySelector('.dropdown-menu');

        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Close all other dropdowns
            navItems.forEach(otherItem => {
                if (otherItem !== item) {
                    const otherDropdown = otherItem.querySelector('.dropdown-menu');
                    const otherLink = otherItem.querySelector('.nav-link');
                    otherDropdown.classList.remove('show');
                    otherLink.classList.remove('active');
                }
            });

            // Toggle current dropdown
            dropdown.classList.toggle('show');
            link.classList.toggle('active');
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-item')) {
            closeAllDropdowns();
        }
    });

    // Close dropdowns when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });
});
