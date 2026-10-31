<?php
/**
 * FR2  Browse Books and Stationery - FR29 -Sort items
 * Figma: "Customer and visitor ui" (home panels) + "Collection page" / "New" / "On sale" / "Bestsellers"
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$v        = new Validator($_GET);
$section  = $v->clean('section');
$section  = isset(SECTIONS[$section]) ? $section : '';
$category = $v->clean('category');
$allCats  = array_merge(...array_values(SECTIONS));
$category = in_array($category, $allCats, true) ? $category : '';
$filter   = $v->clean('filter');
$filter   = in_array($filter, Product::FILTERS, true) ? $filter : '';
$sort     = $v->clean('sort');
$sort     = isset(Product::SORTS[$sort]) ? $sort : ($filter === 'bestsellers' ? 'popular' : 'newest');
$q        = mb_substr($v->clean('q'), 0, 80);
$viewAll  = ($_GET['view'] ?? '') === 'all';

$isHome = !$section && !$category && !$filter && $q === '' && !$viewAll;

// Products that have colour/edition options need the product page before adding to cart
$variantProductIds = ProductVariant::productIds();

if ($isHome) {
    $featured = Product::search(['filter' => 'featured', 'limit' => 6]);
    $sale     = Product::search(['filter' => 'sale', 'limit' => 4, 'sort' => 'price_asc']);
    $popular  = Product::search(['filter' => 'bestsellers', 'limit' => 4]);
    if (!$popular) {
        $popular = Product::search(['limit' => 4]);
    }
} else {
    $products = Product::search(compact('section', 'category', 'filter', 'sort', 'q'));
    $heading  = match (true) {
        $q !== ''              => 'Results for "' . $q . '"',
        $category !== ''       => $category,
        $filter === 'new'      => 'New arrivals',
        $filter === 'sale'     => 'On sale',
        $filter === 'bestsellers' => 'Bestsellers',
        $filter === 'featured' => 'Featured products',
        $section === 'book'    => 'Books',
        $section === 'stationery' => 'Stationery',
        default                => 'All products',
    };
}

$pageTitle  = $isHome ? 'Shop' : $heading;
$showSubnav = true;
$pageCss   = 'shop';
require ROOT_PATH . '/includes/header.php';
?>

<?php if ($isHome): ?>
<div class="container-fluid px-3 px-lg-5 py-4">
    <?php if (!current_customer()): ?>
        <p class="ink-signup-strip mb-4">
            <a href="<?= e(url('auth/register.php')) ?>" class="ink-pill-sm">Sign up</a>
            to receive discounts, sale alerts and new arrivals.
        </p>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="ink-panel h-100">
                <h2 class="ink-panel-title">Featured Products</h2>
                <?php if ($featured): ?>
                <div id="featuredCarousel" class="carousel slide carousel-dark p-3" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($featured as $i => $item): ?>
                        <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                            <div class="row align-items-center g-4">
                                <div class="col-md-7">
                                    <a href="<?= e(url('shop/product.php', ['id' => $item['product_id']])) ?>">
                                        <img src="<?= e(product_image($item['image'])) ?>" class="ink-featured-img" alt="<?= e($item['name']) ?>">
                                    </a>
                                </div>
                                <div class="col-md-5">
                                    <small class="text-uppercase ink-meta"><?= e($item['category']) ?></small>
                                    <h3 class="display-font h3 mt-1"><?= e($item['name']) ?></h3>
                                    <p class="small mb-2"><?= e(mb_strimwidth((string)$item['description'], 0, 120, '…')) ?></p>
                                    <p class="fs-5 fw-semibold"><?= e(money($item['final_price'])) ?></p>
                                    <a class="btn btn-ink" href="<?= e(url('shop/product.php', ['id' => $item['product_id']])) ?>">View product</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($featured) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span><span class="visually-hidden">Previous</span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span><span class="visually-hidden">Next</span></button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <p class="p-4">No featured products yet.</p>
                <?php endif; ?>
            </section>
        </div>

        <div class="col-lg-5 d-flex flex-column gap-4">
            <section class="ink-panel">
                <h2 class="ink-panel-title ink-panel-title-sm">Back to school sale</h2>
                <div class="ink-mini-list p-3">
                    <?php foreach ($sale as $item): ?>
                        <a class="ink-mini" href="<?= e(url('shop/product.php', ['id' => $item['product_id']])) ?>">
                            <img src="<?= e(product_image($item['image'])) ?>" alt="">
                            <span class="small"><?= e($item['name']) ?></span>
                            <span class="small text-danger fw-semibold"><?= e(money($item['final_price'])) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="text-end px-3 pb-3"><a href="<?= e(url('shop/index.php', ['filter' => 'sale'])) ?>" class="small link-dark">See all offers →</a></div>
            </section>
            <section class="ink-panel">
                <h2 class="ink-panel-title ink-panel-title-sm">Popular Products</h2>
                <div class="ink-mini-list p-3">
                    <?php foreach ($popular as $item): ?>
                        <a class="ink-mini" href="<?= e(url('shop/product.php', ['id' => $item['product_id']])) ?>">
                            <img src="<?= e(product_image($item['image'])) ?>" alt="">
                            <span class="small"><?= e($item['name']) ?></span>
                            <span class="small fw-semibold"><?= e(money($item['final_price'])) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>

    <div class="text-end mt-4">
        <a class="btn ink-cta" href="<?= e(url('shop/index.php', ['view' => 'all'])) ?>">Browse our collections</a>
    </div>
</div>

<?php else: ?>
<div class="container-fluid px-3 px-lg-5 py-4">
    <div class="row g-4">
        <aside class="col-lg-3">
            <div class="ink-panel p-3">
                <h2 class="h5 display-font">Sections</h2>
                <?php foreach (SECTIONS as $key => $cats): ?>
                    <a class="d-block fw-semibold mt-2 link-dark<?= $section === $key && !$category ? ' text-decoration-underline' : '' ?>"
                       href="<?= e(url('shop/index.php', ['section' => $key, 'sort' => $sort])) ?>"><?= $key === 'book' ? 'Books' : 'Stationery' ?></a>
                    <ul class="list-unstyled ms-2 mb-2 small">
                        <?php foreach ($cats as $cat): ?>
                            <li><a class="link-dark<?= $category === $cat ? ' fw-bold' : '' ?>"
                                   href="<?= e(url('shop/index.php', ['section' => $key, 'category' => $cat, 'sort' => $sort])) ?>"><?= e($cat) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
                <a class="small link-dark" href="<?= e(url('shop/index.php', ['view' => 'all'])) ?>">Show everything</a>
            </div>
        </aside>

        <section class="col-lg-9">
            <div class="ink-panel p-3 mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h1 class="display-font h3 mb-0"><?= e($heading) ?></h1>
                    <small><?= count($products) ?> item<?= count($products) === 1 ? '' : 's' ?></small>
                </div>
                <form method="get" action="" class="d-flex align-items-center gap-2" id="sortForm">
                    <?php foreach (compact('section', 'category', 'filter', 'q') as $name => $value): ?>
                        <?php if ($value !== ''): ?><input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>"><?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($viewAll): ?><input type="hidden" name="view" value="all"><?php endif; ?>
                    <label for="sort" class="small text-nowrap">Sort by</label>
                    <select name="sort" id="sort" class="form-select form-select-sm js-autosubmit">
                        <?php foreach (Product::SORTS as $key => $option): ?>
                            <option value="<?= e($key) ?>"<?= $sort === $key ? ' selected' : '' ?>><?= e($option['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button class="btn btn-sm btn-ink">Sort</button></noscript>
                </form>
            </div>

            <?php if (!$products): ?>
                <div class="ink-panel p-5 text-center">
                    <p class="lead mb-3">Nothing found here yet.</p>
                    <a href="<?= e(url('shop/index.php', ['view' => 'all'])) ?>" class="btn btn-ink">See all products</a>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3">
                    <?php foreach ($products as $product): ?>
                        <div class="col"><?php require __DIR__ . '/_product_card.php'; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php endif; ?>

<?php require ROOT_PATH . '/includes/footer.php'; ?>
