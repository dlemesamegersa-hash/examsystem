<?php
/**
 * Delete Student Record
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method for deletion.');
    header('Location: ' . BASE_URL . '/students.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    set_flash('danger', 'Security verification failed. Please try again.');
    header('Location: ' . BASE_URL . '/students.php');
    exit;
}

$studentId = (int)($_POST['id'] ?? 0);
if ($studentId <= 0) {
    set_flash('danger', 'Invalid student ID specified.');
    header('Location: ' . BASE_URL . '/students.php');
    exit;
}

try {
    // Retrieve student first to unlink photo
    $stmt = $pdo->prepare("SELECT id, full_name, photo FROM students WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $studentId]);
    $student = $stmt->fetch();

    if ($student) {
        // Delete photo if exists
        delete_photo_file($student['photo']);

        // Delete from database
        $delStmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        $delStmt->execute([':id' => $studentId]);

        set_flash('success', 'Student <strong>' . e($student['full_name']) . '</strong> has been permanently removed.');
    } else {
        set_flash('warning', 'Student record was not found or has already been deleted.');
    }

} catch (PDOException $e) {
    set_flash('danger', 'Database error while deleting student: ' . $e->getMessage());
}

header('Location: ' . BASE_URL . '/students.php');
exit;
