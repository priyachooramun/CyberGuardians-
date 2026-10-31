<?php
/**
 * FR3 – View product details (Figma: "1 item view page")
 * Owner: Member 3 (Catalogue & admin products)
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$id      = int_param($_GET, 'id');
$product = $id ? Product::find($id) : null;
if (!$product) {
    abort(404, 'Sorry, we could not find that product.');
}

$variants    = ProductVariant::forProduct($id);
$recommended = Product::recommended($product, 3);
$stock       = (int)$product['stock'];
$maxQty      = min($stock, Cart::MAX_QTY_PER_LINE);

$pageTitle  = $product['name'];
$showSubnav = true;
$pageCss   = 'product';
require ROOT_PATH . '/includes/header.php';
?>
<div class="container-fluid px-3 px-lg-5 py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb ink-breadcrumb">
            <li class="breadcrumb-item"><a href="<?= e(url('shop/index.php')) ?>">Shop</a></li>
            <li class="breadcrumb-item"><a href="<?= e(url('shop/index.php', ['section' => $product['section']])) ?>"><?= $product['section'] === 'book' ? 'Books' : 'Stationery' ?></a></li>
            <?php if ($product['category']): ?>
            <li class="breadcrumb-item"><a href="<?= e(url('shop/index.php', ['section' => $product['section'], 'category' => $product['category']])) ?>"><?= e($product['category']) ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= e($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="ink-product-hero">
                <img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>" class="ink-product-img">
            </div>

            <div class="row g-4 mt-1">
                <div class="col-md-7">
                    <div class="ink-title-plate">
                    <h1 class="ink-product-name"><?= e($product['name']) ?></h1>
                    <p class="ink-product-price">
                        <?php if ($product['is_on_sale'] && $product['sale_price'] !== null): ?>
                            <?= e(money($product['sale_price'])) ?> <s class="fs-6 opacity-75"><?= e(money($product['price'])) ?></s>
                            <span class="badge bg-danger align-middle fs-6">Sale</span>
                        <?php else: ?>
                            <?= e(money($product['price'])) ?>
                        <?php endif; ?>
                    </p>
                    </div>
                    <?php if ($product['brand']): ?>
                        <p class="mb-1 ink-panel px-3 py-2 d-inline-block"><strong><?= $product['section'] === 'book' ? 'Author' : 'Brand' ?>:</strong> <?= e($product['brand']) ?></p>
                    <?php endif; ?>
                    <p class="mb-3 mt-2">
                        <?php if ($stock < 1): ?>
                            <span class="badge bg-dark">Out of stock</span>
                        <?php elseif ($stock <= LOW_STOCK_LEVEL): ?>
                            <span class="badge bg-warning text-dark">Only <?= $stock ?> left</span>
                        <?php else: ?>
                            <span class="badge bg-success">In stock</span>
                        <?php endif; ?>
                    </p>
                    <div class="ink-panel p-3"><?= e_nl($product['description']) ?></div>
                </div>

                <div class="col-md-5">
                    <?php if ($stock > 0): ?>
                    <form class="ink-panel p-3 js-add-form" method="post" action="<?= e(url('cart/actions.php')) ?>" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                        <?php if ($variants): ?>
                            <label for="variant_id" class="form-label fw-semibold">Option</label>
                            <select name="variant_id" id="variant_id" class="form-select mb-3" required>
                                <option value="">Choose…</option>
                                <?php foreach ($variants as $variant): ?>
                                    <option value="<?= (int)$variant['variant_id'] ?>"><?= e($variant['variant_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <label for="quantity" class="form-label fw-semibold">Quantity</label>
                        <div class="input-group mb-3 ink-qty">
                            <button class="btn btn-outline-dark js-qty-minus" type="button" aria-label="Decrease">−</button>
                            <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1"
                                   min="1" max="<?= $maxQty ?>" step="1" required>
                            <button class="btn btn-outline-dark js-qty-plus" type="button" aria-label="Increase">+</button>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-outline-dark" name="next" value="stay"><i class="bi bi-cart-plus"></i> Add to cart</button>
                            <button type="submit" class="btn ink-buy-now" name="next" value="cart">Buy now</button>
                        </div>
                        <?php if (!current_customer()): ?>
                            <p class="small mt-2 mb-0">You'll be asked to log in or sign up first.</p>
                        <?php endif; ?>
                    </form>
                    <?php else: ?>
                        <div class="ink-panel p-3">This item is out of stock. Please check back soon or <a href="<?= e(url('contact.php')) ?>">contact us</a> to order it in advance.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <aside class="col-lg-4">
            <h2 class="ink-recommended-title">Recommended</h2>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($recommended as $item): ?>
                    <a class="ink-reco" href="<?= e(url('shop/product.php', ['id' => $item['product_id']])) ?>">
                        <img src="<?= e(product_image($item['image'])) ?>" alt="">
                        <div>
                            <div class="fw-semibold"><?= e($item['name']) ?></div>
                            <div class="small"><?= e(money($item['final_price'])) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
