<?php 
    $role = session()->get('role') ?? 'admin'; 
    $username = session()->get('username') ?? 'User';
    
    // Get current URI string - this is the most reliable way in CI4
    $current_path = uri_string();
    $current_tab = service('request')->getGet('tab');

    // Debug-friendly active state logic
    // We check if the current path starts with or exactly matches the menu paths
    $is_pos = ($current_tab === 'pos');
    $is_sales = ($current_tab === 'sales' || strpos($current_path, 'salesHistory') !== false);

    $is_dashboard = ($current_path === $role.'/dashboard' || $current_path === $role || $current_path === '') && !$is_pos && ($current_tab !== 'sales');
    
    $is_products = (strpos($current_path, 'products') !== false);
    
    // Admin specific
    $is_orders = (strpos($current_path, 'admin/orders') === 0 && strpos($current_path, 'admin/orders/items') === false);
    $is_order_items = (strpos($current_path, 'admin/orders/items') === 0);
    $is_shipping = (strpos($current_path, 'admin/shipping') === 0);
    $is_vouchers = (strpos($current_path, 'admin/vouchers') === 0);
    $is_database = (strpos($current_path, 'admin/users') === 0);
    $is_activity_logs = (strpos($current_path, 'admin/activity') === 0);

    // Staff specific
    $is_staff_orders = (strpos($current_path, 'staff/orders') === 0);
    
    // Customer specific
    $is_customer_order_items = (
        strpos($current_path, 'customer/order-items') === 0 || 
        strpos($current_path, 'customer/order-center') === 0 || 
        strpos($current_path, 'customer/order-details') === 0 || 
        strpos($current_path, 'customer/tracking') === 0
    );
?>

<?= $this->include('theme/sidebar_styles') ?>

<!-- OVERLAY FOR MOBILE -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside class="sidebar glass-panel" id="sidebar">
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
                        <a id="nav-pos" onclick="switchTab('pos')" style="cursor: pointer;" class="<?= $is_pos ? 'active' : '' ?>">
                            <i class="fas fa-shrimp"></i> 
                            <span>Seafood POS</span>
                        </a>
                    </li>
                    <li>
                        <a id="nav-sales" onclick="switchTab('sales')" style="cursor: pointer;" class="<?= $is_sales ? 'active' : '' ?>">
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
                        <a href="<?= site_url('admin/users') ?>" class="<?= $is_database ? 'active' : '' ?>">
                            <i class="fas fa-database"></i> 
                            <span>Database</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('admin/activity') ?>" class="<?= $is_activity_logs ? 'active' : '' ?>">
                            <i class="fas fa-history"></i> 
                            <span>Activity Log</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('admin/shipping') ?>" class="<?= $is_shipping ? 'active' : '' ?>">
                            <i class="fas fa-map-marker-alt"></i> 
                            <span>Shipping</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('admin/vouchers') ?>" class="<?= $is_vouchers ? 'active' : '' ?>">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Vouchers</span>
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

<script>
    // Immediate Highlight on Click for Sidebar Items
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            // Only for links that lead to other pages
            if (this.getAttribute('href') && this.getAttribute('href') !== '#') {
                // Remove active from all
                document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
                // Add to clicked
                this.classList.add('active');
            }
        });
    });
</script>