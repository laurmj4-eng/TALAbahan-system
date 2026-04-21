<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<main class="main-content">
    <?php if (session()->getFlashdata('msg')): ?><div class="alert glass-panel">✅ <?= esc(session()->getFlashdata('msg')) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert glass-panel">⚠️ <?= esc($error) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert glass-panel">⚠️ <?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

    <div class="card glass-panel">
        <h2 style="font-size:2rem;">Purchases 📥</h2>
        <p style="opacity:.7;">Record inbound seafood stocks and automatically update inventory.</p>
        <form method="get" class="premium-form" style="margin-bottom:10px;">
            <div class="form-group"><label>Search</label><input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Reference or supplier"></div>
            <div class="form-group"><label>From</label><input type="date" name="from" value="<?= esc($filters['from'] ?? '') ?>"></div>
            <div class="form-group"><label>To</label><input type="date" name="to" value="<?= esc($filters['to'] ?? '') ?>"></div>
            <div class="form-group"><label>Status</label><select name="status"><option value="">All</option><option value="Received" <?= (($filters['status'] ?? '')==='Received')?'selected':''; ?>>Received</option><option value="Draft" <?= (($filters['status'] ?? '')==='Draft')?'selected':''; ?>>Draft</option></select></div>
            <button type="submit" class="btn-primary">Filter</button>
        </form>
        <form action="<?= site_url('admin/purchases/receive') ?>" method="post" class="premium-form">
            <?= csrf_field() ?>
            <div class="form-group"><label>Reference No</label><input type="text" name="reference_no" placeholder="PO-..."></div>
            <div class="form-group"><label>Supplier</label><input type="text" name="supplier_name" value="<?= old('supplier_name') ?>" required><?php if(isset($errors['supplier_name'])): ?><small style="color:#fca5a5;"><?= esc($errors['supplier_name']) ?></small><?php endif; ?></div>
            <div class="form-group"><label>Purchase Date</label><input type="date" name="purchase_date" value="<?= date('Y-m-d') ?>" required></div>
            <div class="form-group"><label>Notes</label><input type="text" name="notes"></div>

            <div id="purchase-items" style="grid-column:1/-1;display:grid;gap:10px;">
                <div class="purchase-row" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:10px;">
                    <select name="product_id[]"><?php foreach (($products ?? []) as $p): ?><option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option><?php endforeach; ?></select>
                    <input type="number" step="0.01" name="quantity[]" placeholder="Qty" required>
                    <input type="number" step="0.01" name="unit_cost[]" placeholder="Unit Cost" required>
                    <button type="button" class="btn-delete" onclick="removePurchaseRow(this)">Remove</button>
                </div>
            </div>
            <button type="button" class="btn-edit" onclick="addPurchaseRow()">Add Row</button>
            <div class="form-group"><label>Estimated Total</label><input type="text" id="estimated-total" value="₱0.00" readonly></div>
            <button type="submit" class="btn-primary">Receive Purchase</button>
        </form>

        <div class="table-responsive glass-panel">
            <table class="premium-table">
                <thead><tr><th>REFERENCE</th><th>SUPPLIER</th><th>DATE</th><th>TOTAL COST</th><th>STATUS</th></tr></thead>
                <tbody>
                <?php if (!empty($purchases)): foreach ($purchases as $p): ?>
                    <tr><td><?= esc($p['reference_no']) ?></td><td><?= esc($p['supplier_name']) ?></td><td><?= esc($p['purchase_date']) ?></td><td>₱<?= number_format((float)$p['total_cost'],2) ?></td><td><?= esc($p['status']) ?></td></tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" style="text-align:center;padding:30px;">No purchases yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($pager)): ?><div style="margin-top:12px;"><?= $pager->links() ?></div><?php endif; ?>
    </div>
</main>

<?= $this->include('theme/footer') ?>
<script>
function addPurchaseRow() {
    const wrapper = document.getElementById('purchase-items');
    const first = wrapper.querySelector('.purchase-row');
    const clone = first.cloneNode(true);
    clone.querySelectorAll('input').forEach(i => i.value = '');
    wrapper.appendChild(clone);
}
function removePurchaseRow(btn) {
    const wrapper = document.getElementById('purchase-items');
    if (wrapper.querySelectorAll('.purchase-row').length === 1) return;
    btn.closest('.purchase-row').remove();
    computeEstimatedTotal();
}
function computeEstimatedTotal() {
    let total = 0;
    document.querySelectorAll('input[name="quantity[]"]').forEach((q, i) => {
        const qty = parseFloat(q.value || '0');
        const cost = parseFloat((document.querySelectorAll('input[name="unit_cost[]"]')[i] || {}).value || '0');
        total += qty * cost;
    });
    document.getElementById('estimated-total').value = '₱' + total.toFixed(2);
}
document.addEventListener('input', function(e) {
    if (e.target.name === 'quantity[]' || e.target.name === 'unit_cost[]') computeEstimatedTotal();
});
</script>
