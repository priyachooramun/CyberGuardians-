<?php

declare(strict_types=1);

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    session_name('INKCORNER_SID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    $now         = time();
    $fingerprint = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

    // Different browser using this session id → probably a stolen cookie
    if (isset($_SESSION['_fingerprint']) && !hash_equals($_SESSION['_fingerprint'], $fingerprint)) {
        security_log('session_hijack', 'Session cookie used from a different browser – session destroyed');
        destroy_session();
        session_start();
    }

    // Idle too long → log out
    if (isset($_SESSION['_last_activity']) && ($now - $_SESSION['_last_activity']) > SESSION_TIMEOUT) {
        $wasLoggedIn = isset($_SESSION['customer']) || isset($_SESSION['admin']);
        destroy_session();
        session_start();
        if ($wasLoggedIn) {
            $_SESSION['flash'][] = ['type' => 'warning', 'message' => 'You were logged out after 30 minutes of inactivity.'];
        }
    }

    $_SESSION['_fingerprint']   = $fingerprint;
    $_SESSION['_last_activity'] = $now;

    // Change the session id regularly
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = $now;
    } elseif ($now - $_SESSION['_created'] > SESSION_REGENERATE) {
        session_regenerate_id(true);
        $_SESSION['_created'] = $now;
    }
}

/** Fresh session id after a privilege change (login) – prevents session fixation. */
function renew_session(): void
{
    session_regenerate_id(true);
    $_SESSION['_created'] = time();
    unset($_SESSION['csrf_token']);   // new token for the new privilege level
}

function destroy_session(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}

function login_customer(array $customer): void
{
    $cart = $_SESSION['cart'] ?? [];          // keep the guest's cart after login
    renew_session();
    unset($_SESSION['admin']);
    $_SESSION['customer'] = [
        'id'    => (int)$customer['customer_id'],
        'name'  => $customer['name'],
        'email' => $customer['email'],
    ];
    $_SESSION['cart'] = $cart;
}

function login_admin(array $admin): void
{
    renew_session();
    unset($_SESSION['customer'], $_SESSION['cart']);
    $_SESSION['admin'] = [
        'id'    => (int)$admin['admin_id'],
        'name'  => $admin['name'],
        'email' => $admin['email'],
    ];
}

function logout_user(): void
{
    destroy_session();
    session_start();
    renew_session();
}


function login_is_locked(string $scope): int
{
    $data = $_SESSION['login_attempts'][$scope] ?? null;
    if (!$data || $data['count'] < MAX_LOGIN_ATTEMPTS) {
        return 0;
    }
    $remaining = $data['time'] + LOGIN_LOCK_SECONDS - time();
    if ($remaining <= 0) {
        unset($_SESSION['login_attempts'][$scope]);
        return 0;
    }
    return $remaining;
}

function login_failed(string $scope): void
{
    $data = $_SESSION['login_attempts'][$scope] ?? ['count' => 0, 'time' => time()];
    $data['count']++;
    $data['time'] = time();
    $_SESSION['login_attempts'][$scope] = $data;
    if ($data['count'] === MAX_LOGIN_ATTEMPTS) {
        security_log($scope === 'admin' ? 'admin_login_locked' : 'login_locked', MAX_LOGIN_ATTEMPTS . ' wrong passwords – locked for ' . (LOGIN_LOCK_SECONDS / 60) . ' min');
    }
}

function login_reset(string $scope): void
{
    unset($_SESSION['login_attempts'][$scope]);
}
