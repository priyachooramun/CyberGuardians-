<?php
/**
 * HTTP security headers sent with every page
 */

declare(strict_types=1);

function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header_remove('X-Powered-By');                       

    // With USE_LOCAL_LIBS every library comes from our own server, so no outside site is allowed at all
    $cdnScript = USE_LOCAL_LIBS ? '' : ' https://code.jquery.com https://cdn.jsdelivr.net';
    $cdnStyle  = USE_LOCAL_LIBS ? '' : ' https://cdn.jsdelivr.net https://fonts.googleapis.com';
    $cdnFont   = USE_LOCAL_LIBS ? '' : ' https://fonts.gstatic.com https://cdn.jsdelivr.net';
    $csp = implode('; ', [
        "default-src 'self'",
        "script-src 'self'" . $cdnScript,
        "style-src 'self'" . $cdnStyle,
        "font-src 'self'" . $cdnFont,
        "img-src 'self' data:",                         // data: = image preview before upload
        "connect-src 'self'",                           // AJAX only to our own api/
        "form-action 'self'",                           // forms can only post to our site
        "frame-ancestors 'self'",                       // no clickjacking in other sites' frames
        "base-uri 'self'",
        "object-src 'none'",
    ]);
    header('Content-Security-Policy: ' . $csp);
    header('X-Content-Type-Options: nosniff');          // never guess file types
    header('X-Frame-Options: SAMEORIGIN');              // clickjacking, for older browsers
    header('Referrer-Policy: same-origin');             // don't leak our URLs to other sites
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    header('Cross-Origin-Opener-Policy: same-origin');

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    if ($https) {
        header('Strict-Transport-Security: max-age=31536000');   // only sent over HTTPS
    }
}

/** Account, cart and admin pages must never be cached (back button after logout shows nothing). */
function send_no_cache_headers(): void
{
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('Pragma: no-cache');
    }
}
