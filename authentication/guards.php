<?php

declare(strict_types=1);

function current_customer(): ?array
{
    return $_SESSION['customer'] ?? null;
}

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function customer_id(): int
{
    return (int)($_SESSION['customer']['id'] ?? 0);
}

function require_customer(string $message = 'Please log in or create an account to continue.'): void
{
    if (current_customer()) {
        send_no_cache_headers();   // personal pages are never cached
        return;
    }
    // Remember where the guest wanted to go (only our own paths)
    $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '';
    flash('info', $message);
    redirect('auth/login.php');
}

function require_admin(): void
{
    if (current_admin()) {
        send_no_cache_headers();
        return;
    }
    if (current_customer()) {
        security_log('access_denied', 'Customer #' . customer_id() . ' tried to open an admin page');
        abort(403, 'This area is for InkCorner staff only.');
    }
    redirect('admin/login.php');
}

function require_guest(): void
{
    if (current_customer()) {
        redirect('shop/index.php');
    }
}

/** Where to send a customer after login – only a path on this site, never an outside URL. */
function after_login_target(): string
{
    $target = $_SESSION['after_login'] ?? '';
    unset($_SESSION['after_login']);
    $base = rtrim(BASE_URL, '/') . '/';
    if (is_string($target) && str_starts_with($target, $base) && !str_contains($target, '//')) {
        return $target;
    }
    return url('shop/index.php');
}
