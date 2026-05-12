<template>
  <div class="chatbot-wrapper">
    <!-- Backdrop for mobile -->
    <div 
      v-if="isOpen" 
      class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[1050] lg:hidden transition-opacity duration-300"
      @click="closeChat"
    ></div>

    <!-- Floating Button -->
    <div class="chat-button-container">
      <div v-if="!isOpen" class="chat-button-pulse"></div>
      <button 
        id="chat-button" 
        @click="toggleChat"
        class="group overflow-hidden"
      >
        <img :src="getLogoUrl()" alt="MJ Bot" class="w-full h-full object-cover scale-125 transition-transform duration-300 group-hover:scale-150" />
      </button>
    </div>

    <!-- Chat Container -->
    <div 
      id="chat-container" 
      :class="{ 'active': isOpen }"
      class="fixed z-[1100] bg-white flex flex-col overflow-hidden transition-all duration-300 ease-in-out border border-black/5 shadow-2xl
             bottom-0 right-0 w-full h-full max-h-full rounded-none 
             sm:bottom-[105px] sm:right-[30px] sm:w-[360px] sm:h-[540px] sm:max-h-[calc(100vh-125px)] sm:rounded-[2rem]"
    >
      <!-- Header -->
      <div class="bg-gradient-to-br from-indigo-600 to-violet-600 p-4 flex justify-between items-center shrink-0 min-h-[85px] border-b border-black/5">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 shrink-0 overflow-hidden">
            <img :src="getLogoUrl()" alt="MJ" class="w-full h-full object-cover scale-125" />
          </div>
          <div class="flex flex-col min-w-0">
            <span class="font-bold text-sm text-white truncate">MJ Assistant</span>
            <select 
              v-model="selectedModel" 
              class="bg-white/10 text-white border border-white/20 rounded px-2 py-0.5 text-[10px] outline-none hover:bg-white/20 transition-colors"
            >
              <option value="google/gemini-2.0-flash-001">Gemini 2.0 Flash</option>
              <option value="anthropic/claude-3-haiku">Claude 3 Haiku</option>
              <option value="openai/gpt-3.5-turbo">GPT-3.5 Turbo</option>
            </select>
          </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <button 
            @click="clearHistory" 
            class="w-8 h-8 rounded-full bg-rose-500/20 border border-rose-500/30 text-rose-200 flex items-center justify-center hover:bg-rose-500/40 transition-all"
            title="Clear History"
          >
            <Trash2 class="w-4 h-4" />
          </button>
          <button 
            @click="closeChat" 
            class="w-8 h-8 rounded-full bg-white/10 border border-white/20 text-white flex items-center justify-center hover:bg-white/20 transition-all"
          >
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Messages Area -->
      <div 
        ref="messageContainer"
        class="flex-1 p-4 overflow-y-auto bg-[#fafafa] flex flex-col gap-4 scroll-smooth"
      >
        <div 
          v-for="(msg, index) in messages" 
          :key="index"
          :class="[
            'flex flex-col max-w-[85%]',
            msg.role === 'user' ? 'self-end items-end' : 'self-start items-start'
          ]"
        >
          <div 
            :class="[
              'px-4 py-3 text-sm shadow-sm',
              msg.role === 'user' 
                ? 'bg-gradient-to-br from-indigo-600 to-violet-600 text-white rounded-[1.2rem_1.2rem_0.2rem_1.2rem]' 
                : 'bg-white text-gray-800 border border-gray-100 rounded-[1.2rem_1.2rem_1.2rem_0.2rem]'
            ]"
            v-html="renderMessage(msg.content)"
          ></div>
          <span class="text-[10px] text-gray-400 mt-1 px-1">{{ msg.timestamp }}</span>
        </div>
        
        <!-- Typing Indicator -->
        <div v-if="isTyping" class="self-start items-start flex flex-col max-w-[85%]">
          <div class="bg-white border border-gray-100 px-4 py-3 rounded-[1.2rem_1.2rem_1.2rem_0.2rem] shadow-sm">
            <div class="flex gap-1">
              <div class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce"></div>
              <div class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce [animation-delay:0.2s]"></div>
              <div class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce [animation-delay:0.4s]"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-4 bg-white border-t border-gray-100 shrink-0">
        <form @submit.prevent="sendMessage" class="flex gap-2 bg-gray-50 border border-gray-200 rounded-2xl p-1.5 pl-4 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/10 transition-all">
          <input 
            v-model="userInput"
            type="text"
            placeholder="Ask MJ anything..."
            class="flex-1 bg-transparent border-none outline-none text-sm text-gray-800"
            :disabled="isTyping"
            ref="inputField"
          />
          <button 
            type="submit"
            :disabled="!userInput.trim() || isTyping"
            class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white flex items-center justify-center hover:scale-105 active:scale-95 disabled:opacity-50 disabled:scale-100 transition-all"
          >
            <Send class="w-5 h-5" />
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { Send, X, Trash2 } from 'lucide-vue-next';
import axios from 'axios';

const isOpen = ref(false);
const isTyping = ref(false);
const userInput = ref('');
const selectedModel = ref('google/gemini-2.0-flash-001');
const messages = ref(JSON.parse(localStorage.getItem('myChatbotHistory')) || []);
const messageContainer = ref(null);
const inputField = ref(null);

const getTimestamp = () => new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

const getLogoUrl = () => {
  // Try window.CHAT_API_BASE_URL or window.BASE_URL, fallback to absolute path
  const base = window.CHAT_API_BASE_URL || window.BASE_URL || '';
  return base.replace(/\/$/, '') + '/images/logo.png';
};

const scrollToBottom = async () => {
  await nextTick();
  if (messageContainer.value) {
    messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
  }
};

const toggleChat = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    nextTick(() => inputField.value?.focus());
    scrollToBottom();
  }
};

const closeChat = () => {
  isOpen.value = false;
};

const clearHistory = () => {
  if (confirm('Clear chat history?')) {
    messages.value = [];
    localStorage.removeItem('myChatbotHistory');
    addBotMessage('History cleared. How can I help you today? ✨');
  }
};

const addBotMessage = (content) => {
  messages.value.push({
    role: 'assistant',
    content,
    timestamp: getTimestamp()
  });
  saveHistory();
  scrollToBottom();
};

const saveHistory = () => {
  localStorage.setItem('myChatbotHistory', JSON.stringify(messages.value));
};

const renderMessage = (content) => {
  if (typeof window.marked !== 'undefined' && typeof window.DOMPurify !== 'undefined') {
    return window.DOMPurify.sanitize(window.marked.parse(content));
  }
  return content.replace(/\n/g, '<br>');
};

const sendMessage = async () => {
  if (!userInput.value.trim() || isTyping.value) return;

  const text = userInput.value.trim();
  userInput.value = '';
  
  messages.value.push({
    role: 'user',
    content: text,
    timestamp: getTimestamp()
  });
  
  saveHistory();
  scrollToBottom();
  
  isTyping.value = true;
  
  try {
    const lowerText = text.toLowerCase();
    const devKeywords = ['developer', 'creator', 'who made', 'who develop', 'gumawa', 'sino ang gumawa'];
    
    if (devKeywords.some(keyword => lowerText.includes(keyword))) {
      setTimeout(() => {
        isTyping.value = false;
        addBotMessage("The developer or the creator of this is MJ the Pogi 😎🔥");
      }, 800);
      return;
    }

    // Use window.CHAT_API_BASE_URL if available, fallback to relative path
    const baseUrl = window.CHAT_API_BASE_URL || '';
    const endpoint = (baseUrl.replace(/\/$/, '')) + '/admin/chatbot/process';

    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        uid: localStorage.getItem('mj_user_uid') || 'guest',
        history: messages.value.map(m => ({ role: m.role, content: m.content })),
        modelName: selectedModel.value
      })
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error('Chat API Error:', errorText);
      throw new Error(`Server returned ${response.status}`);
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let fullContent = '';
    let buffer = '';
    
    isTyping.value = false;
    const botMsgIndex = messages.value.push({
      role: 'assistant',
      content: '',
      timestamp: getTimestamp()
    }) - 1;

    while (true) {
      const { done, value } = await reader.read();
      if (done) break;
      
      buffer += decoder.decode(value, { stream: true });
      const lines = buffer.split('\n');
      buffer = lines.pop();
      
      for (const line of lines) {
        const trimmedLine = line.trim();
        if (!trimmedLine || trimmedLine === 'data: [DONE]') continue;
        
        if (trimmedLine.startsWith('data: ')) {
          try {
            const data = JSON.parse(trimmedLine.substring(6));
            if (data.text) {
              fullContent += data.text;
              messages.value[botMsgIndex].content = fullContent;
              scrollToBottom();
            }
          } catch (e) {
            console.warn('Failed to parse stream chunk:', trimmedLine);
          }
        }
      }
    }
    saveHistory();

  } catch (error) {
    console.error('Chat error:', error);
    isTyping.value = false;
    addBotMessage('❌ Error: ' + (error.message || 'Connection failed. Please check your internet or API key.'));
  }
};

onMounted(() => {
  if (messages.value.length === 0) {
    addBotMessage('Hello! I am Mj. How can I help you today? ✨');
  }
  scrollToBottom();
});

watch(isOpen, (val) => {
  if (val) {
    document.body.style.overflow = window.innerWidth <= 768 ? 'hidden' : '';
  } else {
    document.body.style.overflow = '';
  }
});
</script>

<style scoped>
.chat-button-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (min-width: 640px) {
  .chat-button-container {
    bottom: 30px;
    right: 30px;
  }
}

#chat-button {
  position: relative;
  width: 56px;
  height: 56px;
  background: white;
  border-radius: 50%;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  border: 1px solid rgba(0,0,0,0.05);
}

@media (min-width: 640px) {
  #chat-button {
    width: 64px;
    height: 64px;
  }
}

#chat-button:hover {
  transform: scale(1.1) translateY(-5px);
  box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.3);
}

.chat-button-pulse {
  position: absolute;
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  border-radius: 50%;
  animation: pulseGlow 2.5s infinite;
  opacity: 0.6;
}

@media (min-width: 640px) {
  .chat-button-pulse {
    width: 64px;
    height: 64px;
  }
}

@keyframes pulseGlow {
  0% { transform: scale(1); opacity: 0.6; }
  70% { transform: scale(1.5); opacity: 0; }
  100% { transform: scale(1); opacity: 0; }
}

#chat-container {
  opacity: 0;
  pointer-events: none;
  transform: translateY(20px) scale(0.95);
  transform-origin: bottom right;
}

@media (min-width: 640px) {
  #chat-container {
    transform: translateY(40px) scale(0.95);
  }
}

#chat-container.active {
  opacity: 1;
  pointer-events: all;
  transform: translateY(0) scale(1);
}

/* Scrollbar styling */
::-webkit-scrollbar {
  width: 5px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.2);
}
</style>