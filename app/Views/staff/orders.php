<?= $this->include('theme/header') ?>

    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .page-title {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(to right, #fff, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 14px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        /* Modern Table Styles */
        .table-container {
            padding: 2px;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
            border-radius: 24px;
        }

        .premium-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: -10px;
        }

        .premium-table th {
            padding: 20px;
            text-align: left;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 700;
        }

        .premium-table tbody tr {
            background: rgba(255, 255, 255, 0.03);
            transition: all 0.3s ease;
        }

        .premium-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.07);
            transform: scale(1.002);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .premium-table td {
            padding: 20px;
            vertical-align: middle;
            color: #ffffff;
        }

        .premium-table td:first-child { border-radius: 16px 0 0 16px; }
        .premium-table td:last-child { border-radius: 0 16px 16px 0; }

        /* High-Contrast Status Elements (Black & White) */
        .txn-code {
            font-family: 'Courier New', monospace;
            background: #000000;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .badge-payment {
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            background: #000000;
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .status-select {
            padding: 10px 16px;
            border-radius: 12px;
            background: #000000;
            color: #ffffff;
            border: 2px solid #ffffff;
            font-size: 0.9rem;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .status-select option {
            background: #000000;
            color: #ffffff;
        }

        /* Action Buttons */
        .btn-action {
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 800;
            border: 2px solid #ffffff;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #000000;
            color: #ffffff;
        }
        .btn-action:hover {
            background: #ffffff;
            color: #000000;
            transform: translateY(-2px);
        }

        .btn-view {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            width: 42px; height: 42px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.2);
            display: inline-flex;
            align-items: center; justify-content: center;
            transition: 0.3s;
        }
        .btn-view:hover { background: #ffffff; color: #000000; transform: rotate(15deg); }

        /* Toast Notification */
        .toast {
            position: fixed; bottom: 30px; right: 30px;
            padding: 16px 24px; border-radius: 16px;
            background: #1e1b4b; border: 1px solid rgba(255,255,255,0.1);
            color: #fff; display: flex; align-items: center; gap: 12px;
            transform: translateY(100px); opacity: 0; transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10000; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.success { border-color: #4ade80; }
        .toast.error { border-color: #f87171; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <main class="main-content">
        <div class="header">
            <div>
                <h1 class="page-title">Order Fulfillment</h1>
                <p style="color: rgba(255,255,255,0.5); margin-top: 5px;">Manage and process your fresh seafood deliveries.</p>
            </div>
            <a href="<?= site_url('staff/dashboard') ?>" class="btn btn-back">
                <i class="fas fa-chevron-left"></i> Dashboard
            </a>
        </div>

        <div class="table-container">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Transaction</th>
                        <th>Customer</th>
                        <th>Payment</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
                    <tr><td colspan="6" style="text-align: center; padding: 100px;">
                        <i class="fas fa-circle-notch fa-spin" style="font-size: 2rem; color: #c084fc;"></i>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Order Detail Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 id="modal-txn-title" style="margin: 0;">Order Details</h2>
                <button onclick="closeModal()" style="background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div class="glass-panel" style="padding: 20px;">
                    <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; text-transform: uppercase;">Customer</div>
                    <div id="modal-customer" style="font-weight: 700; font-size: 1.2rem; margin-top: 5px;"></div>
                    <div id="modal-date" style="color: rgba(255,255,255,0.4); font-size: 0.85rem; margin-top: 5px;"></div>
                </div>
                <div class="glass-panel" style="padding: 20px;">
                    <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; text-transform: uppercase;">Total Amount</div>
                    <div id="modal-amount" style="font-weight: 800; font-size: 1.5rem; color: #4ade80; margin-top: 5px;"></div>
                </div>
            </div>

            <table class="premium-table" style="margin-top: 0;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody id="modal-items"></tbody>
            </table>

            <div style="text-align: right; margin-top: 30px;">
                <button class="btn btn-back" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <div id="toast" class="toast">
        <i id="toast-icon" class="fas fa-check-circle"></i>
        <span id="toast-message"></span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', loadOrders);

        async function loadOrders() {
            try {
                const response = await fetch('<?= site_url('staff/getOrders') ?>');
                const orders = await response.json();
                renderOrders(orders);
            } catch (error) {
                showToast('Failed to load orders', 'error');
            }
        }

        function renderOrders(orders) {
            const tbody = document.getElementById('orders-table-body');
            if (!orders || orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 100px; color: rgba(255,255,255,0.3);">No orders found.</td></tr>';
                return;
            }

            tbody.innerHTML = orders.map(o => {
                const status = o.status || 'Pending';
                const payment = o.payment_method || 'COD';
                
                let actionBtn = '';
                if (status === 'Pending') {
                    actionBtn = `<button class="btn-action btn-process" onclick="updateStatus(${o.id}, 'Processing', this)"><i class="fas fa-fire"></i> Process</button>`;
                } else if (status === 'Processing') {
                    actionBtn = `<button class="btn-action btn-ship" onclick="updateStatus(${o.id}, 'Shipped', this)"><i class="fas fa-truck"></i> Ship</button>`;
                } else if (status === 'Shipped') {
                    actionBtn = `<button class="btn-action btn-complete" onclick="updateStatus(${o.id}, 'Completed', this)"><i class="fas fa-check-double"></i> Complete</button>`;
                }

                return `
                    <tr id="order-row-${o.id}">
                        <td><span class="txn-code">${o.transaction_code}</span></td>
                        <td>
                            <div style="font-weight: 700;">${o.customer_name || 'Walk-in'}</div>
                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.4); margin-top: 4px;">
                                ${new Date(o.created_at).toLocaleDateString()} ${new Date(o.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                            </div>
                        </td>
                        <td><span class="badge-payment ${payment.toLowerCase()}">${payment}</span></td>
                        <td><span style="font-weight: 800; color: #4ade80;">₱${parseFloat(o.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</span></td>
                        <td>
                            <select onchange="updateStatus(${o.id}, this.value, this)" class="status-select status-${status.toLowerCase()}">
                                <option value="Pending" ${status==='Pending'?'selected':''}>Pending</option>
                                <option value="Processing" ${status==='Processing'?'selected':''}>Processing</option>
                                <option value="Shipped" ${status==='Shipped'?'selected':''}>Shipped</option>
                                <option value="Completed" ${status==='Completed'?'selected':''}>Completed</option>
                                <option value="Cancelled" ${status==='Cancelled'?'selected':''}>Cancelled</option>
                            </select>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                ${actionBtn}
                                <button class="btn-view" onclick="viewDetails(${o.id})" title="View Details">
                                    <i class="fas fa-receipt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function updateStatus(id, newStatus, element) {
            const btn = element.closest('button') || element;
            const originalHTML = btn.innerHTML;
            
            try {
                // Show loading state
                if (btn.tagName === 'BUTTON') {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
                }

                const csrfName = document.querySelector('meta[name="csrf-name"]').content;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const csrfHeader = document.querySelector('meta[name="csrf-header"]').content;

                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', newStatus);
                formData.append(csrfName, csrfToken);

                const response = await fetch('<?= site_url('staff/updateOrderStatus') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        [csrfHeader]: csrfToken
                    }
                });

                const result = await response.json();
                
                // Always update token for next request
                if (result.token) {
                    document.querySelectorAll('meta[name="csrf-token"]').forEach(m => m.content = result.token);
                }

                if (result.status === 'success') {
                    showToast(result.message, 'success');
                    await loadOrders(); // Full reload to update action buttons
                } else {
                    showToast(result.message || 'Update failed', 'error');
                    if (btn.tagName === 'BUTTON') {
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    }
                }
            } catch (error) {
                console.error(error);
                showToast('Connection error. Check console.', 'error');
                if (btn.tagName === 'BUTTON') {
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            }
        }

        async function viewDetails(id) {
            try {
                const response = await fetch(`<?= site_url('staff/getOrderDetail/') ?>${id}`);
                const result = await response.json();
                
                if (result.status === 'success') {
                    const o = result.data;
                    document.getElementById('modal-txn-title').innerText = `Receipt: ${o.transaction_code}`;
                    document.getElementById('modal-customer').innerText = o.customer_name || 'Walk-in Customer';
                    document.getElementById('modal-date').innerText = new Date(o.created_at).toLocaleString();
                    document.getElementById('modal-amount').innerText = `₱${parseFloat(o.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}`;
                    
                    const itemsBody = document.getElementById('modal-items');
                    itemsBody.innerHTML = o.items.map(item => `
                        <tr>
                            <td><div style="font-weight:600;">${item.product_name}</div></td>
                            <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>${item.quantity} ${item.unit || ''}</td>
                            <td><span style="font-weight:700;">₱${parseFloat(item.subtotal).toFixed(2)}</span></td>
                        </tr>
                    `).join('');

                    document.getElementById('orderModal').classList.add('show');
                } else {
                    showToast(result.message || 'Could not load details', 'error');
                }
            } catch (error) {
                showToast('Could not load details', 'error');
            }
        }

        function closeModal() {
            document.getElementById('orderModal').classList.remove('show');
        }

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            document.getElementById('toast-message').innerText = msg;
            
            toast.className = `toast show ${type}`;
            icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
    </script>
</body>
</html>