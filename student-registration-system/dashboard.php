<?php
/**
 * Admin Dashboard
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Dashboard - ' . APP_NAME;
$currentUser = current_user();

// Fetch summary metrics
$totalStudents = 0;
$activeStudents = 0;
$totalUsers = 0;
$totalMessages = 0;
$recentStudents = [];
$recentMessages = [];

try {
    // 1. Total students
    $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();

    // 2. Active students
    $activeStudents = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'")->fetchColumn();

    // 3. Total users
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // 4. Total contact messages
    $totalMessages = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

    // 5. Recently registered students (latest 5)
    $stmtRecentStudents = $pdo->query("
        SELECT id, full_name, registration_number, department, admission_date, status, photo, created_at
        FROM students
        ORDER BY id DESC
        LIMIT 5
    ");
    $recentStudents = $stmtRecentStudents->fetchAll();

    // 6. Recent inquiries
    $stmtRecentMsg = $pdo->query("
        SELECT id, full_name, email, subject, created_at
        FROM contact_messages
        ORDER BY id DESC
        LIMIT 4
    ");
    $recentMessages = $stmtRecentMsg->fetchAll();

} catch (PDOException $e) {
    set_flash('danger', 'Error loading dashboard metrics: ' . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-tachometer-alt mr-2 text-primary"></i> Admin Dashboard
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Flash Alert -->
            <?= display_flash() ?>

            <!-- Welcome Greeting Banner -->
            <div class="alert alert-primary bg-gradient-primary border-0 text-white shadow-sm mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <h4 class="alert-heading font-weight-bold mb-1">
                            <i class="fas fa-hand-sparkles mr-2"></i> Welcome back, <?= e($currentUser['full_name']) ?>!
                        </h4>
                        <p class="mb-0 text-white-50 small">
                            You are authenticated as <strong class="text-white text-uppercase"><?= e($currentUser['role']) ?></strong>. Manage student admissions, review records, and configure system access below.
                        </p>
                    </div>
                    <div class="mt-2 mt-md-0">
                        <a href="<?= BASE_URL ?>/student_register.php" class="btn btn-light text-primary font-weight-bold btn-sm shadow-sm">
                            <i class="fas fa-user-plus mr-1"></i> New Student
                        </a>
                    </div>
                </div>
            </div>

            <!-- Summary Statistic Cards -->
            <div class="row">
                <!-- Total Students Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $totalStudents ?></h3>
                            <p>Total Registered Students</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <a href="<?= BASE_URL ?>/students.php" class="small-box-footer">
                            View All Students <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Active Students Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $activeStudents ?></h3>
                            <p>Active Student Enrollments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <a href="<?= BASE_URL ?>/students.php?status=active" class="small-box-footer">
                            Filter Active <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- System Users Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner text-white">
                            <h3 class="text-white"><?= $totalUsers ?></h3>
                            <p class="text-white">System Accounts</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <?php if (is_admin()): ?>
                            <a href="<?= BASE_URL ?>/users.php" class="small-box-footer text-white">
                                Manage Users <i class="fas fa-arrow-circle-right text-white"></i>
                            </a>
                        <?php else: ?>
                            <span class="small-box-footer text-white-50">
                                Protected (Admin Only) <i class="fas fa-lock text-white-50"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Contact Messages Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $totalMessages ?></h3>
                            <p>Contact Inquiries</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <a href="<?= BASE_URL ?>/messages.php" class="small-box-footer">
                            View Inquiries <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /.row -->

            <!-- Main Row (Recent Students & Quick Actions) -->
            <div class="row">
                <!-- Recently Registered Students Table -->
                <div class="col-lg-8">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-history mr-1 text-primary"></i> Recently Registered Students
                            </h3>
                            <div class="card-tools">
                                <a href="<?= BASE_URL ?>/students.php" class="btn btn-sm btn-tool font-weight-bold text-primary">
                                    View All Students <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-striped table-valign-middle mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Reg. Number</th>
                                        <th>Department</th>
                                        <th>Admission</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentStudents)): ?>
                                        <?php foreach ($recentStudents as $student): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= get_student_photo_url($student['photo']) ?>" alt="Avatar" class="student-avatar-thumb mr-2">
                                                        <div>
                                                            <div class="font-weight-bold text-dark"><?= e($student['full_name']) ?></div>
                                                            <small class="text-muted">ID: #<?= (int)$student['id'] ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge badge-light border text-monospace"><?= e($student['registration_number']) ?></span></td>
                                                <td><?= e($student['department'] ?: 'Unassigned') ?></td>
                                                <td><?= format_date($student['admission_date']) ?></td>
                                                <td><?= status_badge($student['status']) ?></td>
                                                <td class="text-center">
                                                    <a href="<?= BASE_URL ?>/student_view.php?id=<?= (int)$student['id'] ?>" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>/student_edit.php?id=<?= (int)$student['id'] ?>" class="btn btn-sm btn-primary" title="Edit Student">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fas fa-user-graduate fa-2x mb-2 d-block text-black-50"></i>
                                                No students registered yet. Click <a href="<?= BASE_URL ?>/student_register.php" class="font-weight-bold">Register Student</a> to add the first record.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Quick Shortcuts & Recent Inquiries -->
                <div class="col-lg-4">
                    <!-- Quick Shortcuts -->
                    <div class="card card-outline card-secondary shadow-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-bolt mr-1 text-warning"></i> Quick Operations</h3>
                        </div>
                        <div class="card-body p-3">
                            <a href="<?= BASE_URL ?>/student_register.php" class="btn btn-outline-primary btn-block text-left mb-2 py-2">
                                <i class="fas fa-user-plus mr-2"></i> Register New Student
                            </a>
                            <a href="<?= BASE_URL ?>/students.php" class="btn btn-outline-info btn-block text-left mb-2 py-2">
                                <i class="fas fa-search mr-2"></i> Search & Filter Students
                            </a>
                            <?php if (is_admin()): ?>
                                <a href="<?= BASE_URL ?>/user_add.php" class="btn btn-outline-success btn-block text-left mb-2 py-2">
                                    <i class="fas fa-user-shield mr-2"></i> Add Staff / Admin Account
                                </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/messages.php" class="btn btn-outline-danger btn-block text-left mb-2 py-2">
                                <i class="fas fa-inbox mr-2"></i> Review Public Messages (<?= $totalMessages ?>)
                            </a>
                        </div>
                    </div>

                    <!-- Recent Contact Messages -->
                    <div class="card card-outline card-danger shadow-sm">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-envelope mr-1 text-danger"></i> Recent Messages</h3>
                            <a href="<?= BASE_URL ?>/messages.php" class="btn btn-tool btn-sm text-danger font-weight-bold">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <ul class="products-list product-list-in-card pl-3 pr-3">
                                <?php if (!empty($recentMessages)): ?>
                                    <?php foreach ($recentMessages as $msg): ?>
                                        <li class="item py-2 border-bottom">
                                            <div class="product-info ml-0">
                                                <a href="<?= BASE_URL ?>/messages.php" class="product-title font-weight-bold text-dark">
                                                    <?= e($msg['subject']) ?>
                                                    <span class="badge badge-light border float-right small text-muted"><?= format_date($msg['created_at'], 'M d') ?></span>
                                                </a>
                                                <span class="product-description text-muted small">
                                                    From: <strong><?= e($msg['full_name']) ?></strong> (<?= e($msg['email']) ?>)
                                                </span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="p-3 text-center text-muted small">No contact messages received yet.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->

        </div>
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
