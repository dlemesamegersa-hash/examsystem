<?php
/**
 * Student Registration Page
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Register Student - ' . APP_NAME;

$errors = [];
$fullName       = '';
$gender         = 'male';
$dob            = '';
$email          = '';
$phone          = '';
$address        = '';
$department     = '';
$regNumber      = '';
$admissionDate  = date('Y-m-d');
$status         = 'active';

// Suggested auto-generated reg number helper
$suggestedReg = 'REG-' . date('Y') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Security token invalid or expired. Please reload and try again.';
    } else {
        $fullName       = trim($_POST['full_name'] ?? '');
        $gender         = trim($_POST['gender'] ?? 'male');
        $dob            = trim($_POST['date_of_birth'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $address        = trim($_POST['address'] ?? '');
        $department     = trim($_POST['department'] ?? '');
        $regNumber      = trim($_POST['registration_number'] ?? '');
        $admissionDate  = trim($_POST['admission_date'] ?? date('Y-m-d'));
        $statusInput    = trim($_POST['status'] ?? 'active');
        $status         = in_array($statusInput, ['active', 'inactive']) ? $statusInput : 'active';

        // Validation
        if (empty($fullName)) {
            $errors[] = 'Student full name is required.';
        } elseif (strlen($fullName) < 3 || strlen($fullName) > 100) {
            $errors[] = 'Full name must be between 3 and 100 characters.';
        }

        if (!in_array($gender, ['male', 'female', 'other'])) {
            $errors[] = 'Please select a valid gender option.';
        }

        if (empty($dob)) {
            $errors[] = 'Date of birth is required.';
        }

        if (empty($regNumber)) {
            $errors[] = 'Registration number is required.';
        }

        if (empty($department)) {
            $errors[] = 'Department or course is required.';
        }

        if (empty($admissionDate)) {
            $errors[] = 'Admission date is required.';
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid student email address.';
        }

        // Uniqueness verification
        if (empty($errors)) {
            try {
                // Check duplicate registration number
                $stmtCheckReg = $pdo->prepare("SELECT id FROM students WHERE registration_number = :reg LIMIT 1");
                $stmtCheckReg->execute([':reg' => $regNumber]);
                if ($stmtCheckReg->fetch()) {
                    $errors[] = 'Registration Number "' . e($regNumber) . '" is already assigned to another student.';
                }

                // Check duplicate email (if provided)
                if (!empty($email)) {
                    $stmtCheckEmail = $pdo->prepare("SELECT id FROM students WHERE email = :email LIMIT 1");
                    $stmtCheckEmail->execute([':email' => $email]);
                    if ($stmtCheckEmail->fetch()) {
                        $errors[] = 'Email address "' . e($email) . '" is already registered for another student.';
                    }
                }

                // Handle student photo upload (optional)
                $photoFileName = null;
                if (empty($errors) && isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploadError = '';
                    $uploaded = handle_photo_upload('photo', $uploadError);
                    if ($uploaded === false) {
                        $errors[] = 'Photo upload failed: ' . $uploadError;
                    } else {
                        $photoFileName = $uploaded;
                    }
                }

                // Insert into students table
                if (empty($errors)) {
                    $stmtInsert = $pdo->prepare("
                        INSERT INTO students (
                            full_name, gender, date_of_birth, email, phone, 
                            address, department, registration_number, admission_date, 
                            status, photo
                        ) VALUES (
                            :full_name, :gender, :dob, :email, :phone,
                            :address, :department, :reg_number, :admission_date,
                            :status, :photo
                        )
                    ");
                    $stmtInsert->execute([
                        ':full_name'       => $fullName,
                        ':gender'          => $gender,
                        ':dob'             => $dob ?: null,
                        ':email'           => !empty($email) ? $email : null,
                        ':phone'           => !empty($phone) ? $phone : null,
                        ':address'         => !empty($address) ? $address : null,
                        ':department'      => $department,
                        ':reg_number'      => $regNumber,
                        ':admission_date'  => $admissionDate ?: null,
                        ':status'          => $status,
                        ':photo'           => $photoFileName
                    ]);

                    $newStudentId = (int)$pdo->lastInsertId();
                    set_flash('success', 'Student <strong>' . e($fullName) . '</strong> (Reg: ' . e($regNumber) . ') registered successfully!');
                    header('Location: ' . BASE_URL . '/student_view.php?id=' . $newStudentId);
                    exit;
                }

            } catch (PDOException $e) {
                // If photo was saved, clean it up on DB failure
                if ($photoFileName) {
                    delete_photo_file($photoFileName);
                }
                $errors[] = 'Database error while saving student record: ' . $e->getMessage();
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
                        <i class="fas fa-user-plus mr-2 text-primary"></i> Student Registration
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/students.php">Students</a></li>
                        <li class="breadcrumb-item active">New Registration</li>
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
                    <h6 class="font-weight-bold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i> Registration Errors:</h6>
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

            <form action="<?= BASE_URL ?>/student_register.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="row">
                    <!-- Left: Personal & Academic Info -->
                    <div class="col-lg-8">
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold">
                                    <i class="fas fa-id-badge mr-1 text-primary"></i> Personal & Academic Profile
                                </h3>
                            </div>
                            <div class="card-body">

                                <!-- Full Name & Gender -->
                                <div class="form-row">
                                    <div class="form-group col-md-8">
                                        <label for="full_name" class="font-weight-bold">Student Full Name <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="e.g. Eleanor Vance" value="<?= e($fullName) ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="gender" class="font-weight-bold">Gender <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-venus-mars"></i></span></div>
                                            <select class="custom-select" id="gender" name="gender" required>
                                                <option value="male" <?= ($gender === 'male') ? 'selected' : '' ?>>Male</option>
                                                <option value="female" <?= ($gender === 'female') ? 'selected' : '' ?>>Female</option>
                                                <option value="other" <?= ($gender === 'other') ? 'selected' : '' ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Registration Number & Department -->
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="registration_number" class="font-weight-bold">Registration Number <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-barcode"></i></span></div>
                                            <input type="text" class="form-control" id="registration_number" name="registration_number" placeholder="<?= e($suggestedReg) ?>" value="<?= e($regNumber ?: $suggestedReg) ?>" required>
                                        </div>
                                        <small class="form-text text-muted">Must be a unique student identifier.</small>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="department" class="font-weight-bold">Department / Course <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-graduation-cap"></i></span></div>
                                            <input type="text" class="form-control" id="department" name="department" placeholder="e.g. Computer Science" value="<?= e($department) ?>" list="departmentList" required>
                                            <datalist id="departmentList">
                                                <option value="Computer Science">
                                                <option value="Information Technology">
                                                <option value="Business Administration">
                                                <option value="Electrical Engineering">
                                                <option value="Mechanical Engineering">
                                                <option value="Nursing">
                                                <option value="Economics">
                                                <option value="Accounting & Finance">
                                            </datalist>
                                        </div>
                                    </div>
                                </div>

                                <!-- Date of Birth & Admission Date -->
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="date_of_birth" class="font-weight-bold">Date of Birth <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-calendar-alt"></i></span></div>
                                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?= e($dob) ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="admission_date" class="font-weight-bold">Admission Date <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-calendar-check"></i></span></div>
                                            <input type="date" class="form-control" id="admission_date" name="admission_date" value="<?= e($admissionDate) ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email & Phone -->
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="email" class="font-weight-bold">Student Email</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="student@example.com" value="<?= e($email) ?>">
                                        </div>
                                        <small class="form-text text-muted">Unique official institutional or personal email.</small>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="phone" class="font-weight-bold">Phone Number</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                                            <input type="text" class="form-control" id="phone" name="phone" placeholder="+1 (555) 000-0000" value="<?= e($phone) ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Residential Address -->
                                <div class="form-group">
                                    <label for="address" class="font-weight-bold">Residential / Mailing Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter student permanent address, street, city, postal code..."><?= e($address) ?></textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Status & Photo Upload -->
                    <div class="col-lg-4">
                        <!-- Photo Upload Card -->
                        <div class="card card-outline card-secondary shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold"><i class="fas fa-camera mr-1"></i> Student Photograph</h3>
                            </div>
                            <div class="card-body text-center">
                                <div class="image-preview-container mx-auto mb-3" style="width: 140px; height: 140px; border-radius: 50%;">
                                    <img id="photoPreview" src="<?= BASE_URL ?>/assets/images/avatar.svg" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div class="custom-file text-left mb-2">
                                    <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
                                    <label class="custom-file-label" for="photo">Choose image file...</label>
                                </div>
                                <small class="text-muted d-block">Allowed: JPG, PNG, WEBP, GIF. Max: 3MB.</small>
                            </div>
                        </div>

                        <!-- Status & Action Card -->
                        <div class="card card-outline card-info shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold"><i class="fas fa-toggle-on mr-1"></i> Enrollment Status</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-4">
                                    <label for="status" class="font-weight-bold">Student Status <span class="required-star">*</span></label>
                                    <select class="custom-select" id="status" name="status" required>
                                        <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active (Enrolled)</option>
                                        <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive (Suspended / Withdrawn)</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold shadow-sm">
                                    <i class="fas fa-save mr-2"></i> Register Student
                                </button>
                                <a href="<?= BASE_URL ?>/students.php" class="btn btn-outline-secondary btn-block mt-2">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
