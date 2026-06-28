<template>
  <div class="chatbot-wrapper">
    <!-- Backdrop for mobile -->
    <div 
      v-if="isOpen" 
      class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[1050] transition-opacity duration-300"
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
             sm:bottom-[100px] sm:right-[30px] sm:w-[400px] sm:h-[650px] sm:max-h-[min(750px,calc(100vh-250px))] sm:rounded-[2.5rem]
             lg:bottom-0 lg:right-0 lg:w-1/2 lg:h-full lg:max-h-full lg:rounded-none"
    >
      <!-- Header -->
      <div class="bg-gradient-to-br from-indigo-600 to-violet-600 p-4 flex justify-between items-center shrink-0 min-h-[85px] border-b border-black/5">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 shrink-0 overflow-hidden">
            <img :src="getLogoUrl()" alt="MJ" class="w-full h-full object-cover scale-125" />
          </div>
          <div class="flex flex-col min-w-0">
            <span class="font-bold text-sm lg:text-base text-white truncate">{{ chatbotTitle }}</span>
            <select
              v-model="selectedModel"
              :disabled="allModelsExhausted"
              :title="allModelsExhausted ? 'All models exhausted — come back tomorrow' : 'Choose AI model'"
              class="bg-white/10 text-white border border-white/20 rounded px-2 py-0.5 text-[10px] outline-none hover:bg-white/20 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <option value="gemini" :disabled="isModelExhausted('gemini')">Gemini 2.5 Flash</option>
              <option value="gemma" :disabled="isModelExhausted('gemma')">Gemma 4 31B</option>
              <option value="gpt" :disabled="isModelExhausted('gpt')">GPT-OSS 120B</option>
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
        class="flex-1 p-4 lg:p-6 overflow-y-auto bg-[#fafafa] flex flex-col gap-4 lg:gap-5 scroll-smooth"
      >
        <!-- Limit Info (Customer only) -->
        <div v-if="role === 'customer'" class="sticky top-0 z-10 -mx-4 -mt-4 mb-4 border-b"
          :class="selectedModelQuota.remaining <= 3 ? 'bg-red-50 border-red-200' : 'bg-cyan-50 border-cyan-100'">
          <div class="px-4 py-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-1.5 h-1.5 rounded-full animate-pulse"
                :class="selectedModelQuota.remaining <= 3 ? 'bg-red-500' : 'bg-cyan-500'"></div>
              <span class="text-[10px] font-bold uppercase tracking-wider"
                :class="selectedModelQuota.remaining <= 3 ? 'text-red-700' : 'text-cyan-700'">Daily Limit</span>
            </div>
            <div class="flex items-center gap-1.5">
              <!-- Selected model pill -->
              <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full border"
                :class="selectedModelQuota.remaining <= 3
                  ? 'bg-red-100 text-red-700 border-red-200'
                  : 'bg-blue-100 text-blue-700 border-blue-200'">
                {{ selectedModelLabel }} {{ selectedModelQuota.remaining }}/{{ quota.limit_per_model || 25 }}
              </span>
              <!-- Overall pill -->
              <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                Overall {{ quota.overall_used || 0 }}/{{ quota.overall_limit || 75 }}
              </span>
            </div>
          </div>
          <div class="px-4 pb-2 flex items-center justify-between">
            <span class="text-[9px] text-gray-400">Resets in {{ resetCountdown }}</span>
            <span class="text-[9px] text-gray-400">{{ quota.overall_used || 0 }}/{{ quota.overall_limit || 75 }} used today</span>
          </div>
        </div>

        <!-- Admin/Staff: show unlimited banner -->
        <div v-if="role !== 'customer'" class="sticky top-0 z-10 -mx-4 -mt-4 mb-4 border-b bg-green-50 border-green-100">
          <div class="px-4 py-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-green-700">Unlimited</span>
            </div>
            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200">
              Admin / Staff Access
            </span>
          </div>
        </div>

        <div 
          v-for="(msg, index) in messages" 
          :key="index"
          :class="[
            'flex flex-col max-w-[85%] lg:max-w-[75%]',
            msg.role === 'user' ? 'self-end items-end' : 'self-start items-start'
          ]"
        >
          <div 
            :class="[
              'px-4 py-3 lg:px-5 lg:py-3.5 shadow-sm',
              'text-sm lg:text-base',
              msg.role === 'user' 
                ? (role === 'admin' ? 'bg-gradient-to-br from-indigo-600 to-violet-600 text-white rounded-[1.2rem_1.2rem_0.2rem_1.2rem]' : 'bg-gradient-to-br from-cyan-600 to-blue-600 text-white rounded-[1.2rem_1.2rem_0.2rem_1.2rem]')
                : 'bg-white text-gray-800 border border-gray-100 rounded-[1.2rem_1.2rem_1.2rem_0.2rem]'
            ]"
            v-html="renderMessage(msg.content)"
          ></div>
          <span class="text-[10px] lg:text-xs text-gray-400 mt-1 px-1">{{ msg.timestamp }}</span>
        </div>
        
        <!-- Typing Indicator -->
        <div v-if="isTyping" class="self-start items-start flex flex-col max-w-[85%]">
          <div class="bg-white border border-gray-100 px-4 py-3 rounded-[1.2rem_1.2rem_1.2rem_0.2rem] shadow-sm">
            <div class="flex gap-1">
              <div :class="['w-1.5 h-1.5 rounded-full animate-bounce', role === 'admin' ? 'bg-indigo-400' : 'bg-cyan-400']"></div>
              <div :class="['w-1.5 h-1.5 rounded-full animate-bounce [animation-delay:0.2s]', role === 'admin' ? 'bg-indigo-400' : 'bg-cyan-400']"></div>
              <div :class="['w-1.5 h-1.5 rounded-full animate-bounce [animation-delay:0.4s]', role === 'admin' ? 'bg-indigo-400' : 'bg-cyan-400']"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-4 lg:p-5 bg-white border-t border-gray-100 shrink-0">
        <form @submit.prevent="sendMessage" class="flex gap-2 bg-gray-50 border border-gray-200 rounded-2xl p-1.5 pl-4 lg:pl-5 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/10 transition-all">
          <input 
            v-model="userInput"
            type="text"
            :placeholder="inputPlaceholder"
            class="flex-1 bg-transparent border-none outline-none text-sm lg:text-base text-gray-800"
            :disabled="isTyping"
            ref="inputField"
          />
          <button 
            type="submit"
            :disabled="!userInput.trim() || isTyping || isCoolingDown"
            :class="[
              'w-10 h-10 lg:w-11 lg:h-11 rounded-xl text-white flex items-center justify-center hover:scale-105 active:scale-95 disabled:opacity-50 disabled:scale-100 transition-all',
              role === 'admin' ? 'bg-gradient-to-br from-indigo-600 to-violet-600' : 'bg-gradient-to-br from-cyan-600 to-blue-600'
            ]"
          >
            <Send class="w-5 h-5" />
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue';
import { Send, X, Trash2 } from 'lucide-vue-next';
import axios from 'axios';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

