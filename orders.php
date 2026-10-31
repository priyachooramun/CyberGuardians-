<?php
/**
 * FR14 – View past orders and status
 * Owner: Member 4 (Cart, checkout & orders)
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_customer();

$orders    = Order::forCustomer(customer_id());
$pageTitle = 'My orders';
$noindex   = true;
$pageCss   = 'orders';
require ROOT_PATH . '/includes/header.php';
?>
<div class="container py-5">
    <div class="ink-form-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h1 class="display-font mb-0">My orders</h1>
            <a class="btn btn-outline-dark btn-sm" href="<?= e(url('account/profile.php')) ?>">My profile</a>
        </div>
        <?php if (!$orders): ?>
            <p class="lead">You haven't placed any orders yet.</p>
            <a class="btn btn-ink" href="<?= e(url('shop/index.php')) ?>">Start shopping</a>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Status</th><th class="text-end">Total</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= (int)$order['order_id'] ?></td>
                            <td><?= e(date('d M Y', strtotime($order['order_date']))) ?></td>
                            <td><?= (int)$order['item_count'] ?></td>
                            <td><span class="badge status-badge status-<?= e(strtolower($order['status'])) ?>"><?= e($order['status']) ?></span></td>
                            <td class="text-end"><?= e(money($order['total_amount'])) ?></td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-dark" href="<?= e(url('account/order.php', ['id' => $order['order_id']])) ?>">Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
