a<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | TALAbahan System</title>
    
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* GLOBAL THEME (Matching Admin/Staff) */
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

        /* Main Content Layout */
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }

        .card { border-radius: 24px; padding: 35px; margin-bottom: 25px; position: relative; overflow: hidden; }
        .card::before { 
            content: ""; position: absolute; top: 0; left: -50%; width: 50%; height: 100%; 
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent); 
            transform: skewX(-25deg); animation: shine 6s infinite; 
        }
        @keyframes shine { 0% { left: -50%; } 100% { left: 150%; } }
        
        h1 { margin-top: 0; font-weight: 700; font-size: 2.5rem; color: #fff; }
        p { color: rgba(255,255,255,0.7); font-size: 1.1rem; }

        /* Customer Feature Grid */
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .feature-card { 
            padding: 25px; border-radius: 20px; text-align: center; 
            transition: 0.3s; cursor: pointer; border: 1px solid rgba(255,255,255,0.1);
        }
        .feature-card:hover { 
            background: rgba(255,255,255,0.1); transform: translateY(-5px); 
            border-color: #818cf8; 
        }
        .feature-icon { font-size: 3rem; margin-bottom: 15px; display: block; }

        .btn-logout { 
            display: inline-block; margin-top: 20px; color: #f87171; 
            text-decoration: none; font-weight: 700; padding: 12px 25px; 
            background: rgba(248, 113, 113, 0.1); border-radius: 12px; 
            border: 1px solid rgba(248, 113, 113, 0.2); transition: 0.3s;
        }
        .btn-logout:hover { background: rgba(248, 113, 113, 0.2); transform: translateY(-2px); }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="card glass-panel">
            <h1>Welcome, <?= esc($username) ?>!</h1>
            <p>Welcome to your Seafood Portal. From here you can browse our fresh daily catch, track your orders, and manage your account settings.</p>
            
            <a href="<?= site_url('logout') ?>" class="btn-logout">Secure Logout</a>
        </div>

        <div class="feature-grid">
            <!-- Feature 1: Menu -->
            <div class="feature-card glass-panel">
                <span class="feature-icon">🐟</span>
                <h3>Fresh Menu</h3>
                <p style="font-size: 0.9rem;">Browse today's catch and current prices.</p>
            </div>

            <!-- Feature 2: Orders -->
            <div class="feature-card glass-panel">
                <span class="feature-icon">🛍️</span>
                <h3>My Orders</h3>
                <p style="font-size: 0.9rem;">View your previous purchases and receipts.</p>
            </div>

            <!-- Feature 3: Profile -->
            <div class="feature-card glass-panel">
                <span class="feature-icon">👤</span>
                <h3>Account Settings</h3>
                <p style="font-size: 0.9rem;">Update your profile and security protocols.</p>
            </div>
        </div>

        <!-- System Status Footer -->
        <div style="margin-top: 50px; text-align: center; color: rgba(255,255,255,0.3); font-size: 0.8rem;">
            TALAbahan Mainframe v2.0 | High-Fidelity Customer Node
        </div>
    </main>

</body>
</html>