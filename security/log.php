<?php
/**
 * Security event log (shown as "System Logs" on the admin dashboard)
 */

declare(strict_types=1);

const SECURITY_LOG = ROOT_PATH . '/storage/logs/security.log.php';
const SECURITY_LOG_GUARD = "<?php http_response_code(404); exit; ?>\n";

function security_log(string $event, string $detail = ''): void
{
    $entry = [
        'time'   => date('Y-m-d H:i:s'),
        'event'  => $event,
        'detail' => mb_substr($detail, 0, 200),
        'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'cli',
        'page'   => mb_substr(strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: '', 0, 120),
    ];
    $dir = dirname(SECURITY_LOG);
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    if (!is_file(SECURITY_LOG)) {
        @file_put_contents(SECURITY_LOG, SECURITY_LOG_GUARD, LOCK_EX);
    }
    
    @file_put_contents(SECURITY_LOG, json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function security_events(int $limit = 8): array
{
    if (!is_file(SECURITY_LOG)) {
        return [];
    }
    $lines  = array_filter(file(SECURITY_LOG, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [], static fn($l) => str_starts_with($l, '{'));
    $lines  = array_slice($lines, -$limit);
    $events = array_filter(array_map(static fn($l) => json_decode($l, true), $lines), 'is_array');
    return array_reverse(array_values($events));
}


function security_event_style(string $event): array
{
    return match ($event) {
        'login_failed', 'admin_login_failed' => ['Failed login', 'warning'],
        'login_locked', 'admin_login_locked' => ['Account locked', 'danger'],
        'csrf_rejected'                      => ['CSRF blocked', 'danger'],
        'access_denied'                      => ['Access denied', 'danger'],
        'session_hijack'                     => ['Session hijack blocked', 'danger'],
        'upload_rejected'                    => ['Upload rejected', 'warning'],
        'admin_login'                        => ['Admin login', 'success'],
        default                              => [ucfirst(str_replace('_', ' ', $event)), 'secondary'],
    };
}