// Configure marked once: GitHub-flavored line breaks, no mangle.
marked.setOptions({
  breaks: true,
  gfm: true,
});

// Sandbox DOMPurify: keep the chat output tight (no images/links from model).
const PURIFY_CONFIG = {
  ALLOWED_TAGS: [
    'br', 'p', 'span', 'b', 'strong', 'i', 'em',
    'code', 'pre', 'ul', 'ol', 'li', 'blockquote', 'hr', 'a',
  ],
  ALLOWED_ATTR: ['href', 'target', 'rel'],
};

DOMPurify.addHook('afterSanitizeAttributes', (node) => {
  if (node.tagName === 'A') {
    node.setAttribute('target', '_blank');
    node.setAttribute('rel', 'noopener noreferrer nofollow');
  }
});

const props = defineProps({
  role: {
    type: String,
    default: 'admin'
  }
});

const isOpen = ref(false);
const isTyping = ref(false);
const isCoolingDown = ref(false);
const userInput = ref('');
const selectedModel = ref('gemini');

// Per-model quota state from the backend. Shape:
// { gemini_remaining, gemma_remaining, gpt_remaining, limit_per_model,
//   overall_used, overall_limit, models: { gemini: {remaining,used,exhausted}, ... } }
const quota = ref({
  gemini_remaining: 25, gemma_remaining: 25, gpt_remaining: 25,
  limit_per_model: 25, overall_used: 0, overall_limit: 75,
  models: {
    gemini: { remaining: 25, used: 0, exhausted: false },
    gemma:  { remaining: 25, used: 0, exhausted: false },
    gpt:    { remaining: 25, used: 0, exhausted: false },
  },
});

