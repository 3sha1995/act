<?php
// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="admin-nav">
    <div class="admin-nav-container">
        <div class="admin-nav-brand">
            <a href="../admin/dashboard.php">
                <i class="fas fa-university"></i>
                <span>WMSU Services</span>
            </a>
        </div>
        <div class="admin-nav-menu">
            <a href="../admin/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="../admin/student_affairs/" class="<?php echo strpos($current_page, 'student_affairs') !== false ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i>
                <span>Student Affairs</span>
            </a>
            <a href="../admin/health_services/" class="<?php echo strpos($current_page, 'health_services') !== false ? 'active' : ''; ?>">
                <i class="fas fa-hospital"></i>
                <span>Health Services</span>
            </a>
            <a href="../admin/guidance/" class="<?php echo strpos($current_page, 'guidance') !== false ? 'active' : ''; ?>">
                <i class="fas fa-hands-helping"></i>
                <span>Guidance</span>
            </a>
        </div>
        <div class="admin-nav-user">
            <div class="user-profile">
                <i class="fas fa-user-circle"></i>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
            </div>
            <a href="../includes/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</nav>

<style>
    .admin-nav {
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .admin-nav-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .admin-nav-brand {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .admin-nav-brand a {
        color: var(--primary-color);
        text-decoration: none;
        font-size: 1.2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .admin-nav-brand i {
        font-size: 1.5rem;
    }

    .admin-nav-menu {
        display: flex;
        gap: 20px;
    }

    .admin-nav-menu a {
        color: var(--text-color);
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .admin-nav-menu a:hover,
    .admin-nav-menu a.active {
        background: var(--secondary-color);
        color: white;
    }

    .admin-nav-user {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-color);
    }

    .user-profile i {
        font-size: 1.2rem;
    }

    .logout-btn {
        color: var(--accent-color);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .logout-btn:hover {
        background: var(--accent-color);
        color: white;
    }

    @media (max-width: 768px) {
        .admin-nav-container {
            flex-direction: column;
            gap: 15px;
        }

        .admin-nav-menu {
            flex-wrap: wrap;
            justify-content: center;
        }

        .admin-nav-user {
            width: 100%;
            justify-content: center;
        }
    }
</style> 