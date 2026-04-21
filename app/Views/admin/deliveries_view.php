<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<main class="main-content">
    <?php if (session()->getFlashdata('msg')): ?><div class="alert glass-panel">✅ <?= esc(session()->getFlashdata('msg')) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert glass-panel">⚠️ <?= esc($error) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert glass-panel">⚠️ <?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

    <div class="card glass-panel">
        <h2 style="font-size:2rem;">Deliveries 🚚</h2>
        <p style="opacity:.7;">Monitor order fulfillment and quickly close deliveries.</p>
        <form method="get" class="premium-form" style="margin-bottom:10px;">
            <div class="form-group"><label>Rider Search</label><input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Rider name"></div>
            <div class="form-group"><label>Status</label><select name="status"><option value="">All</option><option value="Scheduled" <?= (($filters['status'] ?? '')==='Scheduled')?'selected':''; ?>>Scheduled</option><option value="InTransit" <?= (($filters['status'] ?? '')==='InTransit')?'selected':''; ?>>In Transit</option><option value="Delivered" <?= (($filters['status'] ?? '')==='Delivered')?'selected':''; ?>>Delivered</option><option value="Cancelled" <?= (($filters['status'] ?? '')==='Cancelled')?'selected':''; ?>>Cancelled</option></select></div>
            <button type="submit" class="btn-primary">Filter</button>
        </form>
        <form action="<?= site_url('admin/deliveries/store') ?>" method="post" class="premium-form">
            <?= csrf_field() ?>
            <div class="form-group"><label>Order</label><select name="order_id" required><?php foreach (($orders ?? []) as $o): ?><option value="<?= $o['id'] ?>"><?= esc($o['transaction_code']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Rider Name</label><input type="text" name="rider_name"><?php if(isset($errors['rider_name'])): ?><small style="color:#fca5a5;"><?= esc($errors['rider_name']) ?></small><?php endif; ?></div>
            <div class="form-group"><label>ETA</label><input type="datetime-local" name="eta_at"></div>
            <div class="form-group"><label>Status</label><select name="status"><option>Scheduled</option><option>InTransit</option><option>Delivered</option><option>Cancelled</option></select></div>
            <div class="form-group"><label>Route Note</label><input type="text" name="route_note"></div>
            <button type="submit" class="btn-primary">Save Delivery</button>
        </form>
        <div class="table-responsive glass-panel">
            <table class="premium-table">
                <thead><tr><th>ORDER ID</th><th>RIDER</th><th>ETA</th><th>STATUS</th><th>DELIVERED AT</th><th>ACTIONS</th></tr></thead>
                <tbody>
                <?php if (!empty($deliveries)): foreach ($deliveries as $d): ?>
                    <tr>
                        <td><?= esc($d['order_id']) ?></td>
                        <td><?= esc($d['rider_name'] ?? '-') ?></td>
                        <td><?= esc($d['eta_at'] ?? '-') ?></td>
                        <td><span class="role-badge <?= $d['status']==='Delivered' ? 'role-admin' : 'role-staff' ?>"><?= esc($d['status']) ?></span></td>
                        <td><?= esc($d['delivered_at'] ?? '-') ?></td>
                        <td><?php if (($d['status'] ?? '') !== 'Delivered'): ?><a class="btn-edit" href="<?= site_url('admin/deliveries/markDelivered/'.$d['id']) ?>">Mark Delivered</a><?php else: ?><span style="opacity:.7;">Done</span><?php endif; ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" style="text-align:center;padding:30px;">No deliveries yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($pager)): ?><div style="margin-top:12px;"><?= $pager->links() ?></div><?php endif; ?>
    </div>
</main>

<?= $this->include('theme/footer') ?>
