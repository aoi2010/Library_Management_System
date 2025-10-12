<?php
// Database connection using PDO
// Do NOT commit secrets. Configure credentials via config/db.local.php (ignored) or environment variables.

// Load local overrides if present (ignored by Git)
if (file_exists(__DIR__ . '/db.local.php')) {
  require_once __DIR__ . '/db.local.php';
}

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'library_db');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed. Please check config/db.php and config/db.local.php.');
        }
    }
    return $pdo;
}

// Global defaults (can be overridden in DB settings table)
if (!defined('FINE_PER_DAY')) define('FINE_PER_DAY', 2); // currency units per overdue day
if (!defined('APP_NAME')) define('APP_NAME', 'LMS');
?>
