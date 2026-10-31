<?php


declare(strict_types=1);

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit('Not found');
}

header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
require __DIR__ . '/config/config.php';

$h      = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$checks = [];
$add    = static function (string $group, string $name, string $status, string $detail, string $fix = '') use (&$checks): void {
    $checks[] = compact('group', 'name', 'status', 'detail', 'fix');
};


$add('PHP', 'PHP version', version_compare(PHP_VERSION, '8.1.0', '>=') ? 'ok' : 'fail', 'PHP ' . PHP_VERSION,
    'Install XAMPP with PHP 8.1 or newer (8.2 recommended) from apachefriends.org.');
foreach (['pdo_mysql' => 'database connection', 'mbstring' => 'text handling', 'fileinfo' => 'product image upload check',
          'json' => 'AJAX / JSON', 'session' => 'logins'] as $ext => $why) {
    $add('PHP', "Extension: {$ext}", extension_loaded($ext) ? 'ok' : 'fail', "Needed for {$why}",
        "Open C:\\xampp\\php\\php.ini, find the line ;extension={$ext}, remove the ; at the start, save, then Stop and Start Apache.");
}


$folderUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$add('Folder', 'BASE_URL matches the folder', $folderUrl === rtrim(BASE_URL, '/') ? 'ok' : 'fail',
    'Site folder in the URL: "' . ($folderUrl ?: '(web root)') . '" · BASE_URL setting: "' . BASE_URL . '"',
    "The folder is normally detected automatically – delete any BASE_URL line from config/config.local.php. If it is still wrong (for example an Apache Alias), put define('BASE_URL', '" . $folderUrl . "'); in config/config.local.php.");
$add('Folder', 'Personal settings file', is_file(__DIR__ . '/config/config.local.php') ? 'ok' : 'info',
    is_file(__DIR__ . '/config/config.local.php') ? 'config/config.local.php found' : 'Not created – the defaults are used (XAMPP: user root, no password)',
    'Only needed if your settings differ: copy config/config.local.example.php to config/config.local.php.');
$add('Folder', 'Not nested twice', is_file(__DIR__ . '/index.php') && is_dir(__DIR__ . '/includes') ? 'ok' : 'fail',
    __DIR__, 'The folder must contain index.php directly (not inkcorner\\inkcorner\\index.php).');


$pdo = null;
try {
    $dsn = DB_DSN !== '' ? DB_DSN : sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]);
    $add('Database', 'Connect to MySQL', 'ok', 'Connected to "' . DB_NAME . '" as ' . DB_USER . ' on port ' . DB_PORT);
} catch (PDOException $e) {
    $msg = $e->getMessage();
    $fix = match (true) {
        str_contains($msg, 'Unknown database')   => 'The database "' . DB_NAME . '" does not exist. In phpMyAdmin create it (or import 01_original_schema.sql), or set DB_NAME in config/config.local.php to your database name.',
        str_contains($msg, 'Access denied')      => 'Wrong MySQL user or password. Set DB_USER / DB_PASS in config/config.local.php (XAMPP default: root and an empty password).',
        str_contains($msg, 'refused') || str_contains($msg, '2002') => 'MySQL is not running or uses another port. Click Start next to MySQL in the XAMPP Control Panel; if its port is not 3306 set DB_PORT in config/config.local.php.',
        default                                  => 'Check DB_HOST, DB_PORT, DB_NAME, DB_USER and DB_PASS in config/config.local.php.',
    };
    $add('Database', 'Connect to MySQL', 'fail', $msg, $fix);
}

if ($pdo) {
    $hasColumn = static function (string $table, string $column) use ($pdo): bool {
        try {
            $pdo->query("SELECT `{$column}` FROM `{$table}` LIMIT 1");
            return true;
        } catch (PDOException) {
            return false;
        }
    };
    $tables  = ['admin', 'bookstore', 'customer', 'inventory', 'orderitem', 'orders', 'payment', 'product', 'productvariant', 'query', 'receipt'];
    $missing = [];
    foreach ($tables as $t) {
        try { $pdo->query("SELECT 1 FROM `{$t}` LIMIT 1"); } catch (PDOException) { $missing[] = $t; }
    }
    $add('Database', 'The 11 tables exist', $missing ? 'fail' : 'ok',
        $missing ? 'Missing: ' . implode(', ', $missing) : 'admin, bookstore, customer, inventory, orderitem, orders, payment, product, productvariant, query, receipt',
        'In phpMyAdmin select the database → Import → database/01_original_schema.sql.');

    $upgrade = ['customer' => 'password_hash', 'admin' => 'password_hash', 'product' => 'section', 'orders' => 'total_amount',
                'payment' => 'transaction_ref', 'orderitem' => 'variant_id', 'receipt' => 'receipt_number', 'query' => 'reply'];
    $notUpgraded = [];
    foreach ($upgrade as $t => $c) {
        if (!in_array($t, $missing, true) && !$hasColumn($t, $c)) {
            $notUpgraded[] = "{$t}.{$c}";
        }
    }
    $add('Database', 'Upgrade script imported (02_upgrade.sql)', !$missing && !$notUpgraded ? 'ok' : 'fail',
        $notUpgraded ? 'Missing columns: ' . implode(', ', $notUpgraded) : ($missing ? 'Import the tables first' : 'All new columns present'),
        'In phpMyAdmin select the database → Import → database/02_upgrade.sql.');

    if (!$missing && !$notUpgraded) {
        $products = (int)$pdo->query('SELECT COUNT(*) FROM product')->fetchColumn();
        $admins   = (int)$pdo->query("SELECT COUNT(*) FROM admin WHERE password_hash IS NOT NULL AND password_hash <> ''")->fetchColumn();
        $add('Database', 'Demo data imported (03_seed.sql)', $products > 0 && $admins > 0 ? 'ok' : 'fail',
            "{$products} products, {$admins} administrator account(s) with a password",
            'In phpMyAdmin select the database → Import → database/03_seed.sql.');
        try {
            $pdo->beginTransaction();
            $pdo->exec("INSERT INTO bookstore (name) VALUES ('setup-check')");
            $autoId = (int)$pdo->lastInsertId();
            $pdo->rollBack();
            $add('Database', 'AUTO_INCREMENT on ids', $autoId > 0 ? 'ok' : 'fail', 'New rows get an id automatically', 'Import database/02_upgrade.sql again.');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $add('Database', 'AUTO_INCREMENT on ids', 'fail', $e->getMessage(), 'Import database/02_upgrade.sql again.');
        }
    }
}


