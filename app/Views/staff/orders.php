<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking | Staff</title>
    
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
            position: relative;
        }

        .modal-close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.8rem;
            color: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .modal-close-btn:hover {
            color: #fff;
        }

        .modal-header {
            font-size: 1.8rem;
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

        .items-table tfoot {
            font-weight: 700;
            background: rgba(255, 255, 255, 0.08);
        }
        .items-table tfoot td {
            padding: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
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
            background: rgba(107, 114, 128, 0.5);
            color: #fff;
        }
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
                <h1><i class="fas fa-clipboard-list"></i> Order Tracking</h1>
                <a href="<?= site_url('staff/dashboard') ?>" class="btn btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>

            <div class="glass-panel" style="padding: 20px; border-radius: 15px;">
                <table>
                    <thead>
                        <tr>
                            <th>TXN CODE</th>
                            <th>DATE</th>
                            <th>CUSTOMER</th>
                            <th>ITEMS</th>
                            <th>TOTAL AMOUNT</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): foreach ($orders as $order): ?>
                            <tr>
                                <td><strong style="color: #818cf8;"><?= esc($order['transaction_code']) ?></strong></td>
                                <td><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></td>
                                <td><strong style="color: #c084fc;"><?= esc($order['customer_name']) ?: 'Walk-in Customer' ?></strong></td>
                                <td><?= esc($order['item_count']) ?> items</td>
                                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <select onchange="updateStatus(<?= $order['id'] ?>, this.value)" style="padding: 8px 12px; border-radius: 10px; background: rgba(0,0,0,0.4); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.9rem;">
                                        <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Completed" <?= $order['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="btn-view" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="7" class="empty-state">No orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
    </main>

    <div id="orderDetailModal" class="modal">
        <div class="modal-content">
            <button class="modal-close-btn" onclick="closeModal()">&times;</button>
            <h2 class="modal-header">Order Details</h2>
            
            <div class="order-detail">
                <div class="order-detail-row">
                    <div class="detail-item">
                        <div class="detail-label">Transaction Code</div>
                        <div class="detail-value" id="modal-txn-code"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Customer Name</div>
                        <div class="detail-value" id="modal-customer-name"></div>
                    </div>
                </div>
                <div class="order-detail-row">
                    <div class="detail-item">
                        <div class="detail-label">Order Date</div>
                        <div class="detail-value" id="modal-order-date"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value" id="modal-status"></div>
                    </div>
                </div>
            </div>

            <h3 style="color: #fff; margin-top: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody id="modal-items-body">
                    <!-- Order items will be loaded here by JS -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;">Total:</td>
                        <td id="modal-total-amount"></td>
                    </tr>
                </tfoot>
            </table>

            <div style="text-align: right; margin-top: 30px;">
                <button class="btn-close-modal" onclick="closeModal()">Close</button>
            </div>
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
                            <button class="btn-view" onclick="viewOrderDetails(${order.id})">View Details</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function viewOrderDetails(orderId) {
            const modal = document.getElementById('orderDetailModal');
            modal.classList.add('show');

            try {
                const response = await fetch(`<?= site_url('staff/getOrderDetail/') ?>${orderId}`);
                const result = await response.json();

                if (result.error) {
                    alert(result.error);
                    closeModal();
                    return;
                }

                const order = result; // Assuming the controller returns the order directly

                document.getElementById('modal-txn-code').textContent = order.transaction_code;
                document.getElementById('modal-customer-name').textContent = order.customer_name || 'Walk-in Customer';
                document.getElementById('modal-order-date').textContent = new Date(order.created_at).toLocaleString();
                document.getElementById('modal-status').textContent = order.status;

                const itemsBody = document.getElementById('modal-items-body');
                itemsBody.innerHTML = '';
                let totalAmount = 0;

                if (order.items && order.items.length > 0) {
                    order.items.forEach(item => {
                        itemsBody.innerHTML += `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
                                <td>${item.quantity} ${item.unit}</td>
                                <td>₱${parseFloat(item.subtotal).toFixed(2)}</td>
                            </tr>
                        `;
                        totalAmount += parseFloat(item.subtotal);
                    });
                } else {
                    itemsBody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:rgba(255,255,255,0.6);">No items found for this order.</td></tr>`;
                }
                document.getElementById('modal-total-amount').textContent = `₱${totalAmount.toFixed(2)}`;

            } catch (error) {
                console.error('Error fetching order details:', error);
                alert('Failed to load order details.');
                closeModal();
            }
        }

        function closeModal() {
            document.getElementById('orderDetailModal').classList.remove('show');
        }

        async function updateStatus(orderId, newStatus) {
            // CSRF Token Info from CodeIgniter
            const csrfTokenName = '<?= csrf_token() ?>';
            const csrfHash = document.querySelector('input[name="' + csrfTokenName + '"]').value;

            const formData = new FormData();
            formData.append('id', orderId);
            formData.append('status', newStatus);
            formData.append(csrfTokenName, csrfHash);

            try {
                const response = await fetch('<?= site_url('admin/orders/updateStatus') ?>', { // Reusing admin endpoint for now
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();

                if (result.status === 'success') {
                    alert('Order status updated successfully!');
                    // Optionally, refresh the table or update the specific row
                    location.reload(); // Simple reload for now
                } else {
                    alert('Failed to update status: ' + (result.message || 'Unknown error'));
                    // Update CSRF token on failure
                    if (result.token) {
                        document.querySelector('input[name="' + csrfTokenName + '"]').value = result.token;
                    }
                    location.reload(); // Reload to revert status if update failed
                }
            } catch (error) {
                console.error('Error updating status:', error);
                alert('An error occurred while updating order status.');
                location.reload(); // Reload to revert status if update failed
            }
        }

        // Load orders when page loads
        document.addEventListener('DOMContentLoaded', loadOrders);

        // Auto-refresh orders every 30 seconds
        setInterval(loadOrders, 30000);
    </script>

</body>
</html>
