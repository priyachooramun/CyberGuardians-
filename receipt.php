<?php
/**
 * FR11 – Receipt (printable). Customers can only open their own receipts.
 * Owner: Member 4 (Cart, checkout & orders)
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_customer();

$orderId = int_param($_GET, 'order');
$order   = $orderId ? Order::findForCustomer($orderId, customer_id()) : null;
$receipt = $order ? Receipt::forOrder($orderId) : null;
if (!$order || !$receipt) {
    abort(404, 'Receipt not found.');
}
$items    = OrderItem::forOrder($orderId);
$customer = Customer::find(customer_id());
$store    = Bookstore::info();

$pageTitle = 'Receipt ' . $receipt['receipt_number'];
$noindex   = true;
$pageCss   = 'receipt';
require ROOT_PATH . '/includes/header.php';
?>
<div class="container py-5">
    <div class="ink-receipt mx-auto">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <img src="<?= e(asset('images/logo.png')) ?>" alt="InkCorner" class="ink-receipt-logo" width="130">
                <h1 class="display-font mb-0"><?= e($store['name']) ?></h1>
                <div class="small"><?= e($store['address']) ?><br><?= e($store['phone']) ?> · <?= e($store['email'] ?? '') ?></div>
            </div>
            <div class="text-end">
                <h2 class="h4 mb-1">Receipt</h2>
                <div class="small">No. <strong><?= e($receipt['receipt_number']) ?></strong><br>
                    Issued <?= e(date('d M Y', strtotime($receipt['issue_date']))) ?><br>
                    Order #<?= (int)$order['order_id'] ?></div>
            </div>
        </div>

        <div class="row mb-4 small">
            <div class="col-sm-6">
                <h3 class="h6">Billed to</h3>
                <?= e($customer['name']) ?><br><?= e($customer['email']) ?>
            </div>
            <div class="col-sm-6 text-sm-end">
                <h3 class="h6">Ship to</h3>
                <?= e($order['shipping_address']) ?>
            </div>
        </div>

        <table class="table">
            <thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Unit price</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['product_name']) ?><?= $item['variant_name'] ? ' – ' . e($item['variant_name']) : '' ?></td>
                    <td class="text-center"><?= (int)$item['quantity'] ?></td>
                    <td class="text-end"><?= e(money($item['price'])) ?></td>
                    <td class="text-end"><?= e(money($item['price'] * $item['quantity'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><th colspan="3" class="text-end">Total paid</th><th class="text-end"><?= e(money($receipt['amount'])) ?></th></tr>
            </tfoot>
        </table>

        <p class="small mb-1"><strong>Payment:</strong> <?= e($receipt['method']) ?> ending in <?= e($receipt['card_last4']) ?> ·
            <?= e($receipt['payment_status']) ?> · Ref <?= e($receipt['transaction_ref']) ?></p>
        <p class="small text-muted">This is a simulated payment for a university project. No money was taken.</p>

        <div class="d-flex justify-content-between gap-2 d-print-none mt-4">
            <a href="<?= e(url('account/orders.php')) ?>" class="btn btn-outline-dark">My orders</a>
            <button type="button" class="btn btn-ink js-print"><i class="bi bi-printer"></i> Print receipt</button>
        </div>
    </div>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
