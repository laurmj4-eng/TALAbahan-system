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
    
    .btn-checkout { width: 100%; padding: 18px; margin-top: 20px; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 16px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4); }
    .btn-checkout:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(16, 185, 129, 0.6); }
</style>

<h2 style="font-size: 2.2rem; margin-bottom: 20px; color: white;">TALAbahan Terminal</h2>
<div class="pos-container">
    <div class="pos-products" id="product-grid"></div>
    <div class="pos-cart glass-panel">
        <div class="cart-header">Current Order</div>
        <div class="cart-items" id="cart-items">
            <div style="color: rgba(255,255,255,0.3); text-align: center; margin-top: 50px;">Select items to add to cart</div>
        </div>
        <div class="cart-summary">
            <div class="summary-row"><span>Subtotal</span> <span id="cart-subtotal">₱0.00</span></div>
            <div class="summary-row"><span>Tax (12%)</span> <span id="cart-tax">₱0.00</span></div>
            <div class="summary-total"><span>Total</span> <span id="cart-total">₱0.00</span></div>
            <button class="btn-checkout" onclick="processCheckout()">PROCESS PAYMENT</button>
        </div>
    </div>
</div>

<script>
    let cart = [];
    let products =[];

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

        const tax = subtotal * 0.12;
        document.getElementById('cart-subtotal').innerText = `₱${subtotal.toFixed(2)}`;
        document.getElementById('cart-tax').innerText = `₱${tax.toFixed(2)}`;
        document.getElementById('cart-total').innerText = `₱${(subtotal + tax).toFixed(2)}`;
    }

    async function processCheckout() {
        if(cart.length === 0) return alert("Cart is empty!");
        const totalText = document.getElementById('cart-total').innerText.replace('₱', '');
        try {
            // UPDATED: Points to the Admin PosController checkout method
            const res = await fetch('<?= site_url('admin/checkout') ?>', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ items: cart, total: parseFloat(totalText) })
            });
            const data = await res.json();
            alert(data.message); cart =[]; updateCartUI();
        } catch (err) {
            alert("Payment processed! (Offline Mode)"); cart =[]; updateCartUI();
        }
    }
</script>