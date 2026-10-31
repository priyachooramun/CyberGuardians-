<?php
/**
 * FR20 / FR21 – Order details and status update
 * Owner: Member 4 (Cart, checkout & orders)
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

$id    = int_param($_GET, 'id');
$order = $id ? Order::find($id) : null;
if (!$order) {
    flash('warning', 'That order does not exist.');
    redirect('admin/orders/index.php');
}

if (is_post()) {
    verify_csrf();
    $status = (string)($_POST['status'] ?? '');
    if (Order::updateStatus($id, $status)) {
        flash('success', "Order #{$id} is now {$status}." . ($status === 'Cancelled' ? ' Items were returned to stock and the payment marked refunded.' : ''));
    } else {
        flash('danger', 'That status change is not allowed.');
    }
    redirect('admin/orders/view.php', ['id' => $id]);
}

$items   = OrderItem::forOrder($id);
$payment = Payment::forOrder($id);

$pageTitle = 'Order #' . $id;
$pageCss   = 'admin-order';
require ROOT_PATH . '/includes/admin_header.php';
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-card">
            <h2 class="ledger-font h5">Items</h2>
            <table class="table align-middle">
                <thead><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['product_name']) ?><?= $item['variant_name'] ? ' <small class="text-muted">– ' . e($item['variant_name']) . '</small>' : '' ?></td>
                        <td class="text-center"><?= (int)$item['quantity'] ?></td>
                        <td class="text-end"><?= e(money($item['price'])) ?></td>
                        <td class="text-end"><?= e(money($item['price'] * $item['quantity'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end"><?= e(money($order['total_amount'])) ?></th></tr></tfoot>
            </table>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card mb-4">
            <h2 class="ledger-font h5">Status</h2>
            <p><span class="badge fs-6 status-badge status-<?= e(strtolower($order['status'])) ?>"><?= e($order['status']) ?></span></p>
            <?php if ($order['status'] !== 'Cancelled'): ?>
            <form method="post" class="d-flex gap-2 js-validate js-confirm-cancel" novalidate>
                <?= csrf_field() ?>
                <select name="status" class="form-select" required aria-label="New status">
                    <?php foreach (Order::STATUSES as $s): ?>
                        <option value="<?= e($s) ?>"<?= $order['status'] === $s ? ' selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-ink">Update</button>
            </form>
            <?php else: ?>
                <p class="small mb-0">Cancelled orders cannot be changed.</p>
            <?php endif; ?>
        </div>
        <div class="admin-card mb-4">
            <h2 class="ledger-font h5">Customer</h2>
            <p class="mb-1"><?= e($order['customer_name']) ?></p>
            <p class="mb-1 small"><?= e($order['customer_email']) ?><br><?= e($order['customer_phone']) ?></p>
            <p class="mb-0 small"><strong>Ship to:</strong> <?= e($order['shipping_address']) ?></p>
        </div>
        <?php if ($payment): ?>
        <div class="admin-card">
            <h2 class="ledger-font h5">Payment</h2>
            <p class="small mb-0"><?= e($payment['method']) ?> •••• <?= e($payment['card_last4']) ?><br>
                <?= e($payment['status']) ?> · <?= e(money($payment['amount'])) ?><br>
                Ref <?= e($payment['transaction_ref']) ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
<p class="mt-3"><a class="link-dark" href="<?= e(url('admin/orders/index.php')) ?>">← All orders</a></p>
<?php require ROOT_PATH . '/includes/admin_footer.php'; ?>
