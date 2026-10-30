<?php

require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

if (is_post()) {
    verify_csrf();
    $id    = int_param($_POST, 'query_id');
    $v     = new Validator($_POST);
    $v->required('reply', 'Reply')->minLength('reply', 2, 'Reply')->maxLength('reply', 2000, 'Reply');
    if (!$id || !Query::find($id)) {
        flash('warning', 'That question does not exist.');
    } elseif ($v->fails()) {
        flash('danger', current($v->errors()));
    } else {
        Query::reply($id, $v->clean('reply'));
        
        flash('success', 'Reply saved. The customer will see it on the Contact page.');
    }
    redirect('admin/queries/index.php', ['open' => $id]);
}

$status  = (string)($_GET['status'] ?? '');
$status  = in_array($status, Query::STATUSES, true) ? $status : '';
$openId  = int_param($_GET, 'open');
$queries = Query::list($status);

$pageTitle = 'Customer Queries';
$pageCss   = 'admin-queries';
require ROOT_PATH . '/includes/admin_header.php';
?>
<div class="admin-card">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="btn-group" role="group" aria-label="Filter questions">
            <a class="btn btn-sm <?= $status === '' ? 'btn-dark' : 'btn-outline-dark' ?>" href="<?= e(url('admin/queries/index.php')) ?>">All</a>
            <?php foreach (Query::STATUSES as $s): ?>
                <a class="btn btn-sm <?= $status === $s ? 'btn-dark' : 'btn-outline-dark' ?>" href="<?= e(url('admin/queries/index.php', ['status' => $s])) ?>"><?= e($s) ?></a>
            <?php endforeach; ?>
        </div>
        <span class="small"><?= Query::countOpen() ?> open question<?= Query::countOpen() === 1 ? '' : 's' ?></span>
    </div>

    <?php if (!$queries): ?>
        <p class="text-center py-4 mb-0">No questions yet. They arrive from the <a href="<?= e(url('contact.php')) ?>" target="_blank" rel="noopener">Contact page</a>.</p>
    <?php endif; ?>

    <div class="accordion" id="queryList">
        <?php foreach ($queries as $q): ?>
            <?php $qid = (int)$q['query_id']; $show = $qid === $openId; ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button<?= $show ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#q<?= $qid ?>" aria-expanded="<?= $show ? 'true' : 'false' ?>">
                        <span class="badge me-2 <?= $q['status'] === 'Open' ? 'bg-warning text-dark' : 'bg-success' ?>"><?= e($q['status']) ?></span>
                        <strong class="me-2"><?= e($q['subject'] ?? 'Question') ?></strong>
                        <small class="text-muted"><?= e($q['customer_name']) ?> · <?= e($q['query_date']) ?></small>
                    </button>
                </h2>
                <div id="q<?= $qid ?>" class="accordion-collapse collapse<?= $show ? ' show' : '' ?>" data-bs-parent="#queryList">
                    <div class="accordion-body">
                        <p class="query-text"><?= e_nl($q['query_text']) ?></p>
                        <p class="small text-muted mb-3">From <?= e($q['customer_name']) ?> (<?= e(mask_email((string)$q['customer_email'])) ?>)</p>
                        <?php if ($q['reply']): ?>
                            <div class="query-reply mb-3"><strong>Your reply (<?= e($q['reply_date']) ?>):</strong><br><?= e_nl($q['reply']) ?></div>
                        <?php endif; ?>
                        <form method="post" class="js-validate" novalidate>
                            <?= csrf_field() ?>
                            <input type="hidden" name="query_id" value="<?= $qid ?>">
                            <label class="form-label" for="reply<?= $qid ?>"><?= $q['reply'] ? 'Update reply' : 'Reply' ?></label>
                            <textarea class="form-control mb-2" id="reply<?= $qid ?>" name="reply" rows="3" required minlength="2" maxlength="2000"><?= e($q['reply'] ?? '') ?></textarea>
                            <button class="btn btn-ink">Send reply</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require ROOT_PATH . '/includes/admin_footer.php'; ?>
