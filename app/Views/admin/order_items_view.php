<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<main class="main-content">
    <div class="card glass-panel">
        <h2 style="font-size: 2.2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">Order Items 🧾</h2>
        <p style="color: rgba(255,255,255,0.6); margin-top: 15px; margin-bottom: 30px;">Line-by-line product receipts from customer orders.</p>

        <div class="table-responsive glass-panel">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>TXN CODE</th>
                        <th>CUSTOMER</th>
                        <th>PRODUCT</th>
                        <th>QTY</th>
                        <th>UNIT PRICE</th>
                        <th>SUBTOTAL</th>
                        <th>STATUS</th>
                        <th>DATE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($orderItems)): ?>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td><strong style="color:#818cf8;"><?= esc($item['transaction_code'] ?? '-') ?></strong></td>
                                <td><?= esc($item['customer_name'] ?? 'Walk-in Customer') ?></td>
                                <td><?= esc($item['product_name']) ?></td>
                                <td><?= esc($item['quantity']) ?> <?= esc($item['unit'] ?? '') ?></td>
                                <td>₱<?= number_format((float) $item['unit_price'], 2) ?></td>
                                <td>₱<?= number_format((float) $item['subtotal'], 2) ?></td>
                                <td><?= esc($item['status'] ?? '-') ?></td>
                                <td><?= ! empty($item['created_at']) ? date('M d, Y h:i A', strtotime($item['created_at'])) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; color:#777; padding:40px;">No order line items recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?= $this->include('theme/footer') ?>
