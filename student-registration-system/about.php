<?php
/**
 * About Us Page
 * Student Registration System
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Us - Student Registration System';
require_once __DIR__ . '/includes/public_header.php';
?>

<!-- Breadcrumb Header -->
<div class="bg-white py-4 border-bottom shadow-sm">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 font-weight-bold mb-0 text-dark">About the System</h1>
                <p class="text-muted small mb-0">Learn about our mission, architecture, and administrative features.</p>
            </div>
            <div class="col-md-6 text-md-right mt-2 mt-md-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-0 justify-content-md-end small">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">About Us</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Purpose Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <span class="badge badge-primary px-3 py-1 mb-2 font-weight-bold">System Purpose</span>
            <h2 class="font-weight-bold mb-3">Empowering Educational Institutions with Modern Records Management</h2>
            <p class="text-secondary leading-relaxed">
                The <strong>Student Registration System</strong> is a comprehensive web-based platform tailored for colleges, universities, and academic institutions to modernize the end-to-end student enrollment lifecycle.
            </p>
            <p class="text-secondary leading-relaxed">
                By replacing error-prone manual paper registers and dispersed spreadsheets, this system establishes a unified, single source of truth for student identity details, enrollment history, contact profiles, and departmental assignments.
            </p>
            <div class="d-flex align-items-center mt-4">
                <a href="<?= BASE_URL ?>/contact.php" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm mr-3">
                    <i class="fas fa-headset mr-2"></i> Get in Touch
                </a>
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline-secondary px-4 py-2 font-weight-bold">
                    <i class="fas fa-user-plus mr-2"></i> Register Account
                </a>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-lg p-4 bg-white">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded p-3 mr-3">
                        <i class="fas fa-bullseye fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold mb-1">Our Core Mission</h5>
                        <p class="text-muted small mb-0">Deliver intuitive, secure, and rapid student administrative workflows.</p>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success text-white rounded p-3 mr-3">
                        <i class="fas fa-lock fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold mb-1">Data Protection & Privacy</h5>
                        <p class="text-muted small mb-0">Prepared SQL statements, Bcrypt password hashing, and CSRF token defenses.</p>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="bg-info text-white rounded p-3 mr-3">
                        <i class="fas fa-mobile-alt fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold mb-1">Responsive on All Devices</h5>
                        <p class="text-muted small mb-0">Seamless experience on desktops, tablets, and smartphones via AdminLTE 3.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Features Breakdown -->
    <div class="text-center mb-5">
        <h3 class="font-weight-bold">Primary System Modules</h3>
        <p class="text-muted">A clear breakdown of key functionalities provided out of the box.</p>
    </div>

    <div class="row">
        <!-- Feature 1 -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-lg p-3">
                <div class="card-body">
                    <div class="text-primary mb-3">
                        <i class="fas fa-user-graduate fa-2x"></i>
                    </div>
                    <h5 class="font-weight-bold">1. Student Registration</h5>
                    <p class="text-muted small">
                        Full intake form capturing student full name, gender, date of birth, personal email, phone number, physical address, department/course, registration number, admission date, active/inactive status, and photo upload.
                    </p>
                </div>
            </div>
        </div>

        <!-- Feature 2 -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-lg p-3">
                <div class="card-body">
                    <div class="text-success mb-3">
                        <i class="fas fa-database fa-2x"></i>
                    </div>
                    <h5 class="font-weight-bold">2. Student Records Management</h5>
                    <p class="text-muted small">
                        Searchable and filterable data tables displaying all registered students. Instant keyword search by registration number, student name, or department. Full capability to view, edit, and safely delete student profiles.
                    </p>
                </div>
            </div>
        </div>

        <!-- Feature 3 -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-lg p-3">
                <div class="card-body">
                    <div class="text-warning mb-3">
                        <i class="fas fa-users-cog fa-2x"></i>
                    </div>
                    <h5 class="font-weight-bold">3. User & Staff Management</h5>
                    <p class="text-muted small">
                        Restricted strictly to administrators. View all system accounts, create staff/admin users, update roles, activate/deactivate accounts, reset passwords, and prevent accidental self-deletion.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Institutional Contact Banner -->
    <div class="bg-primary text-white rounded-lg p-4 p-md-5 mt-4 shadow">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h4 class="font-weight-bold mb-2">Have questions about registration or requirements?</h4>
                <p class="mb-0 text-white-50">
                    Our administrative team is available to assist students, faculty, and administrative staff during business hours.
                </p>
            </div>
            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                <a href="<?= BASE_URL ?>/contact.php" class="btn btn-light btn-lg text-primary font-weight-bold shadow-sm">
                    <i class="fas fa-envelope mr-2"></i> Contact Us Today
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
