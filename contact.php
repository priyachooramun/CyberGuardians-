<?php

require_once __DIR__ . '/includes/bootstrap.php';

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

if (is_post()) {
    require_customer('Please log in to send us a question.');
    verify_csrf();

    $v = new Validator($_POST);
    $v->required('subject', 'Subject')->maxLength('subject', 150, 'Subject')
      ->required('message', 'Message')->minLength('message', 10, 'Message')->maxLength('message', 2000, 'Message');
    if ($v->fails()) {
        $_SESSION['form_errors'] = $v->errors();
        remember_input($_POST);
        redirect('contact.php');
    }
    Query::create(customer_id(), $v->clean('subject'), $v->clean('message'));
    flash('success', 'Thank you! Your question was sent. We usually reply within one working day.');
    redirect('contact.php');
}

$store     = Bookstore::info();
$pageTitle = 'Contact us';
$pageCss   = 'contact';
require ROOT_PATH . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="ink-form-card h-100">
                <h1 class="display-font">Contact us</h1>
                <p>Questions about a book, a price or an order? We're happy to help.</p>
                <p class="mb-2"><i class="bi bi-geo-alt"></i> <?= e($store['address']) ?></p>
                <p class="mb-2"><i class="bi bi-telephone"></i> <?= e($store['phone']) ?></p>
                <p class="mb-2"><i class="bi bi-envelope"></i> <?= e($store['email'] ?? '') ?></p>
                <p class="mb-0"><i class="bi bi-clock"></i> <?= e($store['opening_hours'] ?? '') ?></p>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="ink-form-card h-100">
                <h2 class="h4 display-font">Send us a question</h2>
                <?php if (!current_customer()): ?>
                    <p>Please <a href="<?= e(url('auth/login.php')) ?>">log in</a> or <a href="<?= e(url('auth/register.php')) ?>">create an account</a> to send a question, so we can reply to you.</p>
                <?php else: ?>
                    <form method="post" action="" class="js-validate" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control ink-input<?= field_class($errors, 'subject') ?>"
                                   value="<?= e(old('subject')) ?>" required maxlength="150">
                            <?= field_error($errors, 'subject') ?>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea id="message" name="message" rows="5" class="form-control ink-input<?= field_class($errors, 'message') ?>"
                                      required minlength="10" maxlength="2000"><?= e(old('message')) ?></textarea>
                            <?= field_error($errors, 'message') ?>
                        </div>
                        <div class="text-end"><button class="btn btn-ink px-5" type="submit">Send</button></div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (current_customer() && ($myQueries = Query::forCustomer(customer_id()))): ?>
    <div class="ink-form-card mt-4">
        <h2 class="h4 display-font">Your questions</h2>
        <?php foreach ($myQueries as $mq): ?>
            <div class="ink-query">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <strong><?= e($mq['subject'] ?? 'Question') ?></strong>
                    <span class="badge <?= $mq['status'] === 'Open' ? 'bg-warning text-dark' : 'bg-success' ?>"><?= $mq['status'] === 'Open' ? 'Waiting for reply' : 'Answered' ?></span>
                </div>
                <p class="small text-muted mb-1"><?= e($mq['query_date']) ?></p>
                <p class="mb-2"><?= e_nl($mq['query_text']) ?></p>
                <?php if ($mq['reply']): ?>
                    <div class="ink-query-reply"><strong>InkCorner replied (<?= e($mq['reply_date']) ?>):</strong><br><?= e_nl($mq['reply']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
