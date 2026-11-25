<?php
/**
 * AdminLTE 3 Layout Header & Top Navigation
 * Student Registration System
 */
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/functions.php';
    require_once __DIR__ . '/auth_check.php';
}

$pageTitle = $pageTitle ?? 'Dashboard';
$currentUser = current_user();

// Unread messages count for navbar notification badge
$unreadMsgCount = 0;
try {
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM contact_messages");
    $unreadMsgCount = (int)$stmtCount->fetchColumn();
} catch (Exception $e) {
    $unreadMsgCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Theme style (AdminLTE 3.2) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Custom styling -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button" title="Toggle Sidebar"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= BASE_URL ?>/dashboard.php" class="nav-link font-weight-bold">
                    <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= BASE_URL ?>/students.php" class="nav-link">
                    <i class="fas fa-users mr-1"></i> Students
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= BASE_URL ?>/index.php" target="_blank" class="nav-link text-primary" title="Open Public Website">
                    <i class="fas fa-external-link-alt mr-1"></i> View Public Site
                </a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto align-items-center">
            <!-- Messages Link -->
            <li class="nav-item dropdown">
                <a class="nav-link" href="<?= BASE_URL ?>/messages.php" title="Contact Messages">
                    <i class="far fa-envelope"></i>
                    <?php if ($unreadMsgCount > 0): ?>
                        <span class="badge badge-warning navbar-badge"><?= $unreadMsgCount ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- User Dropdown Menu -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                    <img src="<?= BASE_URL ?>/assets/images/avatar.svg" class="user-image img-circle elevation-1" alt="User Image">
                    <span class="d-none d-md-inline ml-2 font-weight-bold"><?= e($currentUser['full_name']) ?></span>
                    <span class="ml-2"><?= role_badge($currentUser['role']) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow">
                    <!-- User image -->
                    <li class="user-header bg-primary">
                        <img src="<?= BASE_URL ?>/assets/images/avatar.svg" class="img-circle elevation-2" alt="User Image">
                        <p>
                            <?= e($currentUser['full_name']) ?>
                            <small class="d-block text-white-50">@<?= e($currentUser['username']) ?> &bull; <?= strtoupper(e($currentUser['role'])) ?></small>
                        </p>
                    </li>
                    <!-- Menu Footer-->
                    <li class="user-footer d-flex justify-content-between">
                        <?php if (is_admin()): ?>
                            <a href="<?= BASE_URL ?>/user_edit.php?id=<?= (int)$currentUser['id'] ?>" class="btn btn-default btn-flat btn-sm">
                                <i class="fas fa-user-edit mr-1"></i> Edit Profile
                            </a>
                        <?php else: ?>
                            <span class="text-muted small align-self-center"><i class="fas fa-user-check text-success mr-1"></i> Logged In</span>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-danger btn-flat btn-sm">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Toggle Fullscreen">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->