foreach (['uploads/products' => 'product images', 'storage/logs' => 'security log'] as $dir => $why) {
    $path = __DIR__ . '/' . $dir;
    $add('Files', "Writable: {$dir}", is_dir($path) && is_writable($path) ? 'ok' : 'fail', "Needed for {$why}",
        "Create the folder {$dir} if it is missing. On Windows/XAMPP it is writable by default; on macOS run: chmod -R 775 {$dir}");
}
foreach (['assets/images/logo.png', 'assets/images/logo-sm.png', 'assets/images/bg-desk.jpg'] as $img) {
    $add('Files', basename($img), is_file(__DIR__ . '/' . $img) ? 'ok' : 'fail', $img, "Copy {$img} from the project zip.");
}
if (USE_LOCAL_LIBS) {
    foreach (['bootstrap.min.css', 'bootstrap.bundle.min.js', 'jquery.min.js', 'bootstrap-icons/bootstrap-icons.min.css'] as $lib) {
        $add('Files', "Local library: {$lib}", is_file(__DIR__ . '/assets/vendor/' . $lib) ? 'ok' : 'fail',
            'USE_LOCAL_LIBS is on', 'Download it as explained in assets/vendor/README.txt, or set USE_LOCAL_LIBS to false.');
    }
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$probe  = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim($folderUrl, '/') . '/database/03_seed.sql';
$ctx    = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
$body   = @file_get_contents($probe, false, $ctx);
$status = isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m) ? (int)$m[1] : 0;
if ($status === 0) {
    $add('Security', '.htaccess protection', 'info', 'Could not test automatically', 'Open ' . $probe . ' in the browser – you should see "Forbidden", not SQL.');
} else {
    $add('Security', '.htaccess protection', $status === 403 ? 'ok' : 'fail',
        "database/03_seed.sql answered HTTP {$status}" . ($status === 403 ? ' (blocked – good)' : ''),
        'Apache ignores .htaccess files. In C:\\xampp\\apache\\conf\\httpd.conf, inside <Directory "C:/xampp/htdocs">, set AllowOverride All, save and restart Apache.');
}
$add('Security', 'Debug messages', APP_DEBUG ? 'info' : 'ok', APP_DEBUG ? 'APP_DEBUG is on – detailed errors are shown (fine while developing)' : 'APP_DEBUG is off',
    "Before the final demo add define('APP_DEBUG', false); to config/config.local.php.");

$fails = count(array_filter($checks, static fn($c) => $c['status'] === 'fail'));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>InkCorner – setup check</title>
<link href="<?= $h(rtrim($folderUrl, '/')) ?>/assets/css/setup-check.css" rel="stylesheet">
</head>
<body>
<header>
    <img src="<?= $h(rtrim($folderUrl, '/')) ?>/assets/images/logo-sm.png" alt="InkCorner">
    <div>
        <h1>Setup check</h1>
        <p><?= $fails === 0 ? 'Everything is ready. Open the shop below.' : $fails . ' item' . ($fails === 1 ? '' : 's') . ' to fix – follow “How to fix”, then reload this page.' ?></p>
    </div>
</header>
<main>
    <div class="summary <?= $fails === 0 ? 'good' : 'bad' ?>">
        <?php if ($fails === 0): ?>
            ✓ All checks passed.
            <a href="<?= $h(rtrim($folderUrl, '/')) ?>/index.php">Open the shop</a>
            <a href="<?= $h(rtrim($folderUrl, '/')) ?>/admin/login.php">Staff login</a>
        <?php else: ?>
            ✗ <?= $fails ?> check<?= $fails === 1 ? '' : 's' ?> failed. Fix them from the top down – later checks often depend on earlier ones.
        <?php endif; ?>
    </div>
    <table>
        <thead><tr><th></th><th>Check</th><th>Result</th><th>How to fix</th></tr></thead>
        <tbody>
        <?php $group = ''; foreach ($checks as $c): ?>
            <?php if ($c['group'] !== $group): $group = $c['group']; ?>
                <tr class="group"><td colspan="4"><?= $h($group) ?></td></tr>
            <?php endif; ?>
            <tr class="<?= $h($c['status']) ?>">
                <td class="mark"><?= $c['status'] === 'ok' ? '✓' : ($c['status'] === 'fail' ? '✗' : 'i') ?></td>
                <td><?= $h($c['name']) ?></td>
                <td><?= $h($c['detail']) ?></td>
                <td><?= $c['status'] === 'ok' ? '' : $h($c['fix']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="note">This page only opens on this computer (localhost). Keep it for setting up other team members' computers.</p>
</main>
</body>
</html>
