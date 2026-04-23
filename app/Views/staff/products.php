<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management | Staff</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        
        body { 
            margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(120deg, #1e1b4b, #3b0764, #0f172a, #082f49);
            background-size: 300% 300%;
            animation: gradientBg 15s ease infinite;
            color: #ffffff; display: flex; height: 100vh; overflow: hidden; 
        }
        
        @keyframes gradientBg {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border-radius: 20px;
        }

        .main-content { 
            flex: 1; 
            padding: 40px; 
            overflow-y: auto; 
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h1 { 
            margin: 0; 
            font-weight: 700; 
            font-size: 2rem; 
            color: #fff; 
        }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: rgba(168, 85, 247, 0.3);
            border: 1px solid rgba(168, 85, 247, 0.5);
            color: #c4b5fd;
        }

        .btn-primary:hover {
            background: rgba(168, 85, 247, 0.4);
            transform: translateY(-2px);
        }

        .btn-back {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
        }

        .btn-back:hover {
            background: rgba(59, 130, 246, 0.3);
        }

        /* Search Bar */
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-input {
            flex: 1;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: #fff;
            font-family: inherit;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .search-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(168, 85, 247, 0.5);
        }

        /* Table */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background: rgba(255, 255, 255, 0.05);
        }

        th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .stock-low {
            color: #fca5a5;
            font-weight: 600;
        }

        .stock-ok {
            color: #86efac;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-edit {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
        }

        .btn-edit:hover {
            background: rgba(59, 130, 246, 0.3);
        }

        .btn-stock {
            background: rgba(234, 179, 8, 0.2);
            color: #fcd34d;
        }

        .btn-stock:hover {
            background: rgba(234, 179, 8, 0.3);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .alert.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #86efac;
        }

        .alert.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
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
            max-width: 650px;
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

        .modal-header {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 30px;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 20px;
            background: linear-gradient(to right, #fff, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(168, 85, 247, 0.5);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            justify-content: flex-end;
        }

        .btn-modal {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-modal.primary {
            background: rgba(168, 85, 247, 0.4);
            color: #e9d5ff;
        }

        .btn-modal.primary:hover {
            background: rgba(168, 85, 247, 0.5);
        }

        .btn-modal.cancel {
            background: rgba(107, 114, 128, 0.3);
            color: rgba(255, 255, 255, 0.7);
        }

        .btn-modal.cancel:hover {
            background: rgba(107, 114, 128, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        .empty-state p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="header">
            <h1>📦 Product Management</h1>
            <div style="display: flex; gap: 10px;">
                <a href="<?= site_url('staff/dashboard') ?>" class="btn btn-back">← Back to Dashboard</a>
                <button class="btn btn-primary" onclick="openAddModal()">+ Add Product</button>
            </div>
        </div>

        <div id="alertContainer"></div>

        <div class="glass-panel">
            <div class="search-bar">
                <input type="text" id="searchInput" class="search-input" placeholder="Search products by name or category...">
                <button class="btn btn-primary" onclick="filterProducts()">Search</button>
            </div>

            <div class="table-responsive">
                <table id="productsTable">
                    <thead>
                        <tr>
                            <th>Picture</th>
                            <th>Product Name</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Current Stock</th>
                            <th>Unit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsBody">
                        <tr><td colspan="7" style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">Loading products...</td></tr>
                    </tbody>
                </table>
            </div>

            <?php if (empty($products)): ?>
            <div class="empty-state">
                <p>No products found</p>
                <button class="btn btn-primary" onclick="openAddModal()">Add First Product</button>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- ADD/EDIT PRODUCT MODAL -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Add New Product</div>
            <form id="productForm" onsubmit="handleProductSubmit(event)">
                <input type="hidden" id="productId" value="">
                
                <div class="form-group">
                    <label for="productName">Product Name *</label>
                    <input type="text" id="productName" name="name" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="unit">Unit *</label>
                        <input type="text" id="unit" name="unit" required value="piece">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="costPrice">Cost Price (₱) *</label>
                        <input type="number" id="costPrice" name="cost_price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="sellingPrice">Selling Price (₱) *</label>
                        <input type="number" id="sellingPrice" name="selling_price" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="initialStock">Initial Stock</label>
                        <input type="number" id="initialStock" name="initial_stock" step="0.01" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="currentStock">Current Stock *</label>
                        <input type="number" id="currentStock" name="current_stock" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="wastageQty">Wastage Qty</label>
                    <input type="number" id="wastageQty" name="wastage_qty" step="0.01" min="0" value="0">
                </div>

                <div class="form-group">
                    <label for="image">Product Picture</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-modal primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- QUICK STOCK UPDATE MODAL -->
    <div id="stockModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">Update Stock</div>
            <form id="stockForm" onsubmit="handleStockSubmit(event)">
                <input type="hidden" id="stockProductId" value="">
                
                <div class="form-group">
                    <label>Product: <strong id="stockProductName"></strong></label>
                </div>

                <div class="form-group">
                    <label for="newStock">New Stock Quantity *</label>
                    <input type="number" id="newStock" name="current_stock" step="0.01" min="0" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal cancel" onclick="closeStockModal()">Cancel</button>
                    <button type="submit" class="btn-modal primary">Update Stock</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        async function loadProducts() {
            try {
                const response = await fetch('<?= site_url('staff/getProducts') ?>');
                const products = await response.json();
                renderProducts(products);
            } catch (error) {
                showAlert('Error loading products', 'error');
            }
        }

        function renderProducts(products) {
            const tbody = document.getElementById('productsBody');
            
            if (!products || products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">No products available</td></tr>';
                return;
            }

            tbody.innerHTML = products.map(p => `
                <tr>
                    <td>
                        ${p.image ? 
                            `<img src="<?= base_url('uploads/products/') ?>/${p.image}" alt="${p.name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">` :
                            `<div style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-image" style="color: rgba(255,255,255,0.2);"></i></div>`
                        }
                    </td>
                    <td><strong>${p.name}</strong></td>
                    <td>₱${parseFloat(p.cost_price || 0).toFixed(2)}</td>
                    <td>₱${parseFloat(p.selling_price || 0).toFixed(2)}</td>
                    <td class="${parseFloat(p.current_stock || 0) <= 5 ? 'stock-low' : 'stock-ok'}">
                        ${parseFloat(p.current_stock || 0).toFixed(2)}
                    </td>
                    <td>${p.unit || 'piece'}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-sm btn-edit" onclick="editStock(${p.id}, '${p.name}', ${p.current_stock || 0})">Stock</button>
                            <button class="btn-sm btn-edit" onclick="editProduct(${p.id})">Edit</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function openAddModal() {
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('productModal').classList.add('show');
            document.querySelector('.modal-header').textContent = 'Add New Product';
        }

        function closeModal() {
            document.getElementById('productModal').classList.remove('show');
        }

        async function editProduct(productId) {
            try {
                const response = await fetch(`<?= site_url('staff/getDetails/') ?>${productId}`);
                const p = await response.json();
                
                if (p.error) {
                    showAlert(p.error, 'error');
                    return;
                }

                document.getElementById('productId').value = p.id;
                document.getElementById('productName').value = p.name;
                document.getElementById('unit').value = p.unit;
                document.getElementById('costPrice').value = p.cost_price;
                document.getElementById('sellingPrice').value = p.selling_price;
                document.getElementById('initialStock').value = p.initial_stock;
                document.getElementById('currentStock').value = p.current_stock;
                document.getElementById('wastageQty').value = p.wastage_qty;

                document.querySelector('.modal-header').textContent = 'Edit Product';
                document.getElementById('productModal').classList.add('show');
            } catch (error) {
                showAlert('Error fetching product details', 'error');
            }
        }

        function editStock(productId, productName, currentStock) {
            document.getElementById('stockProductId').value = productId;
            document.getElementById('stockProductName').textContent = productName;
            document.getElementById('newStock').value = currentStock;
            document.getElementById('stockModal').classList.add('show');
        }

        function closeStockModal() {
            document.getElementById('stockModal').classList.remove('show');
        }

        async function handleProductSubmit(e) {
            e.preventDefault();
            const productId = document.getElementById('productId').value;
            const url = productId ? '<?= site_url('staff/updateProduct') ?>' : '<?= site_url('staff/addProduct') ?>';
            const form = document.getElementById('productForm');
            const formData = new FormData(form);
            if (productId) formData.append('id', productId);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                if (result.ok) {
                    showAlert(result.message, 'success');
                    closeModal();
                    loadProducts();
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Error saving product', 'error');
            }
        }

        async function handleStockSubmit(e) {
            e.preventDefault();
            const productId = document.getElementById('stockProductId').value;
            const newStock = document.getElementById('newStock').value;

            try {
                const response = await fetch('<?= site_url('staff/updateStock') ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `product_id=${productId}&current_stock=${newStock}`
                });

                const result = await response.json();
                if (result.ok) {
                    showAlert(result.message, 'success');
                    closeStockModal();
                    loadProducts();
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Error updating stock', 'error');
            }
        }

        function showAlert(message, type) {
            const container = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert ${type}`;
            alert.textContent = message;
            container.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
        }

        function filterProducts() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.getElementById('productsTable').getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(search) ? '' : 'none';
            });
        }

        // Load products when page loads
        document.addEventListener('DOMContentLoaded', loadProducts);
    </script>

</body>
</html>
