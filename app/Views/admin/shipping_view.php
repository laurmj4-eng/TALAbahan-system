<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<style>
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
        backdrop-filter: blur(10px);
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal.show {
        display: flex;
        opacity: 1;
    }

    .modal-content {
        background: rgba(20, 20, 45, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 30px;
        padding: 40px;
        width: 90%;
        max-width: 500px;
        position: relative;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        transform: translateY(20px);
        transition: transform 0.3s ease;
    }

    .modal.show .modal-content {
        transform: translateY(0);
    }

    .modal-close-btn {
        position: absolute;
        top: 25px;
        right: 25px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.6);
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.3s;
        font-size: 1.5rem;
        z-index: 10;
    }

    .modal-header {
        margin-top: 0;
        margin-bottom: 30px;
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 20px;
        background: linear-gradient(to right, #fff, #a855f7);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .premium-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255,255,255,0.6);
    }

    .form-group input, .form-group select {
        padding: 15px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        color: #fff;
        font-family: inherit;
        outline: none;
        transition: 0.3s;
    }

    .form-group input:focus {
        border-color: #a855f7;
        background: rgba(255,255,255,0.08);
    }

    .btn-submit {
        margin-top: 10px;
        padding: 15px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #a855f7);
        color: #fff;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(168, 85, 247, 0.3);
    }
</style>

<main class="main-content">
    <div class="card glass-panel" style="padding: 40px; border-radius: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 20px;">
            <div>
                <h2 style="font-size: 2.2rem; margin-bottom: 0;">Shipping Locations 📍</h2>
                <p style="color: rgba(255,255,255,0.6); margin-top: 10px; margin-bottom: 0;">Manage specific barangays and places allowed for delivery.</p>
            </div>
            <button class="btn-primary" onclick="openModal('addLocationModal')" style="padding: 12px 25px; border-radius: 12px; height: fit-content;">
                <i class="fas fa-plus"></i> Add Location
            </button>
        </div>

        <div class="table-responsive glass-panel" style="padding: 20px; border-radius: 15px;">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>BARANGAY NAME</th>
                        <th>CITY / MUNICIPALITY</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($locations)): foreach ($locations as $l): ?>
                        <tr>
                            <td><strong style="color: #fff;"><?= esc($l['barangay_name']) ?></strong></td>
                            <td><?= esc($l['city_municipality']) ?></td>
                            <td>
                                <?php if ($l['is_active']): ?>
                                    <span class="role-badge role-staff">Shippable</span>
                                <?php else: ?>
                                    <span class="role-badge role-admin">Not Shippable</span>
                                <?php endif; ?>
                            </td>
                            <td class="action-cell">
                                <div class="action-btns">
                                    <button class="btn-edit" onclick="editLocation(<?= $l['id'] ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn-delete" onclick="deleteLocation(<?= $l['id'] ?>)">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4" style="text-align: center; color: rgba(255,255,255,0.3); padding: 40px;">No shipping locations found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Location Modal -->
    <div id="addLocationModal" class="modal">
        <div class="modal-content">
            <button class="modal-close-btn" onclick="closeModal('addLocationModal')">&times;</button>
            <h2 class="modal-header">Add New Location</h2>
            <form id="addLocationForm" onsubmit="submitAdd(event)" class="premium-form">
                <div class="form-group">
                    <label>Barangay Name</label>
                    <input type="text" name="barangay_name" placeholder="e.g. Villamonte" required>
                </div>
                <div class="form-group">
                    <label>City / Municipality</label>
                    <input type="text" name="city_municipality" value="Bacolod City" required>
                </div>
                <button type="submit" class="btn-submit">Add Shipping Location</button>
            </form>
        </div>
    </div>

    <!-- Edit Location Modal -->
    <div id="editLocationModal" class="modal">
        <div class="modal-content">
            <button class="modal-close-btn" onclick="closeModal('editLocationModal')">&times;</button>
            <h2 class="modal-header">Edit Location</h2>
            <form id="editLocationForm" onsubmit="submitEdit(event)" class="premium-form">
                <input type="hidden" name="id" id="editLocationId">
                <div class="form-group">
                    <label>Barangay Name</label>
                    <input type="text" name="barangay_name" id="editBarangay" required>
                </div>
                <div class="form-group">
                    <label>City / Municipality</label>
                    <input type="text" name="city_municipality" id="editCity" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" id="editStatus">
                        <option value="1">Shippable</option>
                        <option value="0">Not Shippable</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Update Shipping Location</button>
            </form>
        </div>
    </div>
</main>

<?= $this->include('theme/footer') ?>

<script>
    function openModal(id) { document.getElementById(id).classList.add('show'); }
    function closeModal(id) { document.getElementById(id || 'addLocationModal').classList.remove('show'); document.getElementById('editLocationModal').classList.remove('show'); }

    async function submitAdd(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const res = await fetch('<?= site_url('admin/shipping/store') ?>', { method: 'POST', body: formData });
        const result = await res.json();
        if(result.status === 'success') location.reload(); else alert(result.message);
    }

    async function editLocation(id) {
        const res = await fetch(`<?= site_url('admin/shipping/getDetails/') ?>${id}`);
        const data = await res.json();
        document.getElementById('editLocationId').value = data.id;
        document.getElementById('editBarangay').value = data.barangay_name;
        document.getElementById('editCity').value = data.city_municipality;
        document.getElementById('editStatus').value = data.is_active;
        openModal('editLocationModal');
    }

    async function submitEdit(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const res = await fetch('<?= site_url('admin/shipping/update') ?>', { method: 'POST', body: formData });
        const result = await res.json();
        if(result.status === 'success') location.reload(); else alert(result.message);
    }

    async function deleteLocation(id) {
        if(!confirm('Are you sure you want to delete this shipping location?')) return;
        const formData = new FormData();
        formData.append('id', id);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const res = await fetch('<?= site_url('admin/shipping/delete') ?>', { method: 'POST', body: formData });
        const result = await res.json();
        if(result.status === 'success') location.reload(); else alert(result.message);
    }
</script>
