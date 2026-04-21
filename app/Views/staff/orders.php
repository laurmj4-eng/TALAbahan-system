<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking | Staff</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .btn-back {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
        }

        .btn-back:hover {
            background: rgba(59, 130, 246, 0.3);
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

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-completed {
            background: rgba(16, 185, 129, 0.2);
            color: #86efac;
        }

        .status-pending {
            background: rgba(234, 179, 8, 0.2);
            color: #fcd34d;
        }

        .status-cancelled {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .btn-view {
            padding: 6px 12px;
            font-size: 0.85rem;
            background: rgba(168, 85, 247, 0.2);
            border: 1px solid rgba(168, 85, 247, 0.3);
            color: #c4b5fd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-view:hover {
            background: rgba(168, 85, 247, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: rgba(30, 27, 75, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            max-width: 700px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
        }

        .order-detail {
            margin-bottom: 20px;
        }

        .order-detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }

        .detail-item {
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
        }

        .detail-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            margin-bottom: 4px;
        }

        .detail-value {
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .items-table thead {
            background: rgba(255, 255, 255, 0.05);
        }

        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-close-modal {
            padding: 10px 20px;
            background: rgba(107, 114, 128, 0.3);
            color: rgba(255, 255, 255, 0.7);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-close-modal:hover {
            background: rgba(107, 114, 128, 0.4);
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="header">
            <h1>📋 Order Tracking</h1>
            <a href="<?= site_url('staff/dashboard') ?>" class="btn btn-back">← Back to Dashboard</a>
        </div>

        <div class="glass-panel">
            <div class="table-responsive">
                <table id="ordersTable">
                    <thead>
                        <tr>
                            <th>Transaction Code</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date & Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ordersBody">
                        <tr><td colspan="7" style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">Loading orders...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- ORDER DETAIL MODAL -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Order Details</div>
            
            <div class="order-detail-row">
                <div class="detail-item">
                    <div class="detail-label">Transaction Code</div>
                    <div class="detail-value" id="detailTxnCode">-</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Customer Name</div>
                    <div class="detail-value" id="detailCustomer">-</div>
                </div>
            </div>

            <div class="order-detail-row">
                <div class="detail-item">
                    <div class="detail-label">Total Amount</div>
                    <div class="detail-value" id="detailTotal" style="color: #86efac;">₱0.00</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value" id="detailStatus">-</div>
                </div>
            </div>

            <div class="order-detail-row">
                <div class="detail-item">
                    <div class="detail-label">Date & Time</div>
                    <div class="detail-value" id="detailDateTime">-</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Items Count</div>
                    <div class="detail-value" id="detailItemCount">0</div>
                </div>
            </div>

            <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 1.1rem;">Order Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody id="detailItems">
                    <tr><td colspan="4">Loading items...</td></tr>
                </tbody>
            </table>

            <button class="btn-close-modal" onclick="closeOrderModal()">Close</button>
        </div>
    </div>

    <script>
        async function loadOrders() {
            try {
                const response = await fetch('<?= site_url('staff/getOrders') ?>');
                const orders = await response.json();
                renderOrders(orders);
            } catch (error) {
                console.error('Error loading orders:', error);
                document.getElementById('ordersBody').innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #f87171;">Error loading orders</td></tr>';
            }
        }

        function renderOrders(orders) {
            const tbody = document.getElementById('ordersBody');
            
            if (!orders || orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">No orders found</td></tr>';
                return;
            }

            tbody.innerHTML = orders.map(order => {
                const statusClass = `status-${order.status.toLowerCase()}`;
                const dateObj = new Date(order.created_at);
                const formattedDate = dateObj.toLocaleDateString() + ' ' + dateObj.toLocaleTimeString();
                
                return `
                    <tr>
                        <td><strong style="color: #a855f7;">${order.transaction_code}</strong></td>
                        <td>${order.customer_name || 'N/A'}</td>
                        <td>${order.item_count || 0} items</td>
                        <td style="color: #86efac; font-weight: 600;">₱${parseFloat(order.total_amount || 0).toFixed(2)}</td>
                        <td><span class="status-badge ${statusClass}">${order.status}</span></td>
                        <td style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">${formattedDate}</td>
                        <td>
                            <button class="btn-view" onclick="viewOrder(${order.id})">View Details</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function viewOrder(orderId) {
            try {
                const response = await fetch(`<?= site_url('staff/getOrderDetail') ?>/${orderId}`);
                const order = await response.json();
                
                document.getElementById('detailTxnCode').textContent = order.transaction_code || '-';
                document.getElementById('detailCustomer').textContent = order.customer_name || 'N/A';
                document.getElementById('detailTotal').textContent = '₱' + parseFloat(order.total_amount || 0).toFixed(2);
                document.getElementById('detailStatus').textContent = order.status;
                document.getElementById('detailItemCount').textContent = (order.items || []).length;
                
                const dateObj = new Date(order.created_at);
                document.getElementById('detailDateTime').textContent = dateObj.toLocaleString();

                // Render items table
                const itemsBody = document.getElementById('detailItems');
                if (order.items && order.items.length > 0) {
                    itemsBody.innerHTML = order.items.map(item => `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${parseFloat(item.quantity || 0).toFixed(2)} ${item.unit || 'pc'}</td>
                            <td>₱${parseFloat(item.unit_price || 0).toFixed(2)}</td>
                            <td style="color: #86efac; font-weight: 600;">₱${parseFloat(item.subtotal || 0).toFixed(2)}</td>
                        </tr>
                    `).join('');
                } else {
                    itemsBody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: rgba(255,255,255,0.5);">No items</td></tr>';
                }

                document.getElementById('orderModal').classList.add('show');
            } catch (error) {
                console.error('Error fetching order details:', error);
                alert('Error loading order details');
            }
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('show');
        }

        // Load orders when page loads
        document.addEventListener('DOMContentLoaded', loadOrders);

        // Auto-refresh orders every 30 seconds
        setInterval(loadOrders, 30000);
    </script>

</body>
</html>
