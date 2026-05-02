<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<style>
    .profile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 26px;
        flex-wrap: wrap;
    }
    .profile-title h1 {
        margin: 0 0 6px 0;
        font-size: 2.4rem;
        font-weight: 900;
        letter-spacing: -0.8px;
    }
    .profile-title p {
        margin: 0;
        color: rgba(255,255,255,0.6);
    }
    .profile-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-soft {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 14px;
        text-decoration: none;
        font-weight: 800;
        color: #fff;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        transition: 0.22s ease;
    }
    .btn-soft:hover {
        transform: translateY(-2px);
        border-color: rgba(168, 85, 247, 0.45);
        background: rgba(255,255,255,0.08);
    }

    .purchases-card {
        border-radius: 24px;
        padding: 26px;
        margin-bottom: 22px;
    }

    .purchases-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .purchases-header h3 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 900;
        letter-spacing: -0.2px;
        display: inline-flex;
        gap: 10px;
        align-items: center;
    }
    .purchases-header small {
        color: rgba(255,255,255,0.55);
        font-weight: 600;
    }

    .order-badges {
        display: grid;
        grid-template-columns: repeat(4, minmax(160px, 1fr));
        gap: 14px;
    }
    @media (max-width: 900px) {
        .order-badges { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 520px) {
        .order-badges { grid-template-columns: 1fr; }
    }

    .badge-tile {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 18px;
        border-radius: 18px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.10);
        text-decoration: none;
        color: #fff;
        transition: 0.22s ease;
        position: relative;
        overflow: hidden;
    }
    .badge-tile:hover {
        transform: translateY(-3px);
        border-color: rgba(129, 140, 248, 0.45);
        background: rgba(255,255,255,0.05);
    }
    .badge-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }
    .badge-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(168, 85, 247, 0.14);
        border: 1px solid rgba(168, 85, 247, 0.22);
        flex: 0 0 auto;
    }
    .badge-icon i { font-size: 1.15rem; color: #c084fc; }
    .badge-meta { min-width: 0; }
    .badge-meta .label { font-weight: 900; letter-spacing: -0.2px; }
    .badge-meta .hint { color: rgba(255,255,255,0.5); font-size: 0.85rem; margin-top: 2px; }
    .badge-count {
        font-size: 1.55rem;
        font-weight: 900;
        color: #fff;
        line-height: 1;
        flex: 0 0 auto;
    }
    .badge-pill {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        color: rgba(255,255,255,0.8);
    }
</style>

<main class="main-content">
    <div class="profile-header">
        <div class="profile-title">
            <h1>Profile</h1>
            <p>Welcome, <strong><?= esc($username ?? 'Customer') ?></strong>. Manage your purchases quickly.</p>
        </div>
        <div class="profile-actions">
            <a class="btn-soft" href="<?= site_url('customer/dashboard') ?>">
                <i class="fas fa-store"></i> Back to Shop
            </a>
            <a class="btn-soft" href="<?= site_url('customer/order-center?tab=all') ?>">
                <i class="fas fa-clipboard-list"></i> View All Orders
            </a>
            <a class="btn-soft" href="<?= site_url('logout') ?>" style="color:#fca5a5; border-color: rgba(239,68,68,0.25); background: rgba(239,68,68,0.06);">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="purchases-card glass-panel">
        <div class="purchases-header">
            <h3><i class="fas fa-bag-shopping" style="color:#a855f7;"></i> My Purchases</h3>
            <small>Tap a badge to jump to that order stage.</small>
        </div>

        <?php
            $counts = is_array($counts ?? null) ? $counts : [];
            $toPay = (int) ($counts['to_pay'] ?? 0);
            $toShip = (int) ($counts['to_ship'] ?? 0);
            $toReceive = (int) ($counts['to_receive'] ?? 0);
            $completed = (int) ($counts['completed'] ?? 0);
        ?>

        <div class="order-badges">
            <a class="badge-tile" href="<?= site_url('customer/order-center?tab=to_pay') ?>">
                <span class="badge-pill">To Pay</span>
                <div class="badge-left">
                    <span class="badge-icon"><i class="fas fa-credit-card"></i></span>
                    <div class="badge-meta">
                        <div class="label">Payment</div>
                        <div class="hint">Complete checkout</div>
                    </div>
                </div>
                <div class="badge-count"><?= $toPay ?></div>
            </a>

            <a class="badge-tile" href="<?= site_url('customer/order-center?tab=to_ship') ?>">
                <span class="badge-pill">To Ship</span>
                <div class="badge-left">
                    <span class="badge-icon"><i class="fas fa-box"></i></span>
                    <div class="badge-meta">
                        <div class="label">Preparing</div>
                        <div class="hint">Seller processing</div>
                    </div>
                </div>
                <div class="badge-count"><?= $toShip ?></div>
            </a>

            <a class="badge-tile" href="<?= site_url('customer/order-center?tab=to_receive') ?>">
                <span class="badge-pill">To Receive</span>
                <div class="badge-left">
                    <span class="badge-icon"><i class="fas fa-truck"></i></span>
                    <div class="badge-meta">
                        <div class="label">Shipping</div>
                        <div class="hint">Track delivery</div>
                    </div>
                </div>
                <div class="badge-count"><?= $toReceive ?></div>
            </a>

            <a class="badge-tile" href="<?= site_url('customer/order-center?tab=completed') ?>">
                <span class="badge-pill">Completed</span>
                <div class="badge-left">
                    <span class="badge-icon"><i class="fas fa-circle-check"></i></span>
                    <div class="badge-meta">
                        <div class="label">History</div>
                        <div class="hint">Review & reorder</div>
                    </div>
                </div>
                <div class="badge-count"><?= $completed ?></div>
            </a>
        </div>
    </div>
</main>

<?= $this->include('theme/customer_bottom_nav') ?>
<?= $this->include('theme/footer') ?>

