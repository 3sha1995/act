<?php
/**
 * Student Affairs Sidebar Template
 * 
 * This file provides a consistent sidebar for all Student Affairs CMS pages
 * Include this file in all Student Affairs CMS pages
 */
?>

<!-- Student Affairs Sidebar -->
<div class="sa-sidebar">
    <div class="sa-sidebar-header">
        <div class="sa-sidebar-logo">
            <img src="../../imgs/WMSU-Logo.png" alt="OSA Logo" style="max-width: 40px; height: auto;">
            <h2 class="sa-sidebar-title" style="font-size: 1rem; margin-left: 8px;">Student Affairs</h2>
        </div>
    </div>
    
    <ul class="sa-sidebar-menu">
        <li class="sa-sidebar-item">
            <a href="cms_af_page.php?section=dashboard" class="sa-sidebar-link" data-section="dashboard">
                <i class="fa fa-home sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">Dashboard</span>
            </a>
        </li>
        
        <li class="sa-sidebar-item">
            <a href="cms_af_page.php?section=about" class="sa-sidebar-link" data-section="about">
                <i class="fa fa-info-circle sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">About Section</span>
            </a>
        </li>
        
        <li class="sa-sidebar-item">
            <a href="cms_af_page_contact.php" class="sa-sidebar-link" data-section="contact">
                <i class="fa fa-address-book sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">Contact Information</span>
            </a>
        </li>
        
        <li class="sa-sidebar-item">
            <a href="cms_af_page_services.php" class="sa-sidebar-link" data-section="services">
                <i class="fa fa-clipboard-list sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">Services</span>
            </a>
        </li>
        
        <li class="sa-sidebar-item">
            <a href="cms_af_page_officer.php" class="sa-sidebar-link" data-section="officer">
                <i class="fa fa-user-tie sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">Officers</span>
            </a>
        </li>
        
        <li class="sa-sidebar-item">
            <a href="cms_af_page_info.php" class="sa-sidebar-link" data-section="info">
                <i class="fa fa-info sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">Information</span>
            </a>
        </li>
        
        <li class="sa-sidebar-item">
            <a href="cms_af_page_facilities.php" class="sa-sidebar-link" data-section="facilities">
                <i class="fa fa-building sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">Facilities</span>
            </a>
        </li>
        
        <li class="sa-sidebar-item">
            <a href="cms_af_page_activities.php" class="sa-sidebar-link" data-section="activities">
                <i class="fa fa-calendar-alt sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">Activities</span>
            </a>
        </li>
        
        <li class="sa-sidebar-item">
            <a href="cms_af_page_process.php" class="sa-sidebar-link" data-section="process">
                <i class="fa fa-cogs sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">Processes</span>
            </a>
        </li>
        
        <li class="sa-sidebar-item">
            <a href="cms_af_page_obj_funct.php" class="sa-sidebar-link" data-section="obj-funct">
                <i class="fa fa-bullseye sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">Objectives & Functions</span>
            </a>
        </li>
        
        <li class="sa-sidebar-item">
            <a href="cms_mv_mission_vision.php" class="sa-sidebar-link" data-section="mission-vision">
                <i class="fa fa-eye sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">Mission & Vision</span>
            </a>
        </li>
        
        <!-- Logout option at the bottom -->
        <li class="sa-sidebar-item" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 15px;">
            <a href="../../logout.php" class="sa-sidebar-link" data-section="logout">
                <i class="fa fa-sign-out-alt sa-sidebar-icon"></i>
                <span class="sa-sidebar-text">Logout</span>
            </a>
        </li>
    </ul>
</div>

<!-- Make sure to include the font-awesome CSS in your main template -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- Include the sidebar CSS -->
<link rel="stylesheet" href="student_affairs_sidebar.css">
<!-- Include the sidebar JS -->