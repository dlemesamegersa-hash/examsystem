<?php
/**
 * View Student Profile Details
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$studentId = (int)($_GET['id'] ?? 0);
if ($studentId <= 0) {
    set_flash('danger', 'Invalid student identifier provided.');
    header('Location: ' . BASE_URL . '/students.php');
    exit;
}

// Fetch student record
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $studentId]);
    $student = $stmt->fetch();

    if (!$student) {
        set_flash('danger', 'Student record not found.');
        header('Location: ' . BASE_URL . '/students.php');
        exit;
    }
} catch (PDOException $e) {
    set_flash('danger', 'Database error: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/students.php');
    exit;
}

$pageTitle = 'Student Profile: ' . $student['full_name'] . ' - ' . APP_NAME;

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-id-card mr-2 text-primary"></i> Student Profile
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/students.php">Students</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content pb-5">
        <div class="container-fluid">

            <?= display_flash() ?>

            <div class="row">
                <!-- Left Column: Student Quick Card -->
                <div class="col-md-4">
                    <!-- Profile Card -->
                    <div class="card card-primary card-outline shadow-sm text-center">
                        <div class="card-body box-profile">
                            <div class="text-center mb-3">
                                <img class="student-avatar-lg shadow" 
                                     src="<?= get_student_photo_url($student['photo']) ?>" 
                                     alt="<?= e($student['full_name']) ?>">
                            </div>

                            <h3 class="profile-username font-weight-bold mb-1"><?= e($student['full_name']) ?></h3>
                            <p class="text-muted text-monospace mb-2"><?= e($student['registration_number']) ?></p>

                            <div class="mb-3">
                                <?= status_badge($student['status']) ?>
                                <span class="badge badge-info px-2 py-1 ml-1"><?= e($student['department'] ?: 'Unassigned') ?></span>
                            </div>

                            <ul class="list-group list-group-unbordered mb-3 text-left small">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-venus-mars text-muted mr-2"></i> Gender</span>
                                    <span class="font-weight-bold text-capitalize"><?= e($student['gender']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-birthday-cake text-muted mr-2"></i> Date of Birth</span>
                                    <span class="font-weight-bold"><?= format_date($student['date_of_birth']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-calendar-check text-muted mr-2"></i> Admission Date</span>
                                    <span class="font-weight-bold"><?= format_date($student['admission_date']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-clock text-muted mr-2"></i> Registered Since</span>
                                    <span class="font-weight-bold"><?= format_date($student['created_at'], 'M d, Y H:i') ?></span>
                                </li>
                            </ul>

                            <div class="d-flex justify-content-between">
                                <a href="<?= BASE_URL ?>/student_edit.php?id=<?= (int)$student['id'] ?>" class="btn btn-primary btn-block mr-2 font-weight-bold">
                                    <i class="fas fa-edit mr-1"></i> Edit Profile
                                </a>
                                <button type="button" 
                                        class="btn btn-danger btn-delete-confirm" 
                                        data-id="<?= (int)$student['id'] ?>" 
                                        data-action="<?= BASE_URL ?>/student_delete.php" 
                                        data-message="Are you sure you want to delete student &quot;<?= e($student['full_name']) ?>&quot;? This cannot be undone."
                                        title="Delete Student">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Detailed Breakdown -->
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold mb-0 text-dark">
                                <i class="fas fa-info-circle text-primary mr-2"></i> Comprehensive Student Information
                            </h5>
                            <div>
                                <a href="javascript:window.print()" class="btn btn-outline-secondary btn-sm mr-2">
                                    <i class="fas fa-print mr-1"></i> Print Slip
                                </a>
                                <a href="<?= BASE_URL ?>/students.php" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                                </a>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <!-- Academic Details -->
                            <h6 class="text-uppercase text-primary font-weight-bold border-bottom pb-2 mb-3">
                                <i class="fas fa-graduation-cap mr-1"></i> Academic Record
                            </h6>
                            <div class="row mb-4">
                                <div class="col-sm-4 mb-3">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Student ID</small>
                                    <span class="font-weight-bold text-dark">#<?= (int)$student['id'] ?></span>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Registration Number</small>
                                    <span class="badge badge-light border text-monospace font-weight-bold" style="font-size: 1rem;">
                                        <?= e($student['registration_number']) ?>
                                    </span>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Department / Major</small>
                                    <span class="font-weight-bold text-dark"><?= e($student['department'] ?: 'Not Assigned') ?></span>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Admission Date</small>
                                    <span><?= format_date($student['admission_date']) ?></span>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Enrollment Status</small>
                                    <span><?= status_badge($student['status']) ?></span>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Record Created</small>
                                    <span><?= format_date($student['created_at']) ?></span>
                                </div>
                            </div>

                            <!-- Contact & Personal Details -->
                            <h6 class="text-uppercase text-primary font-weight-bold border-bottom pb-2 mb-3">
                                <i class="fas fa-address-book mr-1"></i> Contact & Residential Details
                            </h6>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Email Address</small>
                                    <?php if (!empty($student['email'])): ?>
                                        <a href="mailto:<?= e($student['email']) ?>" class="font-weight-bold">
                                            <i class="fas fa-envelope mr-1 text-primary"></i> <?= e($student['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No email on file</span>
                                    <?php endif; ?>
                                </div>

                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Phone Number</small>
                                    <?php if (!empty($student['phone'])): ?>
                                        <a href="tel:<?= e($student['phone']) ?>" class="font-weight-bold text-dark">
                                            <i class="fas fa-phone mr-1 text-success"></i> <?= e($student['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No phone on file</span>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 mb-3">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Residential Address</small>
                                    <div class="p-3 bg-light rounded border text-secondary">
                                        <?= !empty($student['address']) ? nl2br(e($student['address'])) : '<em class="text-muted">No physical address recorded.</em>' ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
