<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_guest();

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

if (is_post()) {
    verify_csrf();

    $locked = login_is_locked('customer');
    if ($locked > 0) {
        $_SESSION['login_error'] = 'Too many failed attempts. Try again in ' . ceil($locked / 60) . ' minute(s).';
        redirect('auth/login.php');
    }

    $v     = new Validator($_POST);
    $email = strtolower($v->clean('email'));
    $pass  = (string)($_POST['password'] ?? '');

    $customer = filter_var($email, FILTER_VALIDATE_EMAIL) ? Customer::findByEmailWithHash($email) : null;

    if (!check_password($pass, $customer['password_hash'] ?? null)) {
        login_failed('customer');
        security_log('login_failed', 'Customer login failed for ' . mask_email($email));
        remember_input(['email' => $email]);
        // Same message whether the email or the password was wrong 
        $_SESSION['login_error'] = 'Incorrect email or password.';
        redirect('auth/login.php');
    }

    if (password_needs_upgrade($customer['password_hash'])) {
        Customer::updatePassword((int)$customer['customer_id'], hash_password($pass));
    }
    login_reset('customer');
    login_customer($customer);
    clear_old_input();
    flash('success', 'Welcome back, ' . $customer['name'] . '!');
    header('Location: ' . after_login_target());
    exit;
}

$pageTitle = 'Login';
$noindex   = true;
$pageCss   = 'login';
require ROOT_PATH . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="ink-form-card">
                <img src="<?= e(asset('images/logo.png')) ?>" alt="InkCorner" class="ink-auth-logo" width="170">
                <h1 class="display-font text-center mb-4">Login</h1>
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="post" action="" class="js-validate" novalidate>
                    <?= csrf_field() ?>
                    <div class="ink-field-row">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control ink-input" value="<?= e(old('email')) ?>"
                               required maxlength="100" autocomplete="username" autofocus>
                    </div>
                    <div class="ink-field-row">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control ink-input" required maxlength="72"
                               autocomplete="current-password">
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                        <?php /* TODO (optional): a self-service "reset password by email" page needs an SMTP
                           account and a mail library such as PHPMailer. Until then customers ask the shop
                           through the contact page and the owner resets the password. */ ?>
                        <a class="small" href="<?= e(url('contact.php')) ?>">Forgot your password?</a>
                        <button type="submit" class="btn btn-ink btn-lg px-5">Login</button>
                    </div>
                </form>
                <p class="text-center small mt-5 mb-0">
                    Don't have an account?
                    <a href="<?= e(url('auth/register.php')) ?>" class="ink-pill-sm ms-2">Sign up</a>
                </p>
            </div>
            <p class="text-center small mt-3"><a href="<?= e(url('admin/login.php')) ?>" class="link-dark">Staff login</a></p>
        </div>
    </div>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
