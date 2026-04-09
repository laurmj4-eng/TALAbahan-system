<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mj Chatbot - Admin Dashboard</title>
    

<link rel="stylesheet" href="/css/chat-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
</head>
<body>

    <header>
        <h1>Mj Pogi Portal - Admin Access</h1>
        <p>A smart system protected securely</p>
    </header>
    
    <main>
        <!-- Added PHP Session to display the Admin's Name! -->
        <h2>Welcome to the Admin Dashboard, <?= esc(session()->get('username')) ?>!</h2>
        <p>You have full Administrator privileges. Check out the pulsing bot on the bottom right!</p>
        
        <!-- Added a proper CodeIgniter logout link just in case -->
        <br>
        <a href="<?= base_url('logout') ?>" style="color: red; text-decoration: none; font-weight: bold;">[ Log Out ]</a>
    </main>

    <div class="chat-button-container" id="chat-button-container">
        <div class="chat-button-pulse"></div>
        <button id="chat-button" aria-label="Open Chat with Mj">
 
<img src="/images/logo.png" alt="Chat Bot">
        </button>
    </div>

    <div id="chat-container">
        <div id="chat-header">
            <div class="header-left">
                <div class="ai-info">
                    <span class="ai-title">Mj Assistant (Admin Mode)</span>
                    <select id="model-select">
                        <option value="openrouter/free" selected>Auto Free (Fastest)</option>
                        
                    </select>
                </div>
            </div>
            
            <div id="header-controls">
                <button id="download-chat" class="header-btn" title="Download Chat">💾</button>
                <button id="toggle-sound" class="header-btn" title="Toggle Sound">🔊</button>
                <button id="clear-chat" class="header-btn" title="Clear Chat History">🗑️</button>
                <button id="close-chat" class="header-btn close-btn" title="Close Chat">&times;</button>
                
                <!-- Changed to a proper link to trigger the CodeIgniter Logout -->
             
            </div>
        </div>
        
        <div id="chat-messages"></div>
        
        <div id="chat-input-area">
            <div class="input-wrapper">
                <input type="text" id="chat-input" placeholder="Ask Mj anything..." autocomplete="off">
                <button id="send-btn" aria-label="Send message">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        </div>
    </div>

   
<script src="/js/chat-script.js"></script>
</body>
</html>