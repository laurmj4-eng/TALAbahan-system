let activeSessionUID = localStorage.getItem('mj_user_uid');
if (!activeSessionUID) {
    activeSessionUID = 'user_' + Math.random().toString(36).substr(2, 9);
}

const chatButton = document.getElementById('chat-button');
const chatContainer = document.getElementById('chat-container');
const closeChat = document.getElementById('close-chat');
const chatInput = document.getElementById('chat-input');
const sendBtn = document.getElementById('send-btn');
const chatMessages = document.getElementById('chat-messages');
const modelSelect = document.getElementById('model-select');
const toggleSoundBtn = document.getElementById('toggle-sound');

let chatHistory = JSON.parse(localStorage.getItem('myChatbotHistory')) || [];
let isSoundEnabled = true;

// Force connection to Node.js AI Backend
const BASE_URL = window.location.hostname === 'mjtalabahan.page.gd' 
    ? 'http://mjtalabahan.page.gd' 
    : 'http://localhost:3000'; 

chatButton.addEventListener('click', () => {
    chatContainer.classList.add('active');
    setTimeout(() => chatInput.focus(), 300); 
});
closeChat.addEventListener('click', () => chatContainer.classList.remove('active'));

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
        messageDiv.innerHTML = DOMPurify.sanitize(marked.parse(text));
        messageDiv.querySelectorAll('pre code').forEach((block) => hljs.highlightElement(block));
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
    setTimeout(() => chatMessages.scrollTo({ top: chatMessages.scrollHeight, behavior: 'smooth' }), 50);
    return messageDiv; 
}

async function getBotResponse(userText) {
    const typingWrapperId = 'typing-' + Date.now();
    appendMessage('bot', '', typingWrapperId, null, true); 
    sendBtn.disabled = true; 
    
    chatHistory.push({ role: "user", content: userText, timestamp: getTimestamp() });

    try {
        const response = await fetch(`${BASE_URL}/api/chat`, {
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

document.getElementById('download-chat').addEventListener('click', () => {
    if(chatHistory.length === 0) return alert("No history!"); 
    let textToSave = "--- Chat History ---\n\n";
    chatHistory.forEach(msg => { textToSave += `[${msg.timestamp}] ${msg.role.toUpperCase()}: ${msg.content}\n\n`; });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([textToSave], { type: 'text/plain' }));
    a.download = 'Mj_Chat_History.txt'; a.click();
});

document.getElementById('clear-chat').addEventListener('click', () => {
    if(confirm("Clear conversation history?")) { chatHistory = []; saveHistory(); loadHistoryUI(); }
});

// START THE UI
loadHistoryUI();