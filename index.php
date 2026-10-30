<?php

require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$totalUsers  = Customer::count();
$totalSales  = Order::totalSales();
$toProcess   = Order::countByStatus('Paid') + Order::countByStatus('Processing');
$lowStock    = Product::countLowStock();
$sales       = Order::salesByDay(7);
$maxSale     = max(1, max($sales));
$newUsers    = Customer::recent(5);
$recent      = Order::recent(6);
$queries     = Query::recent(5);
$events      = security_events(6);

$pageTitle = 'Dashboard';
$pageCss   = 'admin-dashboard';
require ROOT_PATH . '/includes/admin_header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-head">Total Users</div><div class="stat-value"><?= $totalUsers ?></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-head">Total sales</div><div class="stat-value"><?= e(money($totalSales)) ?></div></div></div>
    <div class="col-sm-6 col-xl-3"><a class="stat-card" href="<?= e(url('admin/orders/index.php', ['status' => 'Paid'])) ?>"><div class="stat-head">Orders to process</div><div class="stat-value"><?= $toProcess ?></div></a></div>
    <div class="col-sm-6 col-xl-3"><a class="stat-card" href="<?= e(url('admin/products/index.php', ['stock' => 'low'])) ?>"><div class="stat-head">System Alerts</div>
        <div class="stat-value<?= $lowStock ? ' text-danger' : '' ?>"><?= $lowStock ?> <small class="fs-6">low stock</small></div></a></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <section class="dash-panel">
            <h2 class="dash-head">Sales Overview <small>(last 7 days)</small></h2>
            <div class="sales-chart" role="img" aria-label="Sales for the last seven days">
                <?php foreach ($sales as $day => $amount): ?>
                    <div class="sales-bar-wrap" title="<?= e(date('D d M', strtotime($day)) . ': ' . money($amount)) ?>">
                        <div class="sales-bar" data-pct="<?= (int)round($amount / $maxSale * 100) ?>"></div>
                        <span><?= e(date('D', strtotime($day))) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="dash-panel">
            <h2 class="dash-head">User Activity <small>(new sign-ups)</small></h2>
            <ul class="list-group list-group-flush">
                <?php foreach ($newUsers as $u): ?>
                    <li class="list-group-item bg-transparent d-flex justify-content-between">
                        <span><?= e($u['name']) ?></span>
                        <small><?= $u['created_at'] ? e(date('d M Y', strtotime($u['created_at']))) : '' ?></small>
                    </li>
                <?php endforeach; ?>
                <?php if (!$newUsers): ?><li class="list-group-item bg-transparent">No customers yet.</li><?php endif; ?>
            </ul>
        </section>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-4 col-lg-6">
        <section class="dash-panel">
            <h2 class="dash-head">Recent Orders <a class="dash-link" href="<?= e(url('admin/orders/index.php')) ?>">all</a></h2>
            <table class="table table-sm mb-0">
                <?php foreach ($recent as $o): ?>
                    <tr>
                        <td><a class="link-dark" href="<?= e(url('admin/orders/view.php', ['id' => $o['order_id']])) ?>">#<?= (int)$o['order_id'] ?></a></td>
                        <td><?= e($o['customer_name']) ?></td>
                        <td><span class="badge status-badge status-<?= e(strtolower($o['status'])) ?>"><?= e($o['status']) ?></span></td>
                        <td class="text-end"><?= e(money($o['total_amount'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recent): ?><tr><td>No orders yet.</td></tr><?php endif; ?>
            </table>
        </section>
    </div>
    <div class="col-xl-4 col-lg-6">
        <section class="dash-panel">
            <h2 class="dash-head">System Logs <small>(security events)</small></h2>
            <ul class="list-group list-group-flush">
                <?php foreach ($events as $ev): ?>
                    <?php [$label, $colour] = security_event_style((string)$ev['event']); ?>
                    <li class="list-group-item bg-transparent small">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="badge bg-<?= e($colour) ?>"><?= e($label) ?></span>
                            <span class="text-muted"><?= e(substr((string)$ev['time'], 5, 11)) ?></span>
                        </div>
                        <div class="mt-1"><?= e($ev['detail']) ?> <span class="text-muted">· <?= e($ev['ip']) ?></span></div>
                    </li>
                <?php endforeach; ?>
                <?php if (!$events): ?><li class="list-group-item bg-transparent">No security events yet.</li><?php endif; ?>
            </ul>
        </section>
    </div>
    <div class="col-xl-4 col-lg-12">
        <section class="dash-panel">
            <h2 class="dash-head">Customer Queries <small>(<?= Query::countOpen() ?> open)</small> <a class="dash-link" href="<?= e(url('admin/queries/index.php')) ?>">reply</a></h2>
            <ul class="list-group list-group-flush">
                <?php foreach ($queries as $qr): ?>
                    <li class="list-group-item bg-transparent">
                        <a class="link-dark d-flex justify-content-between text-decoration-none" href="<?= e(url('admin/queries/index.php', ['open' => $qr['query_id']])) ?>">
                            <strong><?= e($qr['subject'] ?? 'Question') ?></strong><small><?= e($qr['query_date']) ?></small>
                        </a>
                        <small><?= e($qr['customer_name']) ?> · <?= e($qr['status']) ?></small>
                    </li>
                <?php endforeach; ?>
                <?php if (!$queries): ?><li class="list-group-item bg-transparent">No questions yet.</li><?php endif; ?>
            </ul>
        </section>
    </div>
</div>
<?php require ROOT_PATH . '/includes/admin_footer.php'; ?>