// Client-side-only flag: set when backend reports Gemini API 429 (failover).
// Not persisted — refresh clears it.
const geminiDisabled = ref(false);

// Quota for the currently selected model.
const selectedModelQuota = computed(() => {
  return quota.value.models[selectedModel.value] || { remaining: 25, used: 0, exhausted: false };
});

// Human-readable label for the selected model.
const selectedModelLabel = computed(() => {
  const labels = { gemini: 'Gemini', gemma: 'Gemma', gpt: 'GPT' };
  return labels[selectedModel.value] || 'Gemini';
});

// Check if a specific model is exhausted (0 remaining).
const isModelExhausted = (modelKey) => {
  return geminiDisabled.value && modelKey === 'gemini'
    || (quota.value.models[modelKey]?.exhausted === true);
};

// True only when ALL three models are exhausted.
const allModelsExhausted = computed(() => {
  return ['gemini', 'gemma', 'gpt'].every(k => isModelExhausted(k));
});

const historyKey = computed(() => `mj_chat_history_${props.role}`);
const messages = ref([]);

const chatbotTitle = computed(() => props.role === 'admin' ? 'MJ Assistant' : 'MJ Talabahan Assistant');

const resetCountdown = ref('');
const updateCountdown = () => {
  const now = new Date();
  const midnight = new Date(now);
  midnight.setHours(24, 0, 0, 0);
  const diff = midnight - now;
  const h = Math.floor(diff / 3600000);
  const m = Math.floor((diff % 3600000) / 60000);
  const s = Math.floor((diff % 60000) / 1000);
  resetCountdown.value = `${h}h ${m}m ${s}s`;
};
updateCountdown();
setInterval(updateCountdown, 1000);
const inputPlaceholder = computed(() => props.role === 'admin' ? 'Ask MJ anything...' : 'Ask about our seafood...');

const messageContainer = ref(null);
const inputField = ref(null);

const getTimestamp = () => new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

const fetchQuota = async () => {
  try {
    const base = window.CHAT_API_BASE_URL || window.BASE_URL || '';
    const res = await fetch((base.replace(/\/$/, '')) + '/chatbot/quota');
    if (res.ok) {
      quota.value = await res.json();
    }
  } catch (e) {
    console.warn('Failed to fetch quota:', e);
  }
};

// --- PERF FIX: RAF-debounced scroll ---
// scrollToBottom() is called on every streamed token. Without debouncing this
// triggers dozens of synchronous layout reads per second (forced reflow).
// This RAF version coalesces all scroll requests into at most one per animation frame.
let _scrollRafId = null;
const scheduleScrollToBottom = () => {
  if (_scrollRafId) cancelAnimationFrame(_scrollRafId);
  _scrollRafId = requestAnimationFrame(() => {
    _scrollRafId = null;
    if (messageContainer.value) {
      messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
    }
  });
};
// Full await-version for non-streaming cases (opening chat, adding a message)
const scrollToBottom = async () => {
  await nextTick();
  scheduleScrollToBottom();
};
// ----------------------------------------

const getLogoUrl = () => {
  // Try window.CHAT_API_BASE_URL or window.BASE_URL, fallback to absolute path
  const base = window.CHAT_API_BASE_URL || window.BASE_URL || '';
  return base.replace(/\/$/, '') + '/images/logo.png';
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
    localStorage.removeItem(historyKey.value);
    addBotMessage(props.role === 'admin' 
      ? 'History cleared. How can I help you today? ✨' 
      : 'History cleared! How can I help you find the best seafood today? 🌊');
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
  // Only save non-empty messages
  const toSave = messages.value.filter(m => m.content && m.content.trim() !== '');
  localStorage.setItem(historyKey.value, JSON.stringify(toSave));
};

const renderMessage = (content) => {
  if (!content) return '';
  try {
    // Parse markdown -> HTML, then sanitize before v-html injection.
    const rawHtml = marked.parse(String(content));
    return DOMPurify.sanitize(rawHtml, PURIFY_CONFIG);
  } catch (e) {
    // If parsing fails, fall back to escaped text rather than raw HTML.
    return String(content).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
  }
};

const sendMessage = async () => {
  if (!userInput.value.trim() || isTyping.value) return;

  const text = userInput.value.trim();
  if (text.length > 2000) {
    addBotMessage('Message too long. Please keep messages under 2000 characters.');
    return;
  }
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

    // --- Auto-switch: if selected model is exhausted, pick the first available ---
    if (props.role === 'customer' && isModelExhausted(selectedModel.value)) {
      const fallback = ['gemini', 'gemma', 'gpt'].find(k => !isModelExhausted(k));
      if (fallback && fallback !== selectedModel.value) {
        selectedModel.value = fallback;
      }
    }

    // Use window.CHAT_API_BASE_URL if available, fallback to relative path
    const baseUrl = window.CHAT_API_BASE_URL || '';
    const endpoint = (baseUrl.replace(/\/$/, '')) + '/admin/chatbot/process';

    // Send prior turns as history and the current message separately. The
    // backend validates the new message length independently of history
    // (which may contain long AI replies). Exclude the just-pushed user turn
    // from history so it isn't validated/sent twice.
    const historyPayload = messages.value
      .slice(-31, -1) // drop the last entry (the current user message)
      .map(m => ({ role: m.role, content: m.content }));

    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        message: text,
        history: historyPayload,
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

            // Handle metadata events from the backend (e.g. failover signals).
            if (data.metadata) {
              if (data.disable_gemini) {
                geminiDisabled.value = true;
                // Auto-switch away from the now-disabled Gemini option.
                if (selectedModel.value === 'gemini') {
                  selectedModel.value = 'gemma';
                }
              }
              continue;
            }

            if (data.text) {
              fullContent += data.text;
              messages.value[botMsgIndex].content = fullContent;
              scheduleScrollToBottom(); // PERF FIX: RAF-debounced, not raw scrollTop
            }
          } catch (e) {
            console.warn('Failed to parse stream chunk:', trimmedLine);
          }
        }
      }
    }
    saveHistory();

    // If stream completed with no content, show an error instead of a blank bubble
    if (!fullContent.trim()) {
      messages.value[botMsgIndex].content = 'No response received. Please try again or switch models.';
    }

    fetchQuota();

    isCoolingDown.value = true;
    setTimeout(() => { isCoolingDown.value = false; }, 3000);

  } catch (error) {
    console.error('Chat error:', error);
    isTyping.value = false;
    addBotMessage('❌ Error: ' + (error.message || 'Connection failed. Please check your internet or API key.'));
  }
};

// Load history and filter out any empty messages
onMounted(() => {
  const saved = localStorage.getItem(historyKey.value);
  if (saved) {
    try {
      const parsed = JSON.parse(saved);
      messages.value = parsed.filter(m => m.content && m.content.trim() !== '');
    } catch (e) {
      console.error('Failed to parse history:', e);
      messages.value = [];
    }
  }

  if (messages.value.length === 0) {
    const greeting = props.role === 'admin' 
      ? 'Hello! I am Mj. How can I help you today? ✨' 
      : 'Welcome to MJ Talabahan! 🌊 I am your seafood assistant. How can I help you find the freshest catch today? 🦀';
    
    messages.value.push({
      role: 'assistant',
      content: greeting,
      timestamp: getTimestamp()
    });
  }
  scrollToBottom();
  fetchQuota();
});

watch(isOpen, (val) => {
  document.body.style.overflow = val ? 'hidden' : '';
});
</script>

<style scoped>
.chat-button-container {
  position: fixed;
  bottom: 110px; /* Above mobile bottom nav */
  right: 25px;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (min-width: 1024px) {
  .chat-button-container {
    bottom: 140px; /* Above desktop floating cart */
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

@media (min-width: 1024px) {
  #chat-container {
    bottom: 0;
    right: 0;
    width: 50%;
    height: 100%;
    transform: translateY(0) scale(1);
    border-radius: 0;
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