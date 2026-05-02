<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

    <!-- MAIN CONTENT AREA -->
    <main class="main-content">
        <div class="header">
            <h1 class="premium-title">Welcome back, <?= esc(session()->get('username') ?? 'Staff') ?>!</h1>
            <p class="premium-status-text">Manage products, track orders, and monitor inventory levels.</p>
        </div>

        <!-- KEY STATISTICS CARDS -->
        <div class="premium-cards-grid">
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-info color-info"><i class="fas fa-shopping-cart"></i></div>
                <div class="premium-stat-label">Today's Orders</div>
                <div class="premium-stat-value color-info"><?= (int)($cards['today_orders'] ?? 0) ?></div>
                <div class="premium-stat-desc">Total transactions processed today</div>
            </div>
            
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-premium color-premium"><i class="fas fa-boxes"></i></div>
                <div class="premium-stat-label">Total Products</div>
                <div class="premium-stat-value color-premium"><?= (int)($cards['total_products'] ?? 0) ?></div>
                <div class="premium-stat-desc">All items in your inventory</div>
            </div>
            
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-warning color-warning"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="premium-stat-label">Low Stock Items</div>
                <div class="premium-stat-value color-warning"><?= (int)($cards['low_stock_count'] ?? 0) ?></div>
                <div class="premium-stat-desc">Products with \u2264 5 units left</div>
            </div>

            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-danger color-danger"><i class="fas fa-times-circle"></i></div>
                <div class="premium-stat-label">Out of Stock</div>
                <div class="premium-stat-value color-danger"><?= (int)($cards['out_of_stock'] ?? 0) ?></div>
                <div class="premium-stat-desc">Products with 0 units left</div>
            </div>
        </div>

        <div class="premium-section-header">
            <i class="fas fa-chart-line"></i>
            <h3>Sales Performance</h3>
        </div>
        
        <div class="card glass-panel" style="padding: 30px; border-radius: 24px;">
            <h2 style="font-size:1.5rem; margin-bottom: 8px;">7-Day Sales Trend</h2>
            <p style="color: rgba(255,255,255,0.4); margin-bottom: 24px; font-size: 0.95rem;">Monitor daily sales revenue for the past week.</p>
            <div style="height: 350px;">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <div class="premium-section-header" style="margin-top: 40px;">
            <i class="fas fa-bolt"></i>
            <h3>Quick Actions</h3>
        </div>
        <div class="premium-quick-actions">
            <a href="<?= site_url('staff/products') ?>" class="premium-action-btn glass-panel">
                <i class="fas fa-fish color-info"></i>
                <span>Manage Products</span>
            </a>
            <a href="<?= site_url('staff/orders') ?>" class="premium-action-btn glass-panel">
                <i class="fas fa-clipboard-list color-premium"></i>
                <span>View Orders</span>
            </a>
            <a href="<?= site_url('staff/salesHistory') ?>" class="premium-action-btn glass-panel">
                <i class="fas fa-file-invoice-dollar color-success"></i>
                <span>Sales History</span>
            </a>
            <a href="<?= site_url('logout') ?>" class="premium-action-btn glass-panel">
                <i class="fas fa-sign-out-alt color-danger"></i>
                <span>Logout</span>
            </a>
        </div>

        <!-- STAFF INFORMATION -->
        <div class="premium-section-header">
            <i class="fas fa-user-tie"></i>
            <h3>Staff Information</h3>
        </div>
        <div class="premium-info-section">
            <div class="premium-info-box glass-panel">
                <div class="premium-info-item">
                    <span class="premium-info-label">Full Name</span>
                    <span class="premium-info-value"><?= esc($username) ?></span>
                </div>
                <div class="premium-info-item">
                    <span class="premium-info-label">Access Level</span>
                    <span class="premium-info-value">Staff Member</span>
                </div>
                <div class="premium-info-item">
                    <span class="premium-info-label">Status</span>
                    <span class="premium-info-value color-success">Active</span>
                </div>
            </div>
            
            <div class="premium-info-box glass-panel" style="display: flex; flex-direction: column; justify-content: center; align-items: flex-start;">
                <p style="color: rgba(255, 255, 255, 0.5); margin: 0; font-size: 0.95rem; line-height: 1.6;">
                    You have full access to manage products, view orders, and track sales performance for Mj Pogi Seafood.
                </p>
            </div>
        </div>

        <a href="<?= site_url('logout') ?>" class="premium-logout-btn">
            <i class="fas fa-power-off"></i>
            <span>Secure Logout</span>
        </a>

    </main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const el = document.getElementById('salesTrendChart');
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
<?= $this->include('theme/footer') ?>