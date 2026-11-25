<?php
/**
 * Authentication & Access Control Verification
 * Student Registration System
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// Check if user is authenticated
if (!is_logged_in()) {
    set_flash('danger', 'You must log in to access this page.');
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Function to enforce admin-only access
function require_admin() {
    if (!is_admin()) {
        set_flash('danger', 'Access denied. Administrator privileges are required.');
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}
