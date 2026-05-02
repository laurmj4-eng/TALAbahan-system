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
        left: 12px;
        right: 12px;
        bottom: 12px;
        z-index: 100000; /* below chat widget */
        border-radius: 18px;
        padding: 10px 14px;
        background: rgba(20, 15, 45, 0.72);
        border: 1px solid rgba(255,255,255,0.12);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow: 0 18px 40px rgba(0,0,0,0.45);
        display: none;
    }

    /* Leave space at the right for Cart+Chat floating buttons */
    .customer-bottom-nav-inner {
        display: flex;
        gap: 10px;
        justify-content: space-around;
        align-items: center;
        padding-right: 160px;
    }

    .cbn-item {
        flex: 1 1 0;
        min-width: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 14px;
        text-decoration: none;
        color: rgba(255,255,255,0.75);
        font-weight: 900;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.10);
        transition: 0.2s ease;
        white-space: nowrap;
    }
    .cbn-item i { font-size: 1.05rem; }
    .cbn-item:hover { transform: translateY(-1px); color: #fff; border-color: rgba(168,85,247,0.45); }
    .cbn-item.active {
        background: linear-gradient(135deg, #6366f1, #a855f7);
        border-color: transparent;
        color: #fff;
    }

    /* Mobile only */
    @media (max-width: 1024px) {
        .customer-bottom-nav { display: block; }
        /* Customer mobile: bottom nav replaces hamburger */
        .mobile-toggle { display: none !important; }
        body.customer-has-bottom-nav .main-content { padding-bottom: 110px !important; }
    }

    /* When a modal is open, keep checkout buttons unobstructed */
    body.modal-open .customer-bottom-nav { display: none !important; }
    body.modal-open #chat-button-container { display: none !important; }
    body.modal-open .cart-float { display: none !important; }
    body.modal-open .profile-float { display: none !important; }

    @media (max-width: 420px) {
        .customer-bottom-nav-inner { padding-right: 140px; }
        .cbn-item { gap: 8px; padding: 10px; }
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

