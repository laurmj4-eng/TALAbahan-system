<!-- 1. Include the Header -->
<?= $this->include('theme/header') ?>

<!-- 2. Include the Sidebar -->
<?= $this->include('theme/sidebar') ?>

<!-- MAIN CONTENT -->
<main class="main-content">
    
    <?php if(session()->getFlashdata('msg')): ?>
        <div class="alert glass-panel" id="system-alert">✅ <?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <!-- DASHBOARD TAB -->
    <section id="tab-dashboard" class="tab-section active">
        <div class="card glass-panel">
            <h2>Welcome back, <?= esc(session()->get('username') ?? 'Admin') ?>.</h2>
            <p style="color: rgba(255,255,255,0.7); font-size: 1.1rem; max-width: 700px;">All system servers are running in high-fidelity mode. Navigate to the database to manage portal access, or summon the AI via the button below.</p>
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

    <!-- USER MANAGEMENT TAB -->
    <section id="tab-users" class="tab-section">
        <div class="card glass-panel">
            <h2 style="font-size: 2.2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">System Architecture Database</h2>
            <form action="<?= site_url('admin/saveUser') ?>" method="post" class="premium-form">
                <div class="form-group"><label>Identifier</label><input type="text" name="username" required></div>
                <div class="form-group"><label>Node (Email)</label><input type="email" name="email" required></div>
                <div class="form-group"><label>Auth Hash</label><input type="password" name="password" required></div>
                <div class="form-group">
                    <label>Protocol Level</label>
                    <select name="role" required>
                        <option value="admin">Administrator (Tier 1)</option>
                        <option value="staff">Staff Member (Tier 2)</option>
                        <option value="customer" selected>Customer (Tier 3)</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Append Entity</button>
            </form>

            <div class="table-responsive glass-panel">
                <table class="premium-table">
                    <thead><tr><th>Entity Node</th><th>Authorization Address</th><th>Protocol Level</th><th class="action-cell">Terminal Controls</th></tr></thead>
                    <tbody>
                        <?php if(!empty($users)): foreach($users as $user): ?>
                        <tr>
                            <td><strong><?= esc($user['username']) ?></strong></td>
                            <td><?= esc($user['email']) ?></td>
                            <td><span class="role-badge role-<?= esc($user['role']) ?>"><?= esc($user['role']) ?></span></td>
                            <td class="action-cell">
                                <div class="action-btns">
                                    <button onclick="openEditModal(<?= $user['id'] ?>, '<?= esc($user['username']) ?>', '<?= esc($user['email']) ?>', '<?= esc($user['role']) ?>')" class="btn-edit">EDIT</button>
                                    <a href="<?= site_url('admin/deleteUser/'.$user['id']) ?>" class="btn-delete">DELETE</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="4" style="text-align:center; padding: 30px; color: rgba(255,255,255,0.5);">No records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<!-- EDIT MODAL -->
<div id="editModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); backdrop-filter:blur(8px); z-index:1000; align-items:center; justify-content:center;">
    <div class="card glass-panel" style="width:100%; max-width:450px;">
        <button onclick="document.getElementById('editModal').style.display='none'" style="float:right; background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;">&times;</button>
        <h2>Override Protocol</h2>
        <form action="<?= site_url('admin/updateUser') ?>" method="post" class="premium-form" style="display:flex; flex-direction:column;">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group"><label>Node Identity</label><input type="text" name="username" id="edit_username" required></div>
            <div class="form-group"><label>Transmission Vector</label><input type="email" name="email" id="edit_email" required></div>
            <div class="form-group"><label>Clearance Array</label>
                <select name="role" id="edit_role" required>
                    <option value="admin">Administrator</option><option value="staff">Staff Command</option><option value="customer">Customer Standard</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="margin-top: 15px;">Execute Update</button>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, username, email, role) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_role').value = role;
        document.getElementById('editModal').style.display = 'flex';
    }
</script>

<!-- 3. Include Footer (Chatbot + Scripts) -->
<?= $this->include('theme/footer') ?>