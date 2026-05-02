<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

    <style>
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

        .btn-add-cart {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            border-radius: 10px;
            background: rgba(129, 140, 248, 0.12);
            color: #c7d2fe;
            border: 1px solid rgba(129, 140, 248, 0.35);
            font-weight: 700;
            cursor: pointer;
            transition: 0.25s;
        }
        .btn-add-cart:hover { background: rgba(129, 140, 248, 0.2); }

        /* --- PROFESSIONAL FLOATING BUTTONS LAYOUT --- */
        .cart-float {
            position: fixed;
            bottom: 30px;
            right: 110px; /* Positioned to the left of the chatbot */
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .cart-float:hover {
            transform: scale(1.1) translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
            filter: brightness(1.1);
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
        .checkout-sheet {
            align-items: flex-end !important;
            justify-content: center !important;
            padding: 0 !important;
            z-index: 10020;
        }
        .checkout-sheet-content {
            width: min(680px, 100%);
            max-width: 680px !important;
            border-radius: 24px 24px 0 0;
            background: rgba(20, 15, 45, 0.98);
            border: 1px solid rgba(255,255,255,0.12);
            border-bottom: none;
            box-shadow: 0 -15px 40px rgba(0,0,0,0.45);
            max-height: 88vh;
            overflow-y: auto;
            animation: sheetUp 0.28s ease;
        }
        @keyframes sheetUp {
            from { transform: translateY(20px); opacity: 0.2; }
            to { transform: translateY(0); opacity: 1; }
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
        .location-step {
            display: none;
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 0.22s ease, transform 0.22s ease;
        }
        .location-step.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        .step-actions {
            position: sticky;
            bottom: 0;
            background: linear-gradient(to top, rgba(20,15,45,0.98), rgba(20,15,45,0.86));
            padding-top: 12px;
            margin-top: 18px;
            display: flex;
            gap: 10px;
        }
        .cart-item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
            margin-bottom: 8px;
            gap: 10px;
        }
        .qty-controls {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 6px;
        }
        .qty-btn {
            width: 24px;
            height: 24px;
            border: none;
            border-radius: 8px;
            background: rgba(129,140,248,0.18);
            color: #c7d2fe;
            font-weight: 800;
            cursor: pointer;
            line-height: 24px;
        }
        .qty-btn:hover { background: rgba(129,140,248,0.35); }
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

        @media (max-width: 768px) {
            .welcome-card { padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; }
            .welcome-text h1 { font-size: 1.8rem; }
            .welcome-icon { font-size: 4rem; opacity: 0.05; top: 20px; transform: rotate(10deg); }
            .product-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
            .product-name { font-size: 0.95rem; height: 2.6rem; }
            .product-price { font-size: 1.1rem; }
            .cart-float { width: 60px; height: 60px; bottom: 20px; right: 20px; font-size: 1.5rem; }
            .checkout-sheet-content {
                width: 100%;
                max-width: none !important;
                border-radius: 20px 20px 0 0;
                max-height: 92vh;
                padding: 18px !important;
            }
            .modal-header {
                font-size: 1.35rem !important;
                margin-bottom: 14px !important;
            }
        }
    </style>

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
            <h2 style="margin: 0; color: #fff; font-size: 1.8rem; margin-bottom: 20px;">💎 Available Seafood</h2>
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
                                <button class="btn-buy" onclick="buyNow(<?= $p['id'] ?>, '<?= esc($p['name']) ?>', <?= $p['selling_price'] ?>)">
                                    <i class="fas fa-bolt"></i> Buy Now
                                </button>
                                <button class="btn-add-cart" onclick="addToCart(<?= $p['id'] ?>, '<?= esc($p['name']) ?>', <?= $p['selling_price'] ?>)">
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
        <div id="checkoutModal" class="modal checkout-sheet">
            <div class="modal-content checkout-sheet-content">
                <button class="modal-close-btn" onclick="closeCheckoutModal()">&times;</button>
                
                <!-- STEP 1: CART CONFIRMATION -->
                <div id="checkoutCart" class="location-step active">
                    <div class="modal-header">
                        <i class="fas fa-shopping-basket" style="color: #a855f7; margin-right: 10px;"></i> Confirm Order
                    </div>
                    
                    <div id="cartItemsList" style="margin-bottom: 25px; max-height: 200px; overflow-y: auto;">
                        <!-- Items will be injected here -->
                    </div>

                    <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; margin-bottom: 25px;">
                        <div style="display:flex; justify-content:space-between; font-size:0.95rem; color: rgba(255,255,255,0.7); margin-bottom:8px;">
                            <span>Subtotal:</span>
                            <span id="cartSubtotal">₱0.00</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:0.95rem; color: rgba(255,255,255,0.7); margin-bottom:8px;">
                            <span>Shipping Fee:</span>
                            <span id="cartShippingFee">₱0.00</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:0.95rem; color:#fbbf24; margin-bottom:8px;">
                            <span>Voucher Discount:</span>
                            <span id="cartVoucher">-₱0.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800;">
                            <span>Total Amount:</span>
                            <span id="cartTotal" style="color: #10b981;">₱0.00</span>
                        </div>
                    </div>

                    <div class="step-actions">
                        <button class="btn-buy" style="background: #444; flex: 1;" onclick="closeCheckoutModal()">Cancel</button>
                        <button class="btn-buy" style="flex: 2;" onclick="goToLocation()">Buy Now</button>
                    </div>
                </div>

                <!-- STEP 2: LOCATION & NAME -->
                <div id="checkoutLocation" class="location-step">
                    <div class="modal-header">
                        <i class="fas fa-map-marked-alt" style="color: #a855f7; margin-right: 10px;"></i> Delivery Details
                    </div>

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
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="detectedBarangay" readonly placeholder="Detecting..." style="flex: 1;">
                            <button class="btn-location" onclick="toggleManualSelect()" style="margin-bottom: 0; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>

                    <div class="location-input-group" id="manualBarangayGroup" style="display: none; animation: slideDown 0.3s ease;">
                        <label style="color: #818cf8;">Select Correct Barangay Manually</label>
                        <select id="manualBarangay" onchange="handleManualSelect(this.value)" style="width: 100%; padding: 12px; border-radius: 10px; background: #2d1b4e; border: 1px solid #818cf8; color: #fff; appearance: none; cursor: pointer; outline: none;">
                            <option value="" style="background: #1e1b4b; color: #fff;">-- Choose your Barangay --</option>
                            <?php if(!empty($shippingLocations)): foreach($shippingLocations as $loc): ?>
                                <option value="<?= esc($loc['barangay_name']) ?>" style="background: #1e1b4b; color: #fff; padding: 10px;"><?= esc($loc['barangay_name']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <small style="color: rgba(255,255,255,0.4); margin-top: 5px; display: block;">Use this if auto-detection is incorrect.</small>
                    </div>

                    <div class="step-actions">
                        <button class="btn-buy" style="background: #444; flex: 1;" onclick="backToCart()">Back</button>
                        <button id="btnConfirmLocation" class="btn-buy" style="flex: 2;" onclick="goToPayment()" disabled>Next: Payment Method</button>
                    </div>
                </div>

                <!-- STEP 3: PAYMENT METHOD -->
                <div id="checkoutPayment" class="location-step">
                    <div class="modal-header">
                        <i class="fas fa-credit-card" style="color: #a855f7; margin-right: 10px;"></i> Payment Method
                    </div>

                    <h4 style="margin-bottom: 15px; color: rgba(255,255,255,0.6);">Select Payment Method:</h4>
                    
                    <div class="payment-option" onclick="selectPayment('COD')" id="payCOD">
                        <i class="fas fa-hand-holding-usd"></i>
                        <div>
                            <h4>Cash on Delivery</h4>
                            <p>Pay when your order arrives</p>
                        </div>
                    </div>

                    <div class="payment-option" onclick="selectPayment('GCASH')" id="payGCASH">
                        <i class="fas fa-mobile-alt"></i>
                        <div>
                            <h4>GCash</h4>
                            <p>Pay via GCash transfer</p>
                        </div>
                    </div>

                    <div class="location-input-group" style="margin-top: 8px;">
                        <label>Voucher Code (Optional)</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="voucherCode" placeholder="Enter platform/shop voucher code" style="flex: 1;">
                            <button class="btn-location" style="margin-bottom: 0;" onclick="refreshQuote()">Apply</button>
                        </div>
                        <small id="voucherHint" style="display:block; margin-top: 8px; color: rgba(255,255,255,0.5);">Available vouchers are auto-applied if eligible.</small>
                    </div>

                    <div style="border-top:1px solid rgba(255,255,255,0.1); margin-top:15px; padding-top:12px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="color:rgba(255,255,255,0.7);">Subtotal</span>
                            <span id="paySubtotal">₱0.00</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="color:rgba(255,255,255,0.7);">Shipping</span>
                            <span id="payShipping">₱0.00</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="color:#fbbf24;">Voucher</span>
                            <span id="payVoucher">-₱0.00</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:1.1rem; font-weight:800;">
                            <span>Total</span>
                            <span id="payTotal" style="color:#10b981;">₱0.00</span>
                        </div>
                    </div>

                    <div class="step-actions">
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
        let checkoutQuote = null;

        function addToCart(id, name, price) {
            const index = cart.findIndex(item => item.id === id);
            if (index > -1) {
                cart[index].quantity += 1;
            } else {
                cart.push({ id, name, price, quantity: 1 });
            }
            updateCartUI();
        }

        function buyNow(id, name, price) {
            cart = [{ id, name, price, quantity: 1 }];
            updateCartUI();
            openCheckoutModal();
        }

        function updateCartUI() {
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartCount').innerText = count;
        }

        function updateQty(id, delta) {
            const idx = cart.findIndex(item => item.id === id);
            if (idx === -1) return;
            cart[idx].quantity += delta;
            if (cart[idx].quantity <= 0) {
                cart.splice(idx, 1);
            }
            updateCartUI();
            if (cart.length === 0) {
                closeCheckoutModal();
                return;
            }
            renderCartItems();
        }

        function openCheckoutModal() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            renderCartItems();
            document.getElementById('checkoutModal').classList.add('show');
            document.getElementById('checkoutCart').classList.add('active');
            preloadCheckoutDefaults();
        }

        function closeCheckoutModal() {
            document.getElementById('checkoutModal').classList.remove('show');
            // Reset steps
            document.querySelectorAll('.location-step').forEach(s => s.classList.remove('active'));
            document.getElementById('checkoutCart').classList.add('active');
        }

        function renderCartItems() {
            const list = document.getElementById('cartItemsList');
            let subtotal = 0;
            list.innerHTML = '';
            
            cart.forEach(item => {
                const lineSubtotal = item.price * item.quantity;
                subtotal += lineSubtotal;
                list.innerHTML += `
                    <div class="cart-item-row">
                        <div>
                            <div style="font-weight: 700; color: #fff;">${item.name}</div>
                            <div class="qty-controls">
                                <button class="qty-btn" onclick="updateQty(${item.id}, -1)">-</button>
                                <span style="font-size:0.9rem; color: rgba(255,255,255,0.75);">${item.quantity}</span>
                                <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                                <span style="font-size:0.85rem; color: rgba(255,255,255,0.5); margin-left: 8px;">₱${item.price.toFixed(2)} each</span>
                            </div>
                        </div>
                        <div style="font-weight: 800; color: #818cf8;">₱${lineSubtotal.toFixed(2)}</div>
                    </div>
                `;
            });

            const shipping = checkoutQuote?.shipping_fee ?? 0;
            const voucherDiscount = checkoutQuote?.voucher_discount ?? 0;
            const total = checkoutQuote?.final_total ?? Math.max(0, subtotal + shipping - voucherDiscount);
            document.getElementById('cartSubtotal').innerText = '₱' + subtotal.toFixed(2);
            document.getElementById('cartShippingFee').innerText = '₱' + Number(shipping).toFixed(2);
            document.getElementById('cartVoucher').innerText = '-₱' + Number(voucherDiscount).toFixed(2);
            document.getElementById('cartTotal').innerText = '₱' + total.toFixed(2);
        }

        function preloadCheckoutDefaults() {
            const savedPhone = localStorage.getItem('quick_checkout_phone');
            const savedBarangay = localStorage.getItem('quick_checkout_barangay');
            const savedPayment = localStorage.getItem('quick_checkout_payment');
            const normalizedPayment = savedPayment && savedPayment.toUpperCase() === 'GCASH' ? 'GCASH' : savedPayment;

            if (savedPhone) document.getElementById('deliveryPhone').value = savedPhone;
            if (savedBarangay) {
                document.getElementById('detectedBarangay').value = savedBarangay;
                validateBarangay(savedBarangay);
            }
            if (normalizedPayment && ['COD', 'GCASH'].includes(normalizedPayment)) {
                selectPayment(normalizedPayment);
            } else {
                selectPayment('COD');
            }
        }

        function goToLocation() {
            document.getElementById('checkoutCart').classList.remove('active');
            document.getElementById('checkoutLocation').classList.add('active');
        }

        function backToCart() {
            document.getElementById('checkoutLocation').classList.remove('active');
            document.getElementById('checkoutCart').classList.add('active');
        }

        function toggleManualSelect() {
            const group = document.getElementById('manualBarangayGroup');
            group.style.display = group.style.display === 'none' ? 'block' : 'none';
        }

        function handleManualSelect(val) {
            if(val) {
                document.getElementById('detectedBarangay').value = val;
                validateBarangay(val);
            }
        }

        async function getLocation() {
            const status = document.getElementById('locationError');
            const placeholder = document.getElementById('mapPlaceholder');
            const detectedInput = document.getElementById('detectedBarangay');
            
            status.style.display = 'none';
            placeholder.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i><p>Detecting Location...</p>';

            if (!navigator.geolocation) {
                status.innerText = "Geolocation not supported by your browser.";
                status.style.display = 'block';
                return;
            }

            navigator.geolocation.getCurrentPosition(async (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                placeholder.innerHTML = `<p style="font-size: 0.8rem; color: #86efac;">GPS LOCK: ${lat.toFixed(4)}, ${lon.toFixed(4)}</p>`;
                
                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                    const data = await response.json();
                    const addr = data.address;
                    const bgy = addr.quarter || addr.suburb || addr.neighbourhood || addr.village || addr.city_district;
                    
                    if(bgy) {
                        detectedInput.value = bgy;
                        validateBarangay(bgy);
                    } else {
                        status.innerText = "Could not pinpoint Barangay. Please select manually.";
                        status.style.display = 'block';
                    }
                } catch (e) {
                    status.innerText = "Reverse Geocoding failed. Select manually.";
                    status.style.display = 'block';
                }
            }, (err) => {
                status.innerText = "Permission Denied or Timeout.";
                status.style.display = 'block';
            });
        }

        async function validateBarangay(bgy) {
            const btn = document.getElementById('btnConfirmLocation');
            const status = document.getElementById('locationError');
            
            try {
                const csrfName = document.querySelector('meta[name="csrf-name"]')?.content;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const payload = new URLSearchParams();
                payload.append('barangay', bgy);
                if (csrfName && csrfToken) payload.append(csrfName, csrfToken);

                const response = await fetch('<?= site_url('customer/validate-location') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: payload.toString()
                });
                const result = await response.json();
                
                if(result.status === 'success') {
                    btn.disabled = false;
                    status.style.display = 'none';
                    document.getElementById('detectedBarangay').style.borderColor = '#10b981';
                } else {
                    btn.disabled = true;
                    status.innerText = "Sorry, we don't deliver to this location yet.";
                    status.style.display = 'block';
                    document.getElementById('detectedBarangay').style.borderColor = '#ef4444';
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function goToPayment() {
            const quoteOk = await refreshQuote();
            if (!quoteOk) return;
            document.getElementById('checkoutLocation').classList.remove('active');
            document.getElementById('checkoutPayment').classList.add('active');
        }

        function backToLocation() {
            document.getElementById('checkoutPayment').classList.remove('active');
            document.getElementById('checkoutLocation').classList.add('active');
        }

        function selectPayment(method) {
            selectedPayment = method;
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
            document.getElementById('pay' + method).classList.add('selected');
            document.getElementById('btnPlaceOrder').disabled = false;
            localStorage.setItem('quick_checkout_payment', method);
        }

        function initiateOrder() {
            if (selectedPayment === 'GCASH') {
                document.getElementById('checkoutPayment').style.display = 'none';
                document.getElementById('gcashMock').style.display = 'block';
            } else {
                placeOrder();
            }
        }

        function confirmGcashPayment() {
            alert('GCash Payment Simulated Successfully!');
            placeOrder();
        }

        function cancelGcash() {
            document.getElementById('gcashMock').style.display = 'none';
            document.getElementById('checkoutPayment').style.display = 'block';
        }

        async function refreshQuote() {
            const result = await requestCheckoutQuote();
            if (!result || result.status !== 'success') {
                checkoutQuote = null;
                alert((result && result.message) ? result.message : 'Unable to validate checkout details.');
                return false;
            }

            checkoutQuote = result.data;
            document.getElementById('paySubtotal').innerText = '₱' + Number(checkoutQuote.subtotal || 0).toFixed(2);
            document.getElementById('payShipping').innerText = '₱' + Number(checkoutQuote.shipping_fee || 0).toFixed(2);
            document.getElementById('payVoucher').innerText = '-₱' + Number(checkoutQuote.voucher_discount || 0).toFixed(2);
            document.getElementById('payTotal').innerText = '₱' + Number(checkoutQuote.final_total || 0).toFixed(2);
            document.getElementById('voucherHint').innerText = checkoutQuote.applied_vouchers?.length
                ? 'Applied: ' + checkoutQuote.applied_vouchers.map(v => `${v.code} (-₱${Number(v.discount).toFixed(2)})`).join(', ')
                : 'No eligible vouchers applied for this checkout.';
            renderCartItems();
            return true;
        }

        async function requestCheckoutQuote() {
            const name = document.getElementById('deliveryName').value.trim();
            const phone = document.getElementById('deliveryPhone').value.trim();
            const barangay = document.getElementById('detectedBarangay').value.trim();
            const voucherCode = document.getElementById('voucherCode').value.trim();
            const csrfName = document.querySelector('meta[name="csrf-name"]')?.content;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const payload = {
                items: cart.map(item => ({ id: item.id, quantity: item.quantity })),
                payment_method: selectedPayment || 'COD',
                voucher_code: voucherCode,
                shipping_details: {
                    name: name || 'Customer',
                    phone,
                    barangay
                }
            };

            const formData = new FormData();
            formData.append('order_data', JSON.stringify(payload));
            if (csrfName && csrfToken) formData.append(csrfName, csrfToken);

            try {
                const response = await fetch('<?= site_url('customer/precheckout') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                return await response.json();
            } catch (error) {
                return { status: 'error', message: 'Connection error while validating checkout.' };
            }
        }

        async function placeOrder() {
            const name = document.getElementById('deliveryName').value.trim();
            const phone = document.getElementById('deliveryPhone').value.trim();
            const barangay = document.getElementById('detectedBarangay').value.trim();
            const voucherCode = document.getElementById('voucherCode').value.trim();

            const quoteOk = await refreshQuote();
            if (!quoteOk) return;

            const orderData = {
                items: cart.map(item => ({
                    id: item.id,
                    name: item.name,
                    quantity: item.quantity
                })),
                payment_method: selectedPayment || 'COD',
                voucher_code: voucherCode,
                shipping_details: {
                    name: name || 'Customer',
                    phone: phone,
                    barangay: barangay
                }
            };

            try {
                const csrfName = document.querySelector('meta[name="csrf-name"]')?.content;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const formData = new FormData();
                formData.append('order_data', JSON.stringify(orderData));
                if (csrfName && csrfToken) formData.append(csrfName, csrfToken);

                const response = await fetch('<?= site_url('customer/placeOrder') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const result = await response.json();
                
                if(result.status === 'success') {
                    localStorage.setItem('quick_checkout_phone', phone);
                    localStorage.setItem('quick_checkout_barangay', barangay);
                    alert('Order placed successfully! Code: ' + (result.transaction_code || 'N/A'));
                    cart = [];
                    updateCartUI();
                    window.location.href = '<?= site_url('customer/order-items') ?>';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (e) {
                alert('Connection Error. Try again.');
            }
        }
    </script>

<?= $this->include('theme/footer') ?>
