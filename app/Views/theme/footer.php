    <style>
        /* Floating Actions Positioning */
        body.modal-open #chat-button-container,
        body.details-modal-open #chat-button-container,
        body.modal-open .cart-float {
            display: none !important;
        }
        
        #chat-button-container {
            position: fixed !important;
            bottom: 100px; /* Default: Above cart */
            right: 30px;
            z-index: 100001;
            transition: all 0.3s ease;
        }

        /* If not a customer, move chat down */
        body:not(.customer-has-bottom-nav) #chat-button-container {
            bottom: 30px;
        }

        #chat-container {
            z-index: 100002 !important;
        }

        @media (max-width: 1024px) {
            #chat-button-container {
                bottom: calc(175px + env(safe-area-inset-bottom, 0px)); 
                right: 25px;
                transform: scale(0.95);
            }
            
            /* Adjust for very small screens */
            @media (max-width: 420px) {
                #chat-button-container {
                    bottom: calc(165px + env(safe-area-inset-bottom, 0px));
                    right: 20px;
                }
            }
        }
    </style>

    <?php if (session()->get('role') === 'customer'): ?>
        <!-- Global Cart Button for Customers -->
        <div class="cart-float" id="global-cart-float" onclick="typeof openCheckoutModal === 'function' ? openCheckoutModal() : (window.location.href='<?= site_url('customer/dashboard?openCart=1') ?>')">
            <i class="fas fa-shopping-cart"></i>
            <div class="cart-badge" id="cartCount">0</div>
        </div>

        <style>
            .cart-float {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 55px;
                height: 55px;
                background: linear-gradient(135deg, #818cf8, #a855f7);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.4rem;
                color: #fff;
                box-shadow: 0 10px 25px rgba(168, 85, 247, 0.4);
                cursor: pointer;
                z-index: 100000;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                border: 2px solid rgba(255, 255, 255, 0.2);
            }

            .cart-float:hover {
                transform: scale(1.1);
                filter: brightness(1.1);
            }

            .cart-badge {
                position: absolute;
                top: -5px;
                right: -5px;
                background: #ef4444;
                color: #fff;
                min-width: 22px;
                height: 22px;
                padding: 0 6px;
                border-radius: 11px;
                font-size: 0.75rem;
                font-weight: 800;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px solid #140f2d;
                box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
            }

            @media (max-width: 1024px) {
                .cart-float {
                    bottom: calc(105px + env(safe-area-inset-bottom, 0px)) !important;
                    right: 25px !important;
                    transform: scale(0.95);
                }
            }
            
            @media (max-width: 420px) {
                .cart-float {
                    bottom: calc(95px + env(safe-area-inset-bottom, 0px)) !important;
                    right: 20px !important;
                }
            }
        </style>
    <?php endif; ?>

    <!-- Mj Assistant Integration -->
    <div id="chat-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 45; transition: opacity 0.3s ease;"></div>
    
    <div id="chat-button-container">
        <button id="chat-button" type="button" style="z-index: 10 !important;" aria-label="Open Chatbot">
            <div class="chat-button-pulse" style="background-color: #8b5cf6; position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 50%; opacity: 0.3; animation: pulse-ring 2s infinite; pointer-events: none !important; z-index: -1;"></div>
            <img src="<?= base_url('/images/logo.png') ?>" alt="MJ" style="width: 100%; height: 100%; object-fit: cover; transform: scale(1.35);">
        </button>
    </div>

    <div id="chat-container">
        <div id="chat-header">
            <div class="header-left">
                <div style="width: 40px; height: 40px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid rgba(255,255,255,0.3);">
                    <img src="<?= base_url('/images/logo.png') ?>" alt="MJ" style="width: 100%; height: 100%; object-fit: cover; transform: scale(1.35);">
                </div>
                <div class="ai-info">
                    <span class="ai-title">Mj AI Assistant</span>
                    <select id="model-select">
                        <option value="google/gemini-2.0-flash-001">Gemini 2.0 Flash (Fast)</option>
                        <option value="anthropic/claude-3-haiku">Claude 3 Haiku (Smart)</option>
                        <option value="openai/gpt-3.5-turbo">GPT-3.5 Turbo (Classic)</option>
                        <option value="openrouter/auto">Auto Select (Best)</option>
                    </select>
                </div>
            </div>
            <div id="header-controls">
                <?php if (session()->get('role') === 'admin'): ?>
                    <button id="clear-chat-history" class="header-btn trash-btn" title="Wipe Memory">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                <?php endif; ?>
                <button id="toggle-sound" class="header-btn" title="Toggle Sound">🔊</button>
                <button id="close-chat" class="header-btn close-btn" title="Close Chat">&times;</button>
            </div>
        </div>
        <div id="chat-messages">
            <!-- Messages will be loaded by chat-script.js -->
        </div>
        <div id="chat-input-area">
            <div class="input-wrapper">
                <input type="text" id="chat-input" placeholder="Type your message...">
                <button id="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.CHAT_API_BASE_URL = '<?= base_url() ?>';
    </script>
    <script src="<?= base_url('assets/js/mj-assistant.js') ?>"></script>
    <script>
        // Ensure Chatbot opens even if scripts are re-run by AJAX
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('#chat-button');
            if (btn && (typeof openChat === 'function' || typeof window.openChat === 'function')) {
                e.preventDefault();
                if (typeof openChat === 'function') openChat();
                else window.openChat();
            }
            
            const closeBtn = e.target.closest('#close-chat');
            if (closeBtn && (typeof closeChatFn === 'function' || typeof window.closeChatFn === 'function')) {
                e.preventDefault();
                if (typeof closeChatFn === 'function') closeChatFn();
                else window.closeChatFn();
            }
        });

        function switchTab(tabId) {
            // MAGIC FIX: If on Product view, redirect to dashboard with the tab ID!
            if (!document.getElementById('tab-' + tabId)) {
                window.location.href = "<?= site_url(session()->get('role').'/dashboard') ?>?tab=" + tabId;
                return;
            }

            document.querySelectorAll('.tab-section').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
            
            const targetTab = document.getElementById('tab-' + tabId);
            if(targetTab) targetTab.classList.add('active');
            
            const targetNav = document.getElementById('nav-' + tabId);
            if(targetNav) targetNav.classList.add('active');
            
            if(tabId === 'pos' && typeof loadProducts === 'function') loadProducts(); 
            if(tabId === 'sales' && typeof loadSalesHistory === 'function') loadSalesHistory();
        }

        window.onload = function() {
            // Load cart count from localStorage
            const savedCount = localStorage.getItem('cartCount');
            if (savedCount !== null) {
                const countEl = document.querySelectorAll('#cartCount');
                countEl.forEach(el => el.innerText = savedCount);
            }

            // Handle Alerts
            var alertBox = document.getElementById('system-alert');
            if(alertBox) {
                setTimeout(() => { 
                    alertBox.style.transition = 'all 0.5s ease';
                    alertBox.style.opacity = '0';
                    alertBox.style.transform = 'translateY(-10px)'; 
                    setTimeout(() => alertBox.remove(), 500); 
                }, 3000);
            }

            // Check if URL asks to load a specific tab (e.g. from Product View back to POS)
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) switchTab(tab);

            // Check if URL asks to open the cart
            if (urlParams.get('openCart') === '1') {
                setTimeout(() => {
                    if (typeof openCheckoutModal === 'function') openCheckoutModal();
                }, 500);
            }
        }
    </script>
</body>
</html>