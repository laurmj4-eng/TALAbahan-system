let activeSessionUID = localStorage.getItem('mj_user_uid');
if (!activeSessionUID) {
    activeSessionUID = `user_${Math.random().toString(36).slice(2, 11)}`;
    localStorage.setItem('mj_user_uid', activeSessionUID);
}

const chatButton = document.getElementById('chat-button');
const chatContainer = document.getElementById('chat-container');
const closeChat = document.getElementById('close-chat');
const chatInput = document.getElementById('chat-input');
const sendBtn = document.getElementById('send-btn');
const chatMessages = document.getElementById('chat-messages');
const modelSelect = document.getElementById('model-select');
const toggleSoundBtn = document.getElementById('toggle-sound');
const chatBackdrop = document.getElementById('chat-backdrop');

let chatHistory = JSON.parse(localStorage.getItem('myChatbotHistory')) || [];
let isSoundEnabled = true;

const inferBaseUrl = () => {
    if (window.CHAT_API_BASE_URL) return window.CHAT_API_BASE_URL;
    const host = window.location.hostname;
    if (host === 'mjtalabahan.page.gd' || host === 'mj-talabahan.infy.uk' || host.endsWith('.page.gd') || host.endsWith('.infy.uk')) {
        return `${window.location.protocol}//${host}`;
    }
    return 'http://localhost:3000';
};
const BASE_URL = inferBaseUrl();

// Global function declarations for visibility from theme/footer.php
window.openChat = null;
window.closeChatFn = null;

if (!chatButton || !chatContainer || !closeChat || !chatInput || !sendBtn || !chatMessages || !modelSelect || !toggleSoundBtn) {
    console.warn('Chat UI elements are missing from the page.');
} else {

window.openChat = () => {
    chatContainer.classList.add('active');
    if (chatBackdrop) {
        chatBackdrop.style.display = 'block';
        setTimeout(() => chatBackdrop.style.opacity = '1', 10);
    }
    
    // Mobile optimization: Slight delay for keyboard to prevent layout shift
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    setTimeout(() => {
        chatInput.focus();
        if (isMobile) {
            // Ensure input is visible on mobile when keyboard pops up
            chatInput.scrollIntoView({ behavior: 'smooth' });
        }
    }, 350);
};

window.closeChatFn = () => {
    chatContainer.classList.remove('active');
    if (chatBackdrop) {
        chatBackdrop.style.opacity = '0';
        setTimeout(() => chatBackdrop.style.display = 'none', 300);
    }
    document.body.style.overflow = ''; // Restore scrolling
};

// Use both click and pointerdown for faster response on mobile
if (chatButton) {
    chatButton.onclick = (e) => {
        e.preventDefault();
        window.openChat();
    };
    
    // For touch devices, pointerdown is faster than click
    chatButton.onpointerdown = (e) => {
        if (e.pointerType === 'touch') {
            window.openChat();
        }
    };
}

if (closeChat) {
    closeChat.onclick = (e) => {
        e.preventDefault();
        window.closeChatFn();
    };
}

if (chatBackdrop) {
    chatBackdrop.addEventListener('click', window.closeChatFn);
}

// Close chat on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && chatContainer.classList.contains('active')) {
        window.closeChatFn();
    }
});

toggleSoundBtn.addEventListener('click', () => {
    isSoundEnabled = !isSoundEnabled;
    toggleSoundBtn.style.opacity = isSoundEnabled ? '1' : '0.5';
});

function playPopSound() {
    if (!isSoundEnabled) return;
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(600, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(800, ctx.currentTime + 0.1);
        gain.gain.setValueAtTime(0.2, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);
        osc.start(ctx.currentTime); osc.stop(ctx.currentTime + 0.1);
    } catch(e) {}
}

function saveHistory() { localStorage.setItem('myChatbotHistory', JSON.stringify(chatHistory)); }
function getTimestamp() { return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); }

function loadHistoryUI() {
    chatMessages.innerHTML = ''; 
    if (chatHistory.length === 0) {
        setTimeout(() => appendMessage('bot', 'Hello! I am Mj. How can I help you today? ✨', null, getTimestamp()), 400);
    } else {
        chatHistory.forEach(msg => appendMessage(msg.role, msg.content, null, msg.timestamp || getTimestamp()));
    }
}

function appendMessage(sender, text, id = null, time = null, isTyping = false) {
    const wrapper = document.createElement('div');
    wrapper.classList.add('message-wrapper', sender === 'user' ? 'wrapper-user' : 'wrapper-bot');
    if (id) wrapper.id = id;
    
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('message', sender === 'user' ? 'user-message' : 'bot-message');

    if (isTyping) {
        messageDiv.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
    } else if (sender === 'bot') {
        // Fix for live rendering: Ensure marked and DOMPurify are handled safely
        try {
            const rawHtml = typeof marked !== 'undefined' ? marked.parse(text) : text;
            messageDiv.innerHTML = typeof DOMPurify !== 'undefined' ? DOMPurify.sanitize(rawHtml) : rawHtml;
        } catch (e) {
            console.error('Markdown rendering error:', e);
            messageDiv.textContent = text;
        }
        
        if (typeof hljs !== 'undefined') {
            messageDiv.querySelectorAll('pre code').forEach((block) => hljs.highlightElement(block));
        }
    } else {
        messageDiv.textContent = text;
    }

    wrapper.appendChild(messageDiv);
    
    if (!isTyping) {
        const timeDiv = document.createElement('div');
        timeDiv.classList.add('timestamp');
        timeDiv.textContent = time || getTimestamp();
        wrapper.appendChild(timeDiv);
    }

    chatMessages.appendChild(wrapper);
    // Smooth scroll to bottom
    chatMessages.scrollTo({ top: chatMessages.scrollHeight, behavior: 'smooth' });
    return messageDiv; 
}

async function getBotResponse(userText) {
    const typingWrapperId = 'typing-' + Date.now();
    appendMessage('bot', '', typingWrapperId, null, true); 
    sendBtn.disabled = true; 
    
    // --- LOCAL RESPONSE INTERCEPTION ---
    const lowerText = userText.toLowerCase();
    const devKeywords = ['developer', 'creator', 'who made', 'who develop', 'gumawa', 'sino ang gumawa'];
    
    if (devKeywords.some(keyword => lowerText.includes(keyword))) {
        // Natural 500ms delay for the 'Typing...' animation
        setTimeout(() => {
            document.getElementById(typingWrapperId)?.remove();
            const localReply = "The developer or the creator of this is MJ the Pogi 😎🔥";
            appendMessage('bot', localReply, null, getTimestamp());
            chatHistory.push({ role: "user", content: userText, timestamp: getTimestamp() });
            chatHistory.push({ role: "assistant", content: localReply, timestamp: getTimestamp() });
            saveHistory();
            sendBtn.disabled = false;
            chatInput.focus();
            playPopSound();
        }, 500);
        return;
    }

    chatHistory.push({ role: "user", content: userText, timestamp: getTimestamp() });

    try {
        const response = await fetch(`${BASE_URL}/admin/chatbot/process`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                uid: activeSessionUID, 
                history: chatHistory.map(h => ({ role: h.role, content: h.content })),
                modelName: modelSelect.value 
            })
        });

        document.getElementById(typingWrapperId).remove();
        
        if (!response.ok) throw new Error(response.status);

        const botMessageDiv = appendMessage('bot', '', null, getTimestamp());
        const timestampDiv = botMessageDiv.parentElement.querySelector('.timestamp'); 

        const reader = response.body.getReader();
        const decoder = new TextDecoder("utf-8");
        let fullBotReply = "";
        let buffer = ""; 
        
        if(timestampDiv) timestampDiv.style.opacity = "0";

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            
            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n'); 
            buffer = lines.pop(); 
            
            for (let line of lines) {
                if (line.trim() === 'data: [DONE]') continue;
                if (line.startsWith('data: ')) {
                    const dataStr = line.substring(6); 
                    try {
                        const parsed = JSON.parse(dataStr);
                        if (parsed.text) {
                            fullBotReply += parsed.text;
                            botMessageDiv.innerHTML = DOMPurify.sanitize(marked.parse(fullBotReply));
                            botMessageDiv.querySelectorAll('pre code').forEach(b => hljs.highlightElement(b));
                            chatMessages.scrollTo({ top: chatMessages.scrollHeight });
                        }
                    } catch (e) { } 
                }
            }
        }
        
        if(timestampDiv) timestampDiv.style.opacity = "1";
        playPopSound();
        chatHistory.push({ role: "assistant", content: fullBotReply, timestamp: getTimestamp() });
        saveHistory();

    } catch (error) {
        chatHistory.pop(); 
        document.getElementById(typingWrapperId)?.remove();
        
        let errMsg = "An error occurred.";
        if (error.message === 'Failed to fetch') {
            errMsg = "❌ Cannot connect to the AI Backend! Did you remember to run `npm run dev`?";
        } else if (error.message.includes('429')) {
            errMsg = "Woah, too fast! You hit the rate limit. Please wait a minute. 😅";
        } else {
            errMsg = `Connection error: ${error.message}`;
        }
        
        appendMessage('bot', errMsg, null, getTimestamp());
    } finally {
        sendBtn.disabled = false;
        chatInput.focus();
    }
}

function handleSend() {
    const text = chatInput.value.trim();
    if (text !== '') {
        appendMessage('user', text, null, getTimestamp()); 
        chatInput.value = '';        
        getBotResponse(text);        
    }
}

sendBtn.addEventListener('click', handleSend);
chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') handleSend(); });

const downloadChatBtn = document.getElementById('download-chat');
if (downloadChatBtn) {
    downloadChatBtn.addEventListener('click', () => {
        if (chatHistory.length === 0) return alert("No history!");
        let textToSave = "--- Chat History ---\n\n";
        chatHistory.forEach(msg => { textToSave += `[${msg.timestamp}] ${msg.role.toUpperCase()}: ${msg.content}\n\n`; });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([textToSave], { type: 'text/plain' }));
        a.download = 'Mj_Chat_History.txt';
        a.click();
    });
}

const clearChatBtn = document.getElementById('clear-chat');
if (clearChatBtn) {
    clearChatBtn.addEventListener('click', () => {
        if (confirm("Clear conversation history?")) {
            chatHistory = [];
            saveHistory();
            loadHistoryUI();
        }
    });
}

// --- DELETE HISTORY SYSTEM (ADMIN ONLY) ---
const clearHistoryBtn = document.getElementById('clear-chat-history');
if (clearHistoryBtn) {
    clearHistoryBtn.addEventListener('click', async () => {
        const result = await Swal.fire({
            title: 'Wipe Memory?',
            text: 'MJ, this will permanently delete your chat logs from the system.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Wipe it!',
            background: '#1e1b4b',
            color: '#ffffff',
            backdrop: `rgba(0,0,123,0.4)`
        });

        if (result.isConfirmed) {
            // 1. Fade out effect
            const bubbles = chatMessages.querySelectorAll('.message-wrapper');
            bubbles.forEach((bubble, index) => {
                setTimeout(() => {
                    bubble.classList.add('fade-out');
                }, index * 50); // Staggered fade out
            });

            // 2. Wait for animation to finish then clear UI
            setTimeout(async () => {
                chatHistory = [];
                saveHistory();
                loadHistoryUI();

                // 3. Backend call to log activity
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfName = document.querySelector('meta[name="csrf-name"]').getAttribute('content');
                    
                    const formData = new FormData();
                    formData.append(csrfName, csrfToken);

                    const response = await fetch(`${BASE_URL}/admin/chatbot/deleteHistory`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Wiped!',
                            text: 'Memory has been cleared and logged.',
                            icon: 'success',
                            background: '#1e1b4b',
                            color: '#ffffff',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    console.error('Error clearing history:', error);
                }
            }, bubbles.length > 0 ? 600 : 0);
        }
    });
}

// START THE UI
loadHistoryUI();
}