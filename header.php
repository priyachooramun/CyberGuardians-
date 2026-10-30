<?php
/
 *   $pageTitle  string  – browser tab title
 *   $noindex    bool    – true on account pages so search engines skip them
 *   $showSubnav bool    – show the grey Categories / New / Sale bar
 *   $bodyClass  string  – extra CSS class on <body>
 *   $pageCss    string  – page stylesheet in assets/css/pages/, e.g. 'shop' → pages/shop.css

$pageTitle  = $pageTitle  ?? APP_NAME;
$noindex    = $noindex    ?? false;
$showSubnav = $showSubnav ?? false;
$bodyClass  = $bodyClass  ?? '';

$pageCss    = preg_match('/^[a-z0-9-]+$/', $pageCss ?? '') ? $pageCss : '';
$navCustomer = current_customer();
$navCartCount = (new Cart())->count();
$navCurrent = $_SERVER['SCRIPT_NAME'] ?? '';
$navActive = static fn(string $needle): string => str_contains($navCurrent, $needle) ? ' active' : '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <?php if ($noindex): ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>
    <?= csrf_meta() ?>
    <?= vendor_styles() ?>
    <link href="<?= e(asset('css/base.css')) ?>" rel="stylesheet">
    <?php if ($pageCss !== ''): ?><link href="<?= e(asset('css/pages/' . $pageCss . '.css')) ?>" rel="stylesheet"><?php endif; ?>
</head>
<body class="ink-bg<?= $pageCss ? ' page-' . e($pageCss) : '' ?> <?= e($bodyClass) ?>" data-base-url="<?= e(rtrim(BASE_URL, '/')) ?>">

<nav class="navbar navbar-expand-lg navbar-dark ink-topbar">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand d-flex align-items-center" href="<?= e(url('index.php')) ?>">
            <img src="<?= e(asset('images/logo-sm.png')) ?>" alt="InkCorner" class="brand-logo">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto ink-mainlinks">
                <li class="nav-item"><a class="nav-link<?= $navCurrent === url('index.php') ? ' active' : '' ?>" href="<?= e(url('index.php')) ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link<?= $navActive('/shop/') ?>" href="<?= e(url('shop/index.php')) ?>">Shop</a></li>
                <li class="nav-item"><a class="nav-link<?= $navActive('about.php') ?>" href="<?= e(url('about.php')) ?>">About</a></li>
                <li class="nav-item"><a class="nav-link<?= $navActive('contact.php') ?>" href="<?= e(url('contact.php')) ?>">Contact us</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2 ink-icons">
                <form class="ink-search position-relative" action="<?= e(url('shop/index.php')) ?>" method="get" role="search">
                    <input class="form-control form-control-sm" type="search" name="q" id="liveSearch"
                           placeholder="Search books & stationery" autocomplete="off" maxlength="80"
                           value="<?= e((string)($_GET['q'] ?? '')) ?>" aria-label="Search">
                    <div class="list-group ink-suggestions" id="liveSearchResults"></div>
                </form>
                <button class="ink-icon-btn" type="button" id="searchToggle" aria-label="Search"><i class="bi bi-search"></i></button>

                <?php ?>
                <a class="ink-icon-btn position-relative" href="<?= e(url('cart/index.php')) ?>" aria-label="Cart">
                    <i class="bi bi-cart3"></i>
                    <span class="badge rounded-pill bg-danger ink-cart-badge<?= $navCartCount ? '' : ' d-none' ?>" id="cartBadge"><?= $navCartCount ?></span>
                </a>

                <?php if ($navCustomer): ?>
                    <div class="dropdown">
                        <button class="ink-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="My account">
                            <i class="bi bi-person"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Hi, <?= e($navCustomer['name']) ?></h6></li>
                            <li><a class="dropdown-item" href="<?= e(url('account/profile.php')) ?>">My profile</a></li>
                            <li><a class="dropdown-item" href="<?= e(url('account/orders.php')) ?>">My orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="<?= e(url('auth/logout.php')) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button class="dropdown-item" type="submit">Log out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="btn ink-pill ms-1" href="<?= e(url('auth/login.php')) ?>">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if ($showSubnav): ?>
<div class="ink-subnav">
    <div class="container-fluid px-3 px-lg-4 d-flex flex-wrap align-items-center gap-3 gap-lg-5">
        <div class="dropdown">
            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Categories</a>
            <ul class="dropdown-menu">
                <li><h6 class="dropdown-header">Stationery</h6></li>
                <?php foreach (SECTIONS['stationery'] as $cat): ?>
                    <li><a class="dropdown-item" href="<?= e(url('shop/index.php', ['section' => 'stationery', 'category' => $cat])) ?>"><?= e($cat) ?></a></li>
                <?php endforeach; ?>
                <li><hr class="dropdown-divider"></li>
                <li><h6 class="dropdown-header">Books</h6></li>
                <?php foreach (SECTIONS['book'] as $cat): ?>
                    <li><a class="dropdown-item" href="<?= e(url('shop/index.php', ['section' => 'book', 'category' => $cat])) ?>"><?= e($cat) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="<?= e(url('shop/index.php', ['filter' => 'new'])) ?>">New</a>
        <a href="<?= e(url('shop/index.php', ['filter' => 'sale'])) ?>">Sale</a>
        <a href="<?= e(url('shop/index.php', ['filter' => 'bestsellers'])) ?>">Bestsellers</a>
        <a href="<?= e(url('shop/index.php', ['section' => 'book'])) ?>">Books</a>
        <a href="<?= e(url('shop/index.php', ['section' => 'stationery'])) ?>">Stationery</a>
        <a href="<?= e(url('about.php')) ?>">Info</a>
        <a href="<?= e(url('shop/index.php', ['section' => 'stationery', 'category' => 'Miscellaneous'])) ?>">Miscellaneous</a>
    </div>
</div>
<?php endif; ?>

<main class="ink-main">
<?php foreach (take_flashes() as $flash): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endforeach; ?>
