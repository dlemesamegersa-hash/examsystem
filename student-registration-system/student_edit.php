<?php
/**
 * Edit Student Record
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$studentId = (int)($_GET['id'] ?? 0);
if ($studentId <= 0) {
    set_flash('danger', 'Invalid student ID.');
    header('Location: ' . BASE_URL . '/students.php');
    exit;
}

// Fetch current student record
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

$pageTitle = 'Edit Student: ' . $student['full_name'] . ' - ' . APP_NAME;

$errors = [];
$fullName       = $student['full_name'];
$gender         = $student['gender'];
$dob            = $student['date_of_birth'] ?? '';
$email          = $student['email'] ?? '';
$phone          = $student['phone'] ?? '';
$address        = $student['address'] ?? '';
$department     = $student['department'] ?? '';
$regNumber      = $student['registration_number'];
$admissionDate  = $student['admission_date'] ?? '';
$status         = $student['status'];
$currentPhoto   = $student['photo'];

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
        $admissionDate  = trim($_POST['admission_date'] ?? '');
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
            $errors[] = 'Please provide a valid email address.';
        }

        // Uniqueness checks (ignoring current student ID)
        if (empty($errors)) {
            try {
                // Check duplicate reg number
                $stmtCheckReg = $pdo->prepare("SELECT id FROM students WHERE registration_number = :reg AND id != :id LIMIT 1");
                $stmtCheckReg->execute([':reg' => $regNumber, ':id' => $studentId]);
                if ($stmtCheckReg->fetch()) {
                    $errors[] = 'Registration Number "' . e($regNumber) . '" is already assigned to another student.';
                }

                // Check duplicate email (if provided)
                if (!empty($email)) {
                    $stmtCheckEmail = $pdo->prepare("SELECT id FROM students WHERE email = :email AND id != :id LIMIT 1");
                    $stmtCheckEmail->execute([':email' => $email, ':id' => $studentId]);
                    if ($stmtCheckEmail->fetch()) {
                        $errors[] = 'Email address "' . e($email) . '" is already registered for another student.';
                    }
                }

                // Photo upload handling (replace or keep)
                $newPhoto = $currentPhoto;
                if (empty($errors) && isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploadError = '';
                    $uploaded = handle_photo_upload('photo', $uploadError);
                    if ($uploaded === false) {
                        $errors[] = 'Photo upload error: ' . $uploadError;
                    } else {
                        // Delete old photo if existed
                        delete_photo_file($currentPhoto);
                        $newPhoto = $uploaded;
                    }
                }

                // Update database
                if (empty($errors)) {
                    $stmtUpdate = $pdo->prepare("
                        UPDATE students SET
                            full_name = :full_name,
                            gender = :gender,
                            date_of_birth = :dob,
                            email = :email,
                            phone = :phone,
                            address = :address,
                            department = :department,
                            registration_number = :reg_number,
                            admission_date = :admission_date,
                            status = :status,
                            photo = :photo
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([
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
                        ':photo'           => $newPhoto,
                        ':id'              => $studentId
                    ]);

                    set_flash('success', 'Student record for <strong>' . e($fullName) . '</strong> updated successfully.');
                    header('Location: ' . BASE_URL . '/student_view.php?id=' . $studentId);
                    exit;
                }

            } catch (PDOException $e) {
                $errors[] = 'Database error while updating student: ' . $e->getMessage();
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
                        <i class="fas fa-edit mr-2 text-primary"></i> Edit Student Record
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/students.php">Students</a></li>
                        <li class="breadcrumb-item active">Edit Student #<?= $studentId ?></li>
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
                    <h6 class="font-weight-bold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i> Update Errors:</h6>
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

            <form action="<?= BASE_URL ?>/student_edit.php?id=<?= $studentId ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="row">
                    <!-- Left: Personal & Academic Info -->
                    <div class="col-lg-8">
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold">
                                    <i class="fas fa-user-edit mr-1 text-primary"></i> Update Profile Details
                                </h3>
                            </div>
                            <div class="card-body">

                                <!-- Full Name & Gender -->
                                <div class="form-row">
                                    <div class="form-group col-md-8">
                                        <label for="full_name" class="font-weight-bold">Student Full Name <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?= e($fullName) ?>" required>
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
                                            <input type="text" class="form-control font-weight-bold text-monospace" id="registration_number" name="registration_number" value="<?= e($regNumber) ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="department" class="font-weight-bold">Department / Course <span class="required-star">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-graduation-cap"></i></span></div>
                                            <input type="text" class="form-control" id="department" name="department" value="<?= e($department) ?>" list="departmentList" required>
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
                                            <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>">
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="phone" class="font-weight-bold">Phone Number</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?= e($phone) ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="form-group">
                                    <label for="address" class="font-weight-bold">Residential / Mailing Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?= e($address) ?></textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Status & Photo -->
                    <div class="col-lg-4">
                        <!-- Photo Upload Card -->
                        <div class="card card-outline card-secondary shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold"><i class="fas fa-camera mr-1"></i> Student Photograph</h3>
                            </div>
                            <div class="card-body text-center">
                                <div class="image-preview-container mx-auto mb-3" style="width: 140px; height: 140px; border-radius: 50%;">
                                    <img id="photoPreview" src="<?= get_student_photo_url($currentPhoto) ?>" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div class="custom-file text-left mb-2">
                                    <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
                                    <label class="custom-file-label" for="photo">Change image...</label>
                                </div>
                                <small class="text-muted d-block">Leave blank to retain current photo.</small>
                            </div>
                        </div>

                        <!-- Status & Action Card -->
                        <div class="card card-outline card-info shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold"><i class="fas fa-toggle-on mr-1"></i> Enrollment Status</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-4">
                                    <label for="status" class="font-weight-bold">Status <span class="required-star">*</span></label>
                                    <select class="custom-select" id="status" name="status" required>
                                        <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active (Enrolled)</option>
                                        <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive (Suspended / Withdrawn)</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold shadow-sm">
                                    <i class="fas fa-save mr-2"></i> Save Changes
                                </button>
                                <a href="<?= BASE_URL ?>/student_view.php?id=<?= $studentId ?>" class="btn btn-outline-secondary btn-block mt-2">
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
