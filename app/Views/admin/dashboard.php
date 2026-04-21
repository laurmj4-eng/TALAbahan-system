<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<main class="main-content">
    
    <?php if(session()->getFlashdata('msg')): ?>
        <div class="alert glass-panel" id="system-alert">✅ <?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <!-- DASHBOARD TAB -->
    <section id="tab-dashboard" class="tab-section active">
        <div class="card glass-panel">
            <h2>Welcome back, <?= esc(session()->get('username') ?? 'Admin') ?>.</h2>
            <p style="color: rgba(255,255,255,0.7); font-size: 1.1rem; max-width: 700px;">Track purchases, deliveries, and payment health at a glance.</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-top:20px;">
                <div class="glass-panel" style="padding:14px;border-radius:14px;">
                    <small style="opacity:.7;">Today Purchases</small>
                    <h3 style="margin:8px 0 0;">₱<?= number_format((float)($cards['today_purchases'] ?? 0), 2) ?></h3>
                </div>
                <div class="glass-panel" style="padding:14px;border-radius:14px;">
                    <small style="opacity:.7;">Pending Deliveries</small>
                    <h3 style="margin:8px 0 0;"><?= (int)($cards['pending_deliveries'] ?? 0) ?></h3>
                </div>
                <div class="glass-panel" style="padding:14px;border-radius:14px;">
                    <small style="opacity:.7;">Unpaid Amount</small>
                    <h3 style="margin:8px 0 0;">₱<?= number_format((float)($cards['unpaid_amount'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
    </section>

    <!-- POS TAB -->
    <section id="tab-pos" class="tab-section">
        <?= $this->include('admin/pos_view') ?>
    </section>

    <!-- SALES HISTORY TAB -->
    <section id="tab-sales" class="tab-section">
        <?= $this->include('admin/sales_history_view') ?>
    </section>

    
    <!-- order view -->
    <section id="tab-sales" class="tab-section">
        <?= $this->include('admin/orderview') ?>
    </section>

</main>

<?= $this->include('theme/footer') ?>