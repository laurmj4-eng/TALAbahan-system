<!-- 1. Include Shared Header (CSS/Theme) -->
<?= $this->include('theme/header') ?>

<!-- 2. Include Shared Sidebar -->
<?= $this->include('theme/sidebar') ?>

<!-- 3. Main Content Wrapper -->
<main class="main-content">
    
    <?php if(session()->getFlashdata('msg')): ?>
        <div class="alert glass-panel" id="system-alert">✅ <?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <div class="card glass-panel">
        <h2 style="font-size: 2.2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">Daily Seafood Inventory 🐟</h2>
        <p style="color: rgba(255,255,255,0.6); margin-top: 15px; margin-bottom: 30px;">Append, modify, or terminate entity access securely.</p>
        
        <form action="<?= site_url('admin/products/store') ?>" method="post" class="premium-form">
            <?= csrf_field() ?>
            <div class="form-group"><label>Identifier (Name)</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Unit</label>
                <select name="unit">
                    <option value="kg">Kilogram (kg)</option>
                    <option value="pcs">Pieces (pcs)</option>
                </select>
            </div>
            <div class="form-group"><label>Cost Price</label><input type="number" step="0.01" name="cost_price" required></div>
            <div class="form-group"><label>Selling Price</label><input type="number" step="0.01" name="selling_price" required></div>
            <div class="form-group"><label>Initial Quantity</label><input type="number" name="quantity" required></div>
            <button type="submit" class="btn-primary" style="margin-top:auto;">Append Entity</button>
        </form>

        <div class="table-responsive glass-panel">
            <table class="premium-table">
                <thead>
                    <tr><th>ENTITY NAME</th><th>PRICING DATA</th><th>CURRENT STOCK</th><th>PROTOCOL LEVEL</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): foreach ($products as $p): ?>
                        <tr>
                            <td><strong style="color: #fff;"><?= esc($p['name']) ?></strong></td>
                            <td>₱<?= number_format($p['selling_price'], 2) ?></td>
                            <td><?= esc($p['current_stock']) ?> <?= esc($p['unit']) ?></td>
                            <td><span class="role-badge role-staff">ACTIVE</span></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4" style="text-align: center; color: #444; padding: 40px;">No inventory nodes found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- 4. Include Shared Footer (Chatbot + Navigation Logic) -->
<?= $this->include('theme/footer') ?>