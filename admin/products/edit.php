<?php
/**
 * FR18 – Edit product (also FR30 stock and price changes)
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

$id      = int_param($_GET, 'id');
$product = $id ? Product::find($id, false) : null;
if (!$product) {
    flash('warning', 'That product does not exist.');
    redirect('admin/products/index.php');
}

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

if (is_post()) {
    verify_csrf();
    $result = ProductForm::handle($_POST, $_FILES, $product);

    if ($result['errors']) {
        ImageUpload::delete($result['new_image']);
        $_SESSION['form_errors'] = $result['errors'];
        remember_input($_POST);
        redirect('admin/products/edit.php', ['id' => $id]);
    }

    Database::transaction(static function () use ($id, $result): void {
        Product::update($id, $result['data']);
        Inventory::set($id, $result['stock']);
        ProductVariant::sync($id, $result['variants']);
    });

    // Old picture is no longer used → delete the file
    if ($product['image'] && $product['image'] !== $result['data']['image']) {
        ImageUpload::delete($product['image']);
    }

    flash('success', '"' . $result['data']['name'] . '" was updated.');
    redirect('admin/products/index.php', ['highlight' => $id]);
}

$form        = $product;
$variantText = implode(', ', array_column(ProductVariant::forProduct($id), 'variant_name'));
$submitLabel = 'Save Changes';
$pageTitle   = 'Edit Product';
$pageCss   = 'admin-product-form';
require ROOT_PATH . '/includes/admin_header.php';
?>
<div class="admin-card">
    <?php if (!$product['is_active']): ?>
        <div class="alert alert-secondary">This product is hidden from the shop. Restore it from the product list.</div>
    <?php endif; ?>
    <?php require __DIR__ . '/_form.php'; ?>
</div>
<?php require ROOT_PATH . '/includes/admin_footer.php'; ?>
