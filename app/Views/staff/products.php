<?= $this->include('theme/header') ?>

    <style>
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

        /* Toast Notification */

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
    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="header">
            <h1>📦 Product Inventory</h1>
            <div style="display: flex; gap: 10px;">
                <a href="<?= site_url('staff/dashboard') ?>" class="btn btn-back">← Back to Dashboard</a>
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
                        </tr>
                    </thead>
                    <tbody id="productsBody">
                        <tr><td colspan="6" style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">Loading products...</td></tr>
                    </tbody>
                </table>
            </div>

            <?php if (empty($products)): ?>
            <div class="empty-state">
                <p>No products available</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- CRUD MODALS REMOVED FOR STAFF -->

    <script>
        async function loadProducts() {
            try {
                const response = await fetch('<?= site_url('staff/getProducts') ?>');
                const result = await response.json();
                if (result.status === 'success') {
                    renderProducts(result.data);
                } else {
                    showAlert('Error loading products: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                showAlert('Error loading products', 'error');
            }
        }

        function renderProducts(products) {
            const tbody = document.getElementById('productsBody');
            
            if (!products || products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">No products available</td></tr>';
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
                </tr>
            `).join('');
        }

        // CRUD FUNCTIONS REMOVED FOR STAFF

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
