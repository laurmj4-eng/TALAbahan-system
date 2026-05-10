<?php
/**
 * @var array $locations
 */
?>
<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<style>
    /* Custom adjustments for this view */
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

    /* Toggle Switch Styles */
    .toggle-container {
        display: flex;
        align-items: center;
        gap: 15px;
        background: rgba(255, 255, 255, 0.05);
        padding: 15px 25px;
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 20px;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.2);
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #a855f7;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    .toggle-label {
        font-weight: 600;
        color: #fff;
    }
</style>

<main class="main-content">
    <div class="card glass-panel" style="padding: 40px; border-radius: 30px;">
        <div class="flex-header" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 20px;">
            <div>
                <h2 style="font-size: 2.2rem; margin-bottom: 0;">Shipping Locations 📍</h2>
                <p style="color: rgba(255,255,255,0.6); margin-top: 10px; margin-bottom: 0;">Manage specific barangays and places allowed for delivery.</p>
            </div>
            <button class="btn-primary" onclick="openModal('addLocationModal')" style="padding: 12px 25px; border-radius: 12px; height: fit-content;">
                <i class="fas fa-plus"></i> Add Location
            </button>
        </div>

        <!-- Global Shipping Toggle -->
        <div class="toggle-container">
            <span class="toggle-label">Ship to All Locations (Global)</span>
            <label class="switch">
                <input type="checkbox" id="globalShippingToggle" onchange="toggleGlobalShipping(this)" <?= $ship_to_all === '1' ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
            <span id="toggleStatus" style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">
                <?= $ship_to_all === '1' ? 'Currently: ON (Shipping to all)' : 'Currently: OFF (Specific locations only)' ?>
            </span>
        </div>

        <div class="glass-table-container">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>BARANGAY NAME</th>
                        <th>CITY / MUNICIPALITY</th>
                        <th>STATUS</th>
                        <th class="action-cell">ACTIONS</th>
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
                                    <button type="button" class="btn-edit" onclick="editLocation(<?= $l['id'] ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="btn-delete" onclick="deleteLocation(<?= $l['id'] ?>)">
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
            <div class="modal-header">Add New Location</div>
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
            <div class="modal-header">Edit Location</div>
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

<script>
    function openModal(id) { document.getElementById(id).classList.add('show'); }
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }

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

    async function toggleGlobalShipping(checkbox) {
        const isChecked = checkbox.checked ? '1' : '0';
        const formData = new FormData();
        formData.append('ship_to_all', isChecked);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const statusText = document.getElementById('toggleStatus');
        const originalText = statusText.innerText;
        statusText.innerText = 'Updating...';

        try {
            const res = await fetch('<?= site_url('admin/shipping/updateGlobal') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const result = await res.json();
            
            if (result.status === 'success') {
                statusText.innerText = isChecked === '1' ? 'Currently: ON (Shipping to all)' : 'Currently: OFF (Specific locations only)';
            } else {
                alert(result.message);
                checkbox.checked = !checkbox.checked;
                statusText.innerText = originalText;
            }
        } catch (error) {
            console.error('Error updating global shipping:', error);
            alert('An error occurred. Please try again.');
            checkbox.checked = !checkbox.checked;
            statusText.innerText = originalText;
        }
    }
</script>

<?= $this->include('theme/footer') ?>
