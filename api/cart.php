<?php
/**
 * JSON endpoint for the cart (used by jQuery AJAX in assets/js/main.js)
 * 
 */
require_once __DIR__ . '/../includes/bootstrap.php';

header('Cache-Control: no-store');

/** Guests cannot use the cart (FR6) */
if (!current_customer()) {
    $from = (string)($_SERVER['HTTP_REFERER'] ?? '');
    $path = parse_url($from, PHP_URL_PATH) ?: '';
    $query = parse_url($from, PHP_URL_QUERY);
    if ($path !== '' && str_starts_with($path, rtrim(BASE_URL, '/') . '/') && !str_contains($path, '//')) {
        $_SESSION['after_login'] = $path . ($query ? '?' . $query : '');
    }
    flash('info', 'Please log in or sign up to add items to your cart.');
    json_response([
        'success'   => false,
        'message'   => 'Please log in or sign up to add items to your cart.',
        'login_url' => url('auth/login.php'),
    ], 401);
}

$cart = new Cart();

function cart_payload(Cart $cart): array
{
    $lines = $cart->lines();
    $total = $cart->total($lines);
    return [
        'count'           => $cart->count(),
        'total'           => $total,
        'total_formatted' => money($total),
        'lines'           => array_values(array_map(static fn(array $l) => [
            'key'                => $l['key'],
            'product_id'         => $l['product_id'],
            'name'               => $l['name'],
            'variant'            => $l['variant_name'],
            'quantity'           => $l['qty'],
            'stock'              => $l['stock'],
            'unit_price'         => $l['unit_price'],
            'subtotal'           => $l['subtotal'],
            'subtotal_formatted' => money($l['subtotal']),
        ], $lines)),
    ];
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    json_response(['success' => true, 'message' => 'OK', 'cart' => cart_payload($cart)]);
}

if (!is_post()) {
    json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
}
verify_csrf(true);

$action = (string)($_POST['action'] ?? '');
$qty    = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
$key    = (string)($_POST['key'] ?? '');

if ($key !== '' && !preg_match('/^\d+-\d+$/', $key)) {
    json_response(['success' => false, 'message' => 'Invalid cart item.'], 422);
}

switch ($action) {
    case 'add':
        $productId = int_param($_POST, 'product_id');
        $variantId = int_param($_POST, 'variant_id') ?: null;
        if (!$productId || $qty === false) {
            json_response(['success' => false, 'message' => 'Invalid product or quantity.'], 422);
        }
        [$ok, $message] = $cart->add($productId, $variantId, (int)$qty);
        break;

    case 'update':
        if ($qty === false || $qty < 0) {
            json_response(['success' => false, 'message' => 'Invalid quantity.'], 422);
        }
        [$ok, $message] = $cart->update($key, (int)$qty);
        break;

    case 'remove':
        $cart->remove($key);
        [$ok, $message] = [true, 'Item removed from your cart.'];
        break;

    default:
        json_response(['success' => false, 'message' => 'Unknown action.'], 400);
}

json_response(['success' => $ok, 'message' => $message, 'cart' => cart_payload($cart)], $ok ? 200 : 422);
