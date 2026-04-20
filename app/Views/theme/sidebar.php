<?php 
    $role = session()->get('role') ?? 'admin'; 
    $username = session()->get('username') ?? 'User';
    $is_dashboard = url_is($role.'/dashboard*') || url_is('dashboard*');
?>

<aside class="sidebar glass-panel">
    <div class="sidebar-header">
        <h2>✨ Mj Pogi Portal</h2>
        <small style="color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 1px; font-size: 0.7rem;">
            <?= ($role === 'admin') ? 'Superadmin UI' : 'Staff Command' ?>
        </small>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a id="nav-dashboard" class="<?= $is_dashboard && !isset($_GET['tab']) ? 'active' : '' ?>" onclick="switchTab('dashboard')">
                <span style="margin-right: 12px;">⌘</span> Overview
            </a>
        </li>
        <li>
            <a id="nav-pos" onclick="switchTab('pos')">
                <span style="margin-right: 12px;">🦐</span> Seafood POS
            </a>
        </li>
        <li>
            <a id="nav-sales" onclick="switchTab('sales')">
                <span style="margin-right: 12px;">📊</span> Sales History
            </a>
        </li>
        
        <!-- Inventory Link -->
        <li>
            <a id="nav-inventory" href="<?= site_url('admin/products') ?>" class="<?= url_is('admin/products*') ? 'active' : '' ?>">
                <span style="margin-right: 12px;">🐟</span> Product
            </a>
        </li>

        <!-- NEW: Order View Link -->
        <li>
            <a id="nav-orders" href="<?= site_url('admin/orders') ?>" class="<?= url_is('admin/orders*') ? 'active' : '' ?>">
                <span style="margin-right: 12px;">📑</span> Order Tracking
            </a>
        </li>

        <?php if ($role === 'admin'): ?>
        <!-- Database/Users Link -->
        <li>
            <a id="nav-users" href="<?= site_url('admin/users') ?>" class="<?= url_is('admin/users*') ? 'active' : '' ?>">
                <span style="margin-right: 12px;">👥</span> Database
            </a>
        </li>
        <?php endif; ?>

        <!-- Logout Section locked to bottom -->
        <li style="margin-top: auto; padding-top: 20px;">
            <div style="padding: 10px 15px; font-size: 0.8rem; color: rgba(255,255,255,0.4); border-top: 1px solid rgba(255,255,255,0.05); margin-bottom: 10px;">
                Node: <span style="color: #a855f7;"><?= esc($username) ?></span>
            </div>
            <a href="<?= site_url('logout') ?>" class="logout-btn">
                <span style="margin-right: 12px;">⚡</span> Secure Log Out
            </a>
        </li>
    </ul>
</aside>