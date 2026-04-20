<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Mj Pogi Portal' ?></title>
    
    <link rel="stylesheet" href="/css/chat-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>
    
    <style>
        :root {
            /* Standard modern dashboard sidebar width */
            --sidebar-width: 260px; 
        }

        /* FULL GLASSMORPHISM THEME */
        * { box-sizing: border-box; }
        
        body { 
            margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(120deg, #1e1b4b, #3b0764, #0f172a, #082f49);
            background-size: 300% 300%;
            animation: gradientBg 15s ease infinite;
            color: #ffffff; 
            display: flex; 
            height: 100vh; 
            overflow: hidden; /* Prevents whole page from scrolling, only main-content scrolls */
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

        /* IMPROVED SIDEBAR LAYOUT */
        .sidebar { 
            width: var(--sidebar-width); 
            min-width: var(--sidebar-width);
            display: flex; 
            flex-direction: column; 
            z-index: 10; 
            border-right: 1px solid rgba(255,255,255,0.08); /* Clean dividing line */
            background: rgba(0, 0, 0, 0.2); 
            backdrop-filter: blur(20px);
        }
        
        .sidebar-header { padding: 30px 24px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header h2 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; letter-spacing: 1px; }
        
        .sidebar-menu { list-style: none; padding: 20px 10px; margin: 0; flex: 1; overflow-y: auto; }
        .sidebar-menu a { 
            display: flex; align-items: center; justify-content: flex-start; 
            padding: 14px 18px; margin-bottom: 5px; color: rgba(255,255,255,0.7); 
            text-decoration: none; font-weight: 500; transition: 0.3s ease; border-radius: 12px; cursor: pointer; 
        }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.15); color: #fff; transform: translateX(5px); }
        .logout-btn { color: #f87171 !important; margin-top: auto; }
        .logout-btn:hover { background: rgba(248, 113, 113, 0.2) !important; }
        
        /* IMPROVED MAIN AREA - Edited to expand fully */
        .main-content { 
            flex: 1; 
            padding: 40px; 
            overflow-y: auto; /* Content scrolls beautifully inside this container */
            height: 100vh;
            max-width: none !important; /* Overrides the 800px limit from chat-style.css */
            width: 100% !important;
            margin: 0 !important;
        }

        .tab-section { display: none; animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .tab-section.active { display: block; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* UI/UX Remains exactly the same below */
        .card { border-radius: 24px; padding: 35px; margin-bottom: 25px; position: relative; overflow: hidden; }
        .card::before { content: ""; position: absolute; top: 0; left: -50%; width: 50%; height: 100%; background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent); transform: skewX(-25deg); animation: shine 6s infinite; }
        @keyframes shine { 0% { left: -50%; } 100% { left: 150%; } }
        .card h2 { margin-top: 0; font-weight: 700; font-size: 2rem; color: #fff; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        
        .premium-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; align-items: end; margin-bottom: 35px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-size: 0.85rem; font-weight: 500; margin-bottom: 8px; color: rgba(255,255,255,0.8); text-transform: uppercase; letter-spacing: 0.5px;}
        .premium-form input, .premium-form select { padding: 15px; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-family: inherit; font-size: 1rem; color: #fff; background: rgba(0,0,0,0.2); backdrop-filter: blur(10px); transition: 0.3s; }
        .premium-form input:focus, .premium-form select:focus { outline: none; border-color: #818cf8; background: rgba(0,0,0,0.4); box-shadow: 0 0 15px rgba(129, 140, 248, 0.4); }
        .premium-form select option { background: #1e1b4b; color: #fff; }
        
        .btn-primary { padding: 15px 25px; background: linear-gradient(135deg, #6366f1, #a855f7); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(168, 85, 247, 0.4); height: 53px; }
        .btn-primary:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 8px 25px rgba(168, 85, 247, 0.6); }

        .table-responsive { overflow-x: auto; border-radius: 16px; margin-top: 20px;}
        .premium-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 700px; }
        .premium-table th { background: rgba(0,0,0,0.3); padding: 18px 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.6); text-align: left; }
        .premium-table td { padding: 18px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; }
        .premium-table tr:hover td { background: rgba(255,255,255,0.05); }
        .action-cell { white-space: nowrap; width: 1%; }

        .role-badge { padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .role-admin { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .role-staff { background: rgba(56, 189, 248, 0.2); color: #7dd3fc; border: 1px solid rgba(56, 189, 248, 0.3); }
        .role-customer { background: rgba(255, 255, 255, 0.1); color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.2); }
        
        .action-btns { display: inline-flex; gap: 10px; }
        .btn-edit, .btn-delete { padding: 10px 18px; border-radius: 10px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.3s; border: none; cursor:pointer;}
        .btn-edit { background: rgba(245, 158, 11, 0.2); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.3); }
        .btn-delete { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        
        .alert { padding: 18px 24px; background: rgba(16, 185, 129, 0.2); color: #a7f3d0; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 16px; margin-bottom: 25px; font-weight: 500; }
    </style>
</head>
<body>