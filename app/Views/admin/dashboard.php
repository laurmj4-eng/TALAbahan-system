<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glassmorphism Premium Dashboard | Mj Chatbot</title>
    
    <link rel="stylesheet" href="/css/chat-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>
    
    <style>
        /* FULL GLASSMORPHISM THEME */
        * { box-sizing: border-box; }
        
        body { 
            margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; 
            /* Abstract Animated Dark Mesh Background */
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

        /* Glass Utility Classes */
        .glass-panel {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        /* Sidebar */
        .sidebar { width: 280px; display: flex; flex-direction: column; z-index: 10; border-right: 1px solid rgba(255,255,255,0.08); background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(20px);}
        .sidebar-header { padding: 30px 24px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header h2 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; letter-spacing: 1px; }
        .sidebar-menu { list-style: none; padding: 20px 10px; margin: 0; flex: 1; }
        .sidebar-menu a { display: flex; align-items: center; padding: 16px 20px; margin-bottom: 5px; color: rgba(255,255,255,0.7); text-decoration: none; font-weight: 500; transition: 0.3s ease; border-radius: 12px; cursor: pointer; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.15); color: #fff; transform: translateX(5px); }
        .logout-btn { color: #f87171 !important; margin-top: auto; }
        .logout-btn:hover { background: rgba(248, 113, 113, 0.2) !important; }
        
        /* Main Area */
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .tab-section { display: none; animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .tab-section.active { display: block; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Premium Glass Cards */
        .card { border-radius: 24px; padding: 35px; margin-bottom: 25px; position: relative; overflow: hidden; }
        .card::before { content: ""; position: absolute; top: 0; left: -50%; width: 50%; height: 100%; background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent); transform: skewX(-25deg); animation: shine 6s infinite; }
        @keyframes shine { 0% { left: -50%; } 100% { left: 150%; } }
        
        .card h2 { margin-top: 0; font-weight: 700; font-size: 2rem; color: #fff; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        
        /* Premium Forms */
        .premium-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; align-items: end; margin-bottom: 35px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-size: 0.85rem; font-weight: 500; margin-bottom: 8px; color: rgba(255,255,255,0.8); text-transform: uppercase; letter-spacing: 0.5px;}
        .premium-form input, .premium-form select { 
            padding: 15px; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-family: inherit; 
            font-size: 1rem; color: #fff; background: rgba(0,0,0,0.2); backdrop-filter: blur(10px); transition: 0.3s; 
        }
        .premium-form input::placeholder { color: rgba(255,255,255,0.4); }
        .premium-form select option { background: #1e1b4b; color: #fff; } /* Fix dropdown readability */
        .premium-form input:focus, .premium-form select:focus { outline: none; border-color: #818cf8; background: rgba(0,0,0,0.4); box-shadow: 0 0 15px rgba(129, 140, 248, 0.4); }
        
        .btn-primary { 
            padding: 15px 25px; background: linear-gradient(135deg, #6366f1, #a855f7); color: white; border: none; 
            border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.3s; text-shadow: 0 1px 2px rgba(0,0,0,0.2); 
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.4); height: 53px;
        }
        .btn-primary:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 8px 25px rgba(168, 85, 247, 0.6); }

        /* FIXED TABLE - No cutoffs */
        .table-responsive { overflow-x: auto; border-radius: 16px; }
        .premium-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 700px; }
        .premium-table th { background: rgba(0,0,0,0.3); padding: 18px 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.6); letter-spacing: 1px; text-align: left; }
        .premium-table td { padding: 18px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; color: rgba(255,255,255,0.9); }
        .premium-table tr:hover td { background: rgba(255,255,255,0.05); }
        .action-cell { white-space: nowrap; width: 1%; /* Forces column to shrink-wrap its contents preventing cutoffs */ } 

        /* Badges & Actions */
        .role-badge { padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .role-admin { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .role-staff { background: rgba(56, 189, 248, 0.2); color: #7dd3fc; border: 1px solid rgba(56, 189, 248, 0.3); }
        .role-customer { background: rgba(255, 255, 255, 0.1); color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.2); }
        
        .action-btns { display: inline-flex; gap: 10px; }
        .btn-edit, .btn-delete { display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; border-radius: 10px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.3s; border: none; cursor:pointer;}
        .btn-edit { background: rgba(245, 158, 11, 0.2); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.3); }
        .btn-edit:hover { background: rgba(245, 158, 11, 0.4); transform: translateY(-2px);}
        .btn-delete { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .btn-delete:hover { background: rgba(239, 68, 68, 0.4); transform: translateY(-2px);}

        /* Alerts & Modals */
        .alert { padding: 18px 24px; background: rgba(16, 185, 129, 0.2); color: #a7f3d0; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 16px; margin-bottom: 25px; font-weight: 500; backdrop-filter: blur(10px); }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; }
        .modal.show { display: flex; animation: fadeIn 0.3s; }
        .modal-content { background: rgba(30, 41, 59, 0.8); backdrop-filter: blur(20px); padding: 40px; border-radius: 24px; width: 100%; max-width: 450px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); position: relative; }
        .close-modal { position: absolute; top: 20px; right: 25px; font-size: 2rem; color: rgba(255,255,255,0.5); cursor: pointer; border: none; background: none; transition: 0.3s;}
        .close-modal:hover { color: white; transform: rotate(90deg); }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    </style>
</head>
<body>

    <!-- GLASS SIDEBAR -->
    <aside class="sidebar glass-panel" style="border: none; border-right: 1px solid rgba(255,255,255,0.08);">
        <div class="sidebar-header">
            <h2>✨ Mj Pogi Portal</h2>
            <small style="color: rgba(255,255,255,0.5);">Superadmin UI</small>
        </div>
        <ul class="sidebar-menu">
            <li><a id="nav-dashboard" class="active" onclick="switchTab('dashboard')">⌘ Overview</a></li>
            <li><a id="nav-users" onclick="switchTab('users')">👥 Database</a></li>
            <li style="margin-top: auto;"><a href="<?= site_url('logout') ?>" class="logout-btn">⚡ Secure Log Out</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        
        <?php if(session()->getFlashdata('msg')): ?>
            <div class="alert glass-panel" id="system-alert">✅ <?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <!-- DASHBOARD TAB -->
        <section id="tab-dashboard" class="tab-section active">
            <div class="card glass-panel">
                <h2 style="font-size: 2.5rem; margin-bottom: 10px;">Welcome back, <?= esc(session()->get('username') ?? 'Admin') ?>.</h2>
                <p style="color: rgba(255,255,255,0.7); font-size: 1.1rem; line-height: 1.6; max-width: 700px;">All system servers are running in high-fidelity mode. Navigate to the database using the glass panel sidebar to manage portal access, or summon the AI via the button below.</p>
            </div>
        </section>

        <!-- USER MANAGEMENT TAB -->
        <section id="tab-users" class="tab-section">
            <div class="card glass-panel">
                <h2 style="font-size: 2.2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">System Architecture Database</h2>
                <p style="color: rgba(255,255,255,0.6); margin-top: 15px; margin-bottom: 30px;">Append, modify, or terminate entity access securely.</p>
                
                <!-- Use site_url() to strictly map Codeigniter properly (fixes 404 index error) -->
                <form action="<?= site_url('admin/saveUser') ?>" method="post" class="premium-form">
                    <div class="form-group">
                        <label>Identifier</label>
                        <input type="text" name="username" placeholder="Username..." required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Node (Email)</label>
                        <input type="email" name="email" placeholder="example@node.com" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Auth Hash</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label>Protocol Level</label>
                        <select name="role" required>
                            <option value="admin">Administrator (Tier 1)</option>
                            <option value="staff">Staff Member (Tier 2)</option>
                            <option value="customer" selected>Customer (Tier 3)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Append Entity</button>
                </form>

                <div class="table-responsive glass-panel">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Entity Node</th>
                                <th>Authorization Address</th>
                                <th>Protocol Level</th>
                                <th class="action-cell">Terminal Controls</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($users) && is_array($users)): ?>
                                <?php foreach($users as $user): ?>
                                <tr>
                                    <td><strong style="color: #fff; letter-spacing: 0.5px;"><?= esc($user['username']) ?></strong></td>
                                    <td><?= esc($user['email']) ?></td>
                                    <td><span class="role-badge role-<?= esc($user['role']) ?>"><?= esc($user['role']) ?></span></td>
                                    <!-- Use 'action-cell' white-space: nowrap class to fix cut-off delete button! -->
                                    <td class="action-cell">
                                        <div class="action-btns">
                                            <!-- EDIT BTN -->
                                            <button onclick="openEditModal(<?= $user['id'] ?>, '<?= esc($user['username']) ?>', '<?= esc($user['email']) ?>', '<?= esc($user['role']) ?>')" class="btn-edit">EDIT</button>
                                            
                                            <!-- DELETE BTN using site_url() to prevent 404 -->
                                            <a href="<?= site_url('admin/deleteUser/'.$user['id']) ?>" class="btn-delete" onclick="return confirm('WARNING: Terminating <?= esc($user['username']) ?> from the mainframe. Confirm?')">DELETE</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center; padding: 40px; color:rgba(255,255,255,0.4);">No records found in main sequence.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- UPDATE MODAL OVERLAY -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeEditModal()">&times;</button>
            <h2 style="margin-top:0; color: #fff; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom:15px;">Override User Protocol</h2>
            
            <!-- Update form uses site_url() resolving the huge 404 ERROR! -->
            <form action="<?= site_url('admin/updateUser') ?>" method="post" class="premium-form" style="display:flex; flex-direction:column; gap: 15px; margin-bottom:0;">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label>Node Identity</label>
                    <input type="text" name="username" id="edit_username" required>
                </div>
                
                <div class="form-group">
                    <label>Transmission Vector (Email)</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>

                <div class="form-group">
                    <label>New Cipher (Optional)</label>
                    <input type="password" name="password" placeholder="Bypass update...">
                </div>

                <div class="form-group">
                    <label>Clearance Array</label>
                    <select name="role" id="edit_role" required>
                        <option value="admin">Administrator</option>
                        <option value="staff">Staff Command</option>
                        <option value="customer">Customer Standard</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary" style="margin-top: 10px;">Execute Update</button>
            </form>
        </div>
    </div>


    <!-- ========================= -->
    <!-- Mj Assistant Integration  -->
    <!-- ========================= -->
    <div class="chat-button-container" id="chat-button-container">
        <div class="chat-button-pulse" style="background-color: #8b5cf6;"></div>
        <button id="chat-button" aria-label="Open Chat with Mj" style="border: 2px solid #8b5cf6;">
            <img src="/images/logo.png" alt="Chat Bot">
        </button>
    </div>

    <div id="chat-container">
        <div id="chat-header">
            <div class="header-left">
                <div class="ai-info">
                    <span class="ai-title">Mj Sub-Routine AI</span>
                    <select id="model-select">
                        <option value="openrouter/free" selected>Optimum Protocol</option>
                    </select>
                </div>
            </div>
            
            <div id="header-controls">
                <button id="download-chat" class="header-btn" title="Download Log">💾</button>
                <button id="toggle-sound" class="header-btn" title="Toggle Acoustics">🔊</button>
                <button id="clear-chat" class="header-btn" title="Flush Memory">🗑️</button>
                <button id="close-chat" class="header-btn close-btn" title="Close Panel">&times;</button>
            </div>
        </div>
        
        <div id="chat-messages" style="background: rgba(15, 23, 42, 0.98);"></div>
        
        <div id="chat-input-area" style="background: #1e293b; border-top: 1px solid rgba(255,255,255,0.05);">
            <div class="input-wrapper" style="background: #0f172a; border-color: rgba(255,255,255,0.1);">
                <input type="text" id="chat-input" placeholder="Initiate inquiry..." autocomplete="off" style="color: white;">
                <button id="send-btn" aria-label="Transmit">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Script Connections -->
    <script src="/js/chat-script.js"></script>
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-section').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');
            document.getElementById('nav-' + tabId).classList.add('active');
        }

        const modal = document.getElementById('editModal');
        
        function openEditModal(id, username, email, role) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            modal.classList.add('show');
        }

        function closeEditModal() {
            modal.classList.remove('show');
        }

        // Auto tab router based on operations
        window.onload = function() {
            var alertBox = document.getElementById('system-alert');
            if(alertBox) {
                switchTab('users');
                setTimeout(() => { 
                    alertBox.style.transition = 'all 0.5s ease';
                    alertBox.style.opacity = '0';
                    alertBox.style.transform = 'translateY(-10px)'; 
                    setTimeout(() => alertBox.remove(), 500); 
                }, 3000);
            }
        }
    </script>
</body>
</html> 