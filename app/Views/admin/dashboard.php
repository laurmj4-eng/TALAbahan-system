<?php
/**
 * @var array $cards
 * @var array $chart
 * @var string $username
 */
?>
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
        <div class="header" style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1 class="premium-title">Welcome back, <?= esc(session()->get('username') ?? 'Admin') ?>!</h1>
                <p class="premium-status-text">Manage products, monitor sales, and track order activity in one clean owner dashboard.</p>
                <p style="font-size: 0.8rem; color: rgba(255,255,255,0.4); margin-top: -10px;">Server Time: <span id="server-time"><?= date('M d, Y h:i A') ?></span></p>
            </div>
            <button onclick="window.print()" class="premium-action-btn glass-panel" style="padding: 12px 24px; height: auto;">
                <i class="fas fa-print color-info"></i>
                <span>Print Daily Report</span>
            </button>
        </div>

        <div class="premium-cards-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-success color-success"><i class="fas fa-coins"></i></div>
                <div class="premium-stat-label">Today's Sales</div>
                <div class="premium-stat-value color-success">
                    &#8369;<?= number_format((float)($cards['today_sales'] ?? 0), 2) ?>
                    <?php if (isset($cards['sales_growth'])): ?>
                        <span style="font-size: 0.9rem; margin-left: 10px; font-weight: 600; color: <?= $cards['sales_growth'] >= 0 ? '#4ade80' : '#f87171' ?>;">
                            <?= $cards['sales_growth'] >= 0 ? '↑' : '↓' ?> <?= abs($cards['sales_growth']) ?>%
                        </span>
                    <?php endif; ?>
                </div>
                <div class="premium-stat-desc">Final total for today</div>
            </div>

            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-premium color-premium" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"><i class="fas fa-chart-pie"></i></div>
                <div class="premium-stat-label">Net Profit</div>
                <div class="premium-stat-value" style="color: #8b5cf6;">
                    &#8369;<?= number_format((float)($cards['today_profit'] ?? 0), 2) ?>
                </div>
                <div class="premium-stat-desc">Earnings after cost</div>
            </div>

            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-warning color-warning"><i class="fas fa-percentage"></i></div>
                <div class="premium-stat-label">Profit Margin</div>
                <div class="premium-stat-value color-warning"><?= $cards['profit_margin'] ?? 0 ?>%</div>
                <div class="premium-stat-desc">Profit to sales ratio</div>
            </div>
            
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-info color-info"><i class="fas fa-shopping-cart"></i></div>
                <div class="premium-stat-label">Today's Orders</div>
                <div class="premium-stat-value color-info"><?= (int)($cards['today_orders'] ?? 0) ?></div>
                <div class="premium-stat-desc">Transactions processed</div>
            </div>
        </div>

        <?php if (!empty($stale_orders)): ?>
            <div class="premium-alert warning glass-panel" style="margin-top: 25px; border-left: 4px solid #fbbf24; background: rgba(251, 191, 36, 0.05);">
                <i class="fas fa-exclamation-triangle" style="color: #fbbf24;"></i>
                <div style="flex: 1;">
                    <div style="font-weight: 700; color: #fbbf24;">Action Required: <?= count($stale_orders) ?> Stale Orders</div>
                    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">Some orders have been pending for more than 24 hours. Please review and update their status.</div>
                </div>
                <button onclick="switchTab('orders')" class="premium-action-btn" style="padding: 8px 15px; font-size: 0.8rem; background: #fbbf24; color: #000;">View Orders</button>
            </div>
        <?php endif; ?>

        <div class="premium-section-header">
            <i class="fas fa-chart-line"></i>
            <h3>Performance Overview</h3>
        </div>
        
        <div class="card glass-panel" id="chart-card" style="padding: 30px; border-radius: 24px;">
            <h2 style="font-size:1.5rem; margin-bottom: 8px;">7-Day Sales Trend</h2>
            <p style="color: rgba(255,255,255,0.4); margin-bottom: 24px; font-size: 0.95rem;">Connected to order history for live owner insights.</p>
            <div style="display: grid; grid-template-columns: 1fr 300px; gap: 30px;">
                <div class="chart-container" style="height: 350px; position: relative;">
                    <canvas id="overviewTrendChart"></canvas>
                </div>
                
                <div class="top-products-panel" style="background: rgba(255,255,255,0.03); border-radius: 20px; padding: 20px;">
                    <h4 style="font-size: 1rem; margin-bottom: 20px; color: #a5b4fc;">
                        <i class="fas fa-trophy" style="color: #fbbf24; margin-right: 8px;"></i> Top Products (30d)
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php if (!empty($top_products)): ?>
                            <?php foreach ($top_products as $index => $product): ?>
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <span style="font-size: 0.8rem; font-weight: 800; color: rgba(255,255,255,0.2);">#<?= $index + 1 ?></span>
                                        <span style="font-size: 0.95rem; font-weight: 600;"><?= esc($product['product_name']) ?></span>
                                    </div>
                                    <span style="font-size: 0.85rem; color: #4ade80; font-weight: 700;"><?= number_format($product['total_sold']) ?> sold</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 20px; color: rgba(255,255,255,0.2); font-size: 0.85rem;">
                                No sales data yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @media print {
                /* Hide sidebar, navigation, and background when printing */
                .sidebar, .mobile-toggle, .premium-quick-actions, .header button, .premium-section-header, .sidebar-overlay {
                    display: none !important;
                }
                
                body {
                    background: #ffffff !important;
                    color: #000000 !important;
                    height: auto !important;
                    overflow: visible !important;
                }
                
                .main-content {
                    margin-left: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                    overflow: visible !important;
                    background: #ffffff !important;
                }
                
                .glass-panel {
                    background: #ffffff !important;
                    border: 1px solid #eeeeee !important;
                    box-shadow: none !important;
                    backdrop-filter: none !important;
                    color: #000000 !important;
                    margin-bottom: 20px !important;
                }
                
                .premium-stat-card {
                    break-inside: avoid;
                    border: 2px solid #000000 !important;
                    padding: 20px !important;
                }
                
                .premium-stat-value {
                    color: #000000 !important;
                    font-size: 2.5rem !important;
                }
                
                .premium-stat-label, .premium-stat-desc {
                    color: #666666 !important;
                }
                
                #chart-card {
                    break-inside: avoid;
                    border: 1px solid #dddddd !important;
                }
                
                .top-products-panel {
                    background: #f9f9f9 !important;
                    border: 1px solid #eeeeee !important;
                    color: #000000 !important;
                }
                
                .top-products-panel h4, .top-products-panel span {
                    color: #000000 !important;
                }
                
                .premium-title {
                    color: #000000 !important;
                    font-size: 2rem !important;
                }
                
                .premium-status-text {
                    color: #333333 !important;
                    margin-bottom: 40px !important;
                }

                .chart-container {
                    display: none !important; /* Charts usually don't print well without extra work, focusing on text data */
                }

                #chart-card > div {
                    grid-template-columns: 1fr !important;
                }
                
                .top-products-panel {
                    width: 100% !important;
                }
            }

            @media (max-width: 992px) {
                #chart-card > div { grid-template-columns: 1fr !important; }
            }
            @media (max-width: 576px) {
                #chart-card { padding: 20px !important; }
                .chart-container { height: 250px !important; }
            }
        </style>

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
            <a onclick="switchTab('orders')" class="premium-action-btn glass-panel">
                <i class="fas fa-boxes color-info"></i>
                <span>Order Management</span>
            </a>
            <a href="<?= site_url('admin/users') ?>" class="premium-action-btn glass-panel">
                <i class="fas fa-users-cog color-warning"></i>
                <span>User Management</span>
            </a>
        </div>

        <div class="premium-section-header" style="margin-top: 40px;">
            <i class="fas fa-stream"></i>
            <h3>Live Activity Feed</h3>
        </div>
        
        <div class="glass-panel" style="padding: 25px; border-radius: 24px;">
            <?php if (!empty($activities)): ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($activities as $act): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="width: 40px; height: 40px; border-radius: 12px; background: <?= $act['color'] ?>20; color: <?= $act['color'] ?>; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fas <?= $act['icon'] ?>"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 0.95rem;"><?= esc($act['title']) ?></div>
                                    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.5);"><?= $act['desc'] ?></div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <time class="timeago" datetime="<?= date('c', strtotime($act['time'])) ?>" style="font-size: 0.75rem; color: rgba(255,255,255,0.3);">
                                    <?= date('h:i A', strtotime($act['time'])) ?>
                                </time>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.2);">
                    <i class="fas fa-ghost" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    No recent activity recorded.
                </div>
            <?php endif; ?>
        </div>

        <!-- Add timeago support -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timeago/1.6.7/jquery.timeago.min.js"></script>
        <script>
            $(document).ready(function() {
                $("time.timeago").timeago();

                // Function to fetch and update today's sales data
                function updateTodaySales() {
                    $.ajax({
                        url: '<?= site_url('admin/dashboard/todaySales') ?>',
                        method: 'GET',
                        dataType: 'json',
                        cache: false, // Prevent browser caching
                        data: { _: new Date().getTime() }, // Cache buster
                        success: function(response) {
                            if (response.today_sales !== undefined) {
                                // Update Today's Sales value
                                $('.premium-stat-card:first .premium-stat-value').html(
                                    '&#8369;' + parseFloat(response.today_sales).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) +
                                    (response.sales_growth !== undefined ? 
                                        '<span style="font-size: 0.9rem; margin-left: 10px; font-weight: 600; color: ' + (response.sales_growth >= 0 ? '#4ade80' : '#f87171') + ';">' +
                                        (response.sales_growth >= 0 ? '↑' : '↓') + ' ' + Math.abs(response.sales_growth) + '%' +
                                        '</span>'
                                    : '')
                                );
                            }
                            if (response.server_time !== undefined) {
                                $('#server-time').text(response.server_time);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching today's sales data:", error);
                        }
                    });
                }

                // Initial call to update sales data
                updateTodaySales();

                // Set interval to update sales data every 5 seconds
                setInterval(updateTodaySales, 5000); 
            });
        </script>
    </section>

    <!-- POS TAB -->
    <section id="tab-pos" class="tab-section">
        <?= $this->include('admin/pos_view') ?>
    </section>

    <!-- SALES HISTORY TAB -->
    <section id="tab-sales" class="tab-section">
        <?= $this->include('admin/sales_history_view') ?>
    </section>

    <!-- ORDER VIEW TAB -->
    <section id="tab-orders" class="tab-section">
        <?= $this->include('admin/orderview') ?>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const el = document.getElementById('overviewTrendChart');
    if (!el) return;

    const labels = <?= json_encode($chart['labels'] ?? []) ?>;
    const sales = <?= json_encode($chart['sales'] ?? []) ?>;

    // Empty state handling
    const isAllZero = sales.every(val => val === 0);
    const chartTitle = isAllZero ? '7-Day Trend (Awaiting first sale)' : '7-Day Sales Trend';

    new Chart(el, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Sales Revenue',
                    data: sales,
                    borderColor: isAllZero ? 'rgba(255,255,255,0.1)' : '#10b981',
                    backgroundColor: isAllZero ? 'transparent' : 'rgba(16, 185, 129, 0.18)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: isAllZero ? 0 : 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: !isAllZero,
                    labels: { color: '#e5e7eb' }
                },
                tooltip: {
                    enabled: !isAllZero,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(255,255,255,0.08)' } },
                y: {
                    beginAtZero: true,
                    suggestedMax: isAllZero ? 1000 : undefined,
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