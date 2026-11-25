<?php
/**
 * Public Website Header
 * Student Registration System
 */
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/functions.php';
}

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitle ?? 'Home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <!-- Google Font: Source Sans Pro & Inter -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Bootstrap 4.6 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Top Announcement Bar -->
    <div class="bg-primary text-white py-1 px-3 small d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-university mr-2"></i> Academic Portal - Online Admissions & Student Records
            </div>
            <div>
                <i class="fas fa-envelope mr-1"></i> info@studreg.edu
                <span class="mx-2">|</span>
                <i class="fas fa-phone-alt mr-1"></i> +1 (800) 555-REG-SYS
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand font-weight-bold d-flex align-items-center" href="<?= BASE_URL ?>/index.php">
                <span class="brand-icon mr-2 bg-primary text-white rounded p-1 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="fas fa-graduation-cap"></i>
                </span>
                <span><?= e(APP_NAME) ?></span>
            </a>
            
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="publicNavbar">
                <ul class="navbar-nav ml-auto align-items-lg-center">
                    <li class="nav-item <?= ($currentPage === 'index.php') ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= BASE_URL ?>/index.php">
                            <i class="fas fa-home mr-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item <?= ($currentPage === 'about.php') ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= BASE_URL ?>/about.php">
                            <i class="fas fa-info-circle mr-1"></i> About Us
                        </a>
                    </li>
                    <li class="nav-item <?= ($currentPage === 'contact.php') ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= BASE_URL ?>/contact.php">
                            <i class="fas fa-envelope mr-1"></i> Contact Us
                        </a>
                    </li>
                    <li class="nav-item <?= ($currentPage === 'register.php') ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= BASE_URL ?>/register.php">
                            <i class="fas fa-user-plus mr-1"></i> Registration
                        </a>
                    </li>
                    
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item ml-lg-2">
                            <a class="btn btn-outline-light btn-sm px-3" href="<?= BASE_URL ?>/dashboard.php">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard (<?= e($_SESSION['username']) ?>)
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ml-lg-2 <?= ($currentPage === 'login.php') ? 'active' : '' ?>">
                            <a class="btn btn-primary btn-sm px-3 shadow-sm" href="<?= BASE_URL ?>/login.php">
                                <i class="fas fa-sign-in-alt mr-1"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="flex-grow-1">
