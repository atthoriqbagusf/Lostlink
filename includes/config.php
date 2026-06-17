<?php
/**
 * LostLink - Configuration File
 */

// ============================================================
// SESSION & TIMEZONE
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

// ============================================================
// ERROR REPORTING (Disable in production)
// ============================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// BASE URL
// ============================================================

define('BASE_URL', 'http://localhost/lostlink/');

// ============================================================
// DATABASE CONFIGURATION
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lostlink_db');

// ============================================================
// DATABASE CONNECTION
// ============================================================

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}

function getDB() {
    return Database::getInstance()->getConnection();
}

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user role
 */
function getUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return isLoggedIn() && getUserRole() === 'admin';
}

/**
 * Redirect helper
 */
function redirect($path) {
    if (strpos($path, 'http') === 0) {
        header("Location: " . $path);
        exit();
    }
    if (strpos($path, '/') === 0) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        header("Location: " . $protocol . $host . $path);
        exit();
    }
    header("Location: " . BASE_URL . $path);
    exit();
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash message
 */
function setFlash($message, $type = 'info') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/**
 * Get flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format date
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime) {
    return date('d M Y H:i', strtotime($datetime));
}

/**
 * Upload file helper
 */
function uploadFile($file, $folder = 'uploads/') {
    $uploadDir = __DIR__ . '/../assets/' . $folder;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = time() . '_' . basename($file['name']);
    $targetFile = $uploadDir . $fileName;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan. Hanya JPG/PNG.'];
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 5MB.'];
    }

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => true, 'path' => 'assets/' . $folder . $fileName];
    }

    return ['success' => false, 'message' => 'Gagal mengunggah file.'];
}