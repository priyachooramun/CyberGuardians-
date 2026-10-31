<?php
/**
 * Administrator log in (separate from customer login)
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if (current_admin()) {
    redirect('admin/index.php');
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

if (is_post()) {
    verify_csrf();

    $locked = login_is_locked('admin');
    if ($locked > 0) {
        $_SESSION['login_error'] = 'Too many failed attempts. Try again in ' . ceil($locked / 60) . ' minute(s).';
        redirect('admin/login.php');
    }

    $v     = new Validator($_POST);
    $email = strtolower($v->clean('email'));
    $pass  = (string)($_POST['password'] ?? '');
    $admin = filter_var($email, FILTER_VALIDATE_EMAIL) ? Admin::findByEmailWithHash($email) : null;

    if (!check_password($pass, $admin['password_hash'] ?? null)) {
        login_failed('admin');
        security_log('admin_login_failed', 'Admin login failed for ' . mask_email($email));
        remember_input(['email' => $email]);
        $_SESSION['login_error'] = 'Incorrect email or password.';
        redirect('admin/login.php');
    }

    login_reset('admin');
    login_admin($admin);
    security_log('admin_login', $admin['name'] . ' logged in');
    clear_old_input();
    flash('success', 'Logged in as ' . $admin['name'] . '.');
    redirect('admin/index.php');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Staff login | <?= e(APP_NAME) ?></title>
    <?= vendor_styles(false) ?>
    <link href="<?= e(asset('css/base.css')) ?>" rel="stylesheet">
    <link href="<?= e(asset('css/admin.css')) ?>" rel="stylesheet">
    <link href="<?= e(asset('css/pages/admin-login.css')) ?>" rel="stylesheet">
</head>
<body class="ink-bg page-admin-login">
<header class="admin-topbar d-flex align-items-center">
    <a href="<?= e(url('index.php')) ?>" class="admin-logo">
        <img src="<?= e(asset('images/logo-sm.png')) ?>" alt="InkCorner" class="brand-logo">
    </a>
    <h1 class="admin-title mb-0">Staff login</h1>
</header>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="ink-form-card">
                <h2 class="ledger-font text-center mb-4">Administrator</h2>
                <?php foreach (take_flashes() as $flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                <?php endforeach; ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="post" action="" class="js-validate" novalidate>
                    <?= csrf_field() ?>
                    <div class="ink-field-row">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control ink-input" value="<?= e(old('email')) ?>" required autocomplete="username" autofocus>
                    </div>
                    <div class="ink-field-row">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control ink-input" required autocomplete="current-password" maxlength="72">
                    </div>
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-ink btn-lg px-5">Login</button>
                    </div>
                </form>
            </div>
            <p class="text-center small mt-3"><a class="link-dark" href="<?= e(url('index.php')) ?>">← Back to the shop</a></p>
        </div>
    </div>
</div>
<?= vendor_scripts(false) ?>
<script src="<?= e(asset('js/validation.js')) ?>"></script>
</body>
</html>
<?php clear_old_input(); ?>
