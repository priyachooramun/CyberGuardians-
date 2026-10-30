<?php
/**
 * Product actions from the admin product list, in ONE place:
 *   
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

if (!is_post()) {
    redirect('admin/products/index.php');   // GET never changes anything
}
verify_csrf();

$id      = int_param($_POST, 'product_id');
$product = $id ? Product::find($id, false) : null;
if (!$product) {
    flash('warning', 'That product does not exist.');
    redirect('admin/products/index.php');
}

switch ($_POST['action'] ?? '') {
    case 'stock':
        $qty = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);
        if ($qty === false) {
            flash('danger', 'Stock must be a whole number from 0 to 100000.');
        } else {
            Inventory::set($id, $qty);
            flash('success', 'Stock for "' . $product['name'] . '" set to ' . $qty . '.');
        }
        redirect('admin/products/index.php', ['highlight' => $id]);

    case 'restore':
        Product::restore($id);
        flash('success', '"' . $product['name'] . '" is visible in the shop again.');
        redirect('admin/products/index.php', ['highlight' => $id]);

    case 'remove':
        if (Product::remove($id) === 'deleted') {
            ImageUpload::delete($product['image']);
            flash('success', '"' . $product['name'] . '" was deleted.');
        } else {
            flash('info', '"' . $product['name'] . '" appears on past orders, so it was hidden from the shop instead of deleted.');
        }
        redirect('admin/products/index.php');

    default:
        redirect('admin/products/index.php');
}
