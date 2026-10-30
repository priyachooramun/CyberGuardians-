<?php
/**
 * FR24  Password hashing
 *
 * Passwords are never stored or logged in plain text. password_hash() uses
 * bcrypt with a random salt; password_verify() compares in constant time.
 */

declare(strict_types=1);

function hash_password(string $plain): string
{
    return password_hash($plain, PASSWORD_DEFAULT);
}

/**
 * Check a password against a stored hash. A dummy hash is checked when the
 * account does not exist, so the response time does not reveal which emails
 * are registered.
 */
function check_password(string $plain, ?string $hash): bool
{
    static $dummy = '$2y$10$JIZVhKlxjW8ejwSB3YHCBeVWjGxnH8etV/LVnHlW4FJkoL87gXL7y';
    if ($hash === null || $hash === '') {
        password_verify($plain, $dummy);
        return false;
    }
    return password_verify($plain, $hash);
}

function password_needs_upgrade(string $hash): bool
{
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
}
