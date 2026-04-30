<?= $this->include('theme/header') ?>

    <style>
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
            gap: 16px;
            overflow: hidden;
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

        .order-info {
            min-width: 0;
            flex: 1 1 auto;
        }
        .order-info h3 {
            margin: 0;
            font-size: 1.2rem;
            letter-spacing: 1px;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .order-info p { margin: 5px 0 0 0; color: rgba(255, 255, 255, 0.5); font-size: 0.9rem; }

        .order-amount { font-size: 1.5rem; font-weight: 800; color: #10b981; }
        .order-right { text-align: right; min-width: 140px; }
        .order-actions-wrap {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: flex-end;
        }
        .order-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

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
        .btn-pay { background: rgba(59,130,246,0.2); color: #93c5fd; border: 1px solid rgba(59,130,246,0.35); }
        .btn-pay:hover { background: rgba(59,130,246,0.3); transform: translateY(-2px); }
        .btn-track { background: rgba(14,165,233,0.2); color: #7dd3fc; border: 1px solid rgba(14,165,233,0.35); }
        .btn-track:hover { background: rgba(14,165,233,0.3); transform: translateY(-2px); }
        .btn-review { background: rgba(34,197,94,0.2); color: #86efac; border: 1px solid rgba(34,197,94,0.35); }
        .btn-refund { background: rgba(245,158,11,0.2); color: #fcd34d; border: 1px solid rgba(245,158,11,0.35); }
        .modal-stage { margin-bottom: 12px; color: rgba(255,255,255,0.75); font-size: 0.9rem; }
        .modal-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }

        @media (max-width: 768px) {
            .order-card {
                flex-direction: column;
                align-items: stretch;
                padding: 18px;
                border-radius: 16px;
            }
            .order-card:hover {
                transform: none;
            }
            .order-right {
                text-align: left;
                min-width: 0;
                width: 100%;
            }
            .order-amount {
                font-size: 1.35rem;
            }
            .order-actions-wrap {
                align-items: stretch;
            }
            .order-actions {
                width: 100%;
                justify-content: stretch;
            }
            .order-actions .btn-action {
                flex: 1 1 0;
                min-width: 0;
                justify-content: center;
                padding: 10px 12px;
            }
            .status-badge {
                width: fit-content;
            }
        }

        @media (max-width: 420px) {
            .order-actions {
                flex-direction: column;
            }
            .order-actions .btn-action {
                width: 100%;
            }
            .main-content h1 {
                font-size: 2rem !important;
            }
        }
    </style>
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
                    
                    <div class="order-right">
                        <div class="order-amount">₱<?= number_format($o['total_amount'], 2) ?></div>
                        <div class="order-actions-wrap">
                            <?php 
                                $statusClass = 'status-' . strtolower($o['status']);
                            ?>
                            <span class="status-badge <?= $statusClass ?>"><?= esc($o['status']) ?></span>
                            
                            <div class="order-actions">
                                <button class="btn-action btn-view" onclick="viewDetails(<?= $o['id'] ?>)">
                                    <i class="fas fa-eye"></i> Details
                                </button>
                                <?php if ($o['status'] === 'Processing'): ?>
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
            <button class="modal-close-btn" onclick="closeModal()">&times;</button>
            <div class="modal-header" id="modalTitle">Order Details</div>
            <div id="modalBody">
                <div class="modal-stage" id="modalStage"></div>
                <div class="item-list" id="itemList">
                    <!-- Items will be injected here -->
                </div>
                <div id="trackingBox" style="display:none; padding: 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; margin-bottom: 12px;">
                    <div><strong>Courier:</strong> <span id="trackingCourier">-</span></div>
                    <div><strong>Tracking #:</strong> <span id="trackingNumber">-</span></div>
                </div>
                <div id="trackingEvents" style="display:none; font-size:0.85rem; color:rgba(255,255,255,0.75); margin-bottom:10px;"></div>
                <div class="modal-actions" id="modalActions"></div>
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

        function lifecycleLabel(key) {
            const map = {
                to_pay: 'To Pay',
                to_ship: 'To Ship (Remorse Window)',
                in_transit: 'To Receive / In Transit',
                completed: 'Completed',
                closed: 'Closed'
            };
            return map[key] || 'Order';
        }

        async function viewDetails(orderId) {
            try {
                const response = await fetch(`<?= site_url('customer/order-details/') ?>${orderId}`);
                const result = await response.json();

                if (result.status === 'success') {
                    const order = result.data;
                    const lifecycle = order.lifecycle || { actions: {} };
                    document.getElementById('modalTitle').innerText = `Order ${order.transaction_code}`;
                    document.getElementById('modalTotal').innerText = `₱${parseFloat(order.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}`;
                    document.getElementById('modalStage').innerText = `Stage: ${lifecycleLabel(lifecycle.stage_key)} | Status: ${order.status}`;

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

                    const actions = document.getElementById('modalActions');
                    actions.innerHTML = '';
                    if (lifecycle.actions?.can_pay_now) {
                        actions.innerHTML += `<button class="btn-action btn-pay" onclick="payNow(${order.id})"><i class="fas fa-credit-card"></i> Pay Now</button>`;
                    }
                    if (lifecycle.actions?.can_cancel) {
                        actions.innerHTML += `<button class="btn-action btn-cancel" onclick="cancelOrder(${order.id})"><i class="fas fa-times"></i> Cancel Order</button>`;
                    }
                    if (lifecycle.actions?.can_track) {
                        actions.innerHTML += `<button class="btn-action btn-track" onclick="trackOrder(${order.id})"><i class="fas fa-truck"></i> Track</button>`;
                    }
                    if (lifecycle.actions?.can_review) {
                        actions.innerHTML += `<button class="btn-action btn-review" onclick="writeReview(${order.id})"><i class="fas fa-star"></i> Write Review</button>`;
                    }
                    if (lifecycle.actions?.can_refund_request) {
                        actions.innerHTML += `<button class="btn-action btn-refund" onclick="requestRefund(${order.id})"><i class="fas fa-undo"></i> Return/Refund</button>`;
                    }

                    document.getElementById('trackingBox').style.display = 'none';
                    document.getElementById('trackingEvents').style.display = 'none';
                    document.getElementById('detailsModal').classList.add('show');
                } else {
                    alert(result.message || 'Failed to load details');
                }
            } catch (error) {
                console.error(error);
                alert('Connection error');
            }
        }

        async function payNow(orderId) {
            const formData = new FormData();
            formData.append('id', orderId);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            const response = await fetch('<?= site_url('customer/pay-now') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const result = await response.json();
            alert(result.message || 'Request finished.');
            if (result.status === 'success') location.reload();
        }

        async function trackOrder(orderId) {
            const response = await fetch(`<?= site_url('customer/tracking/') ?>${orderId}`);
            const result = await response.json();
            if (result.status !== 'success') {
                alert(result.message || 'Tracking unavailable.');
                return;
            }

            const data = result.data;
            document.getElementById('trackingCourier').innerText = data.courier_name || '-';
            document.getElementById('trackingNumber').innerText = data.tracking_number || '-';
            document.getElementById('trackingBox').style.display = 'block';

            const events = data.events || [];
            const eventBox = document.getElementById('trackingEvents');
            if (events.length === 0) {
                eventBox.innerText = 'No live tracking updates yet.';
            } else {
                eventBox.innerHTML = events.map(e => `• ${e.label} (${e.at})`).join('<br>');
            }
            eventBox.style.display = 'block';
        }

        async function writeReview(orderId) {
            const rating = prompt('Rate your order from 1 to 5 stars:', '5');
            if (!rating) return;
            const comment = prompt('Optional review comment:', '') || '';
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('rating', rating);
            formData.append('comment', comment);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            const response = await fetch('<?= site_url('customer/review') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const result = await response.json();
            alert(result.message || 'Request finished.');
            if (result.status === 'success') location.reload();
        }

        async function requestRefund(orderId) {
            const reason = prompt('Please enter your refund reason:');
            if (!reason) return;
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('reason', reason);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            const response = await fetch('<?= site_url('customer/refund-request') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const result = await response.json();
            alert(result.message || 'Request finished.');
            if (result.status === 'success') location.reload();
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
