<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | TALAbahan System</title>
    
    <!-- Using the same Premium Fonts and Styles as Admin -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* FULL GLASSMORPHISM THEME (Matching Admin) */
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

        /* Main Area */
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }

        /* Premium Glass Cards */
        .card { border-radius: 24px; padding: 35px; margin-bottom: 25px; position: relative; overflow: hidden; }
        .card::before { 
            content: ""; position: absolute; top: 0; left: -50%; width: 50%; height: 100%; 
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent); 
            transform: skewX(-25deg); animation: shine 6s infinite; 
        }
        @keyframes shine { 0% { left: -50%; } 100% { left: 150%; } }
        
        h1 { margin-top: 0; font-weight: 700; font-size: 2.5rem; color: #fff; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        p { color: rgba(255,255,255,0.7); font-size: 1.1rem; line-height: 1.6; }

        .logout { 
            display: inline-block; margin-top: 20px; color: #f87171; 
            text-decoration: none; font-weight: 700; padding: 12px 25px; 
            background: rgba(248, 113, 113, 0.1); border-radius: 12px; 
            border: 1px solid rgba(248, 113, 113, 0.2); transition: 0.3s;
        }
        .logout:hover { background: rgba(248, 113, 113, 0.2); transform: translateY(-2px); }
    </style>
</head>
<body>

    <!-- INJECTING THE SIDEBAR (Ensure this file exists in Views/theme/) -->
    <?= $this->include('theme/sidebar') ?>

    <!-- MAIN CONTENT AREA -->
    <main class="main-content">
        <div class="card glass-panel">
            <h1>Welcome to the Staff Dashboard, <?= esc($username) ?>!</h1>
            <p>This is where staff members can manage AI chat logs and user inquiries. All operations are logged in the secure cloud mainframe.</p>
            
            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 30px 0;">
            
            <!-- Using site_url for proper routing -->
            <a href="<?= site_url('logout') ?>" class="logout">Secure Logout</a>
        </div>

        <!-- Placeholder for Staff-Specific Tabs -->
        <div class="card glass-panel" style="background: rgba(129, 140, 248, 0.1);">
            <h3 style="margin-top: 0;">⚡ Staff Quick Actions</h3>
            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <div style="padding: 20px; background: rgba(255,255,255,0.05); border-radius: 15px; flex: 1; text-align: center;">
                    <span style="font-size: 2rem;">💬</span>
                    <h4 style="margin: 10px 0 5px 0;">View Inquiries</h4>
                    <small style="color: rgba(255,255,255,0.5);">3 New Messages</small>
                </div>
                <div style="padding: 20px; background: rgba(255,255,255,0.05); border-radius: 15px; flex: 1; text-align: center;">
                    <span style="font-size: 2rem;">📜</span>
                    <h4 style="margin: 10px 0 5px 0;">Chat Logs</h4>
                    <small style="color: rgba(255,255,255,0.5);">Review AI history</small>
                </div>
            </div>
        </div>
    </main>

</body>
</html>