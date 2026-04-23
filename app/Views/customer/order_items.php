<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | TALAbahan</title>
   
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   
    <style>
        /* GLOBAL THEME */
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
        }

        .main-content { flex: 1; padding: 40px; overflow-y: auto; }

        .order-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            transition: 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-card:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: #818cf8;
            transform: scale(1.01);
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
        .status-processing { background: rgba(251, 146, 60, 0.1); color: #fb923c; border: 1px solid rgba(251, 146, 60, 0.2); }
        .status-shipped { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2); }
        .status-completed { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-cancelled { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

        .order-info h3 { margin: 0; font-size: 1.2rem; letter-spacing: 1px; }
        .order-info p { margin: 5px 0 0 0; color: rgba(255, 255, 255, 0.5); font-size: 0.9rem; }

        .order-amount { font-size: 1.5rem; font-weight: 800; color: #10b981; }

        /* MODAL STYLES */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            align-items: center;
            justify-content: center;
        }
        .modal.show { display: flex; }
        .modal-content {
            background: rgba(20, 20, 45, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
        }
        .modal-header h2 { margin: 0; font-size: 1.8rem; font-weight: 800; }
        .close-btn { background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer; }

        .item-list { margin-bottom: 25px; }
        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .item-row:last-child { border-bottom: none; }
        .item-name { font-weight: 600; }
        .item-qty { color: rgba(255, 255, 255, 0.5); font-size: 0.9rem; }
        .item-price { font-weight: 700; color: #10b981; }

        .btn-action {
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            border: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-view { background: rgba(129, 140, 248, 0.15); color: #818cf8; border: 1px solid rgba(129, 140, 248, 0.2); }
        .btn-view:hover { background: rgba(129, 140, 248, 0.25); transform: translateY(-2px); }
        .btn-cancel { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.2); }
        .btn-cancel:hover { background: rgba(239, 68, 68, 0.25); transform: translateY(-2px); }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <main class="main-content">
        <div style="margin-bottom: 40px;">
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 10px;">My Orders 📦</h1>
            <p style="color: rgba(255,255,255,0.6);">Track your fresh seafood deliveries and history.</p>
        </div>

        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $o): ?>
                <div class="order-card glass-panel">
                    <div class="order-info">
                        <h3><?= esc($o['transaction_code']) ?></h3>
                        <p><i class="far fa-calendar-alt"></i> <?= date('M d, Y h:i A', strtotime($o['created_at'])) ?></p>
                        <p><i class="fas fa-wallet"></i> <?= esc($o['payment_method']) ?></p>
                    </div>
                    
                    <div style="text-align: right;">
                        <div class="order-amount">₱<?= number_format($o['total_amount'], 2) ?></div>
                        <div style="margin-top: 10px; display: flex; flex-direction: column; gap: 8px; align-items: flex-end;">
                            <?php 
                                $statusClass = 'status-' . strtolower($o['status']);
                            ?>
                            <span class="status-badge <?= $statusClass ?>"><?= esc($o['status']) ?></span>
                            
                            <div style="display: flex; gap: 10px;">
                                <button class="btn-action btn-view" onclick="viewDetails(<?= $o['id'] ?>)">
                                    <i class="fas fa-eye"></i> Details
                                </button>
                                <?php if ($o['status'] === 'Pending'): ?>
                                    <button class="btn-action btn-cancel" onclick="cancelOrder(<?= $o['id'] ?>)">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 100px; opacity: 0.5;">
                <i class="fas fa-box-open" style="font-size: 4rem; margin-bottom: 20px;"></i>
                <h2>No orders yet</h2>
                <p>Start your first seafood order from the dashboard!</p>
                <a href="<?= site_url('customer/dashboard') ?>" class="btn-buy" style="display: inline-flex; width: auto; padding: 15px 30px; margin-top: 20px; text-decoration: none;">
                    Go to Shop
                </a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Order Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Order Details</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div id="modalBody">
                <div class="item-list" id="itemList">
                    <!-- Items will be injected here -->
                </div>
                <div style="text-align: right; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <div style="font-size: 0.9rem; color: rgba(255, 255, 255, 0.5); margin-bottom: 5px;">Total Amount</div>
                    <div id="modalTotal" style="font-size: 1.8rem; font-weight: 800; color: #10b981;">₱0.00</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById('detailsModal').classList.remove('show');
        }

        async function viewDetails(orderId) {
            try {
                const response = await fetch(`<?= site_url('customer/order-details/') ?>${orderId}`);
                const result = await response.json();

                if (result.status === 'success') {
                    const order = result.data;
                    document.getElementById('modalTitle').innerText = `Order ${order.transaction_code}`;
                    document.getElementById('modalTotal').innerText = `₱${parseFloat(order.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}`;

                    const itemList = document.getElementById('itemList');
                    itemList.innerHTML = order.items.map(item => `
                        <div class="item-row">
                            <div>
                                <div class="item-name">${item.product_name}</div>
                                <div class="item-qty">${item.quantity} ${item.unit || ''} @ ₱${parseFloat(item.unit_price).toFixed(2)}</div>
                            </div>
                            <div class="item-price">₱${parseFloat(item.subtotal).toFixed(2)}</div>
                        </div>
                    `).join('');

                    document.getElementById('detailsModal').classList.add('show');
                } else {
                    alert(result.message || 'Failed to load details');
                }
            } catch (error) {
                console.error(error);
                alert('Connection error');
            }
        }

        async function cancelOrder(orderId) {
            if (!confirm('Are you sure you want to cancel this order? This will return items to stock.')) {
                return;
            }

            try {
                const csrfName = '<?= csrf_token() ?>';
                const csrfHash = '<?= csrf_hash() ?>';

                const formData = new FormData();
                formData.append('id', orderId);
                formData.append(csrfName, csrfHash);

                const response = await fetch('<?= site_url('customer/cancel-order') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                if (result.status === 'success') {
                    alert(result.message);
                    location.reload();
                } else {
                    alert(result.message || 'Failed to cancel order');
                }
            } catch (error) {
                console.error(error);
                alert('Connection error');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
