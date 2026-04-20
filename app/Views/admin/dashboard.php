<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<main class="main-content">
    
    <?php if(session()->getFlashdata('msg')): ?>
        <div class="alert glass-panel" id="system-alert">✅ <?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <!-- DASHBOARD TAB -->
    <section id="tab-dashboard" class="tab-section active">
        <div class="card glass-panel">
            <h2>Welcome back, <?= esc(session()->get('username') ?? 'Admin') ?>.</h2>
            <p style="color: rgba(255,255,255,0.7); font-size: 1.1rem; max-width: 700px;">All system servers are running in high-fidelity mode. Navigate to the database or manage products using the sidebar.</p>
        </div>
    </section>

    <!-- POS TAB -->
    <section id="tab-pos" class="tab-section">
        <?= $this->include('admin/pos_view') ?>
    </section>

    <!-- SALES HISTORY TAB -->
    <section id="tab-sales" class="tab-section">
        <?= $this->include('admin/sales_history_view') ?>
    </section>
</main>

<?= $this->include('theme/footer') ?>