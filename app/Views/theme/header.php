<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Mj Pogi Portal' ?></title>
    
    <link rel="stylesheet" href="<?= base_url('assets/css/chat-style.css') ?>?v=1.0.1">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    
    <style>
        :root {
            /* Standard modern dashboard sidebar width */
            --sidebar-width: 260px; 
        }

        /* MOBILE MENU TOGGLE */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100000; /* Higher than sidebar (99999) */
            background: rgba(168, 85, 247, 0.8);
            color: white;
            border: none;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* SIDEBAR OVERLAY */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 99998 !important; /* High z-index behind sidebar */
            transition: all 0.3s ease;
        }

        .sidebar-overlay.active {
            display: block !important;
        }

        /* FULL GLASSMORPHISM THEME */
        * { box-sizing: border-box; }
        
        body { 
            margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; 
            -webkit-text-size-adjust: 100%;
            -moz-text-size-adjust: 100%;
            text-size-adjust: 100%;
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

        /* Sidebar styles are now in theme/sidebar_styles.php included via theme/sidebar.php */
        
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

        @media (max-width: 576px) {
            .premium-cards-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .premium-stat-card {
                padding: 20px;
            }
            .premium-stat-value {
                font-size: 1.8rem;
            }
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

        @media (max-width: 576px) {
            .premium-quick-actions {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .premium-action-btn {
                padding: 16px 20px;
                flex-direction: row;
                align-items: center;
                gap: 16px;
            }
            .premium-action-btn i {
                font-size: 1.4rem;
            }
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

        /* FLEX HEADER THAT STACKS ON MOBILE */
        .flex-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        @media (max-width: 768px) {
            .flex-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .flex-header > div:last-child, 
            .flex-header > button:last-child {
                width: 100%;
            }
        }

        /* MODAL SYSTEM */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            padding: 20px;
        }

        .modal.show {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: rgba(20, 20, 45, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 32px;
            padding: 40px;
            width: 100%;
            max-width: 700px;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            transform: translateY(30px);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            max-height: 90vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(168, 85, 247, 0.5) transparent;
        }

        @media (max-width: 576px) {
            .modal-content {
                padding: 30px 20px;
                border-radius: 24px;
                margin: 10px;
            }
            .modal-header {
                font-size: 1.6rem;
            }
        }

        .modal.show .modal-content {
            transform: translateY(0);
        }

        .modal-close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.2rem;
            z-index: 100;
        }

        .modal-close-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.3);
            transform: rotate(90deg);
        }

        @media (max-width: 768px) {
            .modal-close-btn {
                top: 15px;
                right: 15px;
                width: 32px;
                height: 32px;
                font-size: 1.1rem;
            }
        }

        .modal-header {
            margin-top: 0;
            margin-bottom: 30px;
            font-size: 2.2rem;
            font-weight: 800;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 20px;
            background: linear-gradient(to right, #fff, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

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

        /* IMPROVED MAIN AREA - Edited to expand fully */
        .main-content { 
            flex: 1; 
            padding: 40px; 
            overflow-y: auto; 
            overflow-x: hidden; /* Prevent horizontal scroll on the main container */
            height: 100vh;
            max-width: none !important;
            width: 100% !important;
            margin: 0 !important;
            position: relative;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 80px 15px 15px 15px; /* Added top padding for mobile toggle space */
            }
        }

        .tab-section { display: none; animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .tab-section.active { display: block; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* UI/UX Remains exactly the same below */
        .card { 
            border-radius: 24px; 
            padding: 35px; 
            margin-bottom: 25px; 
            position: relative; 
            overflow: hidden;
            width: 100%;
            box-sizing: border-box; /* Crucial for glassmorphism with padding */
        }
        
        @media (max-width: 768px) {
            .card {
                padding: 20px;
                border-radius: 16px;
                margin-bottom: 15px;
            }
        }
        .card::before { content: ""; position: absolute; top: 0; left: -50%; width: 50%; height: 100%; background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent); transform: skewX(-25deg); animation: shine 6s infinite; }
        @keyframes shine { 0% { left: -50%; } 100% { left: 150%; } }
        .card h2 { margin-top: 0; font-weight: 700; font-size: 2rem; color: #fff; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        
        .premium-form { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 20px; 
            align-items: end; 
            margin-bottom: 35px; 
        }
        
        @media (max-width: 480px) {
            .premium-form {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
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

        /* RESPONSIVE DESIGN FOR ALL DEVICES */
        @media (max-width: 1200px) {
            .premium-cards-grid { grid-template-columns: repeat(3, 1fr); }
            .premium-quick-actions { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 1024px) {
            :root { --sidebar-width: 0px; }
            .mobile-toggle { display: flex; }
            .main-content { padding: 80px 20px 20px 20px; width: 100% !important; margin: 0 !important; }
            .premium-title { font-size: 2.2rem; }
            .premium-stat-value { font-size: 2.2rem; }
            
            /* Global responsive adjustments */
            .premium-form { grid-template-columns: 1fr 1fr; }
            .modal-content { padding: 30px; width: 95%; }
            .premium-cards-grid { grid-template-columns: repeat(2, 1fr); }
            .premium-quick-actions { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            /* GLOBAL MODAL RESPONSIVENESS - FLOATING CARD LOOK */
            .modal-dialog { 
                width: 85% !important; 
                max-width: 320px !important;
                min-width: auto !important;
                margin: 20px auto !important; 
                display: flex !important;
                align-items: center !important;
                min-height: calc(100% - 40px) !important;
            }

            .modal-content {
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                border-radius: 20px !important;
                background: rgba(30, 20, 60, 0.98) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6) !important;
                overflow: hidden !important;
            }

            .modal-header {
                padding: 15px !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
            }

            .modal-header h2, .modal-title {
                font-size: 1.2rem !important;
                margin: 0 !important;
            }

            /* Better stacking for modal forms */
            .modal-content .premium-form {
                grid-template-columns: 1fr !important;
                gap: 15px !important;
            }

            .modal-content .form-group {
                width: 100% !important;
                margin-bottom: 15px !important;
            }

            .modal-content input, 
            .modal-content select, 
            .modal-content textarea { 
                width: 100% !important; 
            }

            /* Stacked footer buttons */
            .modal-footer, .modal-actions {
                padding: 15px !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 10px !important;
                width: 100% !important;
            }

            .modal-footer button, 
            .modal-actions button,
            .modal-content .btn-primary {
                width: 100% !important;
                height: 48px !important;
                border-radius: 12px !important;
                margin: 0 !important;
            }

            .premium-cards-grid { grid-template-columns: 1fr; } /* Stack vertically */
            .premium-info-section { grid-template-columns: 1fr; }
            .premium-quick-actions { grid-template-columns: 1fr; } /* Stack vertically */
            .modal-header { font-size: 1.6rem !important; }
            .premium-title { font-size: 2rem; }
            
            /* Better table handling for mobile */
            .table-responsive {
                margin: 0 -20px;
                padding: 0 20px;
                width: calc(100% + 40px);
                overflow-x: auto; /* Ensure horizontal swipe */
                -webkit-overflow-scrolling: touch;
            }

            /* STACKED TABLE (CARD VIEW) FOR MOBILE */
            @media (max-width: 600px) {
                .premium-table, .premium-table thead, .premium-table tbody, .premium-table th, .premium-table td, .premium-table tr { 
                    display: block; 
                }
                
                .premium-table thead tr { 
                    position: absolute;
                    top: -9999px;
                    left: -9999px;
                }
                
                .premium-table tr { 
                    margin-bottom: 15px;
                    background: rgba(255, 255, 255, 0.03);
                    border-radius: 16px;
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    padding: 10px;
                }
                
                .premium-table td { 
                    border: none;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.05); 
                    position: relative;
                    padding-left: 45% !important; 
                    text-align: right;
                    min-height: 45px;
                    display: flex;
                    align-items: center;
                    justify-content: flex-end;
                }
                
                .premium-table td:last-child {
                    border-bottom: none;
                }
                
                .premium-table td:before { 
                    position: absolute;
                    left: 15px;
                    width: 40%; 
                    padding-right: 10px; 
                    white-space: nowrap;
                    content: attr(data-label);
                    text-align: left;
                    font-weight: 700;
                    font-size: 0.75rem;
                    text-transform: uppercase;
                    color: rgba(255, 255, 255, 0.4);
                }
            }
        }

        @media (max-width: 576px) {
            .premium-cards-grid { grid-template-columns: 1fr; }
            .premium-quick-actions { grid-template-columns: 1fr; }
            .premium-form { grid-template-columns: 1fr; }
        }

        @media (max-width: 480px) {
            .premium-title { font-size: 1.8rem; }
            .premium-status-text { font-size: 0.9rem; margin-bottom: 25px; }
            .premium-cards-grid { grid-template-columns: 1fr; }
            .premium-stat-card { padding: 20px; }
            .premium-stat-value { font-size: 1.8rem; }
            .card h2 { font-size: 1.5rem; }
            .premium-form { grid-template-columns: 1fr; }
            .btn-primary { width: 100%; }
            .premium-logout-btn { max-width: 100%; }
            .premium-info-section { grid-template-columns: 1fr; }
            .premium-quick-actions { grid-template-columns: 1fr; }
            .main-content { padding: 70px 15px 15px 15px; }
            
            /* Support for small height devices in landscape */
            @media (max-height: 450px) {
                .sidebar-menu { padding-bottom: 80px; }
                .modal-content { padding: 15px; }
            }
        }

        /* Large Screen / Desktop PC / Ultrawide */
        @media (min-width: 1400px) {
            .main-content {
                padding: 50px 60px;
            }
            .premium-cards-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
        }
        
        /* Global Table & Modal Refinements */
        .premium-table th, .premium-table td { padding: 12px 10px; font-size: 0.85rem; }
        .modal-content { scrollbar-width: thin; scrollbar-color: rgba(168, 85, 247, 0.5) transparent; }
    </style>
</head>
<body>
    <!-- MOBILE MENU TOGGLE -->
    <button class="mobile-toggle" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <script>
        /**
         * Global Sidebar Toggle Logic
         * Handles both the sidebar and the background overlay
         */
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar') || document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const icon = document.querySelector('.mobile-toggle i');
            
            if (sidebar) {
                const isActive = sidebar.classList.toggle('active');
                
                // Sync overlay
                if (overlay) {
                    overlay.classList.toggle('active', isActive);
                }
                
                // Sync icon
                if (icon) {
                    if (isActive) {
                        icon.classList.replace('fa-bars', 'fa-times');
                        // Prevent body scroll when sidebar is open on mobile
                        document.body.style.overflow = 'hidden';
                    } else {
                        icon.classList.replace('fa-times', 'fa-bars');
                        document.body.style.overflow = '';
                    }
                }
            }
        }

        // Close sidebar when clicking overlay
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.querySelector('.sidebar-overlay');
            if (overlay) {
                overlay.addEventListener('click', function() {
                    const sidebar = document.querySelector('.sidebar');
                    if (sidebar && sidebar.classList.contains('active')) {
                        toggleSidebar();
                    }
                });
            }
        });
     </script>

    <style>
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