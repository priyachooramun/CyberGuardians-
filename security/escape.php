<?php
/**
 * FR26 – XSS prevention: escape ALL output
 * 
 */

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/** Escape text and keep the user's line breaks (descriptions, addresses). */
function e_nl(mixed $value): string
{
    return nl2br(e($value), false);
}

/** Hide most of an email for the admin customer list: jo***@gmail.com */
function mask_email(string $email): string
{
    [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');
    return mb_substr($name, 0, 2) . str_repeat('*', max(3, mb_strlen($name) - 2)) . '@' . $domain;
}

/** Hide most of a phone number: ****1234 */
function mask_phone(?string $phone): string
{
    $phone = (string)$phone;
    return $phone === '' ? '' : str_repeat('*', max(0, strlen($phone) - 4)) . substr($phone, -4);
}
