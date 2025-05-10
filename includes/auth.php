<?php
/**
 * Authentication functions for WMSU Services
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in
 * @return boolean
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) || isset($_SESSION['user_id']);
}

/**
 * Check if current user has admin privileges
 * @return boolean
 */
function isAdmin() {
    return isset($_SESSION['admin_id']) || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    if (isset($_SESSION['admin_id'])) {
        return $_SESSION['admin_id'];
    } elseif (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    return null;
}

/**
 * Get current user's role
 * @return string|null User role or null if not logged in
 */
function getUserRole() {
    if (isset($_SESSION['user_role'])) {
        return $_SESSION['user_role'];
    } elseif (isset($_SESSION['admin_id'])) {
        return 'admin';
    }
    return null;
}

/**
 * Check if user has permission for specific module
 * @param string $module The module to check access for
 * @return boolean
 */
function hasPermission($module) {
    // If admin, always has permission
    if (isAdmin()) {
        return true;
    }
    
    // Check if user permissions exist and include the module
    if (isset($_SESSION['permissions']) && is_array($_SESSION['permissions'])) {
        return in_array($module, $_SESSION['permissions']);
    }
    
    return false;
}

/**
 * Redirect users without permission
 * @param string $module The module to check
 * @param string $redirect URL to redirect to
 */
function requirePermission($module, $redirect = '../../login.php') {
    if (!hasPermission($module)) {
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Log out current user
 */
function logOut() {
    // Unset all session variables
    $_SESSION = array();
    
    // If it's desired to kill the session, also delete the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Finally, destroy the session.
    session_destroy();
}

/**
 * Create a temporary admin session for testing
 * Only works if no user is currently logged in
 * @param int $adminId Admin ID to set
 * @return boolean Success status
 */
function createAdminSession($adminId = 1) {
    if (!isLoggedIn()) {
        $_SESSION['admin_id'] = $adminId;
        $_SESSION['user_role'] = 'admin';
        return true;
    }
    return false;
} 