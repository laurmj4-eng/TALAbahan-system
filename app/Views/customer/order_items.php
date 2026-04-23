<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | TALAbahan</title>
   
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   
    <style>
        /* GLOBAL THEME */
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
        }

        .main-content { flex: 1; padding: 40px; overflow-y: auto; }

        .order-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            transition: 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-card:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: #818cf8;
            transform: scale(1.01);
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
        .status-completed { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-cancelled { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

        .order-info h3 { margin: 0; font-size: 1.2rem; letter-spacing: 1px; }
        .order-info p { margin: 5px 0 0 0; color: rgba(255, 255, 255, 0.5); font-size: 0.9rem; }

        .order-amount { font-size: 1.5rem; font-weight: 800; color: #10b981; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <main class="main-content">
        <div style="margin-bottom: 40px;">
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 10px;">My Orders 📦</h1>
            <p style="color: rgba(255,255,255,0.6);">Track your fresh seafood deliveries and history.</p>
        </div>

        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $o): ?>
                <div class="order-card glass-panel">
                    <div class="order-info">
                        <h3><?= esc($o['transaction_code']) ?></h3>
                        <p><i class="far fa-calendar-alt"></i> <?= date('M d, Y h:i A', strtotime($o['created_at'])) ?></p>
                        <p><i class="fas fa-wallet"></i> <?= esc($o['payment_method']) ?></p>
                    </div>
                    
                    <div style="text-align: right;">
                        <div class="order-amount">₱<?= number_format($o['total_amount'], 2) ?></div>
                        <div style="margin-top: 10px;">
                            <?php 
                                $statusClass = 'status-pending';
                                if ($o['status'] === 'Completed') $statusClass = 'status-completed';
                                if ($o['status'] === 'Cancelled') $statusClass = 'status-cancelled';
                            ?>
                            <span class="status-badge <?= $statusClass ?>"><?= esc($o['status']) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 100px; opacity: 0.5;">
                <i class="fas fa-box-open" style="font-size: 4rem; margin-bottom: 20px;"></i>
                <h2>No orders yet</h2>
                <p>Start your first seafood order from the dashboard!</p>
                <a href="<?= site_url('customer/dashboard') ?>" class="btn-buy" style="display: inline-flex; width: auto; padding: 15px 30px; margin-top: 20px; text-decoration: none;">
                    Go to Shop
                </a>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
