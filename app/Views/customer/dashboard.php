<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | Customer Dashboard</title>
   
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

        /* --- ENHANCED WELCOME BANNER --- */
        .welcome-card { 
            border-radius: 30px; 
            padding: 40px; 
            margin-bottom: 30px; 
            position: relative; 
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
        }
        
        .welcome-card::before {
            content: ""; position: absolute; top: 0; left: -50%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent);
            transform: skewX(-25deg); animation: shine 6s infinite;
        }
        @keyframes shine { 0% { left: -50%; } 100% { left: 150%; } }

        .welcome-text h1 { 
            margin: 0; 
            font-weight: 800; 
            font-size: 2.5rem; 
            letter-spacing: -1px;
            line-height: 1.1;
        }
        
        .name-gradient {
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-text p { 
            color: rgba(255,255,255,0.7); 
            font-size: 1.1rem; 
            margin-top: 10px;
            max-width: 600px;
            line-height: 1.6;
        }

        .welcome-icon {
            font-size: 6rem; 
            opacity: 0.1; 
            transform: rotate(10deg); 
            user-select: none;
            color: #c084fc;
            position: absolute;
            right: 40px;
            top: 50%;
            transform: translateY(-50%) rotate(10deg);
        }

        /* --- PRODUCT GRID (TikTok/Shopee Style) --- */
        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
            gap: 20px; 
        }

        .product-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.06);
            border-color: #818cf8;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .product-image-container {
            width: 100%;
            aspect-ratio: 1/1;
            overflow: hidden;
            position: relative;
        }

        .product-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .product-card:hover .product-image-container img {
            transform: scale(1.1);
        }

        .product-info {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            margin: 0 0 8px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.8rem;
        }

        .product-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .product-price {
            font-size: 1.4rem;
            font-weight: 800;
            color: #10b981;
        }

        .product-unit {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.4);
        }

        .btn-buy {
            width: 100%;
            margin-top: 15px;
            padding: 12px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            color: #fff;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-buy:hover {
            transform: scale(1.02);
            filter: brightness(1.1);
            box-shadow: 0 8px 20px rgba(168, 85, 247, 0.4);
        }

        /* --- CART FLOATING BUTTON --- */
        .cart-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            cursor: pointer;
            z-index: 100;
            transition: 0.3s;
        }

        .cart-float:hover {
            transform: scale(1.1) rotate(-10deg);
        }

        .cart-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #ef4444;
            color: #fff;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            font-size: 0.8rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #1e1b4b;
        }

        /* --- MODAL (Checkout) --- */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
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
            max-width: 500px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }

        .payment-option {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border: 2px solid rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        .payment-option:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: #818cf8;
        }

        .payment-option.selected {
            background: rgba(129, 140, 248, 0.1);
            border-color: #818cf8;
        }

        .payment-option i {
            font-size: 1.5rem;
            color: #818cf8;
        }

        .payment-option div h4 { margin: 0; font-size: 1.1rem; }
        .payment-option div p { margin: 5px 0 0 0; font-size: 0.85rem; color: rgba(255, 255, 255, 0.5); }

        
        /* LOCATION STEP STYLES */
        .location-step { display: none; }
        .location-step.active { display: block; }
        .location-input-group { margin-bottom: 20px; }
        .location-input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: rgba(255,255,255,0.7); }
        .location-input-group input { width: 100%; padding: 12px; border-radius: 10px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .map-container { width: 100%; height: 200px; border-radius: 15px; background: rgba(255,255,255,0.05); margin-bottom: 20px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .map-placeholder { text-align: center; color: rgba(255,255,255,0.3); }
        .btn-location { background: #818cf8; color: #fff; padding: 10px 15px; border-radius: 10px; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600; transition: 0.3s; margin-bottom: 15px; }
        .btn-location:hover { transform: translateY(-2px); filter: brightness(1.1); }
        .location-status { font-size: 0.85rem; color: #fca5a5; margin-bottom: 15px; display: none; }
        
        .out-of-stock { 
            opacity: 0.5; 
            filter: grayscale(100%); 
            pointer-events: none; 
            position: relative;
        }
        .out-of-stock::after {
            content: 'SOLD OUT';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.2rem;
            letter-spacing: 1px;
            border: 2px solid rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        
        <?php
            $hour = date('H');
            if ($hour < 12) $greeting = "Good Morning";
            elseif ($hour < 18) $greeting = "Good Afternoon";
            else $greeting = "Good Evening";
        ?>

        <div class="welcome-card glass-panel">
            <div class="welcome-text">
                <h1><?= $greeting ?>, <span class="name-gradient"><?= esc($username) ?></span>!</h1>
                <p>Ready for some fresh seafood today? Check out our best catch below.</p>
            </div>
            
            <div class="welcome-icon">
                <i class="fas fa-fish"></i>
            </div>
        </div>

        <!-- STOREFRONT SECTION -->
        <div class="store-header">
            <h2 style="margin: 0; color: #fff; font-size: 1.8rem;">💎 Available Seafood</h2>
        </div>

        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $p): ?>
                    <div class="product-card <?= ($p['current_stock'] <= 0) ? 'out-of-stock' : '' ?>">
                        <div class="product-image-container">
                            <?php if ($p['image']): ?>
                                <img src="<?= base_url('uploads/products/' . $p['image']) ?>" alt="<?= esc($p['name']) ?>">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05);">
                                    <i class="fas fa-image" style="font-size: 3rem; color: rgba(255,255,255,0.1);"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= esc($p['name']) ?></h3>
                            <div class="product-price-row">
                                <div class="product-price">₱<?= number_format($p['selling_price'], 2) ?></div>
                                <div class="product-unit">/ <?= esc($p['unit']) ?></div>
                            </div>
                            
                            <?php if ($p['current_stock'] > 0): ?>
                                <button class="btn-buy" onclick="addToCart(<?= $p['id'] ?>, '<?= esc($p['name']) ?>', <?= $p['selling_price'] ?>)">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            <?php else: ?>
                                <button class="btn-buy" disabled style="opacity: 0.5; background: #444;">
                                    <i class="fas fa-dolly-flatbed"></i> Sold Out
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px; opacity: 0.5;">
                    <span style="font-size: 3rem;">🛰️</span>
                    <p>No products available right now. Please check back later!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- CART FLOATING BUTTON -->
        <div class="cart-float" onclick="openCheckoutModal()">
            <i class="fas fa-shopping-cart"></i>
            <div class="cart-badge" id="cartCount">0</div>
        </div>

        <!-- CHECKOUT MODAL -->
        <div id="checkoutModal" class="modal">
            <div class="modal-content" style="max-width: 550px;">
                
                <!-- STEP 1: CART CONFIRMATION -->
                <div id="checkoutCart" class="location-step active">
                    <h2 style="margin-top: 0; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-shopping-basket" style="color: #a855f7;"></i> Confirm Order
                    </h2>
                    
                    <div id="cartItemsList" style="margin-bottom: 25px; max-height: 200px; overflow-y: auto;">
                        <!-- Items will be injected here -->
                    </div>

                    <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; margin-bottom: 25px;">
                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800;">
                            <span>Total Amount:</span>
                            <span id="cartTotal" style="color: #10b981;">₱0.00</span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 30px;">
                        <button class="btn-buy" style="background: #444; flex: 1;" onclick="closeCheckoutModal()">Cancel</button>
                        <button class="btn-buy" style="flex: 2;" onclick="goToLocation()">Next: Set Delivery Address</button>
                    </div>
                </div>

                <!-- STEP 2: LOCATION & NAME -->
                <div id="checkoutLocation" class="location-step">
                    <h2 style="margin-top: 0; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-map-marked-alt" style="color: #a855f7;"></i> Delivery Details
                    </h2>

                    <div class="location-input-group">
                        <label>Receiver Name</label>
                        <input type="text" id="deliveryName" value="<?= esc($username) ?>" required>
                    </div>

                    <div class="location-input-group">
                        <label>Contact Number</label>
                        <input type="text" id="deliveryPhone" placeholder="09XXXXXXXXX" required>
                    </div>

                    <button class="btn-location" onclick="getLocation()">
                        <i class="fas fa-location-crosshairs"></i> Get Current Location
                    </button>

                    <div id="locationError" class="location-status"></div>

                    <div class="map-container" id="mapContainer">
                        <div class="map-placeholder" id="mapPlaceholder">
                            <i class="fas fa-map-location-dot" style="font-size: 2.5rem; margin-bottom: 10px; display: block;"></i>
                            Waiting for location...
                        </div>
                    </div>

                    <div class="location-input-group">
                        <label>Detected Barangay / Area</label>
                        <input type="text" id="detectedBarangay" readonly placeholder="Detecting...">
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 30px;">
                        <button class="btn-buy" style="background: #444; flex: 1;" onclick="backToCart()">Back</button>
                        <button id="btnConfirmLocation" class="btn-buy" style="flex: 2;" onclick="goToPayment()" disabled>Next: Payment Method</button>
                    </div>
                </div>

                <!-- STEP 3: PAYMENT METHOD -->
                <div id="checkoutPayment" class="location-step">
                    <h2 style="margin-top: 0; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-credit-card" style="color: #a855f7;"></i> Payment Method
                    </h2>

                    <h4 style="margin-bottom: 15px; color: rgba(255,255,255,0.6);">Select Payment Method:</h4>
                    
                    <div class="payment-option" onclick="selectPayment('COD')" id="payCOD">
                        <i class="fas fa-hand-holding-usd"></i>
                        <div>
                            <h4>Cash on Delivery</h4>
                            <p>Pay when your order arrives</p>
                        </div>
                    </div>

                    <div class="payment-option" onclick="selectPayment('GCash')" id="payGCash">
                        <i class="fas fa-mobile-alt"></i>
                        <div>
                            <h4>GCash</h4>
                            <p>Pay via GCash transfer</p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 30px;">
                        <button class="btn-buy" style="background: #444; flex: 1;" onclick="backToLocation()">Back</button>
                        <button id="btnPlaceOrder" class="btn-buy" style="flex: 2;" onclick="initiateOrder()" disabled>Place Order</button>
                    </div>
                </div>

                <!-- GCash Mock UI (Nested Step) -->
                <div id="gcashMock" style="display: none; text-align: center;">
                    <h2 style="margin-top: 0; color: #2175f3;">GCash Checkout</h2>
                    <div style="background: #2175f3; padding: 20px; border-radius: 20px; margin: 20px 0;">
                        <div style="background: #fff; padding: 10px; border-radius: 10px; display: inline-block;">
                            <i class="fas fa-qrcode" style="font-size: 8rem; color: #000;"></i>
                        </div>
                        <p style="color: #fff; margin-top: 15px; font-weight: 700;">Scan to Pay</p>
                    </div>
                    <p style="color: rgba(255,255,255,0.6);">Please complete the payment in your GCash app.</p>
                    <div style="margin-top: 30px;">
                        <button class="btn-buy" style="background: #2175f3;" onclick="confirmGcashPayment()">
                            <i class="fas fa-check-circle"></i> I have paid
                        </button>
                        <button class="btn-buy" style="background: #444; margin-top: 10px;" onclick="cancelGcash()">Cancel</button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top: 50px; text-align: center; color: rgba(255,255,255,0.1); font-size: 0.7rem; letter-spacing: 2px; text-transform: uppercase;">
            TALAbahan Seafood Online | Freshly Harvested
        </div>
    </main>

    <script>
        let cart = [];
        let selectedPayment = null;

        function addToCart(id, name, price) {
            const index = cart.findIndex(item => item.id === id);
            if (index > -1) {
                cart[index].quantity += 1;
            } else {
                cart.push({ id, name, price, quantity: 1 });
            }
            updateCartUI();
            alert(`${name} added to cart!`);
        }

        function updateCartUI() {
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartCount').textContent = count;
            
            const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            document.getElementById('cartTotal').textContent = `₱${total.toLocaleString(undefined, {minimumFractionDigits: 2})}`;

            const list = document.getElementById('cartItemsList');
            if (cart.length === 0) {
                list.innerHTML = '<p style="text-align:center; opacity: 0.5;">Your cart is empty</p>';
            } else {
                list.innerHTML = cart.map(item => `
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding: 10px; background: rgba(255,255,255,0.03); border-radius: 10px;">
                        <div>
                            <div style="font-weight: 700;">${item.name}</div>
                            <div style="font-size: 0.8rem; opacity: 0.5;">₱${item.price} x ${item.quantity}</div>
                        </div>
                        <div style="font-weight: 700; color: #10b981;">₱${(item.price * item.quantity).toFixed(2)}</div>
                    </div>
                `).join('');
            }
        }

        function openCheckoutModal() {
            if (cart.length === 0) {
                alert('Please add some items to your cart first!');
                return;
            }
            // Reset steps
            document.querySelectorAll('.location-step').forEach(s => s.classList.remove('active'));
            document.getElementById('checkoutCart').classList.add('active');
            document.getElementById('gcashMock').style.display = 'none';
            
            document.getElementById('checkoutModal').classList.add('show');
        }

        function closeCheckoutModal() {
            document.getElementById('checkoutModal').classList.remove('show');
        }

        // STEP NAVIGATION
        function goToLocation() {
            document.getElementById('checkoutCart').classList.remove('active');
            document.getElementById('checkoutLocation').classList.add('active');
        }
        function backToCart() {
            document.getElementById('checkoutLocation').classList.remove('active');
            document.getElementById('checkoutCart').classList.add('active');
        }
        function goToPayment() {
            document.getElementById('checkoutLocation').classList.remove('active');
            document.getElementById('checkoutPayment').classList.add('active');
        }
        function backToLocation() {
            document.getElementById('checkoutPayment').classList.remove('active');
            document.getElementById('checkoutLocation').classList.add('active');
        }

        // GEOLOCATION LOGIC
        function getLocation() {
            const status = document.getElementById('locationError');
            const btn = document.querySelector('.btn-location');
            const barangayInput = document.getElementById('detectedBarangay');
            
            status.style.display = 'none';
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Locating...';

            if (!navigator.geolocation) {
                showLocationError("Geolocation is not supported by your browser.");
                return;
            }

            navigator.geolocation.getCurrentPosition(async (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                // 1. Update Map UI (Mock map for now as no API key provided)
                document.getElementById('mapPlaceholder').innerHTML = `
                    <div style="text-align: center;">
                        <i class="fas fa-check-circle" style="font-size: 2.5rem; color: #10b981; margin-bottom: 10px; display: block;"></i>
                        Location Locked!<br>
                        <small style="color: rgba(255,255,255,0.4)">${lat.toFixed(4)}, ${lon.toFixed(4)}</small>
                    </div>
                `;

                // 2. Reverse Geocoding (Using Nominatim - Free OSM API)
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                    const data = await res.json();
                    
                    // Extract barangay/suburb
                    const barangay = data.address.suburb || data.address.neighbourhood || data.address.village || data.address.quarter || "Unknown Area";
                    barangayInput.value = barangay;

                    // 3. Validate against Admin Shipping Rules
                    validateShippingLocation(barangay);

                } catch (err) {
                    showLocationError("Could not identify your barangay. Please try again.");
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-location-crosshairs"></i> Get Current Location';
                }

            }, (error) => {
                showLocationError("Permission denied or location unavailable.");
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-location-crosshairs"></i> Get Current Location';
            });
        }

        async function validateShippingLocation(barangay) {
            try {
                const response = await fetch('<?= site_url('customer/validate-location') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                    body: `barangay=${encodeURIComponent(barangay)}&<?= csrf_token() ?>=<?= csrf_hash() ?>`
                });
                const result = await response.json();

                if (result.status === 'success') {
                    document.getElementById('btnConfirmLocation').disabled = false;
                    document.getElementById('locationError').style.color = '#10b981';
                    document.getElementById('locationError').innerHTML = '<i class="fas fa-check"></i> We deliver to your area!';
                    document.getElementById('locationError').style.display = 'block';
                } else {
                    document.getElementById('btnConfirmLocation').disabled = true;
                    showLocationError("Sorry, we don't ship to this location yet.");
                }
            } catch (e) {
                console.error(e);
            }
        }

        function showLocationError(msg) {
            const status = document.getElementById('locationError');
            status.style.color = '#fca5a5';
            status.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${msg}`;
            status.style.display = 'block';
        }

        function selectPayment(method) {
            selectedPayment = method;
            document.getElementById('payCOD').classList.remove('selected');
            document.getElementById('payGCash').classList.remove('selected');
            
            if (method === 'COD') document.getElementById('payCOD').classList.add('selected');
            else document.getElementById('payGCash').classList.add('selected');

            document.getElementById('btnPlaceOrder').disabled = false;
        }

        function initiateOrder() {
            if (selectedPayment === 'GCash') {
                document.getElementById('checkoutMain').style.display = 'none';
                document.getElementById('gcashMock').style.display = 'block';
            } else {
                placeOrder();
            }
        }

        function cancelGcash() {
            document.getElementById('gcashMock').style.display = 'none';
            document.getElementById('checkoutMain').style.display = 'block';
        }

        function confirmGcashPayment() {
            placeOrder();
        }

        async function placeOrder() {
            const btn = document.getElementById('btnPlaceOrder');
            const gcashBtn = document.querySelector('#gcashMock .btn-buy');
            
            if (selectedPayment === 'GCash') {
                gcashBtn.disabled = true;
                gcashBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying Payment...';
            } else {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }

            const formData = new FormData();
            formData.append('order_data', JSON.stringify({
                items: cart,
                payment_method: selectedPayment,
                shipping_details: {
                    name: document.getElementById('deliveryName').value,
                    phone: document.getElementById('deliveryPhone').value,
                    barangay: document.getElementById('detectedBarangay').value
                }
            }));
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            try {
                const response = await fetch('<?= site_url('customer/placeOrder') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();
                if (result.status === 'success') {
                    alert(result.message + '\nTransaction Code: ' + result.transaction_code);
                    cart = [];
                    updateCartUI();
                    closeCheckoutModal();
                    location.reload(); 
                } else {
                    alert('Error: ' + result.message);
                    resetOrderButtons();
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
                resetOrderButtons();
            }
        }

        function resetOrderButtons() {
            const btn = document.getElementById('btnPlaceOrder');
            const gcashBtn = document.querySelector('#gcashMock .btn-buy');
            btn.disabled = false;
            btn.textContent = 'Place Order';
            gcashBtn.disabled = false;
            gcashBtn.innerHTML = '<i class="fas fa-check-circle"></i> I have paid';
        }
    </script>

</body>
</html>