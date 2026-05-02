<!-- ========================= -->
    <!-- Mj Assistant Integration -->
    <!-- ========================= -->
<<<<<<< HEAD
    <style>
        /* CRITICAL LIVE SERVER OVERRIDES */
        #chat-button-container {
            position: fixed !important;
            bottom: 30px !important;
            right: 30px !important;
            z-index: 2147483647 !important;
            width: 60px !important;
            height: 60px !important;
        }
        #chat-button {
            background: white !important;
            width: 60px !important;
            height: 60px !important;
            border: 2px solid #8b5cf6 !important;
            border-radius: 50% !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            overflow: hidden !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
        }
        #chat-button img {
            width: 45px !important;
            height: 45px !important;
            object-fit: contain !important;
            pointer-events: none !important;
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
            display: none; /* JS will toggle this */
            flex-direction: column !important;
            overflow: hidden !important;
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
            background: #ffffff !important; /* Force white background for messages */
            display: flex !important;
            flex-direction: column !important;
            color: #333333 !important; /* Force dark text color */
        }
        .bot-msg-fallback {
            background: #f1f5f9 !important;
            color: #1e293b !important;
            padding: 12px !important;
            border-radius: 12px !important;
            max-width: 85% !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            margin-bottom: 10px !important;
            border: 1px solid #e2e8f0 !important;
        }
    </style>

    <div id="chat-button-container">
        <button id="chat-button" onclick="toggleChatWindow()" type="button" style="z-index: 10 !important;">
            <div class="chat-button-pulse" style="background-color: #8b5cf6; position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 50%; opacity: 0.3; animation: pulse 2s infinite; pointer-events: none !important; z-index: -1;"></div>
            <img src="<?= base_url('images/logo.png') ?>" alt="MJ" style="pointer-events: none !important;">
        </button>
=======
    <div class="chat-button-container" id="chat-button-container">
        <div class="chat-button-pulse" style="background-color: #8b5cf6;"></div>
        <button id="chat-button" aria-label="Open Chat with Mj" style="border: 2px solid #8b5cf6; background-image: url('<?= base_url('images/logo.png') ?>'); background-size: 70%; background-position: center; background-repeat: no-repeat;"></button>
>>>>>>> 3b91913bdf9b3177363692361a79da2abf36da4d
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
            <div style="display: flex; gap: 8px;">
                <button id="toggle-sound" style="background: none; border: none; color: white; cursor: pointer; opacity: 0.8;">🔊</button>
                <button id="close-chat" onclick="toggleChatWindow()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
        </div>
        <div id="chat-messages">
            <div class="bot-msg-fallback">Hello! I am Mj. How can I help you today? ✨</div>
        </div>
        <div id="chat-input-area" style="padding: 15px; background: white; border-top: 1px solid #e2e8f0;">
            <div class="input-wrapper" style="display: flex; gap: 10px; background: #f1f5f9; padding: 8px 15px; border-radius: 25px; border: 1px solid #e2e8f0;">
                <input type="text" id="chat-input" placeholder="Initiate inquiry..." style="flex: 1; border: none; background: none; outline: none; font-size: 14px; color: #333 !important;">
                <button id="send-btn" style="background: #6366f1; border: none; color: white; width: 30px; height: 30px; border-radius: 50%; cursor: pointer;">></button>
            </div>
        </div>
    </div>

<<<<<<< HEAD
    <script>
        // Use a more robust way to find the chat container
        function toggleChatWindow() {
            // alert('Chat button clicked!'); // Uncomment for emergency debugging
            const chat = document.querySelector('#chat-container');
            if (chat) {
                const isHidden = window.getComputedStyle(chat).display === 'none';
                chat.style.setProperty('display', isHidden ? 'flex' : 'none', 'important');
            }
        }

        // Initialize display state
        document.addEventListener('DOMContentLoaded', function() {
            const chat = document.querySelector('#chat-container');
            if (chat) chat.style.setProperty('display', 'none', 'important');
            
            // Ensure the button is clickable
            const btn = document.querySelector('#chat-button');
            if (btn) {
                btn.style.cursor = 'pointer';
                btn.onclick = function(e) {
                    e.preventDefault();
                    toggleChatWindow();
                };
            }
        });
    </script>

    <script src="<?= base_url('js/chat-script.js') ?>"></script>
=======
    <script src="<?= base_url('js/chat-script.js') ?>?v=<?= time() ?>"></script>
>>>>>>> 3b91913bdf9b3177363692361a79da2abf36da4d
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