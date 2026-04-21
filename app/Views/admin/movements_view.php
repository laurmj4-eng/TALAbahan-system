<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<main class="main-content">
    <?php if (!empty($error)): ?><div class="alert glass-panel">⚠️ <?= esc($error) ?></div><?php endif; ?>
    <div class="card glass-panel">
        <h2 style="font-size:2rem;">Inventory Movements 📒</h2>
        <form method="get" class="premium-form" style="margin-bottom:10px;">
            <div class="form-group"><label>Search</label><input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Notes or ref"></div>
            <div class="form-group"><label>Type</label><select name="type"><option value="">All</option><option value="IN" <?= (($filters['type'] ?? '')==='IN')?'selected':''; ?>>IN</option><option value="OUT" <?= (($filters['type'] ?? '')==='OUT')?'selected':''; ?>>OUT</option><option value="ADJUSTMENT" <?= (($filters['type'] ?? '')==='ADJUSTMENT')?'selected':''; ?>>ADJUSTMENT</option><option value="WASTAGE" <?= (($filters['type'] ?? '')==='WASTAGE')?'selected':''; ?>>WASTAGE</option></select></div>
            <div class="form-group"><label>From</label><input type="date" name="from" value="<?= esc($filters['from'] ?? '') ?>"></div>
            <div class="form-group"><label>To</label><input type="date" name="to" value="<?= esc($filters['to'] ?? '') ?>"></div>
            <button type="submit" class="btn-primary">Filter</button>
        </form>
        <div class="table-responsive glass-panel">
            <table class="premium-table">
                <thead><tr><th>PRODUCT ID</th><th>TYPE</th><th>QTY</th><th>REFERENCE</th><th>NOTES</th><th>DATE</th></tr></thead>
                <tbody>
                <?php if (!empty($movements)): foreach ($movements as $m): ?>
                    <tr>
                        <td><?= esc($m['product_id']) ?></td>
                        <td><?= esc($m['movement_type']) ?></td>
                        <td><?= esc($m['quantity']) ?></td>
                        <td><?= esc(($m['reference_type'] ?? '-') . '#' . ($m['reference_id'] ?? '-')) ?></td>
                        <td><?= esc($m['notes'] ?? '-') ?></td>
                        <td><?= esc($m['created_at'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" style="text-align:center;padding:30px;">No inventory movements yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($pager)): ?><div style="margin-top:12px;"><?= $pager->links() ?></div><?php endif; ?>
    </div>
</main>

<?= $this->include('theme/footer') ?>
