<?php
/**
 * Product list for the administrator: search, low-stock filter, quick stock
 * update (AJAX), edit, remove / restore.
 * Owner: Member 3 (Catalogue & admin products)
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

$v         = new Validator($_GET);
$q         = mb_substr($v->clean('q'), 0, 80);
$section   = $v->clean('section');
$section   = isset(SECTIONS[$section]) ? $section : '';
$lowStock  = ($_GET['stock'] ?? '') === 'low';
$highlight = int_param($_GET, 'highlight');

$products = Product::search([
    'q' => $q, 'section' => $section, 'low_stock' => $lowStock,
    'include_inactive' => true, 'sort' => 'name_asc',
]);

$pageTitle = 'Products';
$pageCss   = 'admin-products';
require ROOT_PATH . '/includes/admin_header.php';
?>
<div class="admin-card">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <form method="get" class="d-flex flex-wrap gap-2" role="search">
            <input type="search" name="q" class="form-control" placeholder="Search name, brand…" value="<?= e($q) ?>" maxlength="80">
            <select name="section" class="form-select">
                <option value="">All sections</option>
                <option value="stationery"<?= $section === 'stationery' ? ' selected' : '' ?>>Stationery</option>
                <option value="book"<?= $section === 'book' ? ' selected' : '' ?>>Books</option>
            </select>
            <div class="form-check align-self-center">
                <input class="form-check-input" type="checkbox" name="stock" value="low" id="lowStock"<?= $lowStock ? ' checked' : '' ?>>
                <label class="form-check-label text-nowrap" for="lowStock">Low stock only</label>
            </div>
            <button class="btn btn-dark">Filter</button>
        </form>
        <a href="<?= e(url('admin/products/create.php')) ?>" class="btn btn-ink"><i class="bi bi-plus-lg"></i> Add product</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle admin-table">
            <thead>
                <tr><th></th><th>Product</th><th>Section</th><th class="text-end">Price</th><th class="col-stock">Stock</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            <?php if (!$products): ?>
                <tr><td colspan="6" class="text-center py-4">No products match.</td></tr>
            <?php endif; ?>
            <?php foreach ($products as $p): ?>
                <?php $low = (int)$p['stock'] <= LOW_STOCK_LEVEL; ?>
                <tr class="<?= (int)$p['product_id'] === $highlight ? 'table-info' : '' ?><?= !$p['is_active'] ? ' opacity-50' : '' ?>">
                    <td><img src="<?= e(product_image($p['image'])) ?>" alt="" class="admin-thumb"></td>
                    <td>
                        <div class="fw-semibold"><?= e($p['name']) ?></div>
                        <small class="text-muted"><?= e($p['brand'] ?? '') ?></small>
                        <?php if (!$p['is_active']): ?><span class="badge bg-secondary">Hidden</span><?php endif; ?>
                        <?php if ($p['is_featured']): ?><span class="badge bg-primary">Featured</span><?php endif; ?>
                    </td>
                    <td><?= $p['section'] === 'book' ? 'Books' : 'Stationery' ?><br><small class="text-muted"><?= e($p['category']) ?></small></td>
                    <td class="text-end">
                        <?php if ($p['is_on_sale']): ?>
                            <span class="text-danger"><?= e(money($p['sale_price'])) ?></span><br><s class="small"><?= e(money($p['price'])) ?></s>
                        <?php else: ?>
                            <?= e(money($p['price'])) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" action="<?= e(url('admin/products/actions.php')) ?>" class="input-group input-group-sm js-stock-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="stock">
                            <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                            <input type="number" name="quantity" min="0" max="100000" value="<?= (int)$p['stock'] ?>"
                                   class="form-control<?= $low ? ' border-danger text-danger fw-bold' : '' ?>" aria-label="Stock for <?= e($p['name']) ?>">
                            <button class="btn btn-outline-dark" type="submit">Save</button>
                        </form>
                    </td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-dark" href="<?= e(url('admin/products/edit.php', ['id' => $p['product_id']])) ?>">Edit</a>
                        <form method="post" action="<?= e(url('admin/products/actions.php')) ?>" class="d-inline js-confirm"
                              data-confirm="<?= $p['is_active'] ? 'Remove ' . e($p['name']) . ' from the shop?' : 'Show ' . e($p['name']) . ' in the shop again?' ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                            <?php if ($p['is_active']): ?>
                                <input type="hidden" name="action" value="remove">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                            <?php else: ?>
                                <input type="hidden" name="action" value="restore">
                                <button class="btn btn-sm btn-outline-success" type="submit">Restore</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require ROOT_PATH . '/includes/admin_footer.php'; ?>
