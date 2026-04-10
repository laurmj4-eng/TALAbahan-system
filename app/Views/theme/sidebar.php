<!-- GLASS SIDEBAR -->
<aside class="sidebar glass-panel" style="border: none; border-right: 1px solid rgba(255,255,255,0.08);">
    <div class="sidebar-header">
        <h2>✨ Mj Pogi Portal</h2>
        <small style="color: rgba(255,255,255,0.5);">Superadmin UI</small>
    </div>
    <ul class="sidebar-menu">
        <li><a id="nav-dashboard" class="active" onclick="switchTab('dashboard')">⌘ Overview</a></li>
        <li><a id="nav-pos" onclick="switchTab('pos')">🦐 Seafood POS</a></li>
        <!-- NEW SALES HISTORY LINK -->
        <li><a id="nav-sales" onclick="switchTab('sales')">📊 Sales History</a></li>
        <li><a id="nav-users" onclick="switchTab('users')">👥 Database</a></li>
        <li style="margin-top: auto;"><a href="<?= site_url('logout') ?>" class="logout-btn">⚡ Secure Log Out</a></li>
    </ul>
</aside>