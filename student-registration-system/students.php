<?php
/**
 * Show Students Module (Listing, Search & Pagination)
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Show Students - ' . APP_NAME;

// Query parameters
$searchKeyword = trim($_GET['search'] ?? '');
$statusFilter  = trim($_GET['status'] ?? '');
$departmentFilter = trim($_GET['department'] ?? '');

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$offset  = ($page - 1) * $perPage;

// Build query
$whereClauses = [];
$params = [];

if ($searchKeyword !== '') {
    $whereClauses[] = "(full_name LIKE :kw OR registration_number LIKE :kw OR email LIKE :kw OR phone LIKE :kw)";
    $params[':kw'] = '%' . $searchKeyword . '%';
}

if ($statusFilter !== '' && in_array($statusFilter, ['active', 'inactive'])) {
    $whereClauses[] = "status = :status";
    $params[':status'] = $statusFilter;
}

if ($departmentFilter !== '') {
    $whereClauses[] = "department = :dept";
    $params[':dept'] = $departmentFilter;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Count total matching records
$totalRows = 0;
$students = [];
$departmentsList = [];

try {
    // Fetch unique departments for filter dropdown
    $departmentsList = $pdo->query("SELECT DISTINCT department FROM students WHERE department IS NOT NULL AND department != '' ORDER BY department ASC")->fetchAll(PDO::FETCH_COLUMN);

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM students $whereSql");
    $countStmt->execute($params);
    $totalRows = (int)$countStmt->fetchColumn();

    // Fetch page records
    $selectSql = "SELECT * FROM students $whereSql ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($selectSql);

    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $students = $stmt->fetchAll();

} catch (PDOException $e) {
    set_flash('danger', 'Error fetching student records: ' . $e->getMessage());
}

$totalPages = ceil($totalRows / $perPage);

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
                        <i class="fas fa-users mr-2 text-primary"></i> Show Students
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Students List</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content pb-5">
        <div class="container-fluid">

            <?= display_flash() ?>

            <!-- Filter & Search Toolbar -->
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="<?= BASE_URL ?>/students.php" method="GET" class="form-row align-items-center">
                        <!-- Search Keyword -->
                        <div class="col-lg-4 col-md-6 mb-2 mb-lg-0">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" name="search" class="form-control" placeholder="Search by name, reg #, email..." value="<?= e($searchKeyword) ?>">
                            </div>
                        </div>

                        <!-- Department Filter -->
                        <div class="col-lg-3 col-md-3 mb-2 mb-lg-0">
                            <select name="department" class="custom-select">
                                <option value="">All Departments</option>
                                <?php foreach ($departmentsList as $dept): ?>
                                    <option value="<?= e($dept) ?>" <?= ($departmentFilter === $dept) ? 'selected' : '' ?>>
                                        <?= e($dept) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-lg-2 col-md-3 mb-2 mb-lg-0">
                            <select name="status" class="custom-select">
                                <option value="">All Statuses</option>
                                <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Active Only</option>
                                <option value="inactive" <?= ($statusFilter === 'inactive') ? 'selected' : '' ?>>Inactive Only</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-lg-3 col-md-12 d-flex">
                            <button type="submit" class="btn btn-primary font-weight-bold flex-fill mr-2">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <?php if ($searchKeyword !== '' || $statusFilter !== '' || $departmentFilter !== ''): ?>
                                <a href="<?= BASE_URL ?>/students.php" class="btn btn-outline-secondary" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/student_register.php" class="btn btn-success font-weight-bold ml-2 text-nowrap">
                                <i class="fas fa-user-plus mr-1"></i> Register New
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Students Data Table Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-user-graduate text-primary mr-2"></i> Registered Students
                        <span class="badge badge-primary ml-2"><?= $totalRows ?> Total</span>
                    </h5>
                    <div>
                        <span class="text-muted small">Showing <?= count($students) ?> of <?= $totalRows ?> records</span>
                    </div>
                </div>

                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped mb-0 text-nowrap">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 60px;">Photo</th>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Reg. Number</th>
                                <th>Gender</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>/student_view.php?id=<?= (int)$student['id'] ?>">
                                                <img src="<?= get_student_photo_url($student['photo']) ?>" alt="Avatar" class="student-avatar-thumb">
                                            </a>
                                        </td>
                                        <td class="font-weight-bold text-muted">#<?= (int)$student['id'] ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/student_view.php?id=<?= (int)$student['id'] ?>" class="font-weight-bold text-primary">
                                                <?= e($student['full_name']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border text-monospace font-weight-bold">
                                                <?= e($student['registration_number']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-capitalize small">
                                                <?php if ($student['gender'] === 'female'): ?>
                                                    <i class="fas fa-venus text-danger mr-1"></i> Female
                                                <?php elseif ($student['gender'] === 'male'): ?>
                                                    <i class="fas fa-mars text-primary mr-1"></i> Male
                                                <?php else: ?>
                                                    <i class="fas fa-genderless text-secondary mr-1"></i> <?= e($student['gender']) ?>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td><?= e($student['email'] ?: 'N/A') ?></td>
                                        <td><?= e($student['phone'] ?: 'N/A') ?></td>
                                        <td>
                                            <span class="badge badge-secondary"><?= e($student['department'] ?: 'Unassigned') ?></span>
                                        </td>
                                        <td><?= status_badge($student['status']) ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <!-- View -->
                                                <a href="<?= BASE_URL ?>/student_view.php?id=<?= (int)$student['id'] ?>" class="btn btn-info" title="View Student Profile">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <!-- Edit -->
                                                <a href="<?= BASE_URL ?>/student_edit.php?id=<?= (int)$student['id'] ?>" class="btn btn-primary" title="Edit Student">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <!-- Delete -->
                                                <button type="button" 
                                                        class="btn btn-danger btn-delete-confirm" 
                                                        data-id="<?= (int)$student['id'] ?>" 
                                                        data-action="<?= BASE_URL ?>/student_delete.php" 
                                                        data-message="Are you sure you want to delete student &quot;<?= e($student['full_name']) ?>&quot; (Reg: <?= e($student['registration_number']) ?>)? This cannot be undone."
                                                        title="Delete Student">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="fas fa-search fa-3x mb-3 text-black-50 d-block"></i>
                                        <h5>No students found matching your criteria.</h5>
                                        <p class="small text-muted mb-3">Try adjusting your search keywords or filter options.</p>
                                        <a href="<?= BASE_URL ?>/students.php" class="btn btn-outline-primary btn-sm mr-2">Reset Filters</a>
                                        <a href="<?= BASE_URL ?>/student_register.php" class="btn btn-primary btn-sm">Register Student</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Card Footer -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center flex-wrap">
                        <div class="text-muted small mb-2 mb-md-0">
                            Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (<?= $totalRows ?> total students)
                        </div>
                        <nav aria-label="Student Table Pagination">
                            <ul class="pagination pagination-sm m-0">
                                <!-- Previous Page -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next Page -->
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
