<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | TALAbahan System</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border-radius: 20px;
        }

        .main-content { 
            flex: 1; 
            padding: 40px; 
            overflow-y: auto; 
            display: flex;
            flex-direction: column;
        }

        .header {
            margin-bottom: 30px;
        }

        h1 { 
            margin: 0 0 10px 0; 
            font-weight: 700; 
            font-size: 2.5rem; 
            color: #fff; 
            text-shadow: 0 2px 10px rgba(0,0,0,0.3); 
        }

        .status-text {
            color: rgba(255,255,255,0.6);
            font-size: 1rem;
            margin-bottom: 20px;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            padding: 25px;
            border-radius: 16px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 40px rgba(168, 85, 247, 0.2);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 15px 0;
            background: linear-gradient(135deg, #a855f7, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: rgba(255,255,255,0.7);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .stat-card.today { border-left: 4px solid #10b981; }
        .stat-card.products { border-left: 4px solid #f59e0b; }
        .stat-card.low-stock { border-left: 4px solid #ef4444; }
        .stat-card.out-stock { border-left: 4px solid #e11d48; }

        /* Quick Action Buttons */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .action-btn {
            padding: 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            border: none;
        }

        .action-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 40px rgba(168, 85, 247, 0.3);
        }

        .action-btn.primary { background: rgba(168, 85, 247, 0.2); border: 1px solid rgba(168, 85, 247, 0.3); }
        .action-btn.secondary { background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.3); }
        .action-btn.info { background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.3); }

        .emoji { font-size: 1.5rem; }

        h3 { 
            margin-top: 30px; 
            margin-bottom: 15px;
            font-size: 1.3rem;
            color: #fff;
            font-weight: 600;
        }

        .alert {
            padding: 18px 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .alert.warning {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fecaca;
        }

        .alert.info {
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.4);
            color: #bfdbfe;
        }

        .info-box {
            padding: 20px;
            border-left: 4px solid rgba(168, 85, 247, 0.5);
            background: rgba(168, 85, 247, 0.08);
            border-radius: 12px;
        }

        .info-box p {
            margin: 10px 0;
            color: rgba(255, 255, 255, 0.9);
        }

        .logout-btn {
            display: inline-block;
            margin-top: 30px;
            color: #f87171;
            text-decoration: none;
            font-weight: 700;
            padding: 12px 25px;
            background: rgba(248, 113, 113, 0.1);
            border-radius: 12px;
            border: 1px solid rgba(248, 113, 113, 0.2);
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: rgba(248, 113, 113, 0.2);
            transform: translateY(-2px);
        }

        hr {
            border: 0;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 25px 0;
        }

        @media (max-width: 768px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }
            h1 {
                font-size: 1.8rem;
            }
            .main-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <!-- MAIN CONTENT AREA -->
    <main class="main-content">
        <div class="header">
            <h1>👋 Welcome, <?= esc($username) ?>!</h1>
            <p class="status-text">Staff Dashboard - Manage Products, Orders & Inventory</p>
        </div>

        <!-- KEY STATISTICS CARDS -->
        <div class="cards-grid">
            <div class="stat-card glass-panel today">
                <div class="stat-label">📦 Today's Orders</div>
                <div class="stat-value"><?= $cards['today_orders'] ?? 0 ?></div>
                <small style="color: rgba(255,255,255,0.5);">Completed orders</small>
            </div>
            
            <div class="stat-card glass-panel products">
                <div class="stat-label">🛍️ Total Products</div>
                <div class="stat-value"><?= $cards['total_products'] ?? 0 ?></div>
                <small style="color: rgba(255,255,255,0.5);">In inventory</small>
            </div>
            
            <div class="stat-card glass-panel low-stock">
                <div class="stat-label">⚠️ Low Stock</div>
                <div class="stat-value"><?= $cards['low_stock_count'] ?? 0 ?></div>
                <small style="color: rgba(255,255,255,0.5);">Below 5 units</small>
            </div>
            
            <div class="stat-card glass-panel out-stock">
                <div class="stat-label">🚨 Out of Stock</div>
                <div class="stat-value"><?= $cards['out_of_stock'] ?? 0 ?></div>
                <small style="color: rgba(255,255,255,0.5);">Need restocking</small>
            </div>
        </div>

        <!-- ALERTS -->
        <?php if ($cards['out_of_stock'] > 0): ?>
        <div class="alert warning">
            ⚠️ <strong><?= $cards['out_of_stock'] ?> product(s) out of stock!</strong> Check inventory and reorder immediately.
        </div>
        <?php endif; ?>

        <?php if ($cards['low_stock_count'] > 0): ?>
        <div class="alert info">
            ℹ️ <strong><?= $cards['low_stock_count'] ?> product(s) running low.</strong> Consider restocking soon.
        </div>
        <?php endif; ?>

        <!-- QUICK ACTION BUTTONS -->
        <h3>🚀 Quick Actions</h3>
        <div class="quick-actions">
            <a href="<?= site_url('staff/products') ?>" class="action-btn glass-panel primary">
                <span class="emoji">📦</span>
                <span>Manage Products</span>
            </a>
            
            <a href="<?= site_url('staff/orders') ?>" class="action-btn glass-panel secondary">
                <span class="emoji">📋</span>
                <span>View Orders</span>
            </a>
            
            <a href="<?= site_url('staff/salesHistory') ?>" class="action-btn glass-panel info">
                <span class="emoji">💰</span>
                <span>Sales History</span>
            </a>
        </div>

        <hr>

        <!-- STAFF INFORMATION -->
        <h3>📋 Staff Information</h3>
        <div class="glass-panel info-box">
            <p><strong>Username:</strong> <?= esc($username) ?></p>
            <p><strong>Role:</strong> Staff Member</p>
            <p style="color: rgba(255,255,255,0.6); margin-bottom: 0;">You have full access to manage products, view orders, and track sales.</p>
        </div>

        <a href="<?= site_url('logout') ?>" class="logout-btn">⚡ Secure Logout</a>

    </main>

</body>
</html>