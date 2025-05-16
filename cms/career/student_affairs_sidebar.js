document.addEventListener('DOMContentLoaded', function() {
  // Get sidebar elements
  const sidebar = document.querySelector('.sa-sidebar');
  const sidebarToggle = document.querySelector('.sa-sidebar-toggle');
  const menuItems = document.querySelectorAll('.sa-sidebar-link');
  const submenuItems = document.querySelectorAll('.sa-sidebar-submenu-link');
  
  // Toggle sidebar collapse/expand
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
      // Desktop behavior (toggle collapsed class)
      if (window.innerWidth >= 992) {
        sidebar.classList.toggle('collapsed');
        
        // Adjust content wrapper
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
          contentWrapper.classList.toggle('sidebar-collapsed');
        }
      } 
      // Mobile behavior (toggle mobile-open class)
      else if (window.innerWidth < 768) {
        sidebar.classList.toggle('mobile-open');
      } 
      // Tablet behavior (toggle expanded class)
      else {
        sidebar.classList.toggle('expanded');
        
        // Adjust content wrapper
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
          contentWrapper.classList.toggle('sidebar-expanded');
        }
      }
    });
  }
  
  // Set active link based on current page and URL parameters
  const currentPath = window.location.pathname;
  const urlParams = new URLSearchParams(window.location.search);
  const currentSection = urlParams.get('section');
  
  menuItems.forEach(function(item) {
    const linkPath = item.getAttribute('href');
    const linkSection = item.getAttribute('data-section');
    
    // Check for direct path match (for specific pages)
    let isActive = linkPath && currentPath.includes(linkPath.split('?')[0]);
    
    // For dashboard/about page, also check the section parameter
    if (currentPath.includes('cms_af_page.php')) {
      // Only activate if sections match
      if (currentSection && linkSection) {
        isActive = currentSection === linkSection;
      } else if (!currentSection && linkSection === 'dashboard') {
        // Default to dashboard if no section specified
        isActive = true;
      }
    }
    
    if (isActive) {
      item.classList.add('active');
      
      // If the link has a parent submenu, expand it
      const parentLi = item.closest('.sa-sidebar-item');
      if (parentLi) {
        const submenu = parentLi.querySelector('.sa-sidebar-submenu');
        if (submenu) {
          submenu.classList.add('open');
        }
      }
    } else {
      item.classList.remove('active');
    }
  });
  
  submenuItems.forEach(function(item) {
    const linkPath = item.getAttribute('href');
    if (linkPath && currentPath.includes(linkPath)) {
      item.classList.add('active');
      
      // Make sure parent menu is also marked active
      const parentUl = item.closest('.sa-sidebar-submenu');
      if (parentUl) {
        parentUl.classList.add('open');
        const parentItem = parentUl.closest('.sa-sidebar-item');
        if (parentItem) {
          const parentLink = parentItem.querySelector('.sa-sidebar-link');
          if (parentLink) {
            parentLink.classList.add('active');
          }
        }
      }
    }
  });
  
  // Add click handlers to ensure the sidebar stays visible
  menuItems.forEach(function(item) {
    item.addEventListener('click', function(e) {
      // For links with submenus
      if (item.classList.contains('has-submenu')) {
        e.preventDefault();
        // Only toggle submenu if not in collapsed mode on desktop
        if (!sidebar.classList.contains('collapsed') || window.innerWidth < 992) {
          const parentLi = this.closest('.sa-sidebar-item');
          const submenu = parentLi.querySelector('.sa-sidebar-submenu');
          
          // Close all other submenus first
          document.querySelectorAll('.sa-sidebar-submenu.open').forEach(function(menu) {
            if (menu !== submenu) {
              menu.classList.remove('open');
            }
          });
          
          // Toggle current submenu
          submenu.classList.toggle('open');
        }
      } else {
        // For regular links, just add active class
        menuItems.forEach(link => link.classList.remove('active'));
        item.classList.add('active');
      }
    });
  });
  
  // Close sidebar when clicking outside on mobile
  document.addEventListener('click', function(e) {
    if (window.innerWidth < 768 && 
        sidebar.classList.contains('mobile-open') && 
        !sidebar.contains(e.target) && 
        e.target !== sidebarToggle) {
      sidebar.classList.remove('mobile-open');
    }
  });
  
  // Handle window resize
  window.addEventListener('resize', function() {
    const contentWrapper = document.querySelector('.content-wrapper');
    
    if (window.innerWidth >= 992) {
      sidebar.classList.remove('expanded');
      sidebar.classList.remove('mobile-open');
      
      if (contentWrapper) {
        contentWrapper.classList.remove('sidebar-expanded');
        if (sidebar.classList.contains('collapsed')) {
          contentWrapper.classList.add('sidebar-collapsed');
        } else {
          contentWrapper.classList.remove('sidebar-collapsed');
        }
      }
    } else if (window.innerWidth >= 768) {
      sidebar.classList.remove('mobile-open');
      
      if (contentWrapper) {
        contentWrapper.classList.remove('sidebar-collapsed');
        if (sidebar.classList.contains('expanded')) {
          contentWrapper.classList.add('sidebar-expanded');
        } else {
          contentWrapper.classList.remove('sidebar-expanded');
        }
      }
    } else {
      sidebar.classList.remove('collapsed');
      sidebar.classList.remove('expanded');
      
      if (contentWrapper) {
        contentWrapper.classList.remove('sidebar-collapsed');
        contentWrapper.classList.remove('sidebar-expanded');
      }
    }
  });
}); 