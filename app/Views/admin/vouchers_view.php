<?php
/**
 * @var array $vouchers
 */
?>
<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<style>
    .premium-form { display: flex; flex-direction: column; gap: 14px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label { font-size: 0.8rem; font-weight: 700; color: rgba(255,255,255,0.65); text-transform: uppercase; }
    .form-group input, .form-group select {
        padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12);
        color: #fff; border-radius: 10px; outline: none;
    }
    .btn-submit {
        border: none; border-radius: 10px; padding: 12px; font-weight: 700; color: #fff;
        background: linear-gradient(135deg, #6366f1, #a855f7); cursor: pointer;
    }
    .badge-active { background: rgba(16,185,129,0.2); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.4); padding: 6px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; }
    .badge-inactive { background: rgba(239,68,68,0.15); color: #fca5a5; border: 1px solid rgba(239,68,68,0.35); padding: 6px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; }
    .btn-toggle {
        border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; padding: 8px 12px;
        background: rgba(255,255,255,0.06); color: #fff; cursor: pointer; font-weight: 700;
    }
    @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>

<main class="main-content">
    <div class="card glass-panel" style="padding: 32px; border-radius: 24px;">
        <div class="flex-header" style="margin-bottom: 18px;">
            <div>
                <h2 style="margin: 0;">Voucher Management</h2>
                <p style="color: rgba(255,255,255,0.6); margin: 8px 0 0;">Create and toggle checkout vouchers without manual SQL.</p>
            </div>
            <button class="btn-primary" onclick="openModal('addVoucherModal')">
                <i class="fas fa-plus"></i> Add Voucher
            </button>
        </div>

        <div class="glass-table-container">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Scope</th>
                        <th>Discount</th>
                        <th>Minimum</th>
                        <th>Payment Limit</th>
                        <th>Status</th>
                        <th class="action-cell">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vouchers)): foreach ($vouchers as $v): ?>
                        <tr>
                            <td><strong><?= esc($v['code']) ?></strong></td>
                            <td><?= esc($v['name']) ?></td>
                            <td><?= strtoupper(esc($v['scope'])) ?></td>
                            <td>
                                <?= $v['discount_type'] === 'percent' ? rtrim(rtrim((string) $v['discount_value'], '0'), '.') . '%' : '₱' . number_format((float) $v['discount_value'], 2) ?>
                                <?php if (!empty($v['max_discount'])): ?>
                                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.45);">Max ₱<?= number_format((float) $v['max_discount'], 2) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>₱<?= number_format((float) ($v['min_order_amount'] ?? 0), 2) ?></td>
                            <td><?= esc($v['payment_method_limit'] ?: 'Any') ?></td>
                            <td>
                                <?php if ((int) $v['is_active'] === 1): ?>
                                    <span class="badge-active">ACTIVE</span>
                                <?php else: ?>
                                    <span class="badge-inactive">INACTIVE</span>
                                <?php endif; ?>
                            </td>
                            <td class="action-cell">
                                <button class="btn-toggle" onclick="toggleVoucher(<?= (int) $v['id'] ?>)">
                                    <i class="fas fa-power-off"></i> <?= (int) $v['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="8" style="text-align: center; padding: 30px; color: rgba(255,255,255,0.45);">No vouchers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="addVoucherModal" class="modal">
        <div class="modal-content">
            <button class="modal-close-btn" onclick="closeModal('addVoucherModal')">&times;</button>
            <div class="modal-header">Create Voucher</div>
            <form id="addVoucherForm" class="premium-form" onsubmit="submitVoucher(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Code</label>
                        <input type="text" name="code" placeholder="PLAT40" required>
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" placeholder="Platform 40 Off" required>
                    </div>
                    <div class="form-group">
                        <label>Scope</label>
                        <select name="scope" required>
                            <option value="platform">Platform</option>
                            <option value="shop">Shop</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Discount Type</label>
                        <select name="discount_type" required>
                            <option value="fixed">Fixed</option>
                            <option value="percent">Percent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Discount Value</label>
                        <input type="number" min="0.01" step="0.01" name="discount_value" required>
                    </div>
                    <div class="form-group">
                        <label>Max Discount (Optional)</label>
                        <input type="number" min="0" step="0.01" name="max_discount" placeholder="120">
                    </div>
                    <div class="form-group">
                        <label>Min Order Amount</label>
                        <input type="number" min="0" step="0.01" name="min_order_amount" value="0">
                    </div>
                    <div class="form-group">
                        <label>Payment Limit (Optional)</label>
                        <select name="payment_method_limit">
                            <option value="">Any</option>
                            <option value="COD">COD only</option>
                            <option value="GCASH">GCash only</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Save Voucher</button>
            </form>
        </div>
    </div>
</main>

<script>
    function openModal(id) { document.getElementById(id).classList.add('show'); }
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }

    async function submitVoucher(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const response = await fetch('<?= site_url('admin/vouchers/store') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const result = await response.json();
        if (result.status === 'success') {
            location.reload();
            return;
        }
        alert(result.message || 'Failed to create voucher.');
    }

    async function toggleVoucher(id) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const response = await fetch('<?= site_url('admin/vouchers/toggle') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const result = await response.json();
        if (result.status === 'success') {
            location.reload();
            return;
        }
        alert(result.message || 'Failed to update voucher.');
    }
</script>

<?= $this->include('theme/footer') ?>
