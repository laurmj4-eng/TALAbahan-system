<!-- 1. Include Shared Header (CSS/Theme) -->
<?= $this->include('theme/header') ?>

<!-- 2. Include Shared Sidebar -->
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
        max-width: 700px;
        position: relative;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        transform: translateY(20px);
        transition: transform 0.3s ease;
        max-height: 90vh;
        overflow-y: auto;
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

    .modal-close-btn:hover {
        background: rgba(239, 68, 68, 0.2);
        color: #fca5a5;
        border-color: rgba(239, 68, 68, 0.3);
        transform: rotate(90deg);
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

    /* Form Adjustment for Modal */
    .modal .premium-form {
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    .modal .form-group {
        margin-bottom: 5px;
    }

    .modal .btn-primary {
        grid-column: 1 / -1;
        margin-top: 20px;
        width: 100%;
        height: 60px;
        font-size: 1.1rem;
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
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 20px;">
            <div>
                <h2 style="font-size: 2.2rem; margin-bottom: 0;">Daily Seafood Inventory 🐟</h2>
                <p style="color: rgba(255,255,255,0.6); margin-top: 10px; margin-bottom: 0;">Manage and update your seafood inventory items below.</p>
            </div>
            <button class="btn-primary" onclick="openModal('addProductModal')" style="padding: 12px 25px; border-radius: 12px; height: fit-content;">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </div>

        <div class="table-responsive glass-panel" style="padding: 20px; border-radius: 15px;">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>PICTURE</th>
                        <th>PRODUCT NAME</th>
                        <th>COST PRICE</th>
                        <th>SELLING PRICE</th>
                        <th>STOCK LEVEL</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
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
                            <td>₱<?= number_format($p['cost_price'], 2) ?></td>
                            <td>₱<?= number_format($p['selling_price'], 2) ?></td>
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
                        <tr><td colspan="7" style="text-align: center; color: #777; padding: 40px;">No products found in inventory.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <button class="modal-close-btn" onclick="closeModal('addProductModal')">&times;</button>
            <h2 class="modal-header">Add New Product</h2>
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
            <h2 class="modal-header">Edit Product</h2>
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

<!-- 4. Include Shared Footer (Chatbot + Navigation Logic) -->
<?= $this->include('theme/footer') ?>

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

            document.getElementById('editProductId').value = result.id;
            document.getElementById('editProductName').value = result.name;
            document.getElementById('editProductUnit').value = result.unit;
            document.getElementById('editCostPrice').value = result.cost_price;
            document.getElementById('editSellingPrice').value = result.selling_price;
            document.getElementById('editCurrentStock').value = result.current_stock;

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

        // CSRF Token Info from CodeIgniter
        const csrfTokenName = '<?= csrf_token() ?>';
        const csrfHash = document.querySelector('input[name="' + csrfTokenName + '"]').value;

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
                    document.querySelector('input[name="' + csrfTokenName + '"]').value = result.token;
                }
            }
        } catch (error) {
            console.error('Error deleting product:', error);
            alert('An error occurred while deleting the product.');
        }
    }
</script>