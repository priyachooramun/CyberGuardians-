<?php
/**
 * Store, view and update personal details; change password
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_customer();

$customerId = customer_id();
$errors     = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $v = new Validator($_POST);
        $v->required('name', 'Full name')->name('name')
          ->required('phone', 'Phone')->phone('phone')
          ->required('address', 'Address')->maxLength('address', 255, 'Address');
        if ($v->fails()) {
            $_SESSION['form_errors'] = $v->errors();
            remember_input($_POST);
            redirect('account/profile.php');
        }
        Customer::updateProfile($customerId, $v->clean('name'), preg_replace('/[\s-]/', '', $v->clean('phone')), $v->clean('address'));
        $_SESSION['customer']['name'] = $v->clean('name');
        flash('success', 'Your details have been updated.');
        redirect('account/profile.php');
    }

    if ($action === 'password') {
        $v = new Validator($_POST);
        $v->strongPassword('password')->matches('password', 'password_confirm', 'The two new passwords do not match.');
        if (!check_password((string)($_POST['current_password'] ?? ''), Customer::passwordHash($customerId))) {
            $v->add('current_password', 'Your current password is incorrect.');
        }
        if ($v->fails()) {
            $_SESSION['form_errors'] = $v->errors();
            redirect('account/profile.php', ['tab' => 'password']);
        }
        Customer::updatePassword($customerId, hash_password((string)$_POST['password']));
        renew_session();   // new session id after a credential change
        flash('success', 'Your password has been changed.');
        redirect('account/profile.php');
    }
    redirect('account/profile.php');
}

$customer  = Customer::find($customerId);
if (!$customer) {           // account removed while logged in
    logout_user();
    redirect('auth/login.php');
}
$tab       = ($_GET['tab'] ?? '') === 'password' ? 'password' : 'profile';
$pageTitle = 'My profile';
$noindex   = true;
$pageCss   = 'profile';
require ROOT_PATH . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="ink-form-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                    <h1 class="display-font mb-0">My profile</h1>
                    <a class="btn btn-outline-dark btn-sm" href="<?= e(url('account/orders.php')) ?>">My orders</a>
                </div>

                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item"><button class="nav-link<?= $tab === 'profile' ? ' active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tabProfile" type="button">Personal details</button></li>
                    <li class="nav-item"><button class="nav-link<?= $tab === 'password' ? ' active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tabPassword" type="button">Password</button></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade<?= $tab === 'profile' ? ' show active' : '' ?>" id="tabProfile">
                        <form method="post" action="" class="js-validate" novalidate>
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="profile">
                            <div class="ink-field-row">
                                <label>Email</label>
                                <div><input type="email" class="form-control ink-input" value="<?= e($customer['email']) ?>" disabled>
                                    <div class="form-text">Your email is your login and cannot be changed.</div></div>
                            </div>
                            <div class="ink-field-row">
                                <label for="name">Full name</label>
                                <div><input type="text" id="name" name="name" class="form-control ink-input<?= field_class($errors, 'name') ?>"
                                            value="<?= e(old('name', $customer['name'])) ?>" required maxlength="100" data-rule="name">
                                    <?= field_error($errors, 'name') ?></div>
                            </div>
                            <div class="ink-field-row">
                                <label for="phone">Phone</label>
                                <div><input type="tel" id="phone" name="phone" class="form-control ink-input<?= field_class($errors, 'phone') ?>"
                                            value="<?= e(old('phone', (string)$customer['phone'])) ?>" required data-rule="phone">
                                    <?= field_error($errors, 'phone') ?></div>
                            </div>
                            <div class="ink-field-row">
                                <label for="address">Address</label>
                                <div><input type="text" id="address" name="address" class="form-control ink-input<?= field_class($errors, 'address') ?>"
                                            value="<?= e(old('address', (string)$customer['address'])) ?>" required maxlength="255">
                                    <?= field_error($errors, 'address') ?></div>
                            </div>
                            <div class="text-end"><button class="btn btn-ink px-5" type="submit">Save details</button></div>
                        </form>
                    </div>

                    <div class="tab-pane fade<?= $tab === 'password' ? ' show active' : '' ?>" id="tabPassword">
                        <form method="post" action="" class="js-validate" novalidate>
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="password">
                            <div class="ink-field-row">
                                <label for="current_password">Current password</label>
                                <div><input type="password" id="current_password" name="current_password" required maxlength="72"
                                            class="form-control ink-input<?= field_class($errors, 'current_password') ?>" autocomplete="current-password">
                                    <?= field_error($errors, 'current_password') ?></div>
                            </div>
                            <div class="ink-field-row">
                                <label for="password">New password</label>
                                <div><input type="password" id="password" name="password" required maxlength="72" data-rule="password"
                                            class="form-control ink-input<?= field_class($errors, 'password') ?>" autocomplete="new-password">
                                    <?= field_error($errors, 'password') ?></div>
                            </div>
                            <div class="ink-field-row">
                                <label for="password_confirm">Confirm new password</label>
                                <div><input type="password" id="password_confirm" name="password_confirm" required maxlength="72" data-match="#password"
                                            class="form-control ink-input<?= field_class($errors, 'password_confirm') ?>" autocomplete="new-password">
                                    <?= field_error($errors, 'password_confirm') ?></div>
                            </div>
                            <div class="text-end"><button class="btn btn-ink px-5" type="submit">Change password</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
