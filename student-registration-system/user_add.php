<?php
/**
 * Add New User (Admin Only)
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

// Enforce admin-only access
require_admin();

$pageTitle = 'Add User - ' . APP_NAME;

$errors = [];
$fullName = '';
$username = '';
$email    = '';
$phone    = '';
$role     = 'staff';
$status   = 'active';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Security token invalid or expired. Please reload and try again.';
    } else {
        $fullName        = trim($_POST['full_name'] ?? '');
        $username        = trim($_POST['username'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $phone           = trim($_POST['phone'] ?? '');
        $roleInput       = trim($_POST['role'] ?? 'staff');
        $role            = in_array($roleInput, ['admin', 'staff']) ? $roleInput : 'staff';
        $statusInput     = trim($_POST['status'] ?? 'active');
        $status          = in_array($statusInput, ['active', 'inactive']) ? $statusInput : 'active';
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        } elseif (strlen($fullName) < 3 || strlen($fullName) > 100) {
            $errors[] = 'Full name must be between 3 and 100 characters.';
        }

        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            $errors[] = 'Username must be 3-30 alphanumeric characters or underscores.';
        }

        if (empty($email)) {
            $errors[] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Password and confirm password do not match.';
        }

        // Uniqueness checks
        if (empty($errors)) {
            try {
                // Check username
                $stmtU = $pdo->prepare("SELECT id FROM users WHERE username = :u LIMIT 1");
                $stmtU->execute([':u' => $username]);
                if ($stmtU->fetch()) {
                    $errors[] = 'Username "' . e($username) . '" is already registered.';
                }

                // Check email
                $stmtE = $pdo->prepare("SELECT id FROM users WHERE email = :e LIMIT 1");
                $stmtE->execute([':e' => $email]);
                if ($stmtE->fetch()) {
                    $errors[] = 'Email address "' . e($email) . '" is already associated with another account.';
                }

                if (empty($errors)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmtInsert = $pdo->prepare("
                        INSERT INTO users (full_name, username, email, phone, password, role, status)
                        VALUES (:full_name, :username, :email, :phone, :password, :role, :status)
                    ");
                    $stmtInsert->execute([
                        ':full_name' => $fullName,
                        ':username'  => $username,
                        ':email'     => $email,
                        ':phone'     => !empty($phone) ? $phone : null,
                        ':password'  => $hash,
                        ':role'      => $role,
                        ':status'    => $status
                    ]);

                    set_flash('success', 'User account <strong>' . e($fullName) . '</strong> (@' . e($username) . ') created successfully.');
                    header('Location: ' . BASE_URL . '/users.php');
                    exit;
                }

            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
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
                        <i class="fas fa-user-plus mr-2 text-primary"></i> Add New System User
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/users.php">Users</a></li>
                        <li class="breadcrumb-item active">Add User</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content pb-5">
        <div class="container-fluid">

            <?= display_flash() ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6 class="font-weight-bold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i> Error Saving User:</h6>
                    <ul class="mb-0 pl-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-user-shield mr-1 text-primary"></i> Account Credentials & Permissions
                            </h3>
                        </div>

                        <form action="<?= BASE_URL ?>/user_add.php" method="POST" class="needs-validation validate-password-match" novalidate>
                            <div class="card-body p-4">
                                <?= csrf_field() ?>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="full_name" class="font-weight-bold">Full Name <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Jane Smith" value="<?= e($fullName) ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="username" class="font-weight-bold">Username <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-at"></i></span></div>
                                            <input type="text" class="form-control" id="username" name="username" placeholder="janesmith" value="<?= e($username) ?>" required pattern="^[a-zA-Z0-9_]{3,30}$">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="email" class="font-weight-bold">Email Address <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="jane@example.com" value="<?= e($email) ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="phone" class="font-weight-bold">Phone Number</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                                            <input type="text" class="form-control" id="phone" name="phone" placeholder="+1 (555) 000-0000" value="<?= e($phone) ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="role" class="font-weight-bold">Role Privilege <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-shield-alt"></i></span></div>
                                            <select class="custom-select" id="role" name="role" required>
                                                <option value="staff" <?= ($role === 'staff') ? 'selected' : '' ?>>Staff Member</option>
                                                <option value="admin" <?= ($role === 'admin') ? 'selected' : '' ?>>Administrator</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="status" class="font-weight-bold">Account Status <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-toggle-on"></i></span></div>
                                            <select class="custom-select" id="status" name="status" required>
                                                <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="password" class="font-weight-bold">Initial Password <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                                            <input type="password" class="form-control" id="password" name="password" minlength="6" placeholder="Min. 6 chars" required>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary toggle-password-btn" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="confirm_password" class="font-weight-bold">Confirm Password <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" placeholder="Re-enter password" required>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary toggle-password-btn" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer bg-light d-flex justify-content-between">
                                <a href="<?= BASE_URL ?>/users.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Back to Users
                                </a>
                                <button type="submit" class="btn btn-primary font-weight-bold px-4">
                                    <i class="fas fa-save mr-1"></i> Create User Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
