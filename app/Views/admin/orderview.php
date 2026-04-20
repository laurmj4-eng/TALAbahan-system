<!-- 1. Include Shared Header (CSS/Theme) -->
<?= $this->include('theme/header') ?>

<!-- 2. Include Shared Sidebar -->
<?= $this->include('theme/sidebar') ?>

<!-- 3. Main Content Wrapper -->
<main class="main-content">
    
    <?php if(session()->getFlashdata('msg')): ?>
        <div class="alert glass-panel" id="system-alert">✅ <?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <div class="card glass-panel">
        <h2 style="font-size: 2.2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">Customer Orders 📑</h2>
        <p style="color: rgba(255,255,255,0.6); margin-top: 15px; margin-bottom: 30px;">View and track all customer transactions and payment status.</p>

        <div class="table-responsive glass-panel">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>CUSTOMER NAME</th>
                        <th>TOTAL AMOUNT</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): foreach ($orders as $o): ?>
                        <tr>
                            <td><?= date('M d, Y h:i A', strtotime($o['created_at'])) ?></td>
                            <td><strong style="color: #fff;"><?= esc($o['customer_name']) ?: 'Walk-in Customer' ?></strong></td>
                            <td>₱<?= number_format($o['total_amount'], 2) ?></td>
                            <td>
                                <?php if($o['status'] == 'Paid'): ?>
                                    <span class="role-badge role-staff" style="background: #2ecc71;">PAID</span>
                                <?php else: ?>
                                    <span class="role-badge role-staff" style="background: #e67e22;">PENDING</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4" style="text-align: center; color: #777; padding: 40px;">No orders recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- 4. Include Shared Footer -->
<?= $this->include('theme/footer') ?>