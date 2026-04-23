<?php 
    $role = session()->get('role') ?? 'admin'; 
    $username = session()->get('username') ?? 'User';
    $is_dashboard = url_is($role.'/dashboard*') || url_is('dashboard*');
    $is_order_items = url_is('admin/orders/items*');
    $is_orders = url_is('admin/orders*');
    $is_products = url_is('staff/products*') || url_is('admin/products*');
    $is_staff_orders = url_is('staff/orders*');
    $is_sales = url_is('staff/salesHistory*') || url_is('admin/sales*');
    $is_customer_order_items = url_is('customer/order-items*');
?>

<?= $this->include('theme/sidebar_styles') ?>

<aside class="sidebar glass-panel">
    <!-- HEADER -->
    <div class="sidebar-header">
        <div class="logo-container">
            <i class="fas fa-gem logo-icon"></i>
            <h2>Mj Pogi</h2>
        </div>
        <small><?= ($role === 'admin') ? 'Superadmin' : (($role === 'staff') ? 'Staff Panel' : 'Portal') ?></small>
    </div>

    <!-- MENU ITEMS -->
    <ul class="sidebar-menu">
        
        <!-- NAVIGATION SECTION -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">
                <i class="fas fa-compass"></i>
                <span>Navigation</span>
            </div>
            
            <li>
                <a href="<?= site_url($role.'/dashboard') ?>" class="<?= $is_dashboard ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> 
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($role === 'customer'): ?>
                <li>
                    <a href="<?= site_url('customer/order-items') ?>" class="<?= $is_customer_order_items ? 'active' : '' ?>">
                        <i class="fas fa-history"></i> 
                        <span>My Orders</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <!-- MANAGEMENT SECTION FOR ADMIN -->
                <div class="sidebar-section-divider"></div>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">
                        <i class="fas fa-tasks"></i>
                        <span>Management</span>
                    </div>
                    
                    <li>
                        <a href="<?= site_url('admin/products') ?>" class="<?= $is_products ? 'active' : '' ?>">
                            <i class="fas fa-fish"></i> 
                            <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('admin/orders') ?>" class="<?= $is_orders ? 'active' : '' ?>">
                            <i class="fas fa-clipboard-list"></i> 
                            <span>Orders</span>
                        </a>
                    </li>
                </div>

                <!-- QUICK ACCESS SECTION FOR ADMIN -->
                <div class="sidebar-section-divider"></div>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">
                        <i class="fas fa-bolt"></i>
                        <span>Quick Access</span>
                    </div>
                    
                    <li>
                        <a onclick="switchTab('pos')" style="cursor: pointer;">
                            <i class="fas fa-shrimp"></i> 
                            <span>Seafood POS</span>
                        </a>
                    </li>
                    <li>
                        <a onclick="switchTab('sales')" style="cursor: pointer;">
                            <i class="fas fa-chart-line"></i> 
                            <span>Sales</span>
                        </a>
                    </li>
                </div>

                <!-- ADMIN SECTION FOR ADMIN -->
                <div class="sidebar-section-divider"></div>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin</span>
                    </div>
                    
                    <li>
                        <a href="<?= site_url('admin/users') ?>" class="<?= url_is('admin/users*') ? 'active' : '' ?>">
                            <i class="fas fa-database"></i> 
                            <span>Database</span>
                        </a>
                    </li>
                </div>

            <?php elseif ($role === 'staff'): ?>
                <!-- MANAGEMENT SECTION FOR STAFF -->
                <div class="sidebar-section-divider"></div>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">
                        <i class="fas fa-tasks"></i>
                        <span>Management</span>
                    </div>
                    
                    <li>
                        <a href="<?= site_url('staff/products') ?>" class="<?= $is_products ? 'active' : '' ?>">
                            <i class="fas fa-box-open"></i> 
                            <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('staff/orders') ?>" class="<?= $is_staff_orders ? 'active' : '' ?>">
                            <i class="fas fa-list-check"></i> 
                            <span>Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('staff/salesHistory') ?>" class="<?= $is_sales ? 'active' : '' ?>">
                            <i class="fas fa-money-bill-trend-up"></i> 
                            <span>Sales</span>
                        </a>
                    </li>
                </div>
            <?php endif; ?>
        </div>

    </ul>

    <!-- FOOTER - USER & LOGOUT -->
    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <div class="user-name"><?= esc(substr($username, 0, 15)) ?></div>
                <div class="user-role"><?= ucfirst($role) ?></div>
            </div>
        </div>
        <a href="<?= site_url('logout') ?>" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>