<?php
/**
 * Public Home Page
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home - Student Registration System';
require_once __DIR__ . '/includes/public_header.php';

// Fetch quick stats for public counter
$totalStudents = 0;
$totalDepartments = 0;
try {
    $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'")->fetchColumn();
    $totalDepartments = (int)$pdo->query("SELECT COUNT(DISTINCT department) FROM students WHERE department IS NOT NULL AND department != ''")->fetchColumn();
} catch (Exception $e) {
    // If db error, fail silently on public stats
}
?>

<!-- Hero Section -->
<section class="hero-section text-center text-lg-left">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-5 mb-lg-0">
                <span class="badge badge-pill badge-light text-primary px-3 py-2 font-weight-bold mb-3 shadow-sm">
                    <i class="fas fa-university mr-1"></i> Academic Year <?= date('Y') ?> Admissions
                </span>
                <h1 class="hero-title mb-3">
                    Student Registration & Management System
                </h1>
                <p class="hero-subtitle mb-4">
                    A centralized, responsive, and secure platform for student enrollment, academic profiling, department records, and role-based administrative control.
                </p>
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start">
                    <?php if (is_logged_in()): ?>
                        <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-warning btn-lg font-weight-bold shadow px-4 py-3 mr-3 mb-2">
                            <i class="fas fa-tachometer-alt mr-2"></i> Go to Admin Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/register.php" class="btn btn-warning btn-lg font-weight-bold shadow px-4 py-3 mr-3 mb-2">
                            <i class="fas fa-user-plus mr-2"></i> User Registration
                        </a>
                        <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline-light btn-lg font-weight-bold px-4 py-3 mb-2">
                            <i class="fas fa-sign-in-alt mr-2"></i> Portal Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="bg-white p-4 rounded-lg shadow-lg text-dark mx-auto" style="max-width: 420px; border-top: 5px solid #007bff;">
                    <div class="feature-icon-wrapper bg-light text-primary mx-auto mb-3">
                        <i class="fas fa-user-graduate fa-2x"></i>
                    </div>
                    <h4 class="font-weight-bold mb-2">Admissions & Records</h4>
                    <p class="text-muted small mb-4">
                        Seamlessly register new students, track enrollment numbers, upload identity photos, and manage academic departments with ease.
                    </p>
                    <div class="row text-center border-top pt-3">
                        <div class="col-6 border-right">
                            <h3 class="font-weight-bold text-primary mb-0"><?= $totalStudents > 0 ? $totalStudents : '5+' ?></h3>
                            <small class="text-muted text-uppercase font-weight-bold">Active Students</small>
                        </div>
                        <div class="col-6">
                            <h3 class="font-weight-bold text-success mb-0"><?= $totalDepartments > 0 ? $totalDepartments : '5+' ?></h3>
                            <small class="text-muted text-uppercase font-weight-bold">Departments</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Flash Alerts if any -->
<div class="container mt-4">
    <?= display_flash() ?>
</div>

<!-- Key Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-primary text-uppercase font-weight-bold tracking-wide">Why Choose Our System</h6>
            <h2 class="font-weight-bold">Core Highlights & Capabilities</h2>
            <p class="text-muted mx-auto" style="max-width: 650px;">
                Designed specifically for institutional scalability, high data integrity, and effortless user experience.
            </p>
        </div>

        <div class="row">
            <!-- Feature 1 -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm feature-card p-3">
                    <div class="card-body">
                        <div class="feature-icon-wrapper bg-primary text-white shadow-sm">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h5 class="card-title font-weight-bold">Student Registration</h5>
                        <p class="card-text text-muted">
                            Capture comprehensive student profiles including registration numbers, contact details, date of birth, course, and photo credentials.
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <a href="<?= BASE_URL ?>/about.php" class="text-primary font-weight-bold small">
                            Learn more <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm feature-card p-3">
                    <div class="card-body">
                        <div class="feature-icon-wrapper bg-success text-white shadow-sm">
                            <i class="fas fa-columns"></i>
                        </div>
                        <h5 class="card-title font-weight-bold">AdminLTE 3 Dashboard</h5>
                        <p class="card-text text-muted">
                            Modern responsive administrative console equipped with collapsible sidebar navigation, live counter widgets, and tabular analytics.
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <a href="<?= BASE_URL ?>/login.php" class="text-success font-weight-bold small">
                            Explore dashboard <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm feature-card p-3">
                    <div class="card-body">
                        <div class="feature-icon-wrapper bg-info text-white shadow-sm">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h5 class="card-title font-weight-bold">Role-Based Access</h5>
                        <p class="card-text text-muted">
                            Multi-tiered authentication separating Administrator rights from Staff operations with Bcrypt password hashing and PDO prepared statements.
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <a href="<?= BASE_URL ?>/about.php" class="text-info font-weight-bold small">
                            Security overview <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container text-center py-4">
        <h3 class="font-weight-bold mb-3">Ready to Manage Student Records Effortlessly?</h3>
        <p class="text-muted mx-auto mb-4" style="max-width: 600px;">
            Access the centralized administration portal or create a new user account to get started immediately.
        </p>
        <div class="d-flex justify-content-center flex-wrap">
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-lg px-4 mx-2 mb-2 font-weight-bold shadow-sm">
                <i class="fas fa-sign-in-alt mr-2"></i> Staff & Admin Login
            </a>
            <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-secondary btn-lg px-4 mx-2 mb-2 font-weight-bold">
                <i class="fas fa-envelope mr-2"></i> Contact Admissions
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
