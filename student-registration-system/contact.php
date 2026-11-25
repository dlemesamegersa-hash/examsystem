<?php
/**
 * Contact Us Page
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact Us - Student Registration System';

$errors = [];
$successMessage = '';

// Pre-fill fields on validation failure
$fullName = '';
$email    = '';
$phone    = '';
$subject  = '';
$message  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Security token invalid or expired. Please reload and try again.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $subject  = trim($_POST['subject'] ?? '');
        $message  = trim($_POST['message'] ?? '');

        // Server-side validation
        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        } elseif (strlen($fullName) < 2 || strlen($fullName) > 100) {
            $errors[] = 'Full name must be between 2 and 100 characters.';
        }

        if (empty($email)) {
            $errors[] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }

        if (empty($subject)) {
            $errors[] = 'Subject is required.';
        } elseif (strlen($subject) > 150) {
            $errors[] = 'Subject must not exceed 150 characters.';
        }

        if (empty($message)) {
            $errors[] = 'Message content is required.';
        } elseif (strlen($message) < 10) {
            $errors[] = 'Message should be at least 10 characters long.';
        }

        // If no errors, persist to contact_messages table
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO contact_messages (full_name, email, phone, subject, message)
                    VALUES (:full_name, :email, :phone, :subject, :message)
                ");
                $stmt->execute([
                    ':full_name' => $fullName,
                    ':email'    => $email,
                    ':phone'    => $phone,
                    ':subject'  => $subject,
                    ':message'  => $message
                ]);

                set_flash('success', 'Thank you! Your message has been successfully submitted. Our administrative office will review it promptly.');
                header('Location: ' . BASE_URL . '/contact.php');
                exit;

            } catch (PDOException $e) {
                $errors[] = 'Failed to submit your message. Database error occurred: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/public_header.php';
?>

<!-- Breadcrumb Header -->
<div class="bg-white py-4 border-bottom shadow-sm">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 font-weight-bold mb-0 text-dark">Contact Admissions & Support</h1>
                <p class="text-muted small mb-0">Send an inquiry or reach out to the campus records team.</p>
            </div>
            <div class="col-md-6 text-md-right mt-2 mt-md-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-0 justify-content-md-end small">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">

    <!-- Flash message on redirect -->
    <?= display_flash() ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="font-weight-bold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i> Please correct the following errors:</h6>
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

    <div class="row">
        <!-- Contact Form Column -->
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title font-weight-bold mb-0 text-primary">
                        <i class="fas fa-paper-plane mr-2"></i> Send Us a Message
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= BASE_URL ?>/contact.php" method="POST" class="needs-validation" novalidate>
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
                                <label for="email" class="font-weight-bold">Email Address <span class="required-star">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="john@example.com" value="<?= e($email) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="phone" class="font-weight-bold">Phone Number</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="+1 (555) 000-0000" value="<?= e($phone) ?>">
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="subject" class="font-weight-bold">Subject <span class="required-star">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="subject" name="subject" placeholder="Inquiry about Registration" value="<?= e($subject) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="message" class="font-weight-bold">Message <span class="required-star">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Type your message or inquiry here..." required><?= e($message) ?></textarea>
                            <small class="form-text text-muted">Minimum 10 characters.</small>
                        </div>

                        <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2 shadow-sm">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Inquiry
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contact Info Column -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-building mr-2 text-primary"></i> Admissions Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex mb-4">
                        <div class="text-primary mr-3 mt-1">
                            <i class="fas fa-map-marker-alt fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold mb-1">Campus Location</h6>
                            <p class="text-muted small mb-0">
                                100 University Boulevard, Student Affairs Building, Suite 300, Education City, CA 90210
                            </p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="text-primary mr-3 mt-1">
                            <i class="fas fa-envelope fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold mb-1">Email Contacts</h6>
                            <p class="text-muted small mb-1">General: info@studreg.edu</p>
                            <p class="text-muted small mb-0">Admissions: admissions@studreg.edu</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="text-primary mr-3 mt-1">
                            <i class="fas fa-phone-alt fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold mb-1">Telephone & Fax</h6>
                            <p class="text-muted small mb-1">Toll Free: +1 (800) 555-REG-SYS</p>
                            <p class="text-muted small mb-0">Helpdesk: +1 (555) 019-2834</p>
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="text-primary mr-3 mt-1">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold mb-1">Office Hours</h6>
                            <p class="text-muted small mb-1">Monday – Friday: 8:00 AM – 5:00 PM</p>
                            <p class="text-muted small mb-0">Saturday – Sunday: Closed</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card for Quick Note -->
            <div class="alert alert-info border-0 shadow-sm p-4">
                <h6 class="font-weight-bold mb-2"><i class="fas fa-info-circle mr-2"></i> Note for Existing Students</h6>
                <p class="small mb-0">
                    If you are already registered in the system, please include your <strong>Registration Number</strong> in the subject line to assist us in pulling up your file quickly.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
