<?php
/**
 * Database Configuration & Global Constants
 * Student Registration System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session parameters
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'stud_reg_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');

// System Information
define('APP_NAME', 'Student Registration System');
define('APP_VERSION', '1.0.0');

// Paths and URLs
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'students' . DIRECTORY_SEPARATOR);

// Dynamic BASE_URL detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = preg_replace('#/(config|includes|assets|database|uploads)(/.*)?$#i', '', $scriptDir);
$basePath = rtrim($basePath, '/');
define('BASE_URL', $protocol . $host . $basePath);

// Establish Database Connection with PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Check if error is unknown database (db exists or not)
    $errorMessage = $e->getMessage();
    $dbMissing = (strpos($errorMessage, 'Unknown database') !== false);
    
    // Friendly error display
    die('<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Database Connection Error - ' . APP_NAME . '</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
        <style>body { background-color: #f4f6f9; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }</style>
    </head>
    <body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card shadow-lg border-0" style="max-width: 650px; width: 90%;">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="fas fa-exclamation-triangle mr-2"></i> Database Connection Error</h4>
            </div>
            <div class="card-body p-4">
                <p class="text-danger font-weight-bold">Could not connect to MySQL database:</p>
                <div class="alert alert-secondary text-monospace small">' . htmlspecialchars($errorMessage) . '</div>
                ' . ($dbMissing ? '
                <div class="alert alert-warning">
                    <strong>Notice:</strong> Database <code>' . DB_NAME . '</code> was not found. Please import the provided SQL file:
                    <div class="mt-2"><strong>File:</strong> <code>database/stud_reg_system.sql</code></div>
                    <div class="mt-1">In <strong>phpMyAdmin</strong>: Create database <code>' . DB_NAME . '</code> and click <em>Import</em>.</div>
                </div>' : '
                <div class="alert alert-info">
                    <strong>Checklist:</strong>
                    <ul class="mb-0 mt-2 pl-3">
                        <li>Make sure <strong>MySQL service is running</strong> in the XAMPP Control Panel.</li>
                        <li>Verify credentials in <code>config/db.php</code> (User: <code>root</code>, Password: empty by default in XAMPP).</li>
                    </ul>
                </div>') . '
                <a href="javascript:location.reload()" class="btn btn-primary mt-2">Retry Connection</a>
            </div>
        </div>
    </body>
    </html>');
}
