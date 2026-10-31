<?php


declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

require_once ROOT_PATH . '/security/escape.php';
require_once ROOT_PATH . '/security/csrf.php';
require_once ROOT_PATH . '/security/validator.php';
require_once ROOT_PATH . '/security/password.php';
require_once ROOT_PATH . '/security/headers.php';
require_once ROOT_PATH . '/security/log.php';


require_once ROOT_PATH . '/auth/session.php';
require_once ROOT_PATH . '/auth/guards.php';

require_once ROOT_PATH . '/includes/helpers.php';

spl_autoload_register(static function (string $class): void {
    foreach (['models', 'services'] as $dir) {
        $file = ROOT_PATH . "/{$dir}/{$class}.php";
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

if (!APP_DEBUG) {
    ini_set('display_errors', '0');
}

send_security_headers();

start_secure_session();
