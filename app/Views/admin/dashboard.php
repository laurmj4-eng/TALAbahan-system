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
            <p style="color: rgba(255,255,255,0.7); font-size: 1.1rem; max-width: 700px;">Manage products, monitor sales, and track order activity in one clean owner dashboard.</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-top:20px;">
                <div class="glass-panel" style="padding:14px;border-radius:14px;">
                    <small style="opacity:.7;">Today Sales</small>
                    <h3 style="margin:8px 0 0;">₱<?= number_format((float)($cards['today_sales'] ?? 0), 2) ?></h3>
                </div>
                <div class="glass-panel" style="padding:14px;border-radius:14px;">
                    <small style="opacity:.7;">Today Orders</small>
                    <h3 style="margin:8px 0 0;"><?= (int)($cards['today_orders'] ?? 0) ?></h3>
                </div>
                <div class="glass-panel" style="padding:14px;border-radius:14px;">
                    <small style="opacity:.7;">Low Stock Items (&le; 5)</small>
                    <h3 style="margin:8px 0 0;"><?= (int)($cards['low_stock_count'] ?? 0) ?></h3>
                </div>
            </div>
        </div>

        <div class="card glass-panel">
            <h2 style="font-size:1.5rem;">7-Day Sales Trend</h2>
            <p style="color: rgba(255,255,255,0.7); margin-bottom: 16px;">Connected to order history for live owner insights.</p>
            <div style="height: 320px;">
                <canvas id="overviewTrendChart"></canvas>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const el = document.getElementById('overviewTrendChart');
    if (!el) return;

    const labels = <?= json_encode($chart['labels'] ?? []) ?>;
    const sales = <?= json_encode($chart['sales'] ?? []) ?>;

    new Chart(el, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Sales Revenue',
                    data: sales,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.18)',
                    fill: true,
                    tension: 0.35
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#e5e7eb' }
                }
            },
            scales: {
                x: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(255,255,255,0.08)' } },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#cbd5e1',
                        callback: (value) => '₱' + value
                    },
                    grid: { color: 'rgba(255,255,255,0.08)' }
                }
            }
        }
    });
})();
</script>