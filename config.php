<?php

declare(strict_types=1);

if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

defined('APP_NAME')  || define('APP_NAME', 'InkCorner');

defined('BASE_URL') || define('BASE_URL', detect_base_url());

function detect_base_url(string $fallback = '/inkcorner'): string
{
    $docRoot = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $appRoot = realpath(dirname(__DIR__));
    if ($docRoot === false || $appRoot === false) {
        return $fallback;                                   // command line, or unusual server
    }
    $docRoot = rtrim(str_replace('\\', '/', $docRoot), '/');
    $appRoot = rtrim(str_replace('\\', '/', $appRoot), '/');
    // Windows paths are not case-sensitive (C:/xampp vs c:/XAMPP)
    $windows = DIRECTORY_SEPARATOR === '\\';
    $hay     = $windows ? strtolower($appRoot) : $appRoot;
    $needle  = $windows ? strtolower($docRoot) : $docRoot;
    if ($hay === $needle) {
        return '';                                          // project IS the web root
    }
    if (!str_starts_with($hay, $needle . '/')) {
        return $fallback;                                   // outside DocumentRoot (Alias) – set it by hand
    }
    $path = substr($appRoot, strlen($docRoot));             // e.g. "/inkcorner" or "/projects/inkcorner-main"
    return implode('/', array_map('rawurlencode', explode('/', $path)));
}
defined('CURRENCY')  || define('CURRENCY', 'Rs');
defined('APP_DEBUG') || define('APP_DEBUG', true);   // set false for the final demo
defined('APP_TIMEZONE') || define('APP_TIMEZONE', 'Indian/Mauritius');   // order dates, receipts, logs
date_default_timezone_set(APP_TIMEZONE);

defined('USE_LOCAL_LIBS') || define('USE_LOCAL_LIBS', false);


defined('DB_DSN')  || define('DB_DSN', '');           // leave empty to build from the values below
defined('DB_HOST') || define('DB_HOST', '127.0.0.1');
defined('DB_PORT') || define('DB_PORT', '3306');
defined('DB_NAME') || define('DB_NAME', 'stationery real');   // your phpMyAdmin database name
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');

defined('SESSION_TIMEOUT')    || define('SESSION_TIMEOUT', 30 * 60);   // idle logout after 30 min
defined('SESSION_REGENERATE') || define('SESSION_REGENERATE', 15 * 60);// new session id every 15 min
defined('MAX_LOGIN_ATTEMPTS') || define('MAX_LOGIN_ATTEMPTS', 5);
defined('LOGIN_LOCK_SECONDS') || define('LOGIN_LOCK_SECONDS', 5 * 60);

defined('LOW_STOCK_LEVEL') || define('LOW_STOCK_LEVEL', 5);
defined('UPLOAD_DIR')      || define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/products/');
defined('UPLOAD_URL')      || define('UPLOAD_URL', BASE_URL . '/uploads/products/');
defined('MAX_UPLOAD_BYTES')|| define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);   // 2 MB


const SECTIONS = [
    'stationery' => ['Pencils', 'Pens', 'Copybooks', 'Notebooks', 'Art Supplies', 'Miscellaneous'],
    'book'       => ['Fiction', 'Non-fiction', 'Academic', 'Children'],
];
