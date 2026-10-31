<?php
/**
 * JSON stock endpoint
 * 
 */
require_once __DIR__ . '/../includes/bootstrap.php';

header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $id      = int_param($_GET, 'product_id');
    $product = $id ? Product::find($id) : null;
    if (!$product) {
        json_response(['success' => false, 'message' => 'Product not found.'], 404);
    }
    json_response([
        'success'    => true,
        'product_id' => $id,
        'in_stock'   => (int)$product['stock'] > 0,
        // Customers only see "low stock" hints, the exact number is for admins
        'quantity'   => current_admin() ? (int)$product['stock'] : min((int)$product['stock'], LOW_STOCK_LEVEL),
        'low_stock'  => (int)$product['stock'] <= LOW_STOCK_LEVEL,
    ]);
}

if (!current_admin()) {
    json_response(['success' => false, 'message' => 'Administrator login required.'], 403);
}
verify_csrf(true);

$id  = int_param($_POST, 'product_id');
$qty = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);
if (!$id || $qty === false || !Product::find($id, false)) {
    json_response(['success' => false, 'message' => 'Enter a whole number from 0 to 100000.'], 422);
}

Inventory::set($id, $qty);
json_response([
    'success'    => true,
    'message'    => 'Stock updated.',
    'product_id' => $id,
    'quantity'   => $qty,
    'low_stock'  => $qty <= LOW_STOCK_LEVEL,
]);
