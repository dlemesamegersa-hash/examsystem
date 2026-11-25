<?php
/**
 * Contact Inquiries & Messages Viewer
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Contact Messages - ' . APP_NAME;

// Handle Message Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf_token($token)) {
        $msgId = (int)($_POST['id'] ?? 0);
        if ($msgId > 0) {
            try {
                $delStmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = :id");
                $delStmt->execute([':id' => $msgId]);
                set_flash('success', 'Message #' . $msgId . ' deleted successfully.');
            } catch (PDOException $e) {
                set_flash('danger', 'Error deleting message: ' . $e->getMessage());
            }
        }
    } else {
        set_flash('danger', 'Invalid security token.');
    }
    header('Location: ' . BASE_URL . '/messages.php');
    exit;
}

// Search & Filter
$search = trim($_GET['search'] ?? '');
$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(full_name LIKE :kw OR email LIKE :kw OR subject LIKE :kw OR message LIKE :kw)";
    $params[':kw'] = '%' . $search . '%';
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$messages = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM contact_messages $whereSql ORDER BY id DESC");
    $stmt->execute($params);
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    set_flash('danger', 'Error loading contact messages: ' . $e->getMessage());
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
                        <i class="fas fa-envelope mr-2 text-primary"></i> Contact Messages & Inquiries
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Messages</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content pb-5">
        <div class="container-fluid">

            <?= display_flash() ?>

            <!-- Search Toolbar -->
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="<?= BASE_URL ?>/messages.php" method="GET" class="form-row align-items-center">
                        <div class="col-lg-6 col-md-8 mb-2 mb-lg-0">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" name="search" class="form-control" placeholder="Search by sender, email, subject, keyword..." value="<?= e($search) ?>">
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 d-flex">
                            <button type="submit" class="btn btn-primary font-weight-bold flex-fill mr-2">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                            <?php if ($search !== ''): ?>
                                <a href="<?= BASE_URL ?>/messages.php" class="btn btn-outline-secondary" title="Reset">
                                    <i class="fas fa-undo"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Messages Table Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-inbox text-danger mr-2"></i> Inquiries Inbox
                        <span class="badge badge-danger ml-2"><?= count($messages) ?> Total</span>
                    </h5>
                </div>

                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Sender</th>
                                <th>Email & Phone</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Received</th>
                                <th class="text-center" style="width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($messages)): ?>
                                <?php foreach ($messages as $msg): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted"><?= (int)$msg['id'] ?></td>
                                        <td>
                                            <div class="font-weight-bold text-dark"><?= e($msg['full_name']) ?></div>
                                        </td>
                                        <td>
                                            <div><a href="mailto:<?= e($msg['email']) ?>" class="small font-weight-bold"><i class="fas fa-envelope text-primary mr-1"></i> <?= e($msg['email']) ?></a></div>
                                            <?php if (!empty($msg['phone'])): ?>
                                                <div><a href="tel:<?= e($msg['phone']) ?>" class="small text-muted"><i class="fas fa-phone text-success mr-1"></i> <?= e($msg['phone']) ?></a></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold"><?= e($msg['subject']) ?></span>
                                        </td>
                                        <td style="max-width: 320px;">
                                            <div class="text-truncate text-secondary small" title="<?= e($msg['message']) ?>">
                                                <?= e($msg['message']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="small text-muted"><?= format_date($msg['created_at'], 'M d, Y H:i') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <!-- View message modal trigger -->
                                                <button type="button" class="btn btn-info btn-view-msg" 
                                                        data-toggle="modal" 
                                                        data-target="#viewMessageModal"
                                                        data-id="<?= (int)$msg['id'] ?>"
                                                        data-name="<?= e($msg['full_name']) ?>"
                                                        data-email="<?= e($msg['email']) ?>"
                                                        data-phone="<?= e($msg['phone'] ?: 'N/A') ?>"
                                                        data-subject="<?= e($msg['subject']) ?>"
                                                        data-time="<?= format_date($msg['created_at'], 'M d, Y H:i:s') ?>"
                                                        data-message="<?= e($msg['message']) ?>"
                                                        title="Read Message">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <!-- Delete button -->
                                                <button type="button" 
                                                        class="btn btn-danger btn-delete-confirm" 
                                                        data-id="<?= (int)$msg['id'] ?>" 
                                                        data-action="<?= BASE_URL ?>/messages.php" 
                                                        data-message="Are you sure you want to delete message #<?= (int)$msg['id'] ?> from &quot;<?= e($msg['full_name']) ?>&quot;?"
                                                        title="Delete Inquiry">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 text-black-50 d-block"></i>
                                        <h5>No contact messages found.</h5>
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

<!-- Message View Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1" role="dialog" aria-labelledby="viewMsgModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="viewMsgModalLabel">
                    <i class="fas fa-envelope-open-text mr-2"></i> Inquiry Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted text-uppercase font-weight-bold d-block">From</small>
                        <h6 class="font-weight-bold mb-0" id="msgModalName"></h6>
                    </div>
                    <div class="col-md-6 mb-2 text-md-right">
                        <small class="text-muted text-uppercase font-weight-bold d-block">Date Received</small>
                        <span id="msgModalTime" class="text-muted small"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted text-uppercase font-weight-bold d-block">Email</small>
                        <a id="msgModalEmail" href="" class="font-weight-bold"></a>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted text-uppercase font-weight-bold d-block">Phone Number</small>
                        <span id="msgModalPhone" class="text-dark"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted text-uppercase font-weight-bold d-block">Subject</small>
                    <div class="font-weight-bold text-dark border-bottom pb-2" id="msgModalSubject"></div>
                </div>

                <div>
                    <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Message Content</small>
                    <div class="p-3 bg-light rounded border text-secondary" id="msgModalBody" style="white-space: pre-wrap; min-height: 120px;"></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <a id="msgModalReplyBtn" href="" class="btn btn-primary">
                    <i class="fas fa-reply mr-1"></i> Reply via Email
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    $('.btn-view-msg').on('click', function () {
        var name    = $(this).data('name');
        var email   = $(this).data('email');
        var phone   = $(this).data('phone');
        var subject = $(this).data('subject');
        var time    = $(this).data('time');
        var message = $(this).data('message');

        $('#msgModalName').text(name);
        $('#msgModalEmail').text(email).attr('href', 'mailto:' + email);
        $('#msgModalPhone').text(phone);
        $('#msgModalSubject').text(subject);
        $('#msgModalTime').text(time);
        $('#msgModalBody').text(message);
        $('#msgModalReplyBtn').attr('href', 'mailto:' + email + '?subject=Re: ' + encodeURIComponent(subject));
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
