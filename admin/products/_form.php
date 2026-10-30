<?php
/**
 * Shared Add/Edit product form (Figma: "Add Product By Admin")
 
 */
if (!defined('ROOT_PATH')) {
    exit;
}
$val = static fn(string $key, $default = '') => old($key, (string)($form[$key] ?? $default));
$section = $val('section', 'stationery');
?>
<form method="post" action="" enctype="multipart/form-data" class="ink-admin-form js-validate" novalidate>
    <?= csrf_field() ?>

    <div class="mb-4">
        <label for="name" class="form-label">Product name</label>
        <input type="text" class="form-control ink-input<?= field_class($errors, 'name') ?>" id="name" name="name"
               value="<?= e($val('name')) ?>" required maxlength="100">
        <?= field_error($errors, 'name') ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <span class="form-label d-block">Section</span>
            <div class="d-flex gap-4">
                <?php foreach (['book' => 'Books', 'stationery' => 'Stationery'] as $key => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input js-section" type="radio" name="section" id="section_<?= $key ?>"
                               value="<?= $key ?>"<?= $section === $key ? ' checked' : '' ?> required>
                        <label class="form-check-label" for="section_<?= $key ?>"><?= $label ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
            <?= field_error($errors, 'section') ?>
        </div>
        <div class="col-md-6">
            <label for="category" class="form-label">Category</label>
            <select class="form-select ink-input<?= field_class($errors, 'category') ?>" id="category" name="category" required>
                <?php foreach (SECTIONS as $key => $cats): ?>
                    <optgroup label="<?= $key === 'book' ? 'Books' : 'Stationery' ?>" data-section="<?= $key ?>">
                        <?php foreach ($cats as $cat): ?>
                            <option value="<?= e($cat) ?>" data-section="<?= $key ?>"<?= $val('category') === $cat ? ' selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'category') ?>
        </div>
    </div>

    <div class="mb-4">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control ink-input<?= field_class($errors, 'description') ?>" id="description" name="description"
                  rows="4" required maxlength="3000"><?= e($val('description')) ?></textarea>
        <?= field_error($errors, 'description') ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <label for="price" class="form-label">Price (Rs)</label>
            <input type="text" inputmode="decimal" class="form-control ink-input<?= field_class($errors, 'price') ?>" id="price" name="price"
                   value="<?= e($val('price')) ?>" required data-rule="money" placeholder="0.00">
            <?= field_error($errors, 'price') ?>
        </div>
        <div class="col-md-4">
            <label for="stock" class="form-label">Stock Quantity</label>
            <input type="number" class="form-control ink-input<?= field_class($errors, 'stock') ?>" id="stock" name="stock"
                   value="<?= e($val('stock', '0')) ?>" min="0" max="100000" step="1" required>
            <?= field_error($errors, 'stock') ?>
        </div>
        <div class="col-md-4">
            <label for="brand" class="form-label"><span class="js-brand-label"><?= $section === 'book' ? 'Author' : 'Brand' ?></span> (optional)</label>
            <input type="text" class="form-control ink-input<?= field_class($errors, 'brand') ?>" id="brand" name="brand"
                   value="<?= e($val('brand')) ?>" maxlength="100">
            <?= field_error($errors, 'brand') ?>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" role="switch" id="is_on_sale" name="is_on_sale" value="1"<?= $val('is_on_sale') ? ' checked' : '' ?>>
                <label class="form-check-label" for="is_on_sale">On sale</label>
            </div>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1"<?= $val('is_featured') ? ' checked' : '' ?>>
                <label class="form-check-label" for="is_featured">Featured on the shop home page</label>
            </div>
        </div>
        <div class="col-md-4 js-sale-price">
            <label for="sale_price" class="form-label">Sale price (Rs)</label>
            <input type="text" inputmode="decimal" class="form-control ink-input<?= field_class($errors, 'sale_price') ?>" id="sale_price"
                   name="sale_price" value="<?= e($val('sale_price')) ?>" data-rule="money" placeholder="0.00">
            <?= field_error($errors, 'sale_price') ?>
        </div>
        <div class="col-md-4">
            <label for="variants" class="form-label">Options / colours (comma separated)</label>
            <input type="text" class="form-control ink-input<?= field_class($errors, 'variants') ?>" id="variants" name="variants"
                   value="<?= e(old('variants', $variantText ?? '')) ?>" placeholder="Blue, Red, Black" maxlength="500">
            <?= field_error($errors, 'variants') ?>
        </div>
    </div>

    <div class="mb-4">
        <span class="form-label d-block">Product Image</span>
        <label class="ink-dropzone<?= isset($errors['image']) ? ' is-invalid' : '' ?>" id="dropzone" for="image">
            <img src="<?= e(product_image($form['image'] ?? null)) ?>" alt="" id="imagePreview" class="<?= empty($form['image']) ? 'd-none' : '' ?>">
            <span><i class="bi bi-cloud-arrow-up fs-3 d-block"></i>Upload, or drag and drop<br><small>JPG, PNG, WEBP or GIF · max 2 MB</small></span>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" class="visually-hidden">
        </label>
        <?= field_error($errors, 'image') ?>
        <?php if (!empty($form['image'])): ?>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                <label class="form-check-label" for="remove_image">Remove current image</label>
            </div>
        <?php endif; ?>
    </div>

    <div class="d-flex justify-content-end gap-3">
        <a href="<?= e(url('admin/products/index.php')) ?>" class="btn btn-light ink-btn-cancel">Cancel</a>
        <button type="submit" class="btn btn-ink ink-btn-confirm"><?= e($submitLabel) ?></button>
    </div>
</form>
