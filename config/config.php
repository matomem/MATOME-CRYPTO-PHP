<?php
// Load local environment before reading any database or service credentials.
// Load environment variables if .env file exists
if (file_exists(APP_ROOT . '/.env')) {
    $env = parse_ini_file(APP_ROOT . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

// Application Configuration
// Database Configuration
// Credentials are supplied by environment variables, not committed source.
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'matomecrypto');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Application Configuration
define('APP_NAME', 'MatomeCrypto');
define('APP_URL', 'http://localhost/matomecrypto'); 
define('APP_ROOT', dirname(__DIR__));

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'matomecrypto_session');

// Security Configuration
define('HASH_COST', 12);
define('TOKEN_EXPIRY', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// Luno API Configuration
define('LUNO_API_KEY', '');
define('LUNO_API_SECRET', '');
define('LUNO_API_URL', 'https://api.Luno.com/api/1');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', '');
define('SMTP_FROM_NAME', APP_NAME);

// File Upload Configuration
define('UPLOAD_DIR', APP_ROOT . '/public/uploads');
define('MAX_FILE_SIZE', 5242880); // 10 G
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_DIR', APP_ROOT . '/storage/cache');
define('CACHE_LIFETIME', 3600); // 1 hour

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', (getenv('APP_ENV') === 'production') ? '0' : '1');
ini_set('log_errors', 1);
ini_set('error_log', APP_ROOT . '/storage/logs/error.log');

// Timezone
date_default_timezone_set('UTC');

// Application Configuration
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === '1' && APP_ENV !== 'production');

// Price Alert Configuration
define('PRICE_CHECK_INTERVAL', 60); // 1 minute
define('PRICE_HISTORY_INTERVAL', 300); // 5 minutes

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', APP_ENV === 'production' ? 1 : 0);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// PDO connection
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(503);
    exit('Database is unavailable.');
} 