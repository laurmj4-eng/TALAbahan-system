<?php
/**
 * @var array $orders
 */
?>
<style>
    .order-tag {
        background: rgba(168, 85, 247, 0.2);
        color: #a855f7;
        padding: 6px 14px;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: 1px solid rgba(168, 85, 247, 0.3);
        margin-left: 15px;
        vertical-align: middle;
        display: inline-block;
    }

    /* High-Contrast Status Elements (Black & White) */
    .txn-pill {
        font-family: 'JetBrains Mono', monospace;
        background: #000000;
        color: #ffffff;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.8rem;
        border: 1px solid rgba(255,255,255,0.2);
    }

    /* Responsive adjustments for Order Card */
    #order-card-container {
        padding: 40px; 
        border-radius: 30px;
    }

    @media (max-width: 768px) {
        #order-card-container {
            padding: 20px;
            border-radius: 20px;
        }
        .order-view-title {
            font-size: 1.6rem !important;
        }
        .order-tag {
            display: block;
            margin: 10px 0 0 0;
            width: fit-content;
        }
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
        border-radius: 14px;
        background: #000000;
        color: #ffffff;
        border: 2px solid #ffffff;
        font-size: 0.9rem;
        font-weight: 800;
        transition: 0.3s;
        cursor: pointer;
    }
    
    .status-select option {
        background: #000000;
        color: #ffffff;
    }

    /* Action Buttons */
    .btn-action {
        padding: 10px 18px;
        border-radius: 14px;
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

    .btn-details {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        width: 42px; height: 42px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,0.2);
        display: inline-flex;
        align-items: center; justify-content: center;
        transition: 0.3s;
    }
    .btn-details:hover { background: #ffffff; color: #000000; transform: scale(1.1); }
</style>

<div class="card glass-panel" id="order-card-container">
    <div class="flex-header" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 20px;">
        <div>
            <h2 class="order-view-title">Customer Orders &#128209; <span class="order-tag">Order</span></h2>
            <p style="color: rgba(255,255,255,0.6); margin-top: 10px; margin-bottom: 0;">Monitor and oversee seafood fulfillment operations.</p>
        </div>
    </div>

    <div class="table-responsive glass-panel" style="padding: 20px; border-radius: 15px;">
        <table class="premium-table">
        <thead>
            <tr>
                <th>Order Info</th>
                <th>Customer</th>
                <th>Payment</th>
                <th>Tracking</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $o): 
                    $status = $o['status'] ?? 'Pending';
                    $actionBtn = '';
                    if ($status === 'Pending') {
                        $actionBtn = '<button class="btn-action btn-process" onclick="updateStatus('.$o['id'].', \'Processing\', this.closest(\'tr\').querySelector(\'select\'))"><i class="fas fa-check"></i> Process</button>';
                    } else if ($status === 'Processing') {
                        $actionBtn = '<button class="btn-action btn-ship" onclick="updateStatus('.$o['id'].', \'Shipped\', this.closest(\'tr\').querySelector(\'select\'))"><i class="fas fa-truck"></i> Ship</button>';
                    } else if ($status === 'Shipped') {
                        $actionBtn = '<button class="btn-action btn-approve" onclick="updateStatus('.$o['id'].', \'Completed\', this.closest(\'tr\').querySelector(\'select\'))"><i class="fas fa-check-double"></i> Complete</button>';
                    }
                ?>
                    <tr>
                        <td>
                            <div class="txn-pill"><?= esc($o['transaction_code']) ?></div>
                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.3); margin-top: 8px;">
                                <?= date('M d, Y • h:i A', strtotime($o['created_at'])) ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #fff;"><?= esc($o['customer_name']) ?: 'Walk-in' ?></div>
                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.4);"><?= $o['item_count'] ?> items</div>
                        </td>
                        <td>
                            <span class="badge-payment <?= strtolower($o['payment_method']) ?>">
                                <?= esc($o['payment_method']) ?: 'COD' ?>
                            </span>
                        </td>
                        <td>
                            <div style="font-size:0.8rem; color:rgba(255,255,255,0.7);">
                                <?= esc($o['courier_name'] ?? '-') ?>
                            </div>
                            <div style="font-size:0.75rem; color:rgba(255,255,255,0.45);">
                                <?= esc($o['tracking_number'] ?? '-') ?>
                            </div>
                        </td>
                        <td><span style="font-weight: 800; color: #4ade80; font-size: 1.1rem;">&#8369;<?= number_format($o['total_amount'], 2) ?></span></td>
                        <td>
                            <select onchange="updateStatus(<?= $o['id'] ?>, this.value, this)" class="status-select <?= strtolower($o['status']) ?>">
                                <option value="Pending" <?= $o['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Processing" <?= $o['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="Shipped" <?= $o['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="Completed" <?= $o['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Cancelled" <?= $o['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </td>
                        <td>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <?= $actionBtn ?>
                                <button class="btn-details" onclick="editTracking(<?= $o['id'] ?>)" title="Update Tracking">
                                    <i class="fas fa-truck"></i>
                                </button>
                                <button onclick="viewOrderDetails(<?= $o['id'] ?>)" class="btn-details" title="View Items">
                                    <i class="fas fa-receipt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center; color: rgba(255,255,255,0.2); padding: 100px;">No orders found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <button class="modal-close-btn" onclick="closeModal('orderModal')">&times;</button>
        <div id="modalTitle" class="modal-header">Order Receipt</div>
        
        <div class="table-responsive glass-panel">
            <table class="premium-table" style="margin-top: 0; min-width: auto;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody id="modalItems"></tbody>
            </table>
        </div>
        
        <div style="text-align: right; margin-top: 30px; font-size: 1.5rem; font-weight: 800;">
            Total: <span id="modalTotal" style="color: #4ade80;">&#8369;0.00</span>
        </div>
    </div>
</div>

<script>
async function viewOrderDetails(id) {
    try {
        const response = await fetch(`<?= site_url('admin/orders/show/') ?>${id}`);
        const result = await response.json();
        
        if (result.status === 'success') {
            const order = result.data;
            document.getElementById('modalTitle').innerText = 'Receipt: ' + order.transaction_code;
            
            const tbody = document.getElementById('modalItems');
            tbody.innerHTML = '';
            
            order.items.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td>${item.product_name}</td>
                        <td>${item.quantity}</td>
                        <td>&#8369;${parseFloat(item.unit_price).toFixed(2)}</td>
                        <td>&#8369;${parseFloat(item.subtotal).toFixed(2)}</td>
                    </tr>
                `;
            });
            
            document.getElementById('modalTotal').innerText = '&#8369;' + parseFloat(order.total_amount).toFixed(2);
            document.getElementById('orderModal').classList.add('show');
        } else {
            alert(result.message);
        }
    } catch (e) {
        console.error(e);
        alert('Failed to load order details');
    }
}

async function updateStatus(id, status, element) {
    const originalStatus = element.value;
    
    if (!confirm(`Update order to ${status}?`)) {
        element.value = originalStatus;
        return;
    }

    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const response = await fetch('<?= site_url('admin/orders/updateStatus') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
            element.value = originalStatus;
        }
    } catch (e) {
        console.error(e);
        alert('Failed to update status');
        element.value = originalStatus;
    }
}

async function editTracking(id) {
    const courier = prompt('Courier name (e.g. J&T, LBC):');
    if (courier === null) return;
    const tracking = prompt('Tracking number:');
    if (tracking === null) return;

    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('courier_name', courier);
        formData.append('tracking_number', tracking);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const response = await fetch('<?= site_url('admin/orders/updateTracking') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const result = await response.json();
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message || 'Failed to update tracking');
        }
    } catch (error) {
        console.error(error);
        alert('Failed to update tracking');
    }
}
</script>
