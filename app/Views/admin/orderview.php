<!-- 1. Include Shared Header (CSS/Theme) -->
<?= $this->include('theme/header') ?>

<!-- 2. Include Shared Sidebar -->
<?= $this->include('theme/sidebar') ?>

<!-- CSRF Protection Metadata -->
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<meta name="csrf-header" content="<?= csrf_header() ?>">
<meta name="csrf-name" content="<?= csrf_token() ?>">

<style>
    .main-content { 
        flex: 1; 
        padding: 40px; 
        overflow-y: auto; 
        scroll-behavior: smooth;
    }

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

    /* Modal Styling */
    .modal-overlay {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.85); backdrop-filter: blur(15px);
        z-index: 1000; justify-content: center; align-items: center;
    }
    .modal-content {
        background: #0f172a; border: 1px solid rgba(255,255,255,0.1);
        border-radius: 32px; padding: 40px; width: 90%; max-width: 750px;
    }
</style>

<main class="main-content">
    <div class="card glass-panel" style="padding: 40px; border-radius: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 20px;">
            <div>
                <h2 style="font-size: 2.2rem; margin-bottom: 0;">Customer Orders 📑 <span class="order-tag">Order</span></h2>
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
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): foreach ($orders as $o): 
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
                        <td><span style="font-weight: 800; color: #4ade80; font-size: 1.1rem;">₱<?= number_format($o['total_amount'], 2) ?></span></td>
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
                                <button onclick="viewOrderDetails(<?= $o['id'] ?>)" class="btn-details" title="View Items">
                                    <i class="fas fa-receipt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" style="text-align: center; color: rgba(255,255,255,0.2); padding: 100px;">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</main>

<!-- Order Details Modal -->
<div id="orderModal" class="modal-overlay">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 id="modalTitle" style="margin: 0; font-weight: 800;">Order Receipt</h2>
            <button onclick="document.getElementById('orderModal').style.display='none'" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        
        <table class="premium-table" style="margin-top: 0;">
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
        
        <div style="text-align: right; margin-top: 30px; font-size: 1.5rem; font-weight: 800;">
            Total: <span id="modalTotal" style="color: #4ade80;">₱0.00</span>
        </div>
    </div>
</div>

<script>
async function viewOrderDetails(orderId) {
    try {
        const response = await fetch(`<?= site_url('admin/orders/show/') ?>${orderId}`);
        const result = await response.json();
        
        if (result.status === 'success') {
            const order = result.data;
            document.getElementById('modalTitle').innerText = `Receipt: ${order.transaction_code}`;
            document.getElementById('modalTotal').innerText = `₱${parseFloat(order.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}`;
            
            const tbody = document.getElementById('modalItems');
            tbody.innerHTML = order.items.map(item => `
                <tr>
                    <td><div style="font-weight:700;">${item.product_name}</div></td>
                    <td>${item.quantity} ${item.unit ?? ''}</td>
                    <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td><span style="font-weight:700;">₱${parseFloat(item.subtotal).toFixed(2)}</span></td>
                </tr>
            `).join('');
            
            document.getElementById('orderModal').style.display = 'flex';
        }
    } catch (error) { console.error(error); }
}

async function updateStatus(orderId, newStatus, element) {
    const btn = element.closest('button') || element;
    const originalHTML = btn.innerHTML;
    
    try {
        if (btn.tagName === 'BUTTON') {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
        }

        const csrfName = document.querySelector('meta[name="csrf-name"]').content;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfHeader = document.querySelector('meta[name="csrf-header"]').content;

        const formData = new FormData();
        formData.append('id', orderId);
        formData.append('status', newStatus);
        formData.append(csrfName, csrfToken);

        const response = await fetch(`<?= site_url('admin/orders/updateStatus') ?>`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            }
        });
        
        const result = await response.json();
        if (result.token) {
            document.querySelectorAll('meta[name="csrf-token"]').forEach(m => m.content = result.token);
        }

        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message || 'Update failed');
            if (btn.tagName === 'BUTTON') {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }
    } catch (error) {
        console.error(error);
        alert('Connection error or session expired.');
    }
}
</script>

<?= $this->include('theme/footer') ?>