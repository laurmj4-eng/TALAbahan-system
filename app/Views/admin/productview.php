<?php
/**
 * @var array $products
 */
?>
<!-- 1. Include Shared Header (CSS/Theme) -->
<?= $this->include('theme/header') ?>

<!-- 2. Include Shared Sidebar -->
<?= $this->include('theme/sidebar') ?>

<style>
    /* Custom adjustments for this view */
    @media (min-width: 769px) {
        .modal .premium-form {
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
    }

    /* Fix for mobile responsiveness: Ensure natural scrolling */
    @media (max-width: 768px) {
        .modal {
            padding: 0 !important;
            align-items: flex-start !important;
            overflow-y: auto !important;
        }

        .modal-content {
            margin: 0 !important;
            width: 100% !important;
            min-height: 100vh !important;
            border-radius: 0 !important;
            padding: 25px 20px 100px 20px !important; /* Extra bottom padding for button visibility */
            display: block !important;
            transform: none !important;
            overflow-y: visible !important;
        }

        .modal-header {
            font-size: 1.8rem !important;
            margin-bottom: 20px !important;
        }

        .premium-form {
            display: flex !important;
            flex-direction: column !important;
            gap: 15px !important;
        }

        .modal .btn-primary {
            position: relative !important;
            width: 100% !important;
            margin-top: 30px !important;
            padding: 16px !important;
        }
    }

    .modal .form-group {
        margin-bottom: 5px;
    }

    /* --- LIVE STATUS TOGGLE SWITCH --- */
    .toggle-switch {
        position: relative;
        width: 48px;
        height: 26px;
        display: inline-block;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 26px;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .toggle-slider::before {
        content: '';
        position: absolute;
        height: 20px;
        width: 20px;
        left: 2px;
        bottom: 2px;
        background: #fff;
        border-radius: 50%;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
    .toggle-switch input:checked + .toggle-slider {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border-color: #22c55e;
    }
    .toggle-switch input:checked + .toggle-slider::before {
        transform: translateX(22px);
    }
    .toggle-switch input:disabled + .toggle-slider {
        opacity: 0.5;
        cursor: wait;
    }

    /* --- LIVE INDICATOR BADGE --- */
    .live-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-top: 6px;
    }
    .live-indicator.live {
        background: rgba(34, 197, 94, 0.15);
        color: #4ade80;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }
    .live-indicator.hidden {
        background: rgba(239, 68, 68, 0.12);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }
    .live-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
    }
    .live-indicator.live .live-dot {
        background: #4ade80;
        box-shadow: 0 0 6px #4ade80;
        animation: livePulse 1.5s ease-in-out infinite;
    }
    .live-indicator.hidden .live-dot {
        background: #f87171;
    }
    @keyframes livePulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.8); }
    }

    .live-status-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
    }
</style>

<!-- 3. Main Content Wrapper -->
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

    <div class="card glass-panel" style="padding: 40px; border-radius: 30px;">
        <div class="flex-header" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 20px;">
            <div>
                <h2 style="font-size: 2.2rem; margin-bottom: 0;">Daily Seafood Inventory 🐟</h2>
                <p style="color: rgba(255,255,255,0.6); margin-top: 10px; margin-bottom: 0;">Manage and update your seafood inventory items below.</p>
            </div>
            <button class="btn-primary" onclick="openModal('addProductModal')" style="padding: 12px 25px; border-radius: 12px; height: fit-content;">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </div>

        <div class="glass-table-container">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>PICTURE</th>
                        <th>PRODUCT NAME</th>
                        <th>COST PRICE</th>
                        <th>SELLING PRICE</th>
                        <th>STOCK LEVEL</th>
                        <th>STATUS</th>
                        <th>LIVE STATUS</th>
                        <th class="action-cell">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <?php if ($p['image']): ?>
                                    <img src="<?= base_url('uploads/products/' . $p['image']) ?>" alt="<?= esc($p['name']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="color: rgba(255,255,255,0.3);"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong style="color: #fff;"><?= esc($p['name']) ?></strong></td>
                            <td>&#8369;<?= number_format($p['cost_price'], 2) ?></td>
                            <td>&#8369;<?= number_format($p['selling_price'], 2) ?></td>
                            <td><?= esc($p['current_stock']) ?> <?= esc($p['unit']) ?></td>
                            <td>
                                <?php if ($p['current_stock'] > 5): ?>
                                    <span class="status-badge status-active">In Stock</span>
                                <?php elseif ($p['current_stock'] > 0): ?>
                                    <span class="status-badge status-warning">Low Stock</span>
                                <?php else: ?>
                                    <span class="status-badge status-danger">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="live-status-cell">
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="toggleAvail_<?= $p['id'] ?>" 
                                               <?= ($p['is_available'] ?? 1) ? 'checked' : '' ?> 
                                               onchange="toggleAvailability(<?= $p['id'] ?>, this)">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="live-indicator <?= ($p['is_available'] ?? 1) ? 'live' : 'hidden' ?>" id="liveLabel_<?= $p['id'] ?>">
                                        <span class="live-dot"></span>
                                        <?= ($p['is_available'] ?? 1) ? 'Live' : 'Hidden' ?>
                                    </span>
                                </div>
                            </td>
                            <td class="action-cell">
                                <button class="btn-edit" onclick="editProduct(<?= $p['id'] ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn-delete" onclick="deleteProduct(<?= $p['id'] ?>)">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="8" style="text-align: center; color: #777; padding: 40px;">No products found in inventory.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <button class="modal-close-btn" onclick="closeModal('addProductModal')">&times;</button>
            <div class="modal-header">Add New Product</div>
             <form id="addProductForm" action="<?= site_url('admin/products/store') ?>" method="post" class="premium-form" enctype="multipart/form-data">
                 <?= csrf_field() ?>
                <div class="form-group">
                    <label for="productName">Product Name</label>
                    <input type="text" id="productName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="productUnit">Unit</label>
                    <select id="productUnit" name="unit">
                        <option value="kg">Kilogram (kg)</option>
                        <option value="pcs">Pieces (pcs)</option>
                        <option value="g">Gram (g)</option>
                        <option value="L">Liter (L)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="costPrice">Cost Price</label>
                    <input type="number" id="costPrice" step="0.01" name="cost_price" required>
                </div>
                <div class="form-group">
                    <label for="sellingPrice">Selling Price</label>
                    <input type="number" id="sellingPrice" step="0.01" name="selling_price" required>
                </div>
                <div class="form-group">
                    <label for="initialQuantity">Initial Quantity</label>
                    <input type="number" id="initialQuantity" name="quantity" required>
                </div>
                <div class="form-group">
                    <label for="productImage">Product Image</label>
                    <input type="file" id="productImage" name="image" accept="image/*">
                </div>
                <button type="submit" class="btn-primary">Add Product</button>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <button class="modal-close-btn" onclick="closeModal('editProductModal')">&times;</button>
            <div class="modal-header">Edit Product</div>
            <form id="editProductForm" action="<?= site_url('admin/products/update') ?>" method="post" class="premium-form" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editProductId">
                <div class="form-group">
                    <label for="editProductName">Product Name</label>
                    <input type="text" id="editProductName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="editProductUnit">Unit</label>
                    <select id="editProductUnit" name="unit">
                        <option value="kg">Kilogram (kg)</option>
                        <option value="pcs">Pieces (pcs)</option>
                        <option value="g">Gram (g)</option>
                        <option value="L">Liter (L)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editCostPrice">Cost Price</label>
                    <input type="number" id="editCostPrice" step="0.01" name="cost_price" required>
                </div>
                <div class="form-group">
                    <label for="editSellingPrice">Selling Price</label>
                    <input type="number" id="editSellingPrice" step="0.01" name="selling_price" required>
                </div>
                <div class="form-group">
                    <label for="editCurrentStock">Current Stock</label>
                    <input type="number" id="editCurrentStock" name="current_stock" required>
                </div>
                <div class="form-group">
                    <label for="editProductImage">Product Image (Leave blank to keep current)</label>
                    <input type="file" id="editProductImage" name="image" accept="image/*">
                </div>
                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</main>

