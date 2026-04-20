<?php 
    // Get the current user's role and username from the session
    $role = session()->get('role'); 
    $username = session()->get('username') ?? 'User';
?>

<!-- GLASS SIDEBAR -->
<aside class="sidebar glass-panel" style="border: none; border-right: 1px solid rgba(255,255,255,0.08);">
    <div class="sidebar-header">
        <h2>✨ Mj Pogi Portal</h2>
        <!-- Dynamically change the subtitle based on role -->
        <small style="color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 1px;">
            <?= ($role === 'admin') ? 'Superadmin UI' : 'Staff Command' ?>
        </small>
    </div>

    <ul class="sidebar-menu">
        <!-- Dashboard: Visible to Everyone -->
        <li>
            <a id="nav-dashboard" class="active" onclick="switchTab('dashboard')">
                <span style="margin-right: 10px;">⌘</span> Overview
            </a>
        </li>

        <!-- Seafood POS: Visible to Admin and Staff -->
        <li>
            <a id="nav-pos" onclick="switchTab('pos')">
                <span style="margin-right: 10px;">🦐</span> Seafood POS
            </a>
        </li>

        <!-- Sales History: Visible to Admin and Staff -->
        <li>
            <a id="nav-sales" onclick="switchTab('sales')">
                <span style="margin-right: 10px;">📊</span> Sales History
            </a>
        </li>

        <!-- Daily Inventory: Visible to Admin and Staff -->
        <li>
            <a id="nav-inventory" href="<?= site_url($role . '/products') ?>">
                <span style="margin-right: 10px;">🐟</span> Daily Inventory
            </a>
        </li>

        <!-- Database (User Management): ONLY VISIBLE TO ADMIN -->
        <?php if ($role === 'admin'): ?>
            <li>
                <a id="nav-users" onclick="switchTab('users')">
                    <span style="margin-right: 10px;">👥</span> Database
                </a>
            </li>
        <?php endif; ?>

        <!-- Logout: Always at the bottom -->
        <li style="margin-top: auto; padding-top: 20px;">
            <div style="padding: 10px 20px; font-size: 0.8rem; color: rgba(255,255,255,0.4); border-top: 1px solid rgba(255,255,255,0.05); margin-bottom: 10px;">
                Logged in as: <span style="color: #a855f7;"><?= esc($username) ?></span>
            </div>
            <a href="<?= site_url('logout') ?>" class="logout-btn">
                <span style="margin-right: 10px;">⚡</span> Secure Log Out
            </a>
        </li>
    </ul>
</aside>

<style>
    /* Ensure the sidebar takes full height and positions logout at the bottom */
    .sidebar {
        display: flex;
        flex-direction: column;
        height: 100vh;
    }
    
    .sidebar-menu {
        display: flex;
        flex-direction: column;
        height: 100%;
        margin: 0;
        padding: 20px 10px;
        list-style: none;
    }

    /* Keep your existing smooth hover transitions */
    .sidebar-menu a {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .sidebar-menu a:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateX(5px);
    }

    .logout-btn {
        color: #f87171 !important;
        background: rgba(248, 113, 113, 0.05);
    }

    .logout-btn:hover {
        background: rgba(248, 113, 113, 0.2) !important;
        color: #fff !important;
    }
</style>