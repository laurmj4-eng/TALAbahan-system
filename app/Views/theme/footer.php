<!-- ========================= -->
    <!-- Mj Assistant Integration -->
    <!-- ========================= -->
    <div class="chat-button-container" id="chat-button-container">
        <div class="chat-button-pulse" style="background-color: #8b5cf6;"></div>
        <button id="chat-button" aria-label="Open Chat with Mj" style="border: 2px solid #8b5cf6; background-image: url('<?= base_url('images/logo.png') ?>'); background-size: 70%; background-position: center; background-repeat: no-repeat;"></button>
    </div>

    <div id="chat-container">
        <div id="chat-header">
            <div class="header-left">
                <div class="ai-info">
                    <span class="ai-title">Mj Sub-Routine AI</span>
                    <select id="model-select">
                        <option value="openrouter/free" selected>Optimum Protocol</option>
                    </select>
                </div>
            </div>
            
            <div id="header-controls">
                <button id="download-chat" class="header-btn" title="Download Log">💾</button>
                <button id="toggle-sound" class="header-btn" title="Toggle Acoustics">🔊</button>
                <button id="clear-chat" class="header-btn" title="Flush Memory">🗑️</button>
                <button id="close-chat" class="header-btn close-btn" title="Close Panel">&times;</button>
            </div>
        </div>
        <div id="chat-messages" style="background: rgba(15, 23, 42, 0.98);"></div>
        <div id="chat-input-area" style="background: #1e293b; border-top: 1px solid rgba(255,255,255,0.05);">
            <div class="input-wrapper" style="background: #0f172a; border-color: rgba(255,255,255,0.1);">
                <input type="text" id="chat-input" placeholder="Initiate inquiry..." autocomplete="off" style="color: white;">
                <button id="send-btn" aria-label="Transmit">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
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