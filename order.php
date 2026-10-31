<?php
/**
 * FR14 – One order with its items and status progress
 * Owner: Member 4 (Cart, checkout & orders)
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_customer();

$id    = int_param($_GET, 'id');
$order = $id ? Order::findForCustomer($id, customer_id()) : null;   // ownership check
if (!$order) {
    abort(404, 'Order not found.');
}
$items   = OrderItem::forOrder($id);
$receipt = Receipt::forOrder($id);
$steps   = ['Paid', 'Processing', 'Shipped', 'Delivered'];
$reached = array_search($order['status'], $steps, true);

$pageTitle = 'Order #' . $id;
$noindex   = true;
$pageCss   = 'order';
require ROOT_PATH . '/includes/header.php';
?>
<div class="container py-5">
    <div class="ink-form-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <h1 class="display-font mb-0">Order #<?= (int)$id ?></h1>
                <small>Placed on <?= e(date('d M Y', strtotime($order['order_date']))) ?></small>
            </div>
            <span class="badge fs-6 status-badge status-<?= e(strtolower($order['status'])) ?>"><?= e($order['status']) ?></span>
        </div>

        <?php if ($order['status'] !== 'Cancelled'): ?>
        <ol class="ink-progress mb-4">
            <?php foreach ($steps as $i => $step): ?>
                <li class="<?= $reached !== false && $i <= $reached ? 'done' : '' ?>"><?= e($step) ?></li>
            <?php endforeach; ?>
        </ol>
        <?php endif; ?>

        <table class="table">
            <thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><a class="link-dark" href="<?= e(url('shop/product.php', ['id' => $item['product_id']])) ?>"><?= e($item['product_name']) ?></a>
                        <?= $item['variant_name'] ? '<small class="text-muted"> – ' . e($item['variant_name']) . '</small>' : '' ?></td>
                    <td class="text-center"><?= (int)$item['quantity'] ?></td>
                    <td class="text-end"><?= e(money($item['price'])) ?></td>
                    <td class="text-end"><?= e(money($item['price'] * $item['quantity'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end"><?= e(money($order['total_amount'])) ?></th></tr></tfoot>
        </table>
        <p class="small"><strong>Shipping to:</strong> <?= e($order['shipping_address']) ?></p>

        <div class="d-flex justify-content-between gap-2">
            <a class="btn btn-outline-dark" href="<?= e(url('account/orders.php')) ?>">← All orders</a>
            <?php if ($receipt): ?>
                <a class="btn btn-ink" href="<?= e(url('checkout/receipt.php', ['order' => $id])) ?>">View receipt</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
