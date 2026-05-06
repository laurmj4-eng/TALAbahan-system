<style>
    .pos-container { display: flex; gap: 25px; align-items: flex-start; }
    .pos-products { flex: 2; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
    .pos-item { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 20px; text-align: center; cursor: pointer; transition: 0.3s; backdrop-filter: blur(10px); }
    .pos-item:hover { transform: translateY(-5px); background: rgba(255, 255, 255, 0.1); border-color: #818cf8; }
    .pos-item .icon { font-size: 3rem; margin-bottom: 10px; display: block; }
    .pos-item .name { font-weight: 600; margin-bottom: 5px; color: #fff; }
    .pos-item .price { color: #a7f3d0; font-weight: 700; font-size: 1.1rem; }
    
    .pos-cart { flex: 1; background: rgba(0,0,0,0.3); border-radius: 24px; padding: 25px; min-width: 300px; position: sticky; top: 0; border: 1px solid rgba(255,255,255,0.1); }
    .cart-header { font-size: 1.2rem; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; color: white;}
    .cart-items { min-height: 200px; max-height: 400px; overflow-y: auto; margin-bottom: 20px; }
    .cart-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed rgba(255,255,255,0.1); }
    .cart-row-details { flex: 1; }
    .cart-row-title { font-weight: 600; font-size: 0.9rem; color: white; }
    .cart-row-price { color: #a7f3d0; font-size: 0.85rem; }
    .cart-qty-controls { display: flex; align-items: center; gap: 10px; color: white; }
    .qty-btn { background: rgba(255,255,255,0.1); border: none; color: white; width: 25px; height: 25px; border-radius: 5px; cursor: pointer; transition: 0.2s;}
    .qty-btn:hover { background: #818cf8; }
    
    .cart-summary { border-top: 2px solid rgba(255,255,255,0.1); padding-top: 15px; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; color: rgba(255,255,255,0.7); }
    .summary-total { display: flex; justify-content: space-between; margin-top: 15px; font-size: 1.5rem; font-weight: 700; color: #fff; }
    
    .voucher-section { margin-top: 15px; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 15px; }
    .voucher-input-group { display: flex; gap: 8px; }
    .voucher-input { flex: 1; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 12px; color: white; font-size: 0.9rem; }
    .btn-apply-voucher { background: #818cf8; color: white; border: none; border-radius: 8px; padding: 8px 15px; cursor: pointer; font-size: 0.85rem; font-weight: 600; }
    .voucher-status { font-size: 0.75rem; margin-top: 5px; min-height: 1rem; }
    .status-success { color: #4ade80; }
    .status-error { color: #f87171; }

    /* Receipt Modal */
    .receipt-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
    .receipt-content { background: white; color: #1a1a1a; width: 380px; max-width: 90%; padding: 30px; border-radius: 4px; font-family: 'Courier New', Courier, monospace; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
    .receipt-header { text-align: center; border-bottom: 2px dashed #eee; padding-bottom: 20px; margin-bottom: 20px; }
    .receipt-title { font-size: 1.5rem; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
    .receipt-body { margin-bottom: 20px; }
    .receipt-item { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; }
    .receipt-divider { border-top: 1px dashed #eee; margin: 15px 0; }
    .receipt-footer { text-align: center; font-size: 0.8rem; color: #666; margin-top: 20px; border-top: 2px dashed #eee; padding-top: 20px; }
    .btn-receipt-close { width: 100%; padding: 12px; background: #1a1a1a; color: white; border: none; border-radius: 4px; margin-top: 20px; cursor: pointer; font-weight: 700; text-transform: uppercase; }

    .customer-selection { margin-bottom: 15px; }
    .customer-input-group { display: flex; gap: 8px; }
    .customer-select { flex: 1; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 12px; color: white; font-size: 0.9rem; }

    .btn-checkout { width: 100%; padding: 18px; margin-top: 20px; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 16px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4); }
    .btn-checkout:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(16, 185, 129, 0.6); }

    /* RESPONSIVE POS */
    @media (max-width: 1024px) {
        .pos-container { flex-direction: column; }
        .pos-cart { position: relative; width: 100%; min-width: 0; }
        .pos-products { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
    }
    
    @media (max-width: 480px) {
        .pos-products { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; }
        .pos-item { padding: 15px; }
        .pos-item .icon { font-size: 2.5rem; }
        .pos-item .name { font-size: 0.9rem; }
        .pos-cart { 
            padding: 20px; 
            margin-bottom: 80px; /* Space for potentially fixed footer if added */
        }
        .summary-total { font-size: 1.2rem; }
        .btn-checkout { padding: 15px; font-size: 1rem; }
    }
</style>

<h2 style="font-size: 2.2rem; margin-bottom: 20px; color: white;">TALAbahan Terminal</h2>
<div class="pos-container">
    <div class="pos-products" id="product-grid"></div>
    <div class="pos-cart glass-panel">
        <div class="cart-header">Current Order</div>
        
        <div class="customer-selection">
            <div class="customer-input-group">
                <select id="customer-select" class="customer-select">
                    <option value="Walk-in Customer">Walk-in Customer</option>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <?php if ($user['role'] === 'customer'): ?>
                                <option value="<?= esc($user['username']) ?>"><?= esc($user['username']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="cart-items" id="cart-items">
            <div style="color: rgba(255,255,255,0.3); text-align: center; margin-top: 50px;">Select items to add to cart</div>
        </div>
        <div class="cart-summary">
            <div class="summary-row"><span>Subtotal</span> <span id="cart-subtotal">₱0.00</span></div>
            <div class="summary-row" id="discount-row" style="display: none;"><span>Discount</span> <span id="cart-discount" class="color-success">-₱0.00</span></div>
            <div class="summary-row"><span>Tax (12%)</span> <span id="cart-tax">₱0.00</span></div>
            <div class="summary-total"><span>Total</span> <span id="cart-total">₱0.00</span></div>
            
            <div class="voucher-section">
                <div class="voucher-input-group">
                    <input type="text" id="voucher-code" class="voucher-input" placeholder="Voucher Code">
                    <button class="btn-apply-voucher" onclick="applyVoucher()">APPLY</button>
                </div>
                <div id="voucher-status" class="voucher-status"></div>
            </div>

            <button class="btn-checkout" onclick="processCheckout()">PROCESS PAYMENT</button>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receipt-modal" class="receipt-modal">
    <div class="receipt-content">
        <div class="receipt-header">
            <h1 class="receipt-title">TALAbahan</h1>
            <div style="font-size: 0.8rem; margin-top: 5px;">Seafood & Grill Terminal</div>
            <div id="receipt-date" style="font-size: 0.75rem; margin-top: 5px;"></div>
        </div>
        <div class="receipt-body">
            <div id="receipt-items"></div>
            <div class="receipt-divider"></div>
            <div class="receipt-item" style="font-weight: 700;">
                <span>TOTAL</span>
                <span id="receipt-total"></span>
            </div>
            <div id="receipt-discount-row" style="display: none; font-size: 0.8rem; color: #666;">
                <span>DISCOUNT</span>
                <span id="receipt-discount"></span>
            </div>
            <div class="receipt-divider"></div>
            <div class="receipt-item" style="font-size: 0.8rem;">
                <span>TRANSACTION</span>
                <span id="receipt-txn" style="font-size: 0.7rem;"></span>
            </div>
        </div>
        <div class="receipt-footer">
            <div>THANK YOU FOR DINING!</div>
            <div style="margin-top: 5px;">Visit us again soon 🌊</div>
        </div>
        <button class="btn-receipt-close" onclick="closeReceipt()">Close & New Order</button>
    </div>
</div>

<script>
    let cart = [];
    let products =[];
    let appliedVoucherCode = '';

    async function loadProducts() {
        try {
            // UPDATED: Points to the Admin PosController getProducts method
            const response = await fetch('<?= site_url('admin/getProducts') ?>');
            products = await response.json();
            renderProducts();
        } catch (e) {
            products =[
                { id: 1, name: "Baked Talaba", price: 250, icon: "🦪" },
                { id: 2, name: "Garlic Butter Crab", price: 850, icon: "🦀" },
                { id: 3, name: "Spicy Cajun Shrimp", price: 450, icon: "🦐" },
                { id: 4, name: "Grilled Squid", price: 320, icon: "🦑" }
            ];
            renderProducts();
        }
    }

    function renderProducts() {
        const grid = document.getElementById('product-grid');
        grid.innerHTML = '';
        products.forEach(p => {
            grid.innerHTML += `<div class="pos-item" onclick="addToCart(${p.id})">
                <span class="icon">${p.icon}</span><div class="name">${p.name}</div><div class="price">₱${parseFloat(p.price).toFixed(2)}</div>
            </div>`;
        });
    }

    function addToCart(productId) {
        const product = products.find(p => p.id === productId);
        const existingItem = cart.find(item => item.id === productId);
        if (existingItem) existingItem.qty += 1;
        else cart.push({ ...product, qty: 1 });
        updateCartUI();
    }

    function changeQty(productId, delta) {
        const item = cart.find(i => i.id === productId);
        if(item) {
            item.qty += delta;
            if(item.qty <= 0) cart = cart.filter(i => i.id !== productId);
        }
        updateCartUI();
    }

    function updateCartUI() {
        const cartDiv = document.getElementById('cart-items');
        if (cart.length === 0) {
            cartDiv.innerHTML = '<div style="color: rgba(255,255,255,0.3); text-align: center; margin-top: 50px;">Select items to add to cart</div>';
            document.getElementById('cart-subtotal').innerText = '₱0.00';
            document.getElementById('cart-tax').innerText = '₱0.00';
            document.getElementById('cart-total').innerText = '₱0.00';
            document.getElementById('discount-row').style.display = 'none';
            appliedVoucherCode = '';
            document.getElementById('voucher-code').value = '';
            document.getElementById('voucher-status').innerHTML = '';
            return;
        }

        cartDiv.innerHTML = '';
        let subtotal = 0;
        cart.forEach(item => {
            subtotal += item.price * item.qty;
            cartDiv.innerHTML += `
                <div class="cart-row">
                    <div class="cart-row-details">
                        <div class="cart-row-title">${item.icon} ${item.name}</div><div class="cart-row-price">₱${parseFloat(item.price).toFixed(2)}</div>
                    </div>
                    <div class="cart-qty-controls">
                        <button class="qty-btn" onclick="changeQty(${item.id}, -1)">-</button><span>${item.qty}</span><button class="qty-btn" onclick="changeQty(${item.id}, 1)">+</button>
                    </div>
                </div>`;
        });

        // We'll calculate a local estimate for UI, but final calculation happens in Backend
        const tax = subtotal * 0.12;
        let total = subtotal + tax;

        document.getElementById('cart-subtotal').innerText = `₱${subtotal.toFixed(2)}`;
        document.getElementById('cart-tax').innerText = `₱${tax.toFixed(2)}`;
        document.getElementById('cart-total').innerText = `₱${total.toFixed(2)}`;
    }

    function applyVoucher() {
        const code = document.getElementById('voucher-code').value.trim();
        const status = document.getElementById('voucher-status');
        
        if (cart.length === 0) {
            status.innerHTML = '<span class="status-error">Add items first</span>';
            return;
        }
        if (!code) {
            status.innerHTML = '<span class="status-error">Enter a code</span>';
            return;
        }

        // In a full implementation, we'd verify the voucher via API here.
        // For now, we'll mark it to be sent during checkout.
        appliedVoucherCode = code;
        status.innerHTML = `<span class="status-success">Code "${code}" applied</span>`;
    }

    async function processCheckout() {
        if(cart.length === 0) return alert("Cart is empty!");
        
        const customerName = document.getElementById('customer-select').value;
        
        try {
            const response = await fetch('<?= site_url('admin/checkout') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    items: cart, 
                    voucher_code: appliedVoucherCode,
                    customer_name: customerName
                })
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                showReceipt(result.data);
                cart = [];
                appliedVoucherCode = '';
                updateCartUI();
            } else {
                alert(result.message || "Checkout failed");
            }
        } catch (err) {
            console.error(err);
            alert("Checkout error. Check console.");
        }
    }

    function showReceipt(data) {
        document.getElementById('receipt-date').innerText = data.date;
        document.getElementById('receipt-txn').innerText = data.transaction_code;
        document.getElementById('receipt-total').innerText = `₱${parseFloat(data.total_amount).toFixed(2)}`;
        
        if (data.discount > 0) {
            document.getElementById('receipt-discount').innerText = `-₱${parseFloat(data.discount).toFixed(2)}`;
            document.getElementById('receipt-discount-row').style.display = 'flex';
        } else {
            document.getElementById('receipt-discount-row').style.display = 'none';
        }

        const itemsDiv = document.getElementById('receipt-items');
        itemsDiv.innerHTML = '';
        data.items.forEach(item => {
            itemsDiv.innerHTML += `
                <div class="receipt-item">
                    <span>${item.quantity}x ${item.product_name}</span>
                    <span>₱${parseFloat(item.subtotal).toFixed(2)}</span>
                </div>`;
        });

        document.getElementById('receipt-modal').style.display = 'flex';
    }

    function closeReceipt() {
        document.getElementById('receipt-modal').style.display = 'none';
    }
</script>