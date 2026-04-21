<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<main class="main-content">
    <?php if (session()->getFlashdata('msg')): ?><div class="alert glass-panel">✅ <?= esc(session()->getFlashdata('msg')) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert glass-panel">⚠️ <?= esc($error) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert glass-panel">⚠️ <?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

    <div class="card glass-panel">
        <h2 style="font-size:2rem;">Payments 💳</h2>
        <p style="opacity:.7;">Track payment status and quickly close pending collections.</p>
        <form method="get" class="premium-form" style="margin-bottom:10px;">
            <div class="form-group"><label>Search</label><input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Reference or method"></div>
            <div class="form-group"><label>Status</label><select name="status"><option value="">All</option><option value="Pending" <?= (($filters['status'] ?? '')==='Pending')?'selected':''; ?>>Pending</option><option value="Paid" <?= (($filters['status'] ?? '')==='Paid')?'selected':''; ?>>Paid</option><option value="Refunded" <?= (($filters['status'] ?? '')==='Refunded')?'selected':''; ?>>Refunded</option></select></div>
            <div class="form-group"><label>From</label><input type="date" name="from" value="<?= esc($filters['from'] ?? '') ?>"></div>
            <div class="form-group"><label>To</label><input type="date" name="to" value="<?= esc($filters['to'] ?? '') ?>"></div>
            <button type="submit" class="btn-primary">Filter</button>
        </form>
        <form action="<?= site_url('admin/payments/store') ?>" method="post" class="premium-form">
            <?= csrf_field() ?>
            <div class="form-group"><label>Order</label><select name="order_id" required><?php foreach (($orders ?? []) as $o): ?><option value="<?= $o['id'] ?>"><?= esc($o['transaction_code']) ?> - ₱<?= number_format((float)$o['total_amount'],2) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Method</label><select name="method"><option>Cash</option><option>GCash</option><option>Bank</option></select></div>
            <div class="form-group"><label>Status</label><select name="status"><option>Paid</option><option>Pending</option><option>Refunded</option></select></div>
            <div class="form-group"><label>Amount</label><input type="number" step="0.01" name="amount" required><?php if(isset($errors['amount'])): ?><small style="color:#fca5a5;"><?= esc($errors['amount']) ?></small><?php endif; ?></div>
            <div class="form-group"><label>Reference No</label><input type="text" name="reference_no"></div>
            <button type="submit" class="btn-primary">Save Payment</button>
        </form>
        <div class="table-responsive glass-panel">
            <table class="premium-table">
                <thead><tr><th>ORDER ID</th><th>METHOD</th><th>STATUS</th><th>AMOUNT</th><th>REFERENCE</th><th>ACTIONS</th></tr></thead>
                <tbody>
                <?php if (!empty($payments)): foreach ($payments as $p): ?>
                    <tr>
                        <td><?= esc($p['order_id']) ?></td>
                        <td><?= esc($p['method']) ?></td>
                        <td><span class="role-badge <?= $p['status']==='Paid' ? 'role-admin' : 'role-customer' ?>"><?= esc($p['status']) ?></span></td>
                        <td>₱<?= number_format((float)$p['amount'],2) ?></td>
                        <td><?= esc($p['reference_no'] ?? '-') ?></td>
                        <td><?php if (($p['status'] ?? '') !== 'Paid'): ?><a class="btn-edit" href="<?= site_url('admin/payments/markPaid/'.$p['id']) ?>">Mark Paid</a><?php else: ?><span style="opacity:.7;">Done</span><?php endif; ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" style="text-align:center;padding:30px;">No payments yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($pager)): ?><div style="margin-top:12px;"><?= $pager->links() ?></div><?php endif; ?>
    </div>
</main>

<?= $this->include('theme/footer') ?>
