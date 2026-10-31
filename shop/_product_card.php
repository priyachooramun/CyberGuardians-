<?php
/**
 * One product tile, reused by the shop, home panels and "Recommended".
 */
if (!defined('ROOT_PATH')) {
    exit;   // not meant to be opened directly
}
$link    = url('shop/product.php', ['id' => $product['product_id']]);
$inStock = (int)$product['stock'] > 0;
$hasVariants = in_array((int)$product['product_id'], $variantProductIds ?? [], true);
?>
<div class="card ink-card h-100">
    <a href="<?= e($link) ?>" class="ink-card-img">
        <img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
        <?php if ($product['is_on_sale']): ?><span class="badge bg-danger ink-badge">Sale</span><?php endif; ?>
        <?php if (!$inStock): ?><span class="badge bg-dark ink-badge ink-badge-right">Out of stock</span><?php endif; ?>
    </a>
    <div class="card-body d-flex flex-column">
        <small class="text-uppercase text-muted ink-meta"><?= e($product['category'] ?? '') ?><?= $product['brand'] ? ' · ' . e($product['brand']) : '' ?></small>
        <h3 class="h6 card-title mt-1"><a href="<?= e($link) ?>" class="stretched-link-off"><?= e($product['name']) ?></a></h3>
        <div class="mt-auto d-flex align-items-center justify-content-between gap-2">
            <div class="ink-price">
                <?php if ($product['is_on_sale'] && $product['sale_price'] !== null): ?>
                    <span class="text-danger fw-semibold"><?= e(money($product['sale_price'])) ?></span>
                    <s class="small text-muted"><?= e(money($product['price'])) ?></s>
                <?php else: ?>
                    <span class="fw-semibold"><?= e(money($product['price'])) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($inStock && !$hasVariants): ?>
                <button type="button" class="btn btn-sm btn-ink js-add-to-cart" data-product-id="<?= (int)$product['product_id'] ?>"
                        aria-label="Add <?= e($product['name']) ?> to cart"><i class="bi bi-cart-plus"></i></button>
            <?php elseif ($inStock): ?>
                <a class="btn btn-sm btn-outline-dark" href="<?= e($link) ?>">Options</a>
            <?php endif; ?>
        </div>
    </div>
</div>
