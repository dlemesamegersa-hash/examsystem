<?php
/**
 * Public User / Staff Registration Page
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$pageTitle = 'Account Registration - Student Registration System';

$errors = [];
$fullName = '';
$email    = '';
$phone    = '';
$username = '';
$role     = 'staff';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Security verification failed. Please try submitting again.';
    } else {
        $fullName        = trim($_POST['full_name'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $phone           = trim($_POST['phone'] ?? '');
        $username        = trim($_POST['username'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $roleInput       = trim($_POST['role'] ?? 'staff');
        $role            = in_array($roleInput, ['admin', 'staff']) ? $roleInput : 'staff';

        // Validation
        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        } elseif (strlen($fullName) < 3 || strlen($fullName) > 100) {
            $errors[] = 'Full name must be between 3 and 100 characters.';
        }

        if (empty($email)) {
            $errors[] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            $errors[] = 'Username must be 3-30 characters long and contain only letters, numbers, and underscores.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        // Check uniqueness for email and username
        if (empty($errors)) {
            try {
                // Check username
                $stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
                $stmtUser->execute([':username' => $username]);
                if ($stmtUser->fetch()) {
                    $errors[] = 'The username "' . e($username) . '" is already taken. Please choose another.';
                }

                // Check email
                $stmtEmail = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
                $stmtEmail->execute([':email' => $email]);
                if ($stmtEmail->fetch()) {
                    $errors[] = 'The email address "' . e($email) . '" is already registered. Please login or use a different email.';
                }

                // If no duplicates, insert new user
                if (empty($errors)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmtInsert = $pdo->prepare("
                        INSERT INTO users (full_name, username, email, phone, password, role, status)
                        VALUES (:full_name, :username, :email, :phone, :password, :role, 'active')
                    ");
                    $stmtInsert->execute([
                        ':full_name' => $fullName,
                        ':username'  => $username,
                        ':email'     => $email,
                        ':phone'     => $phone,
                        ':password'  => $hashedPassword,
                        ':role'      => $role
                    ]);

                    set_flash('success', 'Registration successful! You can now log in with your credentials.');
                    header('Location: ' . BASE_URL . '/login.php');
                    exit;
                }

            } catch (PDOException $e) {
                $errors[] = 'Database error occurred during registration: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/public_header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">

            <!-- Card Container -->
            <div class="card border-0 shadow-lg rounded-lg overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4">
                    <div class="brand-icon mx-auto mb-2 bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h4 class="font-weight-bold mb-1">Create System Account</h4>
                    <p class="small text-white-50 mb-0">Register as an Administrator or Staff Member to access the portal</p>
                </div>

                <div class="card-body p-4 p-md-5">

                    <?= display_flash() ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="font-weight-bold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i> Registration Error:</h6>
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

                    <form action="<?= BASE_URL ?>/register.php" method="POST" class="needs-validation validate-password-match" novalidate>
                        <?= csrf_field() ?>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="full_name" class="font-weight-bold">Full Name <span class="required-star">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="full_name" name="full_name" placeholder="John Doe" value="<?= e($fullName) ?>" required>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="username" class="font-weight-bold">Username <span class="required-star">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="johndoe" value="<?= e($username) ?>" required pattern="^[a-zA-Z0-9_]{3,30}$">
                                </div>
                                <small class="form-text text-muted">Alphanumeric & underscore only (3-30 chars)</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="email" class="font-weight-bold">Email Address <span class="required-star">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="john@example.com" value="<?= e($email) ?>" required>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="phone" class="font-weight-bold">Phone Number</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="+1 (555) 000-0000" value="<?= e($phone) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="role" class="font-weight-bold">System Role <span class="required-star">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                                </div>
                                <select class="custom-select" id="role" name="role" required>
                                    <option value="staff" <?= ($role === 'staff') ? 'selected' : '' ?>>Staff Member (Manage Students & Messages)</option>
                                    <option value="admin" <?= ($role === 'admin') ? 'selected' : '' ?>>Administrator (Full System & User Control)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="password" class="font-weight-bold">Password <span class="required-star">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="At least 6 characters" minlength="6" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-password-btn" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="confirm_password" class="font-weight-bold">Confirm Password <span class="required-star">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" minlength="6" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-password-btn" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="terms" required>
                                <label class="custom-control-label small" for="terms">
                                    I agree to adhere to institutional student data privacy policies and system usage terms.
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold shadow-sm mt-4">
                            <i class="fas fa-user-plus mr-2"></i> Register Account
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted small mb-0">
                            Already registered?
                            <a href="<?= BASE_URL ?>/login.php" class="font-weight-bold text-primary">Login to your account</a>
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
