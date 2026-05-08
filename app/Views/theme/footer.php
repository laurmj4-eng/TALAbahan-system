    <style>
        /* Floating Actions Positioning - Minimal Styles to avoid conflicts with chat-style.css */
        body.modal-open #chat-button-container,
        body.details-modal-open #chat-button-container {
            display: none !important;
        }
        
        #chat-button-container {
            position: fixed !important;
            bottom: 120px !important; 
            right: 30px !important;
            z-index: 2147483647 !important;
        }

        #chat-container {
            z-index: 2147483646 !important;
        }

        @media (max-width: 768px) {
            #chat-button-container {
                bottom: 165px !important; 
                right: 20px !important;
            }
            #chat-container {
                z-index: 2147483647 !important;
            }
        }
    </style>

    <!-- Mj Assistant Integration -->
    <div id="chat-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 2147483645; transition: opacity 0.3s ease;"></div>
    
    <div id="chat-button-container">
        <button id="chat-button" type="button" style="z-index: 10 !important;" aria-label="Open Chatbot">
            <div class="chat-button-pulse" style="background-color: #8b5cf6; position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 50%; opacity: 0.3; animation: pulse-ring 2s infinite; pointer-events: none !important; z-index: -1;"></div>
            <img src="<?= base_url('images/logo.png') ?>" alt="MJ" onerror="this.src='<?= base_url('favicon.ico') ?>'">
        </button>
    </div>

    <div id="chat-container">
        <div id="chat-header">
            <div class="header-left">
                <div style="width: 40px; height: 40px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid rgba(255,255,255,0.3);">
                    <img src="<?= base_url('images/logo.png') ?>" alt="MJ" style="width: 120%; height: 120%; object-fit: cover;">
                </div>
                <div class="ai-info">
                    <span class="ai-title">Mj AI Assistant</span>
                    <select id="model-select">
                        <option value="openrouter/free">Optimum Protocol (Stable)</option>
                    </select>
                </div>
            </div>
            <div id="header-controls">
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

    <script>
        window.CHAT_API_BASE_URL = 'https://talabahan-system.onrender.com';
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
        }
    </script>
</body>
</html>