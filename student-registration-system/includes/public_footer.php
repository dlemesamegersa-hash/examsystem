    </main>

    <!-- Public Footer -->
    <footer class="bg-dark text-white pt-5 pb-3 mt-5 border-top border-secondary">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="text-white font-weight-bold mb-3 d-flex align-items-center">
                        <i class="fas fa-graduation-cap text-primary mr-2"></i> <?= e(APP_NAME) ?>
                    </h5>
                    <p class="text-muted small">
                        A secure, responsive, and robust student records and registration management portal engineered with PHP, MySQL, Bootstrap, and AdminLTE 3.
                    </p>
                    <div class="d-flex text-muted small">
                        <span class="mr-3"><i class="fas fa-shield-alt text-primary mr-1"></i> Role-Based Access</span>
                        <span><i class="fas fa-lock text-primary mr-1"></i> Encrypted Hashes</span>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-uppercase text-white font-weight-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php" class="text-muted text-decoration-none hover-white"><i class="fas fa-angle-right mr-1"></i> Home</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/about.php" class="text-muted text-decoration-none hover-white"><i class="fas fa-angle-right mr-1"></i> About Us</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/contact.php" class="text-muted text-decoration-none hover-white"><i class="fas fa-angle-right mr-1"></i> Contact Us</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/register.php" class="text-muted text-decoration-none hover-white"><i class="fas fa-angle-right mr-1"></i> User Registration</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-uppercase text-white font-weight-bold mb-3">System Features</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Student Bio & Academic Data</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Photo Upload & Archival</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> AdminLTE 3 Responsive Dashboard</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> User & Staff Privilege Management</li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-uppercase text-white font-weight-bold mb-3">Admissions Desk</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><i class="fas fa-map-marker-alt text-danger mr-2"></i> 100 University Boulevard, Education City</li>
                        <li class="mb-2"><i class="fas fa-envelope text-primary mr-2"></i> admissions@studreg.edu</li>
                        <li class="mb-2"><i class="fas fa-phone-alt text-success mr-2"></i> +1 (800) 555-REG-SYS</li>
                        <li class="mb-2"><i class="fas fa-clock text-warning mr-2"></i> Mon - Fri: 8:00 AM - 5:00 PM</li>
                    </ul>
                </div>
            </div>

            <hr class="border-secondary my-3">

            <div class="row align-items-center">
                <div class="col-md-6 small text-muted">
                    &copy; <?= date('Y') ?> <strong><?= e(APP_NAME) ?></strong>. All rights reserved.
                </div>
                <div class="col-md-6 text-md-right small text-muted">
                    Designed for XAMPP Development Environment with PHP & MySQL.
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts: jQuery, Popper.js, Bootstrap 4.6 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/assets/js/custom.js"></script>
</body>
</html>
