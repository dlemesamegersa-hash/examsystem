    <!-- Main Footer -->
    <footer class="main-footer text-sm">
        <strong>Copyright &copy; <?= date('Y') ?> <a href="<?= BASE_URL ?>/index.php"><?= e(APP_NAME) ?></a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> <?= APP_VERSION ?> | Running on XAMPP (PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?>)
        </div>
    </footer>
</div>
<!-- ./wrapper -->

<!-- Global Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel"><i class="fas fa-exclamation-triangle mr-2"></i> Confirm Deletion</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmMessage" class="mb-0">Are you sure you want to permanently delete this record? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancel</button>
                <form id="deleteConfirmForm" method="POST" action="" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                    <input type="hidden" name="id" id="deleteTargetId" value="">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt mr-1"></i> Confirm Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Required Scripts: jQuery, Bootstrap 4.6 Bundle, AdminLTE 3.2 App, Custom JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/custom.js"></script>
</body>
</html>
