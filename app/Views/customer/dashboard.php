<?php if (!($isAJAX ?? false)): ?>
<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>
<div id="page-content">
<?php endif; ?>

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
            user-select: none;
            color: #c084fc;
            position: absolute;
            right: 40px;
            top: 50%;
            transform: translateY(-50%) rotate(10deg);
            pointer-events: none; /* Ensure it doesn't block clicks */
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
            aspect-ratio: 1 / 1.1; /* Slightly taller for more impact */
            overflow: hidden;
            position: relative;
            background: rgba(255, 255, 255, 0.02); /* Placeholder bg */
        }

        .product-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-card:hover .product-image-container img {
            transform: scale(1.08);
        }

        .product-info {
            padding: 12px 14px; /* Reduced internal padding */
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px; /* Consistent spacing between elements */
        }

        .product-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.8rem;
            line-height: 1.4;
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

        /* --- SOCIAL PROOF INDICATORS --- */
        .product-social-proof {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 2px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.45);
            font-weight: 600;
        }

        .rating-stars {
            color: #fbbf24;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .sold-count {
            padding-left: 8px;
            border-left: 1px solid rgba(255, 255, 255, 0.1);
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
            bottom: 30px; /* Moved to bottom position */
            right: 30px;
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
            animation: badgeBounce 0.6s cubic-bezier(0.36, 0, 0.66, -0.56) alternate infinite;
            animation-iteration-count: 2;
        }

        @keyframes badgeBounce {
            0% { transform: scale(1); }
            100% { transform: scale(1.2) translateY(-2px); }
        }

        /* --- TOAST (Quick feedback) --- */
        .toast {
            position: fixed;
            left: 50%;
            bottom: 130px; /* above bottom nav + below action buttons */
            transform: translateX(-50%) translateY(12px);
            background: rgba(20, 15, 45, 0.92);
            border: 1px solid rgba(255,255,255,0.14);
            color: #fff;
            padding: 12px 14px;
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(0,0,0,0.45);
            display: none;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            z-index: 99980; /* below chat widget */
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            max-width: min(520px, calc(100vw - 32px));
        }
        .toast.show {
            display: inline-flex;
            animation: toastIn 0.16s ease-out forwards;
        }
        .toast .toast-icon {
            width: 30px;
            height: 30px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(16,185,129,0.16);
            border: 1px solid rgba(16,185,129,0.25);
            color: #86efac;
            flex: 0 0 auto;
        }
        .toast .toast-text {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(-50%) translateY(12px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
        @media (max-width: 768px) {
            .toast { bottom: 120px; }
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
            padding: 20px 20px 0 20px; /* Added padding for better desktop layout */
            display: flex;
            flex-direction: column;
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
            background: #140f2d; /* Solid background to prevent content overlap */
            padding: 15px 20px;
            margin: 20px -20px 0 -20px;
            display: flex;
            gap: 12px;
            z-index: 100;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .cart-item-row {
            position: relative;
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
            margin-bottom: 8px;
            overflow: hidden; /* Hide the delete button initially */
            touch-action: pan-y; /* Allow vertical scroll but handle horizontal swipe */
        }
        .cart-item-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            gap: 12px;
            position: relative;
            z-index: 2;
            background: #140f2d; /* Match modal bg to cover delete button */
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            width: 100%;
        }
        .cart-item-delete-action {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 70px;
            background: #ef4444;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            z-index: 1;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .cart-item-delete-action:active {
            background: #dc2626;
        }
        .cart-item-thumb {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .cart-item-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .cart-item-info {
            flex: 1;
            min-width: 0;
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
        .map-container { 
            width: 100%; 
            height: 350px; /* Increased height for Desktop */
            border-radius: 20px; 
            background: #242424; /* Solid background before map loads */
            margin-bottom: 20px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            position: relative; 
            overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            z-index: 5;
        }
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
            .welcome-card { 
                padding: 25px; 
                flex-direction: column; 
                align-items: flex-start; 
                text-align: left; 
                min-height: 140px;
                margin-bottom: 20px;
            }
            .welcome-text h1 { font-size: 1.6rem; }
            .welcome-text p { font-size: 0.95rem; line-height: 1.4; margin-top: 8px; max-width: 80%; }
            .welcome-icon { 
                font-size: 3.5rem; 
                opacity: 0.08; 
                right: 20px;
                top: 20px; 
                transform: rotate(15deg); 
            }
            .product-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .product-card { border-radius: 16px; }
            .product-info { padding: 10px; gap: 4px; }
            .product-name { font-size: 0.85rem; height: 2.4rem; line-height: 1.25; }
            .product-price { font-size: 1rem; }
            .product-unit { font-size: 0.7rem; }
            .product-social-proof { font-size: 0.65rem; gap: 6px; }
            .sold-count { padding-left: 6px; }
            .btn-buy { 
                padding: 10px; 
                margin-top: 8px; 
                border-radius: 10px; 
                font-size: 0.85rem;
                gap: 6px;
            }
            .btn-buy i { font-size: 0.9rem; }
            .btn-add-cart { 
                padding: 8px; 
                margin-top: 4px; 
                border-radius: 8px; 
                font-size: 0.8rem; 
            }
            .cart-float { 
                width: 52px !important; 
                height: 52px !important; 
                bottom: 105px !important; 
                right: 20px !important; 
                font-size: 1.2rem !important; 
            }
            .cart-badge {
                width: 22px;
                height: 22px;
                font-size: 0.7rem;
                top: -2px;
                right: -2px;
            }
            .checkout-sheet-content {
                width: 100%;
                max-width: none !important;
                border-radius: 20px 20px 0 0;
                max-height: 95vh; /* Increased height for mobile */
                padding: 0 !important;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
            .modal-header {
                font-size: 1.15rem !important;
                margin-bottom: 8px !important;
                padding: 18px 18px 0 18px;
            }
            .location-step {
                flex: 1;
                display: none;
                flex-direction: column;
                overflow: hidden;
                height: 100%;
            }
            .location-step.active {
                display: flex;
            }
            /* Scrollable area inside the step */
            .location-step > *:not(.modal-header):not(.step-actions) {
                padding-left: 18px;
                padding-right: 18px;
            }
            #cartItemsList, .map-container, .payment-option-container, .step-content-scroll {
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            .map-container {
                height: 180px !important; /* Optimized height for mobile */
                min-height: 180px;
                margin-bottom: 12px;
            }
            .step-actions {
                position: sticky; /* Keep actions visible at bottom */
                bottom: 0;
                margin-top: auto;
                padding: 12px 18px;
                padding-bottom: calc(12px + env(safe-area-inset-bottom));
                background: #140f2d;
                z-index: 101;
                border-top: 1px solid rgba(255,255,255,0.1);
                display: flex;
                gap: 8px;
            }
            #cartItemsList {
                max-height: 35vh !important;
            }
            .location-input-group {
                margin-bottom: 12px;
            }
            .location-input-group label {
                font-size: 0.85rem;
                margin-bottom: 4px;
            }
            .location-input-group input, .location-input-group select {
                padding: 10px;
                font-size: 0.95rem;
            }
            #geocodedAddressContainer {
                padding: 10px !important;
                margin-top: 8px !important;
            }
        }
        .btn-buy:disabled {
        background: #333 !important;
        color: rgba(255,255,255,0.3) !important;
        cursor: not-allowed;
        box-shadow: none;
        border-color: rgba(255,255,255,0.05);
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
                                <img
                                    src="<?= base_url('uploads/products/' . $p['image']) ?>"
                                    alt="<?= esc($p['name']) ?>"
                                    onerror="this.onerror=null;this.src='<?= base_url('images/pic1.jpg') ?>';"
                                >
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
                            
                            <!-- REAL-TIME SOCIAL PROOF INDICATORS -->
                            <div class="product-social-proof">
                                <div class="rating-stars">
                                    <i class="fas fa-star"></i>
                                    <span><?= $p['real_rating'] ? number_format($p['real_rating'], 1) : '5.0' ?></span>
                                </div>
                                <div class="sold-count">
                                    <?= number_format($p['real_sold_count'] ?? 0) ?> Sold
                                </div>
                            </div>
                            
                            <?php if ($p['current_stock'] > 0): ?>
                                <button class="btn-buy" onclick="buyNow(<?= $p['id'] ?>, '<?= esc($p['name']) ?>', <?= $p['selling_price'] ?>, '<?= esc($p['image']) ?>')">
                                    <i class="fas fa-bolt"></i> Buy Now
                                </button>
                                <button class="btn-add-cart" onclick="addToCart(<?= $p['id'] ?>, '<?= esc($p['name']) ?>', <?= $p['selling_price'] ?>, '<?= esc($p['image']) ?>')">
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

        <!-- TOAST -->
        <div class="toast" id="toast">
            <span class="toast-icon"><i class="fas fa-check"></i></span>
            <span class="toast-text" id="toastText">Added to cart</span>
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
                    
                    <div style="flex: 1; overflow-y: auto;">
                        <div id="cartItemsList" style="margin-bottom: 25px;">
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
                    </div>

                    <div class="step-actions">
                        <button class="btn-buy" style="background: #444; flex: 1;" onclick="closeCheckoutModal()">Cancel</button>
                        <button class="btn-buy" style="flex: 2;" onclick="goToLocation()">Buy Now</button>
                    </div>
                </div>

                <div id="checkoutLocation" class="location-step">
                    <div class="modal-header">
                        <i class="fas fa-map-marked-alt" style="color: #a855f7; margin-right: 10px;"></i> Delivery Details
                    </div>

                    <?php if (($ship_to_all ?? '0') !== '1'): ?>
                        <!-- PRE-VALIDATION BANNER (Strict Mode Only) -->
                        <div style="margin: 0 18px 15px 18px; padding: 12px 18px; background: rgba(129, 140, 248, 0.1); border: 1px solid rgba(129, 140, 248, 0.2); border-radius: 12px; display: flex; align-items: center; gap: 12px; animation: fadeIn 0.4s ease;">
                            <span style="font-size: 1.2rem;">🦀</span>
                            <span style="font-size: 0.85rem; color: #c7d2fe; font-weight: 500; line-height: 1.4;">
                                Fresh seafood delivery available for 
                                <strong style="color: #fff;">
                                    <?php 
                                        if (!empty($shippingLocations)) {
                                            $names = array_column($shippingLocations, 'barangay_name');
                                            if (count($names) > 3) {
                                                echo esc(implode(', ', array_slice($names, 0, 3))) . ' and more...';
                                            } else {
                                                echo esc(implode(', ', $names));
                                            }
                                        } else {
                                            echo "selected areas";
                                        }
                                    ?>
                                </strong>!
                            </span>
                        </div>
                    <?php endif; ?>

                    <div style="flex: 1; overflow-y: auto;" class="step-content-scroll">
                        <div class="location-input-group">
                            <label>Receiver Name</label>
                            <input type="text" id="deliveryName" value="<?= esc($username) ?>" required>
                        </div>

                        <div class="location-input-group">
                            <label>Contact Number</label>
                            <input type="text" id="deliveryPhone" placeholder="09XXXXXXXXX" required>
                        </div>

                        <?php if (($ship_to_all ?? '0') === '1'): ?>
                            <!-- MANUAL ADDRESS ENTRY (When Global Shipping is ON) -->
                            <div class="location-input-group">
                                <label>Province / City / Municipality</label>
                                <input type="text" id="manualCity" placeholder="e.g. Negros Occidental, Bacolod City" required>
                            </div>
                            
                            <div class="location-input-group">
                                <label>Barangay</label>
                                <input type="text" id="manualBarangayInput" placeholder="e.g. Villamonte" required>
                            </div>

                            <div class="location-input-group">
                                <label>Street / House Number / Landmarks</label>
                                <textarea id="manualStreet" placeholder="e.g. Blk 12 Lot 5, Moon Street, near Water Station" rows="3" style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; font-family: inherit; outline: none; transition: 0.3s; resize: none;"></textarea>
                            </div>

                            <!-- Hidden field to keep JS compatibility with 'detectedBarangay' -->
                            <input type="hidden" id="detectedBarangay">
                        <?php else: ?>
                            <!-- AUTO DETECTION (When Specific Locations Only) -->
                            <button class="btn-location" onclick="getLocation()">
                                <i class="fas fa-location-crosshairs"></i> Get Current Location
                            </button>

                            <!-- SERVICEABLE AREAS TOOLTIP (TikTok Style) -->
                            <div style="margin-top: -5px; margin-bottom: 15px; display: flex; align-items: center; gap: 6px; color: rgba(255,255,255,0.5); font-size: 0.75rem;">
                                <i class="fas fa-location-dot" style="color: #a855f7;"></i>
                                <span>Currently serving: 
                                    <strong style="color: rgba(255,255,255,0.8);">
                                        <?php 
                                            if (!empty($shippingLocations)) {
                                                $names = array_column($shippingLocations, 'barangay_name');
                                                echo esc(implode(', ', array_slice($names, 0, 3)));
                                                if (count($names) > 3) echo '...';
                                            } else {
                                                echo "selected areas";
                                            }
                                        ?>
                                    </strong>
                                </span>
                            </div>

                            <div id="locationError" class="location-status"></div>
                        
                        <!-- Supported Areas Link -->
                        <div id="supportedAreasHint" style="display: none; margin-top: -10px; margin-bottom: 20px; text-align: center; animation: fadeIn 0.3s ease;">
                            <a href="javascript:void(0)" onclick="openModal('supportedAreasModal')" style="color: #818cf8; font-size: 0.85rem; text-decoration: underline; font-weight: 600;">
                                <i class="fas fa-list-ul"></i> See where we deliver
                            </a>
                        </div>

                        <div class="map-container" id="mapContainer" style="position: relative; min-height: 200px;">
                            <div id="checkoutMap" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 20px; z-index: 1; background: #242424;"></div>
                            <div class="map-placeholder" id="mapPlaceholder" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 2; background: rgba(0,0,0,0.6); display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none; transition: opacity 0.3s ease;">
                                <i class="fas fa-map-location-dot" style="font-size: 2.5rem; margin-bottom: 10px; display: block;"></i>
                                Waiting for location...
                            </div>
                            <!-- Subtle GPS Coordinates -->
                            <div id="gpsCoords" style="position: absolute; bottom: 10px; right: 10px; z-index: 3; font-size: 0.65rem; color: rgba(255,255,255,0.4); background: rgba(0,0,0,0.5); padding: 2px 8px; border-radius: 5px; pointer-events: none; display: none;"></div>
                        </div>

                            <div class="location-input-group">
                                <label>Detected Barangay / Area</label>
                                <input type="text" id="detectedBarangay" readonly placeholder="Detecting..." style="width: 100%;">
                            </div>

                            <!-- Full Address Display (Geocoding Result) -->
                            <div id="geocodedAddressContainer" style="display: none; margin-top: 15px; padding: 15px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 12px; animation: slideDown 0.3s ease;">
                                <label style="color: #10b981; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Full Delivery Address:</label>
                                <div id="fullGeocodedAddress" style="color: #fff; font-size: 0.95rem; line-height: 1.4; font-weight: 500;"></div>
                                <input type="hidden" id="geocodedCity">
                                <input type="hidden" id="geocodedStreet">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="step-actions">
                        <button class="btn-buy" style="background: #444; flex: 1;" onclick="backToCart()">Back</button>
                        <button id="btnConfirmLocation" class="btn-buy" style="flex: 2;" onclick="goToPayment()" <?= (($ship_to_all ?? '0') === '1') ? '' : 'disabled' ?>>Next: Payment Method</button>
                    </div>
                </div>

                <!-- STEP 3: PAYMENT METHOD -->
                <div id="checkoutPayment" class="location-step">
                    <div class="modal-header">
                        <i class="fas fa-credit-card" style="color: #a855f7; margin-right: 10px;"></i> Payment Method
                    </div>

                    <div style="flex: 1; overflow-y: auto;">
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

    <!-- Supported Areas Modal -->
    <div id="supportedAreasModal" class="modal">
        <div class="modal-content" style="max-width: 400px; border-radius: 25px;">
            <button class="modal-close-btn" onclick="closeModal('supportedAreasModal')">&times;</button>
            <div class="modal-header">
                <i class="fas fa-truck-fast" style="color: #a855f7; margin-right: 10px;"></i> Supported Areas
            </div>
            <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 20px;">We currently deliver to these specific barangays:</p>
            
            <div style="max-height: 300px; overflow-y: auto; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding-right: 10px;">
                <?php if(!empty($shippingLocations)): foreach($shippingLocations as $loc): ?>
                    <div class="glass-panel" style="padding: 10px; border-radius: 12px; font-size: 0.85rem; color: #fff; text-align: center; border: 1px solid rgba(255,255,255,0.1);">
                        <?= esc($loc['barangay_name']) ?>
                    </div>
                <?php endforeach; else: ?>
                    <p style="grid-column: span 2; text-align: center; color: rgba(255,255,255,0.3);">No areas listed yet.</p>
                <?php endif; ?>
            </div>
            
            <button class="btn-buy" style="width: 100%; margin-top: 25px;" onclick="closeModal('supportedAreasModal')">Got it!</button>
        </div>
    </div>

    <script>
        (function() {
            let cartItems = [];
            let selectedPayment = null;
            let checkoutQuote = null;
            let toastTimer = null;
            let leafletMap = null;
            let mapMarker = null;

            window.showToast = function(message) {
                const toast = document.getElementById('toast');
                const text = document.getElementById('toastText');
                if (!toast || !text) return;
                text.textContent = message || 'Added to cart';
                toast.classList.add('show');
                if (toastTimer) clearTimeout(toastTimer);
                toastTimer = setTimeout(() => toast.classList.remove('show'), 1300);
            }

            window.addToCart = function(id, name, price, image) {
                const index = cartItems.findIndex(item => item.id === id);
                if (index > -1) {
                    cartItems[index].quantity += 1;
                } else {
                    cartItems.push({ id, name, price, image, quantity: 1 });
                }
                updateCartUI();
                showToast(`Added: ${name}`);
            }

            window.buyNow = function(id, name, price, image) {
                cartItems = [{ id, name, price, image, quantity: 1 }];
                updateCartUI();
                showToast(`Added: ${name}`);
                openCheckoutModal();
            }

            function updateCartUI() {
                const count = cartItems.reduce((sum, item) => sum + item.quantity, 0);
                const countEl = document.getElementById('cartCount');
                if (countEl) countEl.innerText = count;
            }

            window.updateQty = function(id, delta) {
                const idx = cartItems.findIndex(item => item.id === id);
                if (idx === -1) return;
                cartItems[idx].quantity += delta;
                if (cartItems[idx].quantity <= 0) {
                    cartItems.splice(idx, 1);
                }
                updateCartUI();
                if (cartItems.length === 0) {
                    closeCheckoutModal();
                    return;
                }
                renderCartItems();
            }

            window.removeFromCart = function(id) {
                const idx = cartItems.findIndex(item => item.id === id);
                if (idx !== -1) {
                    const name = cartItems[idx].name;
                    cartItems.splice(idx, 1);
                    updateCartUI();
                    showToast(`Removed: ${name}`);
                    if (cartItems.length === 0) {
                        closeCheckoutModal();
                    } else {
                        renderCartItems();
                    }
                }
            }

            // Swipe-to-Delete Logic
            let touchStartX = 0;
            let touchEndX = 0;
            let activeSwipeItem = null;

            window.handleTouchStart = function(e) {
                touchStartX = e.changedTouches[0].screenX;
                activeSwipeItem = e.currentTarget;
                // Reset other open items
                document.querySelectorAll('.cart-item-content').forEach(item => {
                    if (item !== activeSwipeItem) item.style.transform = 'translateX(0)';
                });
            }

            window.handleTouchMove = function(e) {
                const currentX = e.changedTouches[0].screenX;
                const diff = touchStartX - currentX;
                if (diff > 0 && diff <= 70) { // Swiping left
                    activeSwipeItem.style.transform = `translateX(-${diff}px)`;
                }
            }

            window.handleTouchEnd = function(e) {
                touchEndX = e.changedTouches[0].screenX;
                const diff = touchStartX - touchEndX;
                if (diff > 35) { // Threshold for keeping it open
                    activeSwipeItem.style.transform = 'translateX(-70px)';
                } else {
                    activeSwipeItem.style.transform = 'translateX(0)';
                }
            }

            window.openCheckoutModal = function() {
                if (cartItems.length === 0) {
                    alert('Your cart is empty!');
                    return;
                }
                document.body.classList.add('modal-open');
                
                // Hide Chatbot when cart is open
                const chatbotContainer = document.getElementById('chat-button-container');
                if (chatbotContainer) chatbotContainer.style.display = 'none';
                
                renderCartItems();
                document.getElementById('checkoutModal').classList.add('show');
                document.getElementById('checkoutCart').classList.add('active');
                preloadCheckoutDefaults();
            }

            window.closeCheckoutModal = function() {
                document.getElementById('checkoutModal').classList.remove('show');
                document.body.classList.remove('modal-open');
                
                // Show Chatbot again when cart is closed
                const chatbotContainer = document.getElementById('chat-button-container');
                if (chatbotContainer) chatbotContainer.style.display = 'flex';
                
                // Reset steps
                document.querySelectorAll('.location-step').forEach(s => s.classList.remove('active'));
                document.getElementById('checkoutCart').classList.add('active');
            }

            function renderCartItems() {
                const list = document.getElementById('cartItemsList');
                let subtotal = 0;
                list.innerHTML = '';
                
                cartItems.forEach((item, index) => {
                    const lineSubtotal = item.price * item.quantity;
                    subtotal += lineSubtotal;
                    const imgSrc = item.image ? `<?= base_url('uploads/products/') ?>${item.image}` : `<?= base_url('images/pic1.jpg') ?>`;
                    list.innerHTML += `
                        <div class="cart-item-row" data-index="${index}">
                            <div class="cart-item-delete-action" onclick="removeFromCart(${item.id})">
                                <i class="fas fa-trash-alt"></i>
                            </div>
                            <div class="cart-item-content" 
                                 ontouchstart="handleTouchStart(event)" 
                                 ontouchmove="handleTouchMove(event)" 
                                 ontouchend="handleTouchEnd(event)">
                                <div class="cart-item-thumb">
                                    <img src="${imgSrc}" alt="${item.name}" onerror="this.src='<?= base_url('images/pic1.jpg') ?>'">
                                </div>
                                <div class="cart-item-info">
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
                    const bgyEl = document.getElementById('detectedBarangay');
                    if (bgyEl) {
                        bgyEl.value = savedBarangay;
                        validateBarangay(savedBarangay);
                    }
                }
                if (normalizedPayment && ['COD', 'GCASH'].includes(normalizedPayment)) {
                    selectPayment(normalizedPayment);
                } else {
                    selectPayment('COD');
                }
            }

            window.goToLocation = function() {
                document.getElementById('checkoutCart').classList.remove('active');
                document.getElementById('checkoutLocation').classList.add('active');
                
                // Refresh map size if it already exists
                if (leafletMap) {
                    setTimeout(() => {
                        leafletMap.invalidateSize();
                    }, 300);
                }
            }

            window.backToCart = function() {
                document.getElementById('checkoutLocation').classList.remove('active');
                document.getElementById('checkoutCart').classList.add('active');
            }

            window.getLocation = async function() {
                const status = document.getElementById('locationError');
                const placeholder = document.getElementById('mapPlaceholder');
                const detectedInput = document.getElementById('detectedBarangay');
                const btn = document.getElementById('btnConfirmLocation');
                
                status.style.display = 'none';
                btn.disabled = true; // Disable button while detecting
                placeholder.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i><p>Detecting Location...</p>';

                if (!navigator.geolocation) {
                    status.innerText = "Geolocation not supported by your browser.";
                    status.style.display = 'block';
                    return;
                }

                navigator.geolocation.getCurrentPosition(async (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    // Hide placeholder and show coordinates subtly
                    placeholder.style.opacity = '0';
                    placeholder.style.pointerEvents = 'none';
                    setTimeout(() => placeholder.style.display = 'none', 300);
                    const coordsDiv = document.getElementById('gpsCoords');
                    coordsDiv.innerText = `${lat.toFixed(4)}, ${lon.toFixed(4)}`;
                    coordsDiv.style.display = 'block';

                    // Initialize or update Leaflet Map
                    if (!leafletMap) {
                        leafletMap = L.map('checkoutMap', {
                            zoomControl: true,
                            dragging: true,
                            touchZoom: true
                        }).setView([lat, lon], 16);
                        
                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        }).addTo(leafletMap);
                    } else {
                        leafletMap.setView([lat, lon], 16);
                    }

                    // Add or move marker
                    if (mapMarker) {
                        mapMarker.setLatLng([lat, lon]);
                    } else {
                        mapMarker = L.marker([lat, lon]).addTo(leafletMap);
                    }

                    // Ensure map renders correctly in modal
                    setTimeout(() => leafletMap.invalidateSize(), 200);
                    
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                        const data = await response.json();
                        const addr = data.address;
                        
                        // Extract components
                        const bgy = addr.quarter || addr.suburb || addr.neighbourhood || addr.village || addr.city_district;
                        const city = addr.city || addr.town || addr.municipality;
                        const street = addr.road || addr.residential;
                        const state = addr.province || addr.state;

                        if(bgy) {
                            detectedInput.value = bgy;
                            
                            // Update full address display
                            const fullAddr = [street, bgy, city, state].filter(Boolean).join(', ');
                            document.getElementById('fullGeocodedAddress').innerText = fullAddr;
                            document.getElementById('geocodedAddressContainer').style.display = 'block';
                            
                            // Store detailed parts for checkout
                            document.getElementById('geocodedCity').value = city || '';
                            document.getElementById('geocodedStreet').value = street || '';

                            validateBarangay(bgy);
                        } else {
                            status.innerText = "Could not pinpoint Barangay. Please try again.";
                            status.style.display = 'block';
                            btn.disabled = true;
                        }
                    } catch (e) {
                        status.innerText = "Reverse Geocoding failed. Please try again.";
                        status.style.display = 'block';
                        btn.disabled = true;
                    }
                }, (err) => {
                    status.innerText = "Permission Denied or Timeout.";
                    status.style.display = 'block';
                    btn.disabled = true;
                });
            }

            async function validateBarangay(bgy) {
                const btn = document.getElementById('btnConfirmLocation');
                const status = document.getElementById('locationError');
                
                try {
                    const csrfName = document.querySelector('meta[name="csrf-name"]')?.content;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.content || 'X-CSRF-TOKEN';

                    const payload = new URLSearchParams();
                    payload.append('barangay', bgy);
                    if (csrfName && csrfToken) payload.append(csrfName, csrfToken);

                    const response = await fetch('<?= site_url('customer/validate-location') ?>', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest',
                            [csrfHeader]: csrfToken
                        },
                        body: payload.toString()
                    });
                    
                    // Update CSRF token if returned in headers or body
                    const data = await response.clone().json();
                    const newToken = response.headers.get('X-CSRF-TOKEN') || data.token;
                    if (newToken) {
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) meta.content = newToken;
                    }

                    const result = data;
                    
                    if(result.status === 'success') {
                        btn.disabled = false;
                        status.style.display = 'none';
                        document.getElementById('supportedAreasHint').style.display = 'none';
                        document.getElementById('detectedBarangay').style.borderColor = '#10b981';
                        
                        // Reset address box to success state
                        const addrBox = document.getElementById('geocodedAddressContainer');
                        addrBox.style.background = 'rgba(16, 185, 129, 0.1)';
                        addrBox.style.borderColor = 'rgba(16, 185, 129, 0.2)';
                        addrBox.querySelector('label').style.color = '#10b981';

                        if (mapMarker) {
                            mapMarker.getElement().style.filter = "hue-rotate(0deg)";
                        }
                    } else {
                        btn.disabled = true;
                        status.innerHTML = "We currently only deliver our fresh catch to <strong style='color: #2dd4bf;'>Bocana</strong> and nearby spots. Check back soon as we expand our route!";
                        status.style.display = 'block';
                        document.getElementById('supportedAreasHint').style.display = 'block';
                        document.getElementById('detectedBarangay').style.borderColor = '#ef4444';
                        
                        // Change address box to error state (Amber/Red)
                        const addrBox = document.getElementById('geocodedAddressContainer');
                        addrBox.style.background = 'rgba(245, 158, 11, 0.1)';
                        addrBox.style.borderColor = 'rgba(245, 158, 11, 0.4)';
                        addrBox.querySelector('label').style.color = '#fbbf24';

                        if (mapMarker) {
                            mapMarker.getElement().style.filter = "hue-rotate(140deg) saturate(3)";
                        }
                    }
                } catch (e) {
                    console.error(e);
                }
            }

            window.goToPayment = async function() {
                const quoteOk = await refreshQuote();
                if (!quoteOk) return;
                document.getElementById('checkoutLocation').classList.remove('active');
                document.getElementById('checkoutPayment').classList.add('active');
            }

            window.backToLocation = function() {
                document.getElementById('checkoutPayment').classList.remove('active');
                document.getElementById('checkoutLocation').classList.add('active');
            }

            window.selectPayment = function(method) {
                selectedPayment = method;
                document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
                const payEl = document.getElementById('pay' + method);
                if (payEl) payEl.classList.add('selected');
                const placeBtn = document.getElementById('btnPlaceOrder');
                if (placeBtn) placeBtn.disabled = false;
                localStorage.setItem('quick_checkout_payment', method);
            }

            window.initiateOrder = function() {
                if (selectedPayment === 'GCASH') {
                    document.getElementById('checkoutPayment').style.display = 'none';
                    document.getElementById('gcashMock').style.display = 'block';
                } else {
                    placeOrder();
                }
            }

            window.confirmGcashPayment = function() {
                alert('GCash Payment Simulated Successfully!');
                placeOrder();
            }

            window.cancelGcash = function() {
                document.getElementById('gcashMock').style.display = 'none';
                document.getElementById('checkoutPayment').style.display = 'block';
            }

            window.refreshQuote = async function() {
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
                
                let barangay, city, street;
                const shipToAll = '<?= $ship_to_all ?? '0' ?>' === '1';

                if (shipToAll) {
                    city = document.getElementById('manualCity')?.value.trim();
                    barangay = document.getElementById('manualBarangayInput')?.value.trim();
                    street = document.getElementById('manualStreet')?.value.trim();
                    
                    if (!city || !barangay || !street) {
                        return { status: 'error', message: 'Please fill in all address fields.' };
                    }
                } else {
                    barangay = document.getElementById('detectedBarangay').value.trim();
                    city = document.getElementById('geocodedCity')?.value.trim();
                    street = document.getElementById('geocodedStreet')?.value.trim();

                    if (!barangay) {
                        return { status: 'error', message: 'Please select or detect your barangay.' };
                    }
                }

                const voucherCode = document.getElementById('voucherCode').value.trim();
                const csrfName = document.querySelector('meta[name="csrf-name"]')?.content;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.content || 'X-CSRF-TOKEN';

                const payload = {
                    items: cartItems.map(item => ({ id: item.id, quantity: item.quantity })),
                    payment_method: selectedPayment || 'COD',
                    voucher_code: voucherCode,
                    shipping_details: {
                        name: name || 'Customer',
                        phone,
                        barangay,
                        city,
                        street
                    }
                };

                const formData = new FormData();
                formData.append('order_data', JSON.stringify(payload));
                if (csrfName && csrfToken) formData.append(csrfName, csrfToken);

                try {
                    const response = await fetch('<?= site_url('customer/precheckout') ?>', {
                        method: 'POST',
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest',
                            [csrfHeader]: csrfToken
                        },
                        body: formData
                    });

                    // Update CSRF token if returned in headers or body
                    const data = await response.clone().json();
                    const newToken = response.headers.get('X-CSRF-TOKEN') || data.token;
                    if (newToken) {
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) meta.content = newToken;
                    }

                    return data;
                } catch (error) {
                    return { status: 'error', message: 'Connection error while validating checkout.' };
                }
            }

            async function placeOrder() {
                const name = document.getElementById('deliveryName').value.trim();
                const phone = document.getElementById('deliveryPhone').value.trim();
                
                let barangay, city, street;
                const shipToAll = '<?= $ship_to_all ?? '0' ?>' === '1';

                if (shipToAll) {
                    city = document.getElementById('manualCity')?.value.trim();
                    barangay = document.getElementById('manualBarangayInput')?.value.trim();
                    street = document.getElementById('manualStreet')?.value.trim();
                } else {
                    barangay = document.getElementById('detectedBarangay').value.trim();
                    city = document.getElementById('geocodedCity')?.value.trim();
                    street = document.getElementById('geocodedStreet')?.value.trim();
                }

                const voucherCode = document.getElementById('voucherCode').value.trim();

                const quoteOk = await refreshQuote();
                if (!quoteOk) return;

                const orderData = {
                    items: cartItems.map(item => ({
                        id: item.id,
                        name: item.name,
                        quantity: item.quantity
                    })),
                    payment_method: selectedPayment || 'COD',
                    voucher_code: voucherCode,
                    shipping_details: {
                        name: name || 'Customer',
                        phone: phone,
                        barangay: barangay,
                        city: city,
                        street: street
                    }
                };

                try {
                    const csrfName = document.querySelector('meta[name="csrf-name"]')?.content;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.content || 'X-CSRF-TOKEN';
                    
                    const formData = new FormData();
                    formData.append('order_data', JSON.stringify(orderData));
                    if (csrfName && csrfToken) formData.append(csrfName, csrfToken);

                    const response = await fetch('<?= site_url('customer/placeOrder') ?>', {
                        method: 'POST',
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest',
                            [csrfHeader]: csrfToken
                        },
                        body: formData
                    });

                    // Update CSRF token if returned in headers or body
                    const data = await response.clone().json();
                    const newToken = response.headers.get('X-CSRF-TOKEN') || data.token;
                    if (newToken) {
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) meta.content = newToken;
                    }

                    const result = data;
                    
                    if(result.status === 'success') {
                        localStorage.setItem('quick_checkout_phone', phone);
                        localStorage.setItem('quick_checkout_barangay', barangay);
                        alert('Order placed successfully! Code: ' + (result.transaction_code || 'N/A'));
                        cartItems = [];
                        updateCartUI();
                        window.location.href = '<?= site_url('customer/order-items') ?>';
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (e) {
                    alert('Connection Error. Try again.');
                }
            }
        })();
    </script>

<?php if (!($isAJAX ?? false)): ?>
</div>
<?= $this->include('theme/customer_bottom_nav') ?>
<?= $this->include('theme/footer') ?>
<?php endif; ?>

<style>
    /* Customer Profile quick access (top-right) */
    .profile-float {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 48px; /* Slightly smaller */
        height: 48px;
        border-radius: 16px; /* Squircle for modern look */
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.16);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        cursor: pointer;
        z-index: 99990; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .profile-float:hover {
        transform: translateY(-3px) scale(1.05);
        background: rgba(168, 85, 247, 0.15);
        border-color: rgba(168, 85, 247, 0.4);
        box-shadow: 0 12px 25px rgba(168, 85, 247, 0.2);
    }
    .profile-float i { font-size: 1.1rem; }
    .profile-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: 8px; /* Match squircle */
        background: #ef4444;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #1e1b4b;
        box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        animation: badgeBounce 0.6s cubic-bezier(0.36, 0, 0.66, -0.56) alternate infinite;
        animation-iteration-count: 2;
    }
    @media (max-width: 768px) {
        .profile-float { 
            top: 15px; 
            right: 15px; 
            width: 42px; 
            height: 42px; 
            border-radius: 12px;
        }
        .profile-float i { font-size: 1rem; }
        .profile-badge { 
            top: -4px; 
            right: -4px; 
            min-width: 18px; 
            height: 18px; 
            font-size: 0.65rem; 
        }
    }
</style>

<a class="profile-float" href="<?= site_url('customer/profile') ?>" title="Profile">
    <i class="fas fa-user"></i>
    <?php if (!empty($activeOrdersCount)): ?>
        <span class="profile-badge"><?= (int) $activeOrdersCount ?></span>
    <?php endif; ?>
</a>
