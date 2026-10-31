<?php

$pageTitle = $pageTitle ?? 'Dashboard';
$pageCss   = preg_match('/^[a-z0-9-]+$/', $pageCss ?? '') ? $pageCss : '';
$navAdmin = current_admin();
$navCurrent = $_SERVER['SCRIPT_NAME'] ?? '';
$navSide = static fn(string $needle): string => str_contains($navCurrent, $needle) ? ' active' : '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?> Admin</title>
    <?= csrf_meta() ?>
    <?= vendor_styles() ?>
    <link href="<?= e(asset('css/base.css')) ?>" rel="stylesheet">
    <link href="<?= e(asset('css/admin.css')) ?>" rel="stylesheet">
    <?php if ($pageCss !== ''): ?><link href="<?= e(asset('css/pages/' . $pageCss . '.css')) ?>" rel="stylesheet"><?php endif; ?>
</head>
<body class="ink-bg admin-body<?= $pageCss ? ' page-' . e($pageCss) : '' ?>" data-base-url="<?= e(rtrim(BASE_URL, '/')) ?>">

<header class="admin-topbar d-flex align-items-center">
    <a href="<?= e(url('admin/index.php')) ?>" class="admin-logo">
        <img src="<?= e(asset('images/logo-sm.png')) ?>" alt="InkCorner" class="brand-logo">
    </a>
    <h1 class="admin-title mb-0"><?= e($pageTitle) ?></h1>
    <div class="ms-auto d-flex align-items-center gap-2 pe-3">
        <a class="admin-icon" href="<?= e(url('admin/products/index.php', ['stock' => 'low'])) ?>" title="Low-stock alerts"><i class="bi bi-bell"></i></a>
        <a class="admin-icon" href="<?= e(url('admin/products/index.php')) ?>" title="Search products"><i class="bi bi-search"></i></a>
        <div class="dropdown">
            <button class="admin-icon admin-user" data-bs-toggle="dropdown" aria-expanded="false" title="Account"><i class="bi bi-person-circle"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header"><?= e($navAdmin['name'] ?? 'Admin') ?></h6></li>
                <li><a class="dropdown-item" href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener">View shop</a></li>
                <li>
                    <form action="<?= e(url('auth/logout.php')) ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item">Log out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

<nav class="admin-sidebar" aria-label="Admin navigation">
    <a class="<?= $navCurrent === url('admin/index.php') ? 'active' : '' ?>" href="<?= e(url('admin/index.php')) ?>" title="Dashboard"><i class="bi bi-house-door"></i><span>Dashboard</span></a>
    <a class="<?= $navSide('/admin/products/') ?>" href="<?= e(url('admin/products/index.php')) ?>" title="Products"><i class="bi bi-box-seam"></i><span>Products</span></a>
    <a class="<?= $navSide('/admin/products/create') ?>" href="<?= e(url('admin/products/create.php')) ?>" title="Add product"><i class="bi bi-plus-square"></i><span>Add</span></a>
    <a class="<?= $navSide('/admin/orders/') ?>" href="<?= e(url('admin/orders/index.php')) ?>" title="Orders"><i class="bi bi-envelope"></i><span>Orders</span></a>
    <a class="<?= $navSide('/admin/customers/') ?>" href="<?= e(url('admin/customers/index.php')) ?>" title="Customers"><i class="bi bi-people"></i><span>Customers</span></a>
    <a class="<?= $navSide('/admin/queries/') ?>" href="<?= e(url('admin/queries/index.php')) ?>" title="Customer queries"><i class="bi bi-chat-dots"></i><span>Queries</span></a>
</nav>

<main class="admin-main">
<?php foreach (take_flashes() as $flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endforeach; ?>
