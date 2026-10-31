<?php

require_once __DIR__ . '/includes/bootstrap.php';

$store     = Bookstore::info();
$pageTitle = 'About us';
$showSubnav = true;
$pageCss   = 'about';
require ROOT_PATH . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-7">
            <div class="ink-form-card h-100">
                <h1 class="display-font">About InkCorner</h1>
                <p>InkCorner is a family-owned bookstore and stationery shop in Port Louis, now run by the third generation.
                   For four decades, students, journalists, professionals and casual readers have come to us for books,
                   journals and everyday supplies.</p>
                <p>Until now everything happened on paper and in person. This website lets you browse our full catalogue,
                   check what is in stock, order in advance and pay online, without having to visit during opening hours.</p>
                <h2 class="h5 mt-4">What you can do here</h2>
                <ul>
                    <li><strong>Visitors</strong> can browse books and stationery, see prices and stock, and sort items.</li>
                    <li><strong>Registered customers</strong> can buy online, track their orders and print receipts.</li>
                    <li><strong>Our team</strong> keeps the catalogue, prices and stock up to date every day.</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="ink-form-card h-100">
                <img src="<?= e(asset('images/logo.png')) ?>" alt="InkCorner" class="ink-about-logo" width="220">
                <h2 class="h4 display-font">Visit the shop</h2>
                <p class="mb-2"><i class="bi bi-geo-alt"></i> <?= e($store['address']) ?></p>
                <p class="mb-2"><i class="bi bi-clock"></i> <?= e($store['opening_hours'] ?? '') ?></p>
                <p class="mb-2"><i class="bi bi-telephone"></i> <?= e($store['phone']) ?></p>
                <p class="mb-4"><i class="bi bi-envelope"></i> <?= e($store['email'] ?? '') ?></p>
                <a class="btn btn-ink" href="<?= e(url('contact.php')) ?>">Contact us</a>
            </div>
        </div>
    </div>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