<script>
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.add('show');
    }

    document.getElementById('addProductForm').onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                alert('Product added successfully!');
                location.reload();
            } else {
                alert('Failed to add product: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error adding product:', error);
            alert('An error occurred while adding the product.');
        }
    };

    async function editProduct(productId) {
        const modal = document.getElementById('editProductModal');
        openModal('editProductModal');

        try {
            const response = await fetch(`<?= site_url('admin/products/getDetails/') ?>${productId}`);
            const result = await response.json();

            if (result.error) {
                alert(result.error);
                closeModal('editProductModal');
                return;
            }

            const product = result.data;
            document.getElementById('editProductId').value = product.id;
            document.getElementById('editProductName').value = product.name;
            document.getElementById('editProductUnit').value = product.unit;
            document.getElementById('editCostPrice').value = product.cost_price;
            document.getElementById('editSellingPrice').value = product.selling_price;
            document.getElementById('editCurrentStock').value = product.current_stock;

        } catch (error) {
            console.error('Error fetching product details:', error);
            alert('Failed to load product details.');
            closeModal('editProductModal');
        }
    }

    document.getElementById('editProductForm').onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                alert('Product updated successfully!');
                location.reload();
            } else {
                alert('Failed to update product: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error updating product:', error);
            alert('An error occurred while updating the product.');
        }
    };

    async function deleteProduct(productId) {
        if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
            return;
        }

        // CSRF Token Info from Meta Tags in header.php
        const csrfTokenName = document.querySelector('meta[name="csrf-name"]').getAttribute('content');
        const csrfHash = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const formData = new FormData();
        formData.append(csrfTokenName, csrfHash);
        formData.append('id', productId);

        try {
            const response = await fetch('<?= site_url('admin/products/delete') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                alert('Product deleted successfully!');
                location.reload();
            } else {
                alert('Failed to delete product: ' + (result.message || 'Unknown error'));
                if (result.token) {
                    // Update meta tag for subsequent requests
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', result.token);
                }
            }
        } catch (error) {
            console.error('Error deleting product:', error);
            alert('An error occurred while deleting the product.');
        }
    }

    /**
     * AJAX Toggle: Flip product Live Availability without page reload
     */
    async function toggleAvailability(productId, toggleEl) {
        toggleEl.disabled = true;

        const csrfTokenName = document.querySelector('meta[name="csrf-name"]').getAttribute('content');
        const csrfHash = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const formData = new FormData();
        formData.append(csrfTokenName, csrfHash);

        try {
            const response = await fetch(`<?= site_url('admin/products/toggleStatus/') ?>${productId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                // Update the live indicator badge
                const label = document.getElementById('liveLabel_' + productId);
                if (result.is_available === 1) {
                    label.className = 'live-indicator live';
                    label.innerHTML = '<span class="live-dot"></span> Live';
                } else {
                    label.className = 'live-indicator hidden';
                    label.innerHTML = '<span class="live-dot"></span> Hidden';
                }
            } else {
                // Revert the toggle on failure
                toggleEl.checked = !toggleEl.checked;
                alert('Error: ' + (result.message || 'Unknown error'));
            }

            // Update CSRF token if returned
            if (result.token) {
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', result.token);
            }
        } catch (error) {
            console.error('Toggle error:', error);
            toggleEl.checked = !toggleEl.checked;
            alert('An error occurred while toggling availability.');
        } finally {
            toggleEl.disabled = false;
        }
    }
</script>
<?= $this->include('theme/footer') ?>
