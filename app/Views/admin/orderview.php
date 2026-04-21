<!-- 1. Include Shared Header (CSS/Theme) -->
<?= $this->include('theme/header') ?>

<!-- 2. Include Shared Sidebar -->
<?= $this->include('theme/sidebar') ?>

<!-- 3. Main Content Wrapper -->
<main class="main-content">
    
    <div class="card glass-panel">
        <h2 style="font-size: 2.2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">Customer Orders 📑</h2>
        <p style="color: rgba(255,255,255,0.6); margin-top: 15px; margin-bottom: 30px;">View and track all customer transactions and fulfillment status.</p>

        <div class="table-responsive glass-panel">
            <table class="premium-table">
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
                    <?php if (!empty($orders)): foreach ($orders as $o): ?>
                        <tr>
                            <td><strong style="color: #818cf8;"><?= esc($o['transaction_code']) ?></strong></td>
                            <td><?= date('M d, Y h:i A', strtotime($o['created_at'])) ?></td>
                            <td><strong style="color: #fff;"><?= esc($o['customer_name']) ?: 'Walk-in Customer' ?></strong></td>
                            <td><?= esc($o['item_count']) ?> items</td>
                            <td>₱<?= number_format($o['total_amount'], 2) ?></td>
                            <td>
                                <select onchange="updateStatus(<?= $o['id'] ?>, this.value)" style="padding: 5px; border-radius: 8px; background: rgba(0,0,0,0.3); color: white; border: 1px solid rgba(255,255,255,0.2);">
                                    <option value="Pending" <?= $o['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Completed" <?= $o['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </td>
                            <td class="action-cell">
                                <button onclick="viewOrderDetails(<?= $o['id'] ?>)" class="btn-edit" style="background: rgba(56, 189, 248, 0.2); color: #7dd3fc; border-color: rgba(56, 189, 248, 0.3);">👁️ View Details</button>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="7" style="text-align: center; color: #777; padding: 40px;">No orders recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Order Details Modal -->
<div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center;">
    <div class="glass-panel" style="background: #1e1b4b; padding: 30px; border-radius: 20px; max-width: 600px; width: 90%;">
        <h3 id="modalTitle" style="margin-top: 0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">Order Details</h3>
        
        <table class="premium-table" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody id="modalItems">
                <!-- Items injected here via JS -->
            </tbody>
        </table>
        
        <div style="text-align: right; margin-top: 20px; font-size: 1.2rem;">
            <strong>Total: <span id="modalTotal" style="color: #10b981;">₱0.00</span></strong>
        </div>

        <div style="text-align: right; margin-top: 30px;">
            <button onclick="document.getElementById('orderModal').style.display='none'" class="btn-primary" style="padding: 10px 20px; height: auto;">Close Modal</button>
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
            document.getElementById('modalTotal').innerText = `₱${parseFloat(order.total_amount).toFixed(2)}`;
            
            const tbody = document.getElementById('modalItems');
            tbody.innerHTML = '';
            
            if (!order.items || order.items.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:#aaa;">No line items found for this order.</td></tr>`;
            } else {
                order.items.forEach(item => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.quantity} ${item.unit ?? ''}</td>
                            <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>₱${parseFloat(item.subtotal).toFixed(2)}</td>
                        </tr>
                    `;
                });
            }
            
            document.getElementById('orderModal').style.display = 'flex';
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error(error);
        alert('Failed to load order details.');
    }
}

async function updateStatus(orderId, newStatus) {
    try {
        const formData = new FormData();
        formData.append('id', orderId);
        formData.append('status', newStatus);

        const response = await fetch(`<?= site_url('admin/orders/updateStatus') ?>`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.status !== 'success') {
            alert(result.message);
        }
    } catch (error) {
        console.error(error);
        alert('Failed to update status.');
    }
}
</script>

<!-- 4. Include Shared Footer -->
<?= $this->include('theme/footer') ?>