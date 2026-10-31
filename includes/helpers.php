<?php


declare(strict_types=1);


function url(string $path = '', array $query = []): string
{
    $link = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    return $query ? $link . '?' . http_build_query($query) : $link;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path, array $query = []): never
{
    header('Location: ' . url($path, $query));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}


function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}


function remember_input(array $data): void
{
    unset($data['password'], $data['password_confirm'], $data['current_password'],
          $data['card_number'], $data['cvv'], $data['csrf_token']);
    $_SESSION['old_input'] = $data;
}

function old(string $key, string $default = ''): string
{
    return (string)($_SESSION['old_input'][$key] ?? $default);
}

function clear_old_input(): void
{
    unset($_SESSION['old_input']);
}

function money(float|string|null $amount): string
{
    return CURRENCY . ' ' . number_format((float)$amount, 2);
}

function product_image(?string $file): string
{
    return $file ? UPLOAD_URL . rawurlencode($file) : asset('images/placeholder.svg');
}

/** Effective price after a sale. */
function effective_price(array $product): float
{
    return (!empty($product['is_on_sale']) && $product['sale_price'] !== null)
        ? (float)$product['sale_price']
        : (float)$product['price'];
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    exit;
}

function abort(int $status, string $message = 'Page not found'): never
{
    http_response_code($status);
    $pageTitle = (string)$status;
    $pageCss   = 'error';
    require ROOT_PATH . '/includes/header.php';
    echo '<div class="container py-5 text-center"><div class="ink-panel p-5">';
    echo '<h1 class="display-font">' . $status . '</h1><p class="lead">' . e($message) . '</p>';
    echo '<a class="btn btn-ink" href="' . e(url('shop/index.php')) . '">Back to the shop</a></div></div>';
    require ROOT_PATH . '/includes/footer.php';
    exit;
}

function int_param(array $source, string $key): int
{
    $value = filter_var($source[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $value === false ? 0 : $value;
}


function vendor_styles(bool $icons = true): string
{
    if (USE_LOCAL_LIBS) {
        
        $out = '<link href="' . e(asset('vendor/bootstrap.min.css')) . '" rel="stylesheet">' . "\n";
        if ($icons) {
            $out .= '    <link href="' . e(asset('vendor/bootstrap-icons/bootstrap-icons.min.css')) . '" rel="stylesheet">' . "\n";
        }
        return $out;
    }
    $out = '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n"
         . '    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n"
         . '    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Jacques+Francois&amp;family=Jeju+Hallasan&amp;family=Ledger&amp;display=swap" rel="stylesheet">' . "\n"
         . '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">' . "\n";
    if ($icons) {
        $out .= '    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">' . "\n";
    }
    return $out;
}


function vendor_scripts(bool $bootstrap = true): string
{
    $jquery = USE_LOCAL_LIBS ? asset('vendor/jquery.min.js') : 'https://code.jquery.com/jquery-3.7.1.min.js';
    $bs     = USE_LOCAL_LIBS ? asset('vendor/bootstrap.bundle.min.js') : 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js';
    $out    = '<script src="' . e($jquery) . '"></script>' . "\n";
    if ($bootstrap) {
        $out .= '<script src="' . e($bs) . '"></script>' . "\n";
    }
    return $out;
}
