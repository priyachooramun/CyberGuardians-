<?php
/**
 * FR17 Add product (with image)
 * Owner: Member 3 (Catalogue & admin products)
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

if (is_post()) {
    verify_csrf();
    $result = ProductForm::handle($_POST, $_FILES);

    if ($result['errors']) {
        ImageUpload::delete($result['new_image']);
        $_SESSION['form_errors'] = $result['errors'];
        remember_input($_POST);
        redirect('admin/products/create.php');
    }

    $id = Database::transaction(static function () use ($result): int {
        $id = Product::create($result['data']);
        Inventory::set($id, $result['stock']);
        ProductVariant::sync($id, $result['variants']);
        return $id;
    });

    flash('success', '"' . $result['data']['name'] . '" was added to the catalogue.');
    redirect('admin/products/index.php', ['highlight' => $id]);
}

$form        = ['section' => 'stationery', 'stock' => 0];
$variantText = '';
$submitLabel = 'Confirm To Add';
$pageTitle   = 'Add Product';
$pageCss   = 'admin-product-form';
require ROOT_PATH . '/includes/admin_header.php';
?>
<div class="admin-card">
    <?php require __DIR__ . '/_form.php'; ?>
</div>
<?php require ROOT_PATH . '/includes/admin_footer.php'; ?>
