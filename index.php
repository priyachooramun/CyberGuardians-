<?php

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'More Than Just Supplies';
$bodyClass = 'landing';
$pageCss   = 'home';
require ROOT_PATH . '/includes/header.php';
?>
<section class="ink-hero">
    <div class="container">
        <div class="ink-hero-copy">
            <h1 class="ink-hero-title">More Than Just Supplies.</h1>
            <p class="ink-hero-text">
        
            </p>
            <div class="d-flex flex-wrap gap-3 justify-content-end">
                <a href="<?= e(url('shop/index.php')) ?>" class="btn ink-explore">Explore</a>
            </div>
        </div>
    </div>
</section>

<section class="container pb-5">
    <div class="row g-4">
        <div class="col-md-4">
            <a class="ink-tile" href="<?= e(url('shop/index.php', ['section' => 'book'])) ?>">
                <i class="bi bi-book"></i><h2 class="h5">Books</h2><p class="small mb-0">Fiction, academic and children's titles.</p>
            </a>
        </div>
        <div class="col-md-4">
            <a class="ink-tile" href="<?= e(url('shop/index.php', ['section' => 'stationery'])) ?>">
                <i class="bi bi-pencil"></i><h2 class="h5">Stationery</h2><p class="small mb-0">Pens, pencils, copybooks and notebooks.</p>
            </a>
        </div>
        <div class="col-md-4">
            <a class="ink-tile" href="<?= e(url('shop/index.php', ['filter' => 'sale'])) ?>">
                <i class="bi bi-tag"></i><h2 class="h5">On sale</h2><p class="small mb-0">Back-to-school offers and bundles.</p>
            </a>
        </div>
    </div>
</section>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
