<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | Customer Dashboard</title>
   
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
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

        /* --- ENHANCED WELCOME BANNER --- */
        .welcome-card { 
            border-radius: 30px; 
            padding: 50px; 
            margin-bottom: 30px; 
            position: relative; 
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
        }
        
        .welcome-card::before {
            content: ""; position: absolute; top: 0; left: -50%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent);
            transform: skewX(-25deg); animation: shine 6s infinite;
        }
        @keyframes shine { 0% { left: -50%; } 100% { left: 150%; } }

        .welcome-text h1 { 
            margin: 0; 
            font-weight: 800; 
            font-size: 3rem; 
            letter-spacing: -1px;
            line-height: 1.1;
        }
        
        .name-gradient {
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-text p { 
            color: rgba(255,255,255,0.6); 
            font-size: 1.2rem; 
            margin-top: 15px;
            max-width: 600px;
        }

        .welcome-badges { display: flex; gap: 15px; margin-top: 25px; }
        .badge { 
            padding: 8px 16px; 
            border-radius: 12px; 
            background: rgba(255,255,255,0.08); 
            font-size: 0.85rem; 
            font-weight: 600; 
            color: #a7f3d0;
            border: 1px solid rgba(167, 243, 208, 0.2);
        }

        .btn-logout {
            display: inline-block; margin-top: 30px; color: #f87171;
            text-decoration: none; font-weight: 700; padding: 12px 25px;
            background: rgba(248, 113, 113, 0.1); border-radius: 12px;
            border: 1px solid rgba(248, 113, 113, 0.2); transition: 0.3s;
        }
        .btn-logout:hover { background: rgba(248, 113, 113, 0.2); transform: translateY(-2px); }

        /* --- QUICK ACTIONS & PRODUCTS --- */
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .feature-card {
            padding: 15px; border-radius: 16px; text-align: center;
            transition: 0.3s; cursor: pointer; border: 1px solid rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .feature-card:hover { background: rgba(255,255,255,0.1); transform: translateY(-3px); border-color: #818cf8; }

        .store-header { border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; margin-bottom: 25px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .product-card {
            background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05);
            border-radius: 20px; padding: 25px 20px; text-align: center;
            transition: 0.3s; backdrop-filter: blur(10px); display: flex; flex-direction: column;
        }
        .product-card:hover { transform: translateY(-5px); border-color: #10b981; background: rgba(16, 185, 129, 0.05); }
        .product-price { color: #10b981; font-size: 1.5rem; font-weight: 700; }
        
        .btn-buy {
            background: linear-gradient(135deg, #10b981, #059669); color: white; border: none;
            padding: 12px; border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.3s;
            margin-top: auto; width: 100%;
        }
        .btn-buy:hover { box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4); transform: scale(1.05); }
        
        .out-of-stock { opacity: 0.4; filter: grayscale(100%); pointer-events: none; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        
        <!-- --- NEW PREMIUM WELCOME BANNER --- -->
        <?php
            $hour = date('H');
            if ($hour < 12) $greeting = "Good Morning";
            elseif ($hour < 18) $greeting = "Good Afternoon";
            else $greeting = "Good Evening";
        ?>

        <div class="welcome-card glass-panel">
            <div class="welcome-text">
                <h1><?= $greeting ?>, <br><span class="name-gradient"><?= esc($username) ?></span>!</h1>
                <p>Welcome back to the <strong>TALAbahan Mainframe</strong>. The ocean's finest bounty is harvested and ready for your selection today.</p>
                
                <div class="welcome-badges">
                    <div class="badge">✓ Verified Customer Node</div>
                    <div class="badge" style="color: #60a5fa; border-color: rgba(96, 165, 250, 0.2);">✦ Fresh Catch Online</div>
                </div>

                <a href="<?= site_url('logout') ?>" class="btn-logout">Disconnect Terminal</a>
            </div>
            
            <!-- Large decorative icon -->
            <div style="font-size: 8rem; opacity: 0.2; transform: rotate(10deg); user-select: none;">
                🌊
            </div>
        </div>

        <!-- Quick Links -->
        <div class="feature-grid">
            <div class="feature-card glass-panel">
                <span style="font-size: 1.5rem;">🛍️</span>
                <h4 style="margin:0;">Track Orders</h4>
            </div>
            <div class="feature-card glass-panel">
                <span style="font-size: 1.5rem;">👤</span>
                <h4 style="margin:0;">Profile Settings</h4>
            </div>
            <div class="feature-card glass-panel">
                <span style="font-size: 1.5rem;">💬</span>
                <h4 style="margin:0;">Support AI</h4>
            </div>
        </div>

        <!-- STOREFRONT SECTION -->
        <div class="card glass-panel" style="padding: 40px; border-radius: 30px;">
            <div class="store-header">
                <h2 style="margin: 0; color: #fff; font-size: 1.8rem;">💎 Today's Premium Selection</h2>
                <p style="color: rgba(255,255,255,0.5); margin: 5px 0 0 0;">Stock levels are live from our central inventory.</p>
            </div>

            <div class="product-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                        <div class="product-card <?= ($p['current_stock'] <= 0) ? 'out-of-stock' : '' ?>">
                            <h3 style="margin-bottom: 5px;"><?= esc($p['name']) ?></h3>
                            <div class="product-price">₱<?= number_format($p['selling_price'], 2) ?></div>
                            <div style="font-size: 0.8rem; color: rgba(255,255,255,0.4); margin-bottom: 20px;">Unit: <?= esc($p['unit']) ?></div>
                            
                            <?php if ($p['current_stock'] > 0): ?>
                                <button class="btn-buy" onclick="alert('Added to cart!')">
                                    + Add to Cart
                                </button>
                            <?php else: ?>
                                <button class="btn-buy" style="background: rgba(255,255,255,0.1); color: #666;" disabled>Sold Out</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px; opacity: 0.5;">
                        <span style="font-size: 3rem;">🛰️</span>
                        <p>No products detected in the mainframe database.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top: 50px; text-align: center; color: rgba(255,255,255,0.2); font-size: 0.75rem; letter-spacing: 2px; text-transform: uppercase;">
            TALAbahan Security Protocol Active | Node: <?= esc($username) ?>
        </div>
    </main>

</body>
</html>