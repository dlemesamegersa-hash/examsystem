<?php
/**
 * Delete User Account (Admin Only)
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

// Enforce admin-only access
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid deletion request method.');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    set_flash('danger', 'Security verification failed.');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$targetId = (int)($_POST['id'] ?? 0);
$currentAdminId = (int)$_SESSION['user_id'];

// Safeguard: Cannot delete self
if ($targetId === $currentAdminId) {
    set_flash('danger', 'Security violation: You cannot delete your own active administrator account.');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

if ($targetId <= 0) {
    set_flash('danger', 'Invalid user specified for deletion.');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

try {
    // Check if target user exists
    $stmt = $pdo->prepare("SELECT id, username, full_name FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $targetId]);
    $user = $stmt->fetch();

    if ($user) {
        $delStmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $delStmt->execute([':id' => $targetId]);

        set_flash('success', 'User <strong>' . e($user['full_name']) . '</strong> (@' . e($user['username']) . ') has been deleted successfully.');
    } else {
        set_flash('warning', 'User account was not found or already deleted.');
    }

} catch (PDOException $e) {
    set_flash('danger', 'Database error while deleting user: ' . $e->getMessage());
}

header('Location: ' . BASE_URL . '/users.php');
exit;
