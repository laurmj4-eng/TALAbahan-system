<?php
/**
 * @var array $users
 */
?>
<!-- 1. Include Shared Header -->
<?= $this->include('theme/header') ?>

<!-- 2. Include Shared Sidebar -->
<?= $this->include('theme/sidebar') ?>

<!-- MAIN CONTENT -->
<main class="main-content">
    
    <?php if(session()->getFlashdata('msg')): ?>
        <div class="premium-alert success glass-panel" id="system-alert">
            <i class="fas fa-check-circle"></i>
            <div><?= session()->getFlashdata('msg') ?></div>
        </div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="premium-alert error glass-panel" id="system-alert">
            <i class="fas fa-times-circle"></i>
            <div><?= session()->getFlashdata('error') ?></div>
        </div>
    <?php endif; ?>

    <!-- USER MANAGEMENT SECTION -->
    <div class="card glass-panel">
        <h2 style="font-size: 2.2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">System Architecture Database</h2>
        <p style="color: rgba(255,255,255,0.6); margin-top: 15px; margin-bottom: 30px;">Append, modify, or terminate entity access securely.</p>
        
        <form action="<?= site_url('admin/saveUser') ?>" method="post" class="premium-form">
            <?= csrf_field() ?>
            <div class="form-group"><label>ID</label><input type="text" name="username" placeholder="Username..." required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="Your Gmail" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="••••••••" required></div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff Member</option>
                    <option value="customer" selected>Customer</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Add+</button>
        </form>

        <div class="glass-table-container">
            <table class="premium-table">
                <thead><tr><th>ID</th><th>Email Address</th><th>Role</th><th class="action-cell">ACTIONS</th></tr></thead>
                <tbody>
                    <?php if(!empty($users)): foreach($users as $user): ?>
                    <tr>
                        <td><strong><?= esc($user['username']) ?></strong></td>
                        <td><?= esc($user['email']) ?></td>
                        <td><span class="role-badge role-<?= esc($user['role']) ?>"><?= esc($user['role']) ?></span></td>
                        <td class="action-cell">
                            <div class="action-btns">
                                <button onclick="openEditModal(<?= $user['id'] ?>, '<?= esc($user['username']) ?>', '<?= esc($user['email']) ?>', '<?= esc($user['role']) ?>')" class="btn-edit">
                                    <i class="fas fa-edit"></i> EDIT
                                </button>
                                <a href="<?= site_url('admin/deleteUser/'.$user['id']) ?>" class="btn-delete" onclick="return confirm('Confirm Termination?')">
                                    <i class="fas fa-trash-alt"></i> DELETE
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4" style="text-align:center; padding: 30px;">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- EDIT MODAL (Specific to this view) -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <button class="modal-close-btn" onclick="closeModal('editModal')">&times;</button>
        <div class="modal-header">Override Protocol</div>
        <form action="<?= site_url('admin/updateUser') ?>" method="post" class="premium-form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label for="edit_username">Node Identity</label>
                <input type="text" name="username" id="edit_username" required>
            </div>
            <div class="form-group">
                <label for="edit_email">Transmission Vector</label>
                <input type="email" name="email" id="edit_email" required>
            </div>
            <div class="form-group">
                <label for="edit_password">New Clearance Key (Optional)</label>
                <input type="password" name="password" id="edit_password" placeholder="Leave blank to keep current password">
            </div>
            <div class="form-group">
                <label for="edit_role">Clearance Array</label>
                <select name="role" id="edit_role" required>
                    <option value="admin">Administrator</option>
                    <option value="staff">Staff Command</option>
                    <option value="customer">Customer Standard</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Execute Update</button>
        </form>
    </div>
</div>

<script>
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    function openEditModal(id, username, email, role) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_role').value = role;
        document.getElementById('edit_password').value = ''; // Clear password field on open
        document.getElementById('editModal').classList.add('show');
    }
</script>

<?= $this->include('theme/footer') ?>
