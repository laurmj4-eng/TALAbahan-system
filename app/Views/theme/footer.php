<style>
        /* SENIOR FRONT-END: PROFESSIONAL FLOATING ACTIONS */
        #chat-button-container {
            position: fixed !important;
            bottom: 30px !important;
            right: 30px !important; /* Chatbot remains the primary far-right action */
            z-index: 2147483647 !important;
            width: 60px !important;
            height: 60px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        #chat-button {
            width: 60px !important;
            height: 60px !important;
            border-radius: 50% !important;
            border: 2px solid #8b5cf6 !important;
            background: #ffffff !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            overflow: hidden !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2) !important;
            transition: all 0.3s ease !important;
        }
        #chat-button:hover {
            transform: scale(1.1) translateY(-5px) !important;
            box-shadow: 0 12px 30px rgba(0,0,0,0.3) !important;
        }
        #chat-button img {
            width: 40px !important;
            height: 40px !important;
            object-fit: contain !important;
            pointer-events: none !important;
        }
        .chat-button-pulse {
            position: absolute !important;
            width: 100% !important;
            height: 100% !important;
            border-radius: 50% !important;
            background-color: #8b5cf6 !important;
            opacity: 0.2 !important;
            animation: pulse-ring 2s infinite !important;
            z-index: -1 !important;
            pointer-events: none !important;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.2; }
            50% { transform: scale(1.2); opacity: 0.1; }
            100% { transform: scale(0.95); opacity: 0.2; }
        }

        #chat-container {
            position: fixed !important;
            bottom: 100px !important;
            right: 30px !important;
            width: 380px !important;
            height: 600px !important;
            max-height: 80vh !important;
            max-width: 90vw !important;
            z-index: 2147483646 !important;
            background: white !important;
            border-radius: 16px !important;
            box-shadow: 0 12px 40px rgba(0,0,0,0.3) !important;
            display: none !important; /* Managed by .active class */
            flex-direction: column !important;
            overflow: hidden !important;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }
        #chat-container.active {
            display: flex !important;
            opacity: 1;
            transform: translateY(0);
        }
        #chat-header {
            background: linear-gradient(135deg, #6366f1, #a855f7) !important;
            color: white !important;
            padding: 15px !important;
            min-height: 70px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }
        #chat-messages {
            flex: 1 !important;
            overflow-y: auto !important;
            padding: 20px !important;
            background: #ffffff !important;
            display: flex !important;
            flex-direction: column !important;
        }
        .bot-msg {
            background: #f1f5f9 !important;
            color: #1e293b !important;
            padding: 12px !important;
            border-radius: 12px !important;
            max-width: 85% !important;
            font-size: 14px !important;
            margin-bottom: 10px !important;
        }
        @media (max-width: 768px) {
            #chat-button-container {
                bottom: 20px !important;
                right: 20px !important;
                width: 55px !important;
                height: 55px !important;
            }
            #chat-button {
                width: 55px !important;
                height: 55px !important;
            }
            #chat-button img {
                width: 35px !important;
                height: 35px !important;
            }
            #chat-container {
                bottom: 85px !important;
                right: 10px !important;
                width: calc(100vw - 20px) !important;
                height: 70vh !important;
            }
        }
    </style>

    <!-- Mj Assistant Integration -->
    <div id="chat-button-container">
        <button id="chat-button" type="button" style="z-index: 10 !important;">
            <div class="chat-button-pulse" style="background-color: #8b5cf6; position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 50%; opacity: 0.3; animation: pulse-ring 2s infinite; pointer-events: none !important; z-index: -1;"></div>
            <img src="<?= base_url('images/logo.png') ?>" alt="MJ">
        </button>
    </div>

    <div id="chat-container">
        <div id="chat-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 700; font-size: 14px;">Mj Sub-Routine AI</span>
                    <select id="model-select" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 4px; font-size: 11px; padding: 2px 5px;">
                        <option value="openrouter/free">Optimum Protocol</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button id="toggle-sound" style="background: none; border: none; color: white; cursor: pointer; font-size: 18px;">🔊</button>
                <button id="close-chat" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
            </div>
        </div>
        <div id="chat-messages">
            <!-- Messages will be loaded by chat-script.js -->
        </div>
        <div id="chat-input-area" style="padding: 15px; background: white; border-top: 1px solid #e2e8f0;">
            <div class="input-wrapper" style="display: flex; gap: 10px; background: #f1f5f9; padding: 8px 15px; border-radius: 25px; border: 1px solid #e2e8f0;">
                <input type="text" id="chat-input" placeholder="Initiate inquiry..." style="flex: 1; border: none; background: none; outline: none; font-size: 14px; color: #333 !important;">
                <button id="send-btn" style="background: #6366f1; border: none; color: white; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 18px;">&gt;</span>
                </button>
            </div>
        </div>
    </div>

    <script src="<?= base_url('js/chat-script.js') ?>?v=<?= time() ?>"></script>
    <script>
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