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

        /* IMPROVED SIDEBAR LAYOUT - MODERN DESIGN */
        .sidebar { 
            width: var(--sidebar-width); 
            min-width: var(--sidebar-width);
            display: flex; 
            flex-direction: column; 
            z-index: 10; 
            border-right: 1px solid rgba(255,255,255,0.08);
            background: rgba(0, 0, 0, 0.3); 
            backdrop-filter: blur(20px);
        }
        
        .sidebar-header { 
            padding: 28px 20px; 
            text-align: center; 
            border-bottom: 2px solid rgba(168, 85, 247, 0.2);
            background: rgba(168, 85, 247, 0.08);
        }
        .sidebar-header h2 { 
            margin: 0 0 6px 0; 
            font-size: 1.3rem; 
            font-weight: 700; 
            color: #fff; 
            letter-spacing: 0.5px;
        }
        .sidebar-header small {
            display: block;
            color: rgba(168, 85, 247, 0.9);
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .sidebar-menu { 
            list-style: none !important; 
            padding: 20px 8px; 
            margin: 0; 
            flex: 1; 
            overflow-y: auto; 
        }

        .sidebar-section {
            margin-bottom: 24px;
        }

        .sidebar-section-title {
            padding: 0 16px 12px 16px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: rgba(168, 85, 247, 0.6);
            margin: 0;
        }

        .sidebar-menu li { 
            margin-bottom: 4px !important;
            list-style: none !important;
        }

        .sidebar-menu li:last-child {
            margin-top: auto;
        }

        .sidebar-menu a { 
            display: flex !important; 
            align-items: center; 
            justify-content: flex-start; 
            padding: 12px 16px; 
            color: rgba(255,255,255,0.75) !important; 
            text-decoration: none !important;
            font-weight: 500; 
            font-size: 0.95rem;
            transition: all 0.25s ease; 
            border-radius: 10px; 
            cursor: pointer;
            gap: 12px;
            border-left: 3px solid transparent;
            width: 100%;
            position: relative;
        }

        .sidebar-menu a span:first-child { 
            font-size: 1.3rem; 
            flex-shrink: 0;
            line-height: 1;
        }

        .sidebar-menu a:hover { 
            background: rgba(168, 85, 247, 0.12); 
            color: #e9d5ff !important;
            transform: translateX(3px);
            border-left-color: #a855f7;
        }

        .sidebar-menu a.active { 
            background: rgba(168, 85, 247, 0.2); 
            color: #c4b5fd !important;
            border-left-color: #a855f7;
            font-weight: 600;
            box-shadow: inset -3px 0 0 #a855f7;
        }

        .sidebar-footer {
            padding: 20px 8px;
            border-top: 2px solid rgba(255,255,255,0.05);
            background: rgba(0, 0, 0, 0.2);
        }

        .user-info {
            padding: 12px 16px;
            background: rgba(168, 85, 247, 0.1);
            border-radius: 10px;
            margin-bottom: 12px;
            text-align: center;
        }

        .user-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(168, 85, 247, 0.7);
            margin-bottom: 6px;
        }

        .user-name {
            font-size: 0.95rem;
            color: #e9d5ff;
            font-weight: 600;
            word-break: break-word;
        }

        .logout-btn { 
            display: flex !important;
            align-items: center;
            gap: 12px;
            color: #fecaca !important;
            padding: 12px 16px !important;
            background: rgba(248, 113, 113, 0.12) !important;
            border: 1px solid rgba(248, 113, 113, 0.3) !important;
            border-radius: 10px !important;
            width: 100%;
            margin: 0 !important;
            padding: 12px 16px !important;
            transition: all 0.25s ease;
        }

        .logout-btn span:first-child {
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .logout-btn:hover { 
            background: rgba(248, 113, 113, 0.2) !important; 
            color: #ffb4a0 !important;
            transform: translateX(-2px);
            border-color: rgba(248, 113, 113, 0.5) !important;
        }
        
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

        /* Global pagination cleanup: remove white bullets and style links */
        nav[aria-label="Page navigation"] { margin-top: 14px; }
        ul.pagination {
            list-style: none !important;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        ul.pagination li {
            list-style: none !important;
            margin: 0;
            padding: 0;
        }
        /* Hide pagination when only one page exists */
        ul.pagination li:only-child {
            display: none;
        }
        ul.pagination:has(li:only-child) {
            display: none;
        }
        ul.pagination .page-link {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 10px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.16);
            background: rgba(0,0,0,0.18);
        }
        /* Fallback for CI pager templates that don't use .page-link */
        ul.pagination a,
        ul.pagination span {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 10px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.16);
            background: rgba(0,0,0,0.18);
            line-height: 1;
            min-width: 34px;
            text-align: center;
        }
        ul.pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-color: transparent;
            color: #fff;
            font-weight: 600;
        }
        ul.pagination .active a,
        ul.pagination .active span {
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-color: transparent;
            color: #fff;
            font-weight: 600;
        }
        ul.pagination .page-item.disabled .page-link {
            opacity: 0.45;
            pointer-events: none;
        }
        ul.pagination .disabled a,
        ul.pagination .disabled span {
            opacity: 0.45;
            pointer-events: none;
        }
    </style>
</head>
<body>