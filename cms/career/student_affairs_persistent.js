/**
 * Student Affairs Sidebar Persistence Script
 * 
 * This script should be included on all Student Affairs CMS pages
 * to ensure the sidebar remains visible and properly highlighted
 * when navigating between pages.
 */

document.addEventListener('DOMContentLoaded', function() {
  // CRITICAL: Force sidebar to remain visible
  ensureSidebarVisible();
  
  // Save current page URL before navigating to preserve state
  const saveCurrentState = () => {
    sessionStorage.setItem('sa_prev_page', window.location.href);
  };

  // Handle navigation between pages
  const handleNavigation = (e) => {
    // Don't interfere with form submissions or links with target="_blank"
    if (e.target.tagName === 'FORM' || 
        (e.target.tagName === 'A' && e.target.getAttribute('target') === '_blank')) {
      return;
    }

    // Save current state
    saveCurrentState();
  };

  // Add event listeners to forms and links
  document.addEventListener('submit', handleNavigation);
  document.addEventListener('click', function(e) {
    // Find closest anchor element if clicked on a child
    const anchor = e.target.closest('a');
    if (anchor) {
      // If this is a sidebar link, ensure sidebar visibility is maintained
      if (anchor.classList.contains('sa-sidebar-link')) {
        e.preventDefault();
        
        // Store the href for later navigation
        const href = anchor.getAttribute('href');
        
        // First, highlight the clicked link
        document.querySelectorAll('.sa-sidebar-link').forEach(link => {
          link.classList.remove('active');
        });
        anchor.classList.add('active');
        
        // Then make the sidebar visible
        ensureSidebarVisible();
        
        // Finally, navigate to the page with a small delay to ensure UI updates
        setTimeout(() => {
          window.location.href = href;
        }, 100);
      } else {
        handleNavigation(e);
      }
    }
  });

  // Enhanced function to make sure sidebar is visible and properly styled
  function ensureSidebarVisible() {
    const sidebar = document.querySelector('.sa-sidebar');
    if (sidebar) {
      // Ensure it's visible with !important flags to override any page-specific styles
      sidebar.style.cssText = `
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        height: 100% !important;
        display: block !important;
        z-index: 1000 !important;
        width: 250px !important;
        overflow-y: auto !important;
      `;
      
      // Reset any classes that might hide or collapse it
      sidebar.classList.remove('collapsed');
      
      // Apply proper mobile vs desktop styling
      if (window.innerWidth < 768) {
        // On mobile, make sure it's properly shown when toggled
        if (sidebar.classList.contains('mobile-open')) {
          document.body.classList.add('sidebar-open');
        } else {
          document.body.classList.remove('sidebar-open');
        }
      } else {
        // On desktop, make sure content is properly aligned
        document.body.style.marginLeft = '250px';
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
          contentWrapper.style.maxWidth = 'calc(100% - 40px)';
          contentWrapper.style.margin = '20px auto';
        }
      }
    }
  }

  // Apply active class to the current page link
  const highlightCurrentLink = () => {
    const currentPath = window.location.pathname;
    const currentPageName = currentPath.split('/').pop(); // Get just the filename
    
    document.querySelectorAll('.sa-sidebar-link').forEach(link => {
      const linkHref = link.getAttribute('href');
      
      // First remove all active classes
      link.classList.remove('active');
      
      // Then apply to the matching link
      if (linkHref && linkHref.includes(currentPageName)) {
        link.classList.add('active');
      }
      
      // Special case for cms_af_page.php with section parameter
      if (currentPageName === 'cms_af_page.php') {
        const urlParams = new URLSearchParams(window.location.search);
        const section = urlParams.get('section');
        const linkSection = link.getAttribute('data-section');
        
        if (section && linkSection && section === linkSection) {
          link.classList.add('active');
        } else if (!section && linkSection === 'dashboard') {
          link.classList.add('active');
        }
      }
    });
  };
  
  // Execute on page load
  highlightCurrentLink();
  
  // Toggle sidebar when the toggle button is clicked
  const sidebarToggle = document.querySelector('.sa-sidebar-toggle');
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
      const sidebar = document.querySelector('.sa-sidebar');
      if (sidebar) {
        // Desktop behavior
        if (window.innerWidth >= 992) {
          sidebar.classList.toggle('collapsed');
          
          // Adjust content wrapper
          const contentWrapper = document.querySelector('.content-wrapper');
          if (contentWrapper) {
            contentWrapper.classList.toggle('sidebar-collapsed');
          }
        } 
        // Mobile behavior
        else if (window.innerWidth < 768) {
          sidebar.classList.toggle('mobile-open');
          document.body.classList.toggle('sidebar-open');
        }
        // Tablet behavior
        else {
          sidebar.classList.toggle('expanded');
          
          // Adjust content wrapper
          const contentWrapper = document.querySelector('.content-wrapper');
          if (contentWrapper) {
            contentWrapper.classList.toggle('sidebar-expanded');
          }
        }
      }
    });
  }
  
  // Handle window resize
  window.addEventListener('resize', function() {
    ensureSidebarVisible();
  });
  
  // Apply these fixes when window loads fully
  window.addEventListener('load', function() {
    ensureSidebarVisible();
  });
  
  // Apply fixes periodically to ensure sidebar stays visible
  setInterval(ensureSidebarVisible, 1000);
}); 