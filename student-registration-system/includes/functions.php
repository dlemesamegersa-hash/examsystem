<?php
/**
 * Helper Functions & Security Utilities
 * Student Registration System
 */

// Escape output to prevent XSS attacks
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Generate CSRF Token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Render CSRF Hidden Input Field
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

// Verify CSRF Token
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Set Flash Message (success, danger, warning, info)
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Display and Clear Flash Message
function display_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        
        $type = e($flash['type']);
        $message = $flash['message']; // Can contain safe HTML or plaintext
        $icon = 'fas fa-info-circle';
        if ($type === 'success') $icon = 'fas fa-check-circle';
        elseif ($type === 'danger') $icon = 'fas fa-exclamation-triangle';
        elseif ($type === 'warning') $icon = 'fas fa-exclamation-circle';

        return '
        <div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
            <i class="' . $icon . ' mr-2"></i> ' . $message . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>';
    }
    return '';
}

// Check if User is Logged In
function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

// Check if Logged In User is Admin
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Get Current User Info
function current_user($key = null) {
    if (!is_logged_in()) {
        return null;
    }
    if ($key !== null) {
        return $_SESSION[$key] ?? null;
    }
    return [
        'id'        => $_SESSION['user_id'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? 'User',
        'username'  => $_SESSION['username'] ?? '',
        'email'     => $_SESSION['email'] ?? '',
        'role'      => $_SESSION['role'] ?? 'staff',
        'status'    => $_SESSION['status'] ?? 'active'
    ];
}

// Format Date for Display
function format_date($dateStr, $format = 'M d, Y') {
    if (empty($dateStr) || $dateStr === '0000-00-00' || $dateStr === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    $timestamp = strtotime($dateStr);
    return $timestamp ? date($format, $timestamp) : 'N/A';
}

// Return Badge for Status (active/inactive)
function status_badge($status) {
    $status = strtolower((string)$status);
    if ($status === 'active') {
        return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Active</span>';
    }
    return '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-ban mr-1"></i>Inactive</span>';
}

// Return Badge for Role (admin/staff)
function role_badge($role) {
    $role = strtolower((string)$role);
    if ($role === 'admin') {
        return '<span class="badge badge-primary px-2 py-1"><i class="fas fa-shield-alt mr-1"></i>Admin</span>';
    }
    return '<span class="badge badge-info px-2 py-1"><i class="fas fa-user mr-1"></i>Staff</span>';
}

// Upload Student Photo safely
function handle_photo_upload($fileInputName, &$errorMessage = '') {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // No file uploaded, optional
    }

    $file = $_FILES[$fileInputName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'File upload error code: ' . $file['error'];
        return false;
    }

    // Limit size: 3MB
    $maxSize = 3 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        $errorMessage = 'Photo file size exceeds the 3MB maximum limit.';
        return false;
    }

    // Allowed mime types & extensions
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mime, $allowedTypes)) {
        $errorMessage = 'Invalid file format. Only JPG, PNG, WEBP, and GIF images are allowed.';
        return false;
    }

    $ext = $allowedTypes[$mime];
    $newFileName = 'student_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    $targetDir = UPLOAD_DIR;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $targetPath = $targetDir . $newFileName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newFileName;
    } else {
        $errorMessage = 'Failed to move uploaded photo to storage directory.';
        return false;
    }
}

// Safely delete student photo file
function delete_photo_file($fileName) {
    if (!empty($fileName)) {
        $filePath = UPLOAD_DIR . basename($fileName);
        if (file_exists($filePath) && is_file($filePath)) {
            @unlink($filePath);
        }
    }
}

// Student photo URL resolver
function get_student_photo_url($photoName) {
    if (!empty($photoName)) {
        $filePath = UPLOAD_DIR . basename($photoName);
        if (file_exists($filePath)) {
            return BASE_URL . '/uploads/students/' . rawurlencode($photoName);
        }
    }
    return BASE_URL . '/assets/images/avatar.svg';
}
