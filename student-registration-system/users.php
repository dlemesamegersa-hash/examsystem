<?php
/**
 * User Management Module (Admin Only)
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

// Enforce admin-only access
require_admin();

$pageTitle = 'User Management - ' . APP_NAME;
$currentUserId = (int)$_SESSION['user_id'];

// Filter parameters
$roleFilter   = trim($_GET['role'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$search       = trim($_GET['search'] ?? '');

$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(full_name LIKE :kw OR username LIKE :kw OR email LIKE :kw OR phone LIKE :kw)";
    $params[':kw'] = '%' . $search . '%';
}

if ($roleFilter !== '' && in_array($roleFilter, ['admin', 'staff'])) {
    $whereClauses[] = "role = :role";
    $params[':role'] = $roleFilter;
}

if ($statusFilter !== '' && in_array($statusFilter, ['active', 'inactive'])) {
    $whereClauses[] = "status = :status";
    $params[':status'] = $statusFilter;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$users = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM users $whereSql ORDER BY id ASC");
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    set_flash('danger', 'Error loading user records: ' . $e->getMessage());
}

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
                        <i class="fas fa-user-cog mr-2 text-primary"></i> User Management
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content pb-5">
        <div class="container-fluid">

            <?= display_flash() ?>

            <!-- Action Toolbar & Filters -->
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="<?= BASE_URL ?>/users.php" method="GET" class="form-row align-items-center">
                        <div class="col-lg-4 col-md-5 mb-2 mb-lg-0">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" name="search" class="form-control" placeholder="Search by name, username, email..." value="<?= e($search) ?>">
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-3 mb-2 mb-lg-0">
                            <select name="role" class="custom-select">
                                <option value="">All Roles</option>
                                <option value="admin" <?= ($roleFilter === 'admin') ? 'selected' : '' ?>>Admins Only</option>
                                <option value="staff" <?= ($roleFilter === 'staff') ? 'selected' : '' ?>>Staff Only</option>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4 mb-2 mb-lg-0">
                            <select name="status" class="custom-select">
                                <option value="">All Statuses</option>
                                <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Active Only</option>
                                <option value="inactive" <?= ($statusFilter === 'inactive') ? 'selected' : '' ?>>Inactive Only</option>
                            </select>
                        </div>

                        <div class="col-lg-4 col-md-12 d-flex">
                            <button type="submit" class="btn btn-primary font-weight-bold flex-fill mr-2">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <?php if ($search !== '' || $roleFilter !== '' || $statusFilter !== ''): ?>
                                <a href="<?= BASE_URL ?>/users.php" class="btn btn-outline-secondary" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/user_add.php" class="btn btn-success font-weight-bold ml-2 text-nowrap">
                                <i class="fas fa-user-plus mr-1"></i> Add New User
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Data Table Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-shield-alt text-primary mr-2"></i> System User Accounts
                        <span class="badge badge-primary ml-2"><?= count($users) ?> Total</span>
                    </h5>
                    <a href="<?= BASE_URL ?>/user_add.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus mr-1"></i> Add User
                    </a>
                </div>

                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped mb-0 text-nowrap">
                        <thead class="thead-light">
                            <tr>
                                <th>User ID</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registration Date</th>
                                <th class="text-center" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <?php $isSelf = ($user['id'] == $currentUserId); ?>
                                    <tr class="<?= $isSelf ? 'table-warning-subtle' : '' ?>">
                                        <td class="font-weight-bold">#<?= (int)$user['id'] ?></td>
                                        <td>
                                            <div class="font-weight-bold text-dark">
                                                <?= e($user['full_name']) ?>
                                                <?php if ($isSelf): ?>
                                                    <span class="badge badge-warning text-dark ml-1 font-weight-bold">You</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><code>@<?= e($user['username']) ?></code></td>
                                        <td><?= e($user['email']) ?></td>
                                        <td><?= e($user['phone'] ?: 'N/A') ?></td>
                                        <td><?= role_badge($user['role']) ?></td>
                                        <td><?= status_badge($user['status']) ?></td>
                                        <td><?= format_date($user['created_at']) ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <!-- Edit User -->
                                                <a href="<?= BASE_URL ?>/user_edit.php?id=<?= (int)$user['id'] ?>" class="btn btn-primary" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Delete User (Prevent deleting currently logged-in admin) -->
                                                <?php if ($isSelf): ?>
                                                    <button type="button" class="btn btn-secondary disabled" title="You cannot delete your own active account" disabled>
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-delete-confirm" 
                                                            data-id="<?= (int)$user['id'] ?>" 
                                                            data-action="<?= BASE_URL ?>/user_delete.php" 
                                                            data-message="Are you sure you want to delete user &quot;<?= e($user['full_name']) ?>&quot; (@<?= e($user['username']) ?>)? This account will lose access immediately."
                                                            title="Delete User">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fas fa-user-slash fa-3x mb-3 text-black-50 d-block"></i>
                                        <h5>No users found matching query.</h5>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
