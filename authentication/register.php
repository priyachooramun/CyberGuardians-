<?php

require_once __DIR__ . '/../includes/bootstrap.php';
require_guest();

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

if (is_post()) {
    verify_csrf();

    $v = new Validator($_POST);
    $v->required('name', 'Full name')->name('name')->maxLength('name', 100, 'Full name')
      ->required('email', 'Email')->email('email')
      ->required('phone', 'Phone')->phone('phone')
      ->required('address', 'Address')->maxLength('address', 255, 'Address')
      ->strongPassword('password')
      ->matches('password', 'password_confirm', 'The two passwords do not match.');

    $email = strtolower($v->clean('email'));
    if (!$v->fails() && Customer::emailExists($email)) {
        $v->add('email', 'An account with this email already exists. Try logging in.');
    }
    if (empty($_POST['terms'])) {
        $v->add('terms', 'Please accept the terms to create an account.');
    }

    if ($v->fails()) {
        $_SESSION['form_errors'] = $v->errors();
        remember_input($_POST);
        redirect('auth/register.php');
    }

    $id = Customer::create(
        $v->clean('name'),
        $email,
        preg_replace('/[\s-]/', '', $v->clean('phone')),
        $v->clean('address'),
        hash_password((string)$_POST['password'])
    );
    login_customer(Customer::find($id));
    clear_old_input();
    flash('success', 'Welcome to InkCorner! Your account has been created.');
    header('Location: ' . after_login_target());
    exit;
}

$pageTitle = 'Sign up';
$noindex   = true;
$pageCss   = 'register';
require ROOT_PATH . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="ink-form-card">
                <img src="<?= e(asset('images/logo.png')) ?>" alt="InkCorner" class="ink-auth-logo" width="150">
                <h1 class="display-font text-center mb-1">Create your account</h1>
                <p class="text-center mb-4">Sign up to receive discounts, sale alerts and new arrivals.</p>

                <form method="post" action="" class="js-validate" novalidate>
                    <?= csrf_field() ?>
                    <div class="ink-field-row">
                        <label for="name">Full name</label>
                        <div>
                            <input type="text" id="name" name="name" class="form-control ink-input<?= field_class($errors, 'name') ?>"
                                   value="<?= e(old('name')) ?>" maxlength="100" required data-rule="name" autocomplete="name">
                            <?= field_error($errors, 'name') ?>
                        </div>
                    </div>
                    <div class="ink-field-row">
                        <label for="email">Email</label>
                        <div>
                            <input type="email" id="email" name="email" class="form-control ink-input<?= field_class($errors, 'email') ?>"
                                   value="<?= e(old('email')) ?>" maxlength="100" required autocomplete="email">
                            <?= field_error($errors, 'email') ?>
                        </div>
                    </div>
                    <div class="ink-field-row">
                        <label for="phone">Phone</label>
                        <div>
                            <input type="tel" id="phone" name="phone" class="form-control ink-input<?= field_class($errors, 'phone') ?>"
                                   value="<?= e(old('phone')) ?>" placeholder="5712 3456" required data-rule="phone" autocomplete="tel">
                            <?= field_error($errors, 'phone') ?>
                        </div>
                    </div>
                    <div class="ink-field-row">
                        <label for="address">Address</label>
                        <div>
                            <input type="text" id="address" name="address" class="form-control ink-input<?= field_class($errors, 'address') ?>"
                                   value="<?= e(old('address')) ?>" maxlength="255" required autocomplete="street-address">
                            <?= field_error($errors, 'address') ?>
                        </div>
                    </div>
                    <div class="ink-field-row">
                        <label for="password">Password</label>
                        <div>
                            <input type="password" id="password" name="password" class="form-control ink-input<?= field_class($errors, 'password') ?>"
                                   required data-rule="password" autocomplete="new-password" maxlength="72">
                            <div class="form-text">8+ characters with an uppercase letter, a lowercase letter and a number.</div>
                            <?= field_error($errors, 'password') ?>
                        </div>
                    </div>
                    <div class="ink-field-row">
                        <label for="password_confirm">Confirm password</label>
                        <div>
                            <input type="password" id="password_confirm" name="password_confirm"
                                   class="form-control ink-input<?= field_class($errors, 'password_confirm') ?>"
                                   required data-match="#password" autocomplete="new-password" maxlength="72">
                            <?= field_error($errors, 'password_confirm') ?>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input<?= field_class($errors, 'terms') ?>" type="checkbox" id="terms" name="terms" value="1" required>
                        <label class="form-check-label" for="terms">I agree that InkCorner stores my details to process my orders.</label>
                        <?= field_error($errors, 'terms') ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <span class="small">Already have an account? <a href="<?= e(url('auth/login.php')) ?>" class="ink-pill-sm">Login</a></span>
                        <button type="submit" class="btn btn-ink btn-lg px-5">Sign up</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
