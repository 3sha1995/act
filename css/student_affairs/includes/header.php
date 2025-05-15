<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f1c40f;
            --info-color: #3498db;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        /* Sidebar Styles */
        .cms-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background: var(--dark-color);
            color: white;
            padding: 20px 0;
            overflow-y: auto;
            transition: width 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header i {
            font-size: 24px;
        }

        .sidebar-header span {
            font-size: 20px;
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 0 10px;
        }

        .nav-section h5 {
            padding: 10px;
            margin: 0;
            color: var(--light-color);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-section li {
            margin: 5px 0;
        }

        .nav-section a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-section a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-section li.active a {
            background: var(--primary-color);
        }

        /* Editor Styles */
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

        /* Image Preview Styles */
        .preview-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .current-image-preview {
            max-width: 300px;
            margin: 10px 0;
            border: 2px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .image-preview-container {
            margin: 15px 0;
        }

        .image-preview-label {
            display: block;
            font-weight: 500;
            margin-bottom: 10px;
            color: #495057;
        }

        .no-image-placeholder {
            padding: 20px;
            background: #e9ecef;
            border: 2px dashed #ced4da;
            border-radius: 4px;
            text-align: center;
            color: #6c757d;
        }

        /* Switch Toggle */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
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
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--success-color);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* Tooltip */
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .cms-sidebar {
                width: 60px;
            }

            .cms-sidebar .sidebar-header span,
            .cms-sidebar .nav-section h5,
            .cms-sidebar .nav-section a span {
                display: none;
            }

            body {
                margin-left: 60px;
            }

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
    <!-- Mobile Sidebar Toggle -->
    <button class="toggle-sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Navigation -->
    <nav class="cms-sidebar">
        <div class="sidebar-header">
            <h3>CMS Navigation</h3>
        </div>
        <ul class="sidebar-nav">
            <li>
                <a href="cms_af_page.php" <?= basename($_SERVER['PHP_SELF']) === 'cms_af_page.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-info-circle"></i> About Section
                </a>
            </li>
            <li>
                <a href="cms_mv_mission_vision.php" <?= basename($_SERVER['PHP_SELF']) === 'cms_mv_mission_vision.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-bullseye"></i> Mission & Vision
                </a>
            </li>
            <li>
                <a href="cms_af_page_officer.php" <?= basename($_SERVER['PHP_SELF']) === 'cms_af_page_officer.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-users"></i> Officers
                </a>
            </li>
            <li>
                <a href="cms_af_page_services.php" <?= basename($_SERVER['PHP_SELF']) === 'cms_af_page_services.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-handshake"></i> Services
                </a>
            </li>
            <li>
                <a href="cms_af_page_process.php" <?= basename($_SERVER['PHP_SELF']) === 'cms_af_page_process.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-tasks"></i> Process
                </a>
            </li>
            <li>
                <a href="cms_af_page_activities.php" <?= basename($_SERVER['PHP_SELF']) === 'cms_af_page_activities.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-calendar-alt"></i> Activities
                </a>
            </li>
            <li>
                <a href="cms_af_page_facilities.php" <?= basename($_SERVER['PHP_SELF']) === 'cms_af_page_facilities.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-building"></i> Facilities
                </a>
            </li>
            <li>
                <a href="cms_af_page_obj_funct.php" <?= basename($_SERVER['PHP_SELF']) === 'cms_af_page_obj_funct.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-list-check"></i> Objectives & Functions
                </a>
            </li>
            <li>
                <a href="cms_af_page_contact.php" <?= basename($_SERVER['PHP_SELF']) === 'cms_af_page_contact.php' ? 'class="active"' : '' ?>>
                    <i class="fas fa-address-book"></i> Contact Information
                </a>
            </li>
        </ul>
    </nav>

    <?php include 'sidebar.php'; ?>
</body>
</html> 