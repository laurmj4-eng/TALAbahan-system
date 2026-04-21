<?php 
    $role = session()->get('role') ?? 'admin'; 
    $username = session()->get('username') ?? 'User';
    $is_dashboard = url_is($role.'/dashboard*') || url_is('dashboard*');
    $is_order_items = url_is('admin/orders/items*');
    $is_orders = url_is('admin/orders*') && ! $is_order_items;
    $is_products = url_is('staff/products*') || url_is('admin/products*');
    $is_staff_orders = url_is('staff/orders*');
    $is_sales = url_is('staff/salesHistory*') || url_is('admin/sales*');
?>

<aside class="sidebar glass-panel">
    <!-- HEADER -->
    <div class="sidebar-header">
        <h2>✨ Mj Pogi</h2>
        <small><?= ($role === 'admin') ? 'Superadmin' : (($role === 'staff') ? 'Staff Panel' : 'Portal') ?></small>
    </div>

    <!-- MENU ITEMS -->
    <ul class="sidebar-menu">
        
        <!-- NAVIGATION SECTION -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Navigation</div>
            
            <li>
                <a href="<?= site_url($role.'/dashboard') ?>" class="<?= $is_dashboard ? 'active' : '' ?>">
                    <span>⌘</span> 
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($role === 'admin'): ?>
                <!-- MANAGEMENT SECTION FOR ADMIN -->
                <div class="sidebar-section" style="border-top: 1px solid rgba(255,255,255,0.05); margin-top: 16px; padding-top: 16px;">
                    <div class="sidebar-section-title">Management</div>
                    
                    <li>
                        <a href="<?= site_url('admin/products') ?>" class="<?= $is_products ? 'active' : '' ?>">
                            <span>🐟</span> 
                            <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('admin/orders') ?>" class="<?= $is_orders ? 'active' : '' ?>">
                            <span>📑</span> 
                            <span>Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('admin/orders/items') ?>" class="<?= $is_order_items ? 'active' : '' ?>">
                            <span>🧾</span> 
                            <span>Order Items</span>
                        </a>
                    </li>
                </div>

                <!-- QUICK ACCESS SECTION FOR ADMIN -->
                <div class="sidebar-section" style="border-top: 1px solid rgba(255,255,255,0.05); margin-top: 16px; padding-top: 16px;">
                    <div class="sidebar-section-title">Quick Access</div>
                    
                    <li>
                        <a onclick="switchTab('pos')" style="cursor: pointer;">
                            <span>🦐</span> 
                            <span>Seafood POS</span>
                        </a>
                    </li>
                    <li>
                        <a onclick="switchTab('sales')" style="cursor: pointer;">
                            <span>📊</span> 
                            <span>Sales</span>
                        </a>
                    </li>
                </div>

                <!-- ADMIN SECTION FOR ADMIN -->
                <div class="sidebar-section" style="border-top: 1px solid rgba(255,255,255,0.05); margin-top: 16px; padding-top: 16px;">
                    <div class="sidebar-section-title">Admin</div>
                    
                    <li>
                        <a href="<?= site_url('admin/users') ?>" class="<?= url_is('admin/users*') ? 'active' : '' ?>">
                            <span>👥</span> 
                            <span>Database</span>
                        </a>
                    </li>
                </div>

            <?php elseif ($role === 'staff'): ?>
                <!-- MANAGEMENT SECTION FOR STAFF -->
                <div class="sidebar-section" style="border-top: 1px solid rgba(255,255,255,0.05); margin-top: 16px; padding-top: 16px;">
                    <div class="sidebar-section-title">Management</div>
                    
                    <li>
                        <a href="<?= site_url('staff/products') ?>" class="<?= $is_products ? 'active' : '' ?>">
                            <span>📦</span> 
                            <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('staff/orders') ?>" class="<?= $is_staff_orders ? 'active' : '' ?>">
                            <span>📋</span> 
                            <span>Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('staff/salesHistory') ?>" class="<?= $is_sales ? 'active' : '' ?>">
                            <span>💰</span> 
                            <span>Sales</span>
                        </a>
                    </li>
                </div>
            <?php endif; ?>
        </div>

    </ul>

    <!-- FOOTER - USER & LOGOUT -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-label">User Account</div>
            <div class="user-name"><?= esc(substr($username, 0, 15)) ?></div>
        </div>
        <a href="<?= site_url('logout') ?>" class="logout-btn">
            <span>⚡</span>
            <span>Logout</span>
        </a>
    </div>
</aside>