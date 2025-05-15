<?php
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="cms-sidebar">
    <div class="sidebar-header">
        <i class="fas fa-university"></i>
        <span>CMS Dashboard</span>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <h5>Content Management</h5>
            <ul>
                <li class="<?= $current_page === 'cms_af_page.php' ? 'active' : '' ?>">
                    <a href="cms_af_page.php"><i class="fas fa-home"></i> About Section</a>
                </li>
                <li class="<?= $current_page === 'cms_mv_mission_vision.php' ? 'active' : '' ?>">
                    <a href="cms_mv_mission_vision.php"><i class="fas fa-bullseye"></i> Mission & Vision</a>
                </li>
                <li class="<?= $current_page === 'cms_af_page_obj_funct.php' ? 'active' : '' ?>">
                    <a href="cms_af_page_obj_funct.php"><i class="fas fa-tasks"></i> Objectives & Functions</a>
                </li>
                <li class="<?= $current_page === 'cms_af_page_services.php' ? 'active' : '' ?>">
                    <a href="cms_af_page_services.php"><i class="fas fa-concierge-bell"></i> Services</a>
                </li>
                <li class="<?= $current_page === 'cms_af_page_activities.php' ? 'active' : '' ?>">
                    <a href="cms_af_page_activities.php"><i class="fas fa-calendar-alt"></i> Activities</a>
                </li>
                <li class="<?= $current_page === 'cms_af_page_facilities.php' ? 'active' : '' ?>">
                    <a href="cms_af_page_facilities.php"><i class="fas fa-building"></i> Facilities</a>
                </li>
            </ul>
        </div>

        <div class="nav-section">
            <h5>Information</h5>
            <ul>
                <li class="<?= $current_page === 'cms_af_page_info.php' ? 'active' : '' ?>">
                    <a href="cms_af_page_info.php"><i class="fas fa-info-circle"></i> Page Info</a>
                </li>
                <li class="<?= $current_page === 'cms_af_page_contact.php' ? 'active' : '' ?>">
                    <a href="cms_af_page_contact.php"><i class="fas fa-address-book"></i> Contact Info</a>
                </li>
                <li class="<?= $current_page === 'cms_af_page_officer.php' ? 'active' : '' ?>">
                    <a href="cms_af_page_officer.php"><i class="fas fa-users"></i> Officers</a>
                </li>
                <li class="<?= $current_page === 'cms_af_page_process.php' ? 'active' : '' ?>">
                    <a href="cms_af_page_process.php"><i class="fas fa-clipboard-list"></i> Process</a>
                </li>
            </ul>
        </div>
    </nav>
</div>

<style>
.cms-sidebar {
    width: 280px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: #2c3e50;
    color: #ecf0f1;
    padding: 20px 0;
    overflow-y: auto;
    z-index: 1000;
    transition: all 0.3s ease;
}

.sidebar-header {
    padding: 0 20px 20px;
    border-bottom: 1px solid #34495e;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.2em;
    font-weight: bold;
}

.sidebar-nav {
    padding: 20px 0;
}

.nav-section {
    margin-bottom: 20px;
}

.nav-section h5 {
    padding: 0 20px;
    color: #95a5a6;
    font-size: 0.9em;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav li {
    margin: 2px 0;
}

.sidebar-nav li a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    color: #ecf0f1;
    text-decoration: none;
    transition: all 0.2s ease;
}

.sidebar-nav li a i {
    width: 20px;
    text-align: center;
}

.sidebar-nav li:hover a {
    background: #34495e;
    color: #3498db;
}

.sidebar-nav li.active a {
    background: #3498db;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .cms-sidebar {
        width: 60px;
    }

    .cms-sidebar:hover {
        width: 280px;
    }

    .sidebar-header span,
    .nav-section h5 {
        display: none;
    }

    .cms-sidebar:hover .sidebar-header span,
    .cms-sidebar:hover .nav-section h5 {
        display: block;
    }

    .sidebar-nav li a span {
        display: none;
    }

    .cms-sidebar:hover .sidebar-nav li a span {
        display: inline;
    }
}

/* Main content adjustment */
.container {
    margin-left: 280px;
    padding: 20px;
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .container {
        margin-left: 60px;
    }
}
</style> 