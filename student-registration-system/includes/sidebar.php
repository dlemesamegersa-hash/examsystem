<?php
/**
 * AdminLTE 3 Sidebar Navigation
 * Student Registration System
 */

$currentPage = basename($_SERVER['PHP_SELF']);
$currentUser = current_user();

// Count total contact messages for badge
$msgCount = 0;
try {
    $qMsg = $pdo->query("SELECT COUNT(*) FROM contact_messages");
    $msgCount = (int)$qMsg->fetchColumn();
} catch (Exception $e) {
    $msgCount = 0;
}
?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= BASE_URL ?>/dashboard.php" class="brand-link text-center">
        <i class="fas fa-graduation-cap brand-image img-circle elevation-3 ml-2 mr-2" style="font-size: 1.4rem; line-height: 1.6; opacity: .9; background: #007bff; color: white; padding: 4px 6px;"></i>
        <span class="brand-text font-weight-bold">SRS Admin</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
                <img src="<?= BASE_URL ?>/assets/images/avatar.svg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="<?= BASE_URL ?>/dashboard.php" class="d-block font-weight-bold text-white mb-0">
                    <?= e($currentUser['full_name']) ?>
                </a>
                <span class="badge <?= ($currentUser['role'] === 'admin') ? 'badge-primary' : 'badge-info' ?> text-uppercase mt-1" style="font-size: 0.65rem;">
                    <?= e($currentUser['role']) ?>
                </span>
                <span class="badge badge-success" style="font-size: 0.6rem;"><i class="fas fa-circle mr-1" style="font-size: 0.5rem;"></i> Online</span>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/dashboard.php" class="nav-link <?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header text-uppercase font-weight-bold text-muted small">Student Records</li>

                <!-- Student Registration -->
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/student_register.php" class="nav-link <?= ($currentPage === 'student_register.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-user-plus"></i>
                        <p>Student Registration</p>
                    </a>
                </li>

                <!-- Show Students -->
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/students.php" class="nav-link <?= (in_array($currentPage, ['students.php', 'student_view.php', 'student_edit.php'])) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Show Students</p>
                    </a>
                </li>

                <li class="nav-header text-uppercase font-weight-bold text-muted small">Administration</li>

                <!-- User Management (Admin Only) -->
                <?php if (is_admin()): ?>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/users.php" class="nav-link <?= (in_array($currentPage, ['users.php', 'user_add.php', 'user_edit.php'])) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>User Management</p>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Contact Messages -->
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/messages.php" class="nav-link <?= ($currentPage === 'messages.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-envelope"></i>
                        <p>
                            Contact Messages
                            <?php if ($msgCount > 0): ?>
                                <span class="badge badge-info right"><?= $msgCount ?></span>
                            <?php endif; ?>
                        </p>
                    </a>
                </li>

                <li class="nav-header text-uppercase font-weight-bold text-muted small">Session</li>

                <!-- Public Portal Link -->
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/index.php" target="_blank" class="nav-link">
                        <i class="nav-icon fas fa-globe"></i>
                        <p>Public Website <i class="fas fa-external-link-alt right text-muted"></i></p>
                    </a>
                </li>

                <!-- Logout -->
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/logout.php" class="nav-link text-danger logout-btn">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
