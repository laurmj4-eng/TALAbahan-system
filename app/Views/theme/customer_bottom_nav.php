<?php
    $role = session()->get('role') ?? '';
    if ($role !== 'customer') return;

    $isShop = url_is('customer/dashboard*');
    $isOrders = url_is('customer/order-center*') || url_is('customer/order-items*');
    $isProfile = url_is('customer/profile*');
?>

<style>
    .customer-bottom-nav {
        position: fixed;
        left: 20px;
        right: 20px;
        bottom: 25px;
        z-index: 100000; /* below chat widget */
        border-radius: 25px;
        padding: 12px 16px;
        background: rgba(20, 15, 45, 0.85);
        border: 1px solid rgba(255,255,255,0.18);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.6);
        display: none;
    }

    /* Inner container: full width on mobile, centered */
    .customer-bottom-nav-inner {
        display: flex;
        gap: 20px;
        justify-content: center;
        align-items: center;
        /* Remove large padding-right to allow buttons to space out */
        padding-right: 0;
    }

    .cbn-item {
        flex: 1;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 12px 8px;
        border-radius: 18px;
        text-decoration: none;
        color: rgba(255,255,255,0.65);
        font-weight: 800;
        font-size: 0.75rem;
        background: transparent;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
    }
    .cbn-item i { 
        font-size: 1.3rem; 
        transition: transform 0.3s ease;
    }
    .cbn-item:hover { color: #fff; }
    .cbn-item:hover i { transform: translateY(-3px); }
    
    .cbn-item.active {
        color: #fff;
        background: rgba(255, 255, 255, 0.1);
    }
    .cbn-item.active i {
        color: #a855f7;
        text-shadow: 0 0 15px rgba(168, 85, 247, 0.6);
    }

    /* Mobile only */
    @media (max-width: 1024px) {
        .customer-bottom-nav { display: block; }
        .mobile-toggle { display: none !important; }
        body.customer-has-bottom-nav .main-content { padding-bottom: 120px !important; }
        
        /* Reposition cart float to be above nav */
        .cart-float {
            bottom: 110px !important;
            right: 25px !important;
        }
        #chat-button-container {
            bottom: 190px !important; /* Stacked above cart icon */
            right: 25px !important;
            display: flex !important; /* Ensure it stays visible */
            left: auto !important; /* Override any left: 20px from chat-style.css */
        }
        #chat-container {
            bottom: 250px !important; /* Stacked above button */
            right: 15px !important;
        }
    }

    @media (max-width: 420px) {
        .customer-bottom-nav {
            left: 15px;
            right: 15px;
            bottom: 20px;
        }
        .customer-bottom-nav-inner { gap: 12px; }
        .cbn-item { padding: 10px 5px; }
    }

    /* When a modal is open, keep checkout buttons unobstructed */
    body.modal-open .customer-bottom-nav { 
        pointer-events: none !important;
        opacity: 0.1 !important;
        transform: translateY(20px);
        transition: all 0.3s ease;
    }
</style>

<script>
    (function () {
        try { document.body.classList.add('customer-has-bottom-nav'); } catch (e) {}
    })();
</script>

<nav class="customer-bottom-nav" aria-label="Customer navigation">
    <div class="customer-bottom-nav-inner">
        <a class="cbn-item <?= $isShop ? 'active' : '' ?>" href="<?= site_url('customer/dashboard') ?>">
            <i class="fas fa-store"></i> <span>Shop</span>
        </a>
        <a class="cbn-item <?= $isOrders ? 'active' : '' ?>" href="<?= site_url('customer/order-center?tab=all') ?>">
            <i class="fas fa-clipboard-list"></i> <span>Orders</span>
        </a>
        <a class="cbn-item <?= $isProfile ? 'active' : '' ?>" href="<?= site_url('customer/profile') ?>">
            <i class="fas fa-user"></i> <span>Profile</span>
        </a>
    </div>
</nav>

