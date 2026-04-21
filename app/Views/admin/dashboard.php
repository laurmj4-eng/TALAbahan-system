<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<main class="main-content">
    
    <?php if(session()->getFlashdata('msg')): ?>
        <div class="premium-alert success glass-panel" id="system-alert">
            <i class="fas fa-check-circle"></i>
            <div><?= session()->getFlashdata('msg') ?></div>
        </div>
    <?php endif; ?>

    <!-- DASHBOARD TAB -->
    <section id="tab-dashboard" class="tab-section active">
        <div class="header">
            <h1 class="premium-title">Welcome back, <?= esc(session()->get('username') ?? 'Admin') ?>!</h1>
            <p class="premium-status-text">Manage products, monitor sales, and track order activity in one clean owner dashboard.</p>
        </div>

        <div class="premium-cards-grid">
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-success color-success"><i class="fas fa-coins"></i></div>
                <div class="premium-stat-label">Today's Sales</div>
                <div class="premium-stat-value color-success">₱<?= number_format((float)($cards['today_sales'] ?? 0), 2) ?></div>
                <div class="premium-stat-desc">Total revenue generated today</div>
            </div>
            
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-info color-info"><i class="fas fa-shopping-cart"></i></div>
                <div class="premium-stat-label">Today's Orders</div>
                <div class="premium-stat-value color-info"><?= (int)($cards['today_orders'] ?? 0) ?></div>
                <div class="premium-stat-desc">Total transactions processed</div>
            </div>
            
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-premium color-premium"><i class="fas fa-layer-group"></i></div>
                <div class="premium-stat-label">Low Stock Items</div>
                <div class="premium-stat-value color-premium"><?= (int)($cards['low_stock_count'] ?? 0) ?></div>
                <div class="premium-stat-desc">Products with ≤ 5 units left</div>
            </div>
        </div>

        <div class="premium-section-header">
            <i class="fas fa-chart-line"></i>
            <h3>Performance Overview</h3>
        </div>
        
        <div class="card glass-panel" style="padding: 30px; border-radius: 24px;">
            <h2 style="font-size:1.5rem; margin-bottom: 8px;">7-Day Sales Trend</h2>
            <p style="color: rgba(255,255,255,0.4); margin-bottom: 24px; font-size: 0.95rem;">Connected to order history for live owner insights.</p>
            <div style="height: 350px;">
                <canvas id="overviewTrendChart"></canvas>
            </div>
        </div>

        <div class="premium-section-header" style="margin-top: 40px;">
            <i class="fas fa-bolt"></i>
            <h3>Quick Management</h3>
        </div>
        <div class="premium-quick-actions">
            <a onclick="switchTab('pos')" class="premium-action-btn glass-panel">
                <i class="fas fa-cash-register color-premium"></i>
                <span>Open Seafood POS</span>
            </a>
            <a href="<?= site_url('admin/products') ?>" class="premium-action-btn glass-panel">
                <i class="fas fa-fish color-info"></i>
                <span>Inventory Manager</span>
            </a>
            <a onclick="switchTab('sales')" class="premium-action-btn glass-panel">
                <i class="fas fa-file-invoice-dollar color-success"></i>
                <span>Detailed Sales</span>
            </a>
            <a href="<?= site_url('admin/users') ?>" class="premium-action-btn glass-panel">
                <i class="fas fa-users-cog color-warning"></i>
                <span>User Management</span>
            </a>
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