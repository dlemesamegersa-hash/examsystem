<?php
/**
 * Login Page
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, redirect straight to dashboard
if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$pageTitle = 'Login - Student Registration System';
$errors = [];
$loginInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid security session. Please reload and try again.';
    } else {
        $loginInput = trim($_POST['login_input'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (empty($loginInput)) {
            $errors[] = 'Please enter your username or email address.';
        }
        if (empty($password)) {
            $errors[] = 'Please enter your password.';
        }

        if (empty($errors)) {
            try {
                // Find user by username OR email
                $stmt = $pdo->prepare("
                    SELECT id, full_name, username, email, password, role, status
                    FROM users
                    WHERE username = :login_user OR email = :login_email
                    LIMIT 1
                ");
                $stmt->execute([
                    ':login_user'  => $loginInput,
                    ':login_email' => $loginInput
                ]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Check if account is active
                    if ($user['status'] !== 'active') {
                        $errors[] = 'Your account has been deactivated. Please contact the system administrator.';
                    } else {
                        // Prevent session fixation
                        session_regenerate_id(true);

                        // Save session details
                        $_SESSION['user_id']   = (int)$user['id'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['username']  = $user['username'];
                        $_SESSION['email']     = $user['email'];
                        $_SESSION['role']      = $user['role'];
                        $_SESSION['status']    = $user['status'];
                        $_SESSION['logged_in'] = true;

                        set_flash('success', 'Welcome back, ' . e($user['full_name']) . '! You are logged in as ' . strtoupper(e($user['role'])) . '.');

                        // Redirect to intended page or dashboard
                        $redirectTo = BASE_URL . '/dashboard.php';
                        if (!empty($_SESSION['redirect_to'])) {
                            $target = $_SESSION['redirect_to'];
                            unset($_SESSION['redirect_to']);
                            // Ensure internal redirect only
                            if (strpos($target, 'http') === false || strpos($target, BASE_URL) === 0) {
                                $redirectTo = $target;
                            }
                        }

                        header('Location: ' . $redirectTo);
                        exit;
                    }
                } else {
                    $errors[] = 'Invalid username/email or password. Please verify and try again.';
                }

            } catch (PDOException $e) {
                $errors[] = 'Database error during authentication: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/public_header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">

            <!-- Card Container -->
            <div class="card border-0 shadow-lg rounded-lg overflow-hidden">
                <div class="card-header bg-dark text-white text-center py-4">
                    <div class="brand-icon mx-auto mb-2 bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h4 class="font-weight-bold mb-1">Administrative Login</h4>
                    <p class="small text-white-50 mb-0">Sign in to access student records and controls</p>
                </div>

                <div class="card-body p-4 p-md-5">

                    <?= display_flash() ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <?php foreach ($errors as $error): ?>
                                <div><?= e($error) ?></div>
                            <?php endforeach; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/login.php" method="POST" class="needs-validation" novalidate>
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label for="login_input" class="font-weight-bold">Username or Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" class="form-control" id="login_input" name="login_input" placeholder="admin or admin@example.com" value="<?= e($loginInput) ?>" required autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="font-weight-bold">Password</label>
                            </div>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password-btn" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold shadow-sm mt-4">
                            <i class="fas fa-sign-in-alt mr-2"></i> Log In to Dashboard
                        </button>
                    </form>

                    <!-- Default Credentials Helper Card -->
                    <div class="alert alert-light border mt-4 mb-0 small text-muted">
                        <div class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-key text-warning mr-1"></i> Default Demo Credentials:
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                            <span><strong>Admin:</strong> <code>admin</code></span>
                            <span><strong>Pass:</strong> <code>admin123</code></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><strong>Staff:</strong> <code>staff</code></span>
                            <span><strong>Pass:</strong> <code>staff123</code></span>
                        </div>
                    </div>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted small mb-0">
                            Don't have an administrative account?
                            <a href="<?= BASE_URL ?>/register.php" class="font-weight-bold text-primary">Register new user</a>
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
