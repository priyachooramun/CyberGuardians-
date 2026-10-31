<?php
/**
 * FR9 / FR10 – Cart form actions in ONE place: add, update, remove.
 * (Merged from the old cart/add.php, cart/update.php and cart/remove.php.)
 * With JavaScript on, the same actions go through api/cart.php by AJAX;
 * this page is the fallback when JavaScript is off.
 * Owner: Member 4 (Cart, checkout & orders)
 *
 *   POST action=add     product_id, variant_id?, quantity, next=stay|cart
 *   POST action=update  qty[<key>] = quantity  (0 removes the line)
 *   POST action=remove  key
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if (!is_post()) {
    redirect('cart/index.php');
}
verify_csrf();

$action = (string)($_POST['action'] ?? '');

// Guests may not use the cart (FR6) – send them to log in, then back to the product
if (!current_customer()) {
    $productId = int_param($_POST, 'product_id');
    $_SESSION['after_login'] = $productId ? url('shop/product.php', ['id' => $productId]) : url('cart/index.php');
    flash('info', 'Please log in or sign up to add items to your cart.');
    redirect('auth/login.php');
}

$cart = new Cart();

switch ($action) {
    case 'add':
        $productId = int_param($_POST, 'product_id');
        $qty       = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT) ?: 0;
        [$ok, $message] = $cart->add($productId, int_param($_POST, 'variant_id') ?: null, (int)$qty);
        flash($ok ? 'success' : 'warning', $message);
        if ($ok && ($_POST['next'] ?? '') === 'cart') {
            redirect('cart/index.php');
        }
        redirect('shop/product.php', ['id' => $productId]);

    case 'update':
        foreach ((array)($_POST['qty'] ?? []) as $key => $qty) {
            $qty = filter_var($qty, FILTER_VALIDATE_INT);
            if (is_string($key) && preg_match('/^\d+-\d+$/', $key) && $qty !== false) {
                $cart->update($key, max(0, (int)$qty));
            }
        }
        flash('success', 'Your cart has been updated.');
        redirect('cart/index.php');

    case 'remove':
        $key = (string)($_POST['key'] ?? '');
        if (preg_match('/^\d+-\d+$/', $key)) {
            $cart->remove($key);
            flash('success', 'Item removed from your cart.');
        }
        redirect('cart/index.php');

    default:
        redirect('cart/index.php');
}
