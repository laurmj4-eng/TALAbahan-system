<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | TALAbahan System</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        
        body { 
            margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(120deg, #1e1b4b, #3b0764, #0f172a, #082f49);
            background-size: 300% 300%;
            animation: gradientBg 15s ease infinite;
            color: #ffffff; display: flex; height: 100vh; overflow: hidden; 
        }
        
        @keyframes gradientBg {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            border-radius: 24px;
        }

        .main-content { 
            flex: 1; 
            padding: 40px 60px; 
            overflow-y: auto; 
            display: flex;
            flex-direction: column;
            background: rgba(0, 0, 0, 0.2);
        }

        /* PREMIUM DASHBOARD STYLES */
        .premium-title { 
            margin: 0 0 10px 0; 
            font-weight: 800; 
            font-size: 3rem; 
            color: #fff; 
            letter-spacing: -1px;
            background: linear-gradient(to right, #fff, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .premium-status-text {
            color: rgba(255,255,255,0.5);
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 40px;
        }

        .premium-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .premium-stat-card {
            padding: 30px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
        }

        .premium-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.05), transparent);
            transition: 0.5s;
        }

        .premium-stat-card:hover::before { left: 100%; }

        .premium-stat-card:hover {
            transform: translateY(-10px);
            border-color: rgba(168, 85, 247, 0.4);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .premium-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
        }

        .premium-stat-value {
            font-size: 3rem;
            font-weight: 800;
            margin: 10px 0;
            line-height: 1;
            letter-spacing: -1px;
        }

        .premium-stat-label {
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .premium-stat-desc {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.3);
            margin-top: 8px;
        }

        /* Premium Colors */
        .color-success { color: #10b981 !important; }
        .color-warning { color: #f59e0b !important; }
        .color-danger { color: #ef4444 !important; }
        .color-info { color: #3b82f6 !important; }
        .color-premium { color: #a855f7 !important; }

        .bg-success { background: rgba(16, 185, 129, 0.1) !important; }
        .bg-warning { background: rgba(245, 158, 11, 0.1) !important; }
        .bg-danger { background: rgba(239, 68, 68, 0.1) !important; }
        .bg-info { background: rgba(59, 130, 246, 0.1) !important; }
        .bg-premium { background: rgba(168, 85, 247, 0.1) !important; }

        .premium-section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .premium-section-header i { color: #a855f7; font-size: 1.2rem; }
        .premium-section-header h3 { margin: 0; font-size: 1.4rem; color: #fff; font-weight: 700; letter-spacing: -0.5px; }

        .premium-quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .premium-action-btn {
            padding: 24px;
            border-radius: 24px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: #fff;
            display: flex;
            flex-direction: column;
            gap: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(255, 255, 255, 0.02);
        }

        .premium-action-btn i { font-size: 1.8rem; transition: transform 0.3s; }
        .premium-action-btn span { font-weight: 700; font-size: 1.1rem; letter-spacing: -0.2px; }
        .premium-action-btn:hover {
            transform: translateY(-5px);
            background: rgba(168, 85, 247, 0.1);
            border-color: rgba(168, 85, 247, 0.3);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        .premium-action-btn:hover i { transform: scale(1.1) rotate(-5deg); color: #a855f7; }

        .premium-alert {
            padding: 20px 24px;
            border-radius: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            font-weight: 500;
        }

        .premium-info-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .premium-info-box {
            padding: 30px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .premium-info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .premium-info-item:last-child { border-bottom: none; }
        .premium-info-label { color: rgba(255, 255, 255, 0.4); font-weight: 500; font-size: 0.9rem; }
        .premium-info-value { color: #fff; font-weight: 600; }

        .premium-logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: #fca5a5;
            text-decoration: none;
            font-weight: 700;
            padding: 18px;
            background: rgba(239, 68, 68, 0.05);
            border-radius: 16px;
            border: 1px solid rgba(239, 68, 68, 0.1);
            transition: 0.3s;
            max-width: 200px;
        }
        .premium-logout-btn:hover {
            background: rgba(239, 68, 68, 0.15);
            transform: translateY(-2px);
            border-color: rgba(239, 68, 68, 0.3);
            color: #fff;
        }

        @media (max-width: 1024px) {
            .main-content { padding: 40px; }
        }

        @media (max-width: 768px) {
            .premium-title { font-size: 2.2rem; }
            .premium-cards-grid { grid-template-columns: 1fr; }
            .main-content { padding: 20px; }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <!-- MAIN CONTENT AREA -->
    <main class="main-content">
        <div class="header">
            <h1 class="premium-title">Welcome, <?= esc($username) ?>!</h1>
            <p class="premium-status-text">Staff Dashboard - Manage Products, Orders & Inventory</p>
        </div>

        <!-- KEY STATISTICS CARDS -->
        <div class="premium-cards-grid">
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-success color-success"><i class="fas fa-shopping-bag"></i></div>
                <div class="premium-stat-label">Today's Orders</div>
                <div class="premium-stat-value color-success"><?= $cards['today_orders'] ?? 0 ?></div>
                <div class="premium-stat-desc">Successfully completed</div>
            </div>
            
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-warning color-warning"><i class="fas fa-boxes-stacked"></i></div>
                <div class="premium-stat-label">Total Products</div>
                <div class="premium-stat-value color-warning"><?= $cards['total_products'] ?? 0 ?></div>
                <div class="premium-stat-desc">Available in inventory</div>
            </div>
            
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-premium color-premium"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="premium-stat-label">Low Stock</div>
                <div class="premium-stat-value color-premium"><?= $cards['low_stock_count'] ?? 0 ?></div>
                <div class="premium-stat-desc">Items below 5 units</div>
            </div>
            
            <div class="premium-stat-card glass-panel">
                <div class="premium-stat-icon bg-danger color-danger"><i class="fas fa-circle-exclamation"></i></div>
                <div class="premium-stat-label">Out of Stock</div>
                <div class="premium-stat-value color-danger"><?= $cards['out_of_stock'] ?? 0 ?></div>
                <div class="premium-stat-desc">Need immediate restocking</div>
            </div>
        </div>

        <!-- ALERTS -->
        <?php if ($cards['out_of_stock'] > 0): ?>
        <div class="premium-alert warning">
            <i class="fas fa-triangle-exclamation"></i>
            <div>
                <strong>Action Required:</strong> <?= $cards['out_of_stock'] ?> product(s) out of stock! Check inventory and reorder immediately.
            </div>
        </div>
        <?php endif; ?>

        <?php if ($cards['low_stock_count'] > 0): ?>
        <div class="premium-alert info">
            <i class="fas fa-circle-info"></i>
            <div>
                <strong>Stock Alert:</strong> <?= $cards['low_stock_count'] ?> product(s) running low. Consider restocking soon.
            </div>
        </div>
        <?php endif; ?>

        <!-- QUICK ACTION BUTTONS -->
        <div class="premium-section-header">
            <i class="fas fa-bolt"></i>
            <h3>Quick Actions</h3>
        </div>
        <div class="premium-quick-actions">
            <a href="<?= site_url('staff/products') ?>" class="premium-action-btn glass-panel">
                <i class="fas fa-box-open color-premium"></i>
                <span>Manage Products</span>
            </a>
            
            <a href="<?= site_url('staff/orders') ?>" class="premium-action-btn glass-panel">
                <i class="fas fa-clipboard-list color-success"></i>
                <span>View Orders</span>
            </a>
            
            <a href="<?= site_url('staff/salesHistory') ?>" class="premium-action-btn glass-panel">
                <i class="fas fa-chart-line color-info"></i>
                <span>Sales History</span>
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

</body>
</html>