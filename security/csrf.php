<?php
/**
 * FR27 CSRF protection: a secret token on every form and AJAX request
 */

declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_meta(): string
{
    return '<meta name="csrf-token" content="' . e(csrf_token()) . '">';
}

function csrf_is_valid(): bool
{
    $sent = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($sent) && $sent !== '' && hash_equals(csrf_token(), $sent);
}


function verify_csrf(bool $json = false): void
{
    if (csrf_is_valid()) {
        return;
    }
    security_log('csrf_rejected', ($_SERVER['REQUEST_METHOD'] ?? '') . ' without a valid token');
    $isAjax = $json
        || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    if ($isAjax) {
        json_response(['success' => false, 'message' => 'Security token expired. Please reload the page.'], 419);
    }
    http_response_code(419);
    flash('danger', 'Your session expired or the form was tampered with. Please try again.');
    $back = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // Only go back to a page on our own site (never an outside link)
    if ($back !== '' && parse_url($back, PHP_URL_HOST) === parse_url('http://' . $host, PHP_URL_HOST)) {
        header('Location: ' . $back);
        exit;
    }
    redirect('index.php');
}
