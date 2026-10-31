<?php
/**
 * FR11 / FR13 – Checkout with simulated payment (Figma: "Payment page")
 * GET  shows the payment form.
 * POST validates it, reserves stock, "charges" the card and saves the order,
 *      items, payment and receipt in ONE database transaction – if anything
 *      fails, everything is rolled back (no half-saved orders).
 * (Merged from the old checkout/process.php.)
 * Owner: Member 4 (Cart, checkout & orders)
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_customer('Please log in to check out.');

$cart  = new Cart();
$lines = $cart->lines();
if (!$lines) {
    flash('info', 'Your cart is empty.');
    redirect('cart/index.php');
}
$total = $cart->total($lines);

if (is_post()) {
    verify_csrf();

    // Double-submit protection
    $nonce = (string)($_POST['checkout_nonce'] ?? '');
    if (empty($_SESSION['checkout_nonce']) || !hash_equals($_SESSION['checkout_nonce'], $nonce)) {
        flash('warning', 'This payment form was already submitted. Please check your orders before trying again.');
        redirect('account/orders.php');
    }
    unset($_SESSION['checkout_nonce']);

    // ---- Validate the form (server side) --------------------------------------
    $v = new Validator($_POST);
    $v->required('card_name', 'Name on card')->name('card_name', 'Name on card')
      ->required('shipping_address', 'Shipping address')->maxLength('shipping_address', 255, 'Shipping address');

    $cardNumber = (string)($_POST['card_number'] ?? '');
    foreach (PaymentSimulator::validate($cardNumber, (string)($_POST['expiry'] ?? ''), (string)($_POST['cvv'] ?? '')) as $field => $message) {
        $v->add($field, $message);
    }

    if ($v->fails()) {
        $_SESSION['form_errors'] = $v->errors();
        remember_input($_POST);   // card number and CVV are NOT remembered
        redirect('checkout/index.php');
    }

    // ---- Place the order ----------------------------------------------------------
    try {
        $orderId = Database::transaction(static function () use ($lines, $total, $v, $cardNumber): int {
            $orderId = Order::create(customer_id(), $total, $v->clean('shipping_address'), 'Pending');

            foreach ($lines as $line) {
                if (!Inventory::decrement($line['product_id'], $line['qty'])) {
                    throw new DomainException('Sorry, "' . $line['name'] . '" just sold out. Your cart has been updated – nothing was charged.');
                }
                OrderItem::add($orderId, $line['product_id'], $line['variant_id'], $line['qty'], $line['unit_price']);
            }

            $charge = PaymentSimulator::charge($cardNumber, $total);
            if (!$charge['approved']) {
                throw new DomainException($charge['message'] . ' Nothing was charged – please try another card.');
            }

            $paymentId = Payment::create($orderId, $total, $charge['method'], 'Success', $charge['reference'], $charge['last4']);
            Receipt::create($paymentId);
            Order::markPaid($orderId);
            return $orderId;
        });
    } catch (DomainException $e) {
        remember_input($_POST);
        flash('danger', $e->getMessage());
        redirect('checkout/index.php');
    } catch (Throwable $e) {
        error_log('Checkout failed: ' . $e->getMessage());
        flash('danger', 'Something went wrong while placing your order. Nothing was charged. Please try again.');
        redirect('checkout/index.php');
    }

    $cart->clear();
    flash('success', 'Thank you! Your payment was approved and your order is confirmed.');
    redirect('checkout/receipt.php', ['order' => $orderId]);
}

$customer = Customer::find(customer_id());
$errors   = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

// One-time token: stops the same order being placed twice (double click / refresh)
$_SESSION['checkout_nonce'] = bin2hex(random_bytes(16));

$pageTitle = 'Payment';
$noindex   = true;
$pageCss   = 'checkout';
require ROOT_PATH . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="ink-form-card">
                <h1 class="display-font mb-1">Payment</h1>
                <p class="small mb-4"><i class="bi bi-shield-lock"></i> Demo checkout – no real payment is taken and card numbers are never stored.</p>

                <form method="post" action="<?= e(url('checkout/index.php')) ?>" id="paymentForm" class="js-validate" novalidate autocomplete="off">
                    <?= csrf_field() ?>
                    <input type="hidden" name="checkout_nonce" value="<?= e($_SESSION['checkout_nonce']) ?>">

                    <div class="ink-field-row">
                        <label for="card_name">Name</label>
                        <div><input type="text" id="card_name" name="card_name" class="form-control ink-input<?= field_class($errors, 'card_name') ?>"
                                    value="<?= e(old('card_name', $customer['name'])) ?>" required maxlength="100" data-rule="name" placeholder="Name on card">
                            <?= field_error($errors, 'card_name') ?></div>
                    </div>
                    <div class="ink-field-row">
                        <label for="card_number">Card Number</label>
                        <div><input type="text" id="card_number" name="card_number" class="form-control ink-input<?= field_class($errors, 'card_number') ?>"
                                    inputmode="numeric" required maxlength="23" data-rule="card" placeholder="4242 4242 4242 4242">
                            <?= field_error($errors, 'card_number') ?></div>
                    </div>
                    <div class="ink-field-row">
                        <label for="expiry">Expiration Date</label>
                        <div><input type="text" id="expiry" name="expiry" class="form-control ink-input<?= field_class($errors, 'expiry') ?>"
                                    inputmode="numeric" required maxlength="5" data-rule="expiry" placeholder="MM/YY" value="<?= e(old('expiry')) ?>">
                            <?= field_error($errors, 'expiry') ?></div>
                    </div>
                    <div class="ink-field-row">
                        <label for="cvv">CVV/CVC Number</label>
                        <div><input type="password" id="cvv" name="cvv" class="form-control ink-input<?= field_class($errors, 'cvv') ?>"
                                    inputmode="numeric" required maxlength="4" data-rule="cvv" placeholder="123">
                            <?= field_error($errors, 'cvv') ?></div>
                    </div>
                    <div class="ink-field-row">
                        <label for="shipping_address">Shipping Address</label>
                        <div><input type="text" id="shipping_address" name="shipping_address"
                                    class="form-control ink-input<?= field_class($errors, 'shipping_address') ?>"
                                    value="<?= e(old('shipping_address', (string)$customer['address'])) ?>" required maxlength="255">
                            <?= field_error($errors, 'shipping_address') ?></div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-ink btn-lg px-5" id="payButton">
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="ink-form-card">
                <h2 class="h4 display-font">Order summary</h2>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($lines as $line): ?>
                        <li class="list-group-item bg-transparent d-flex justify-content-between">
                            <span><?= e($line['name']) ?><?= $line['variant_name'] ? ' (' . e($line['variant_name']) . ')' : '' ?> × <?= (int)$line['qty'] ?></span>
                            <span><?= e(money($line['subtotal'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item bg-transparent d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span><span><?= e(money($total)) ?></span>
                    </li>
                </ul>
                <a href="<?= e(url('cart/index.php')) ?>" class="small link-dark">← Edit cart</a>
            </div>

            <div class="ink-form-card mt-4 small">
                <h2 class="h6">Test cards for the demo</h2>
                <table class="table table-sm mb-0">
                    <tr><td><code>4242 4242 4242 4242</code></td><td>Approved (Visa)</td></tr>
                    <tr><td><code>5555 5555 5555 4444</code></td><td>Approved (Mastercard)</td></tr>
                    <tr><td><code>4000 0000 0000 0002</code></td><td>Declined</td></tr>
                    <tr><td><code>4000 0000 0000 9995</code></td><td>Insufficient funds</td></tr>
                </table>
                <p class="mb-0 mt-2">Use any future expiry date and any 3-digit CVV.</p>
            </div>
        </div>
    </div>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
