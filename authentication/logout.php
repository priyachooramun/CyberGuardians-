<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!is_post()) {
    redirect(current_admin() ? 'admin/index.php' : 'index.php');
}
verify_csrf();

$wasAdmin = current_admin() !== null;
logout_user();

if ($wasAdmin) {
    flash('success', 'You have been logged out.');
    redirect('admin/login.php');
}
flash('success', 'You have been logged out. See you soon!');
redirect('index.php');
