<template>
  <div class="modern-navy-gradient min-h-screen flex flex-col lg:flex-row font-['Poppins',sans-serif] text-white overflow-hidden relative">
    
    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex flex-col w-[200px] bg-black/10 backdrop-blur-3xl border-r border-white/5 z-50 overflow-hidden relative sidebar-container transition-all duration-300">
      <!-- Sidebar Glow -->
      <div class="absolute -top-24 -left-24 w-48 h-48 bg-indigo-500/10 blur-[100px] pointer-events-none"></div>

      <!-- Header -->
      <div class="p-8 px-4 text-center animate-slide-in-top">
        <div class="flex items-center justify-center gap-2 mb-1">
          <Gem class="text-cyan-400 w-5 h-5 drop-shadow-[0_0_8px_rgba(34,211,238,0.5)]" />
          <h2 class="text-lg font-black tracking-tight m-0">TALAbahan</h2>
        </div>
        <small class="text-cyan-400/80 font-black tracking-[0.3em] text-[0.55rem] uppercase">Customer</small>
      </div>

      <!-- Nav -->
      <nav class="flex-1 px-3 py-6 space-y-2 overflow-y-auto">
        <div class="px-4 pb-4 text-[0.55rem] font-black tracking-[0.2em] text-white/20 uppercase animate-fade-in">
          Main Menu
        </div>
        <ul class="space-y-1 list-none p-0">
          <li v-for="(item, index) in navItems" :key="item.path" :style="{ animationDelay: `${index * 100}ms` }" class="animate-slide-in-left">
            <Link
              :href="item.path"
              class="flex items-center gap-3 px-4 py-3 text-white/40 font-bold text-[0.8rem] rounded-xl transition-all duration-300 group relative hover:text-white hover:bg-white/5"
              :class="{ 'active-nav-item': $page.url.startsWith(item.path) }"
            >
              <component :is="item.icon" class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" />
              <span>{{ item.name }}</span>
              <div v-if="$page.url.startsWith(item.path)" class="absolute right-3 w-1 h-1 rounded-full bg-cyan-400 shadow-[0_0_10px_#22d3ee]"></div>
            </Link>
          </li>
        </ul>
      </nav>

      <!-- Footer -->
      <div class="p-4 mt-auto space-y-3 animate-slide-in-bottom">
        <div class="p-3 bg-white/5 border border-white/10 rounded-xl flex items-center gap-2 backdrop-blur-md">
          <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center text-cyan-400 border border-cyan-500/30">
            <User class="w-4 h-4" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-[0.65rem] font-black truncate">{{ username }}</div>
            <div class="text-[0.5rem] text-cyan-400/80 font-black uppercase tracking-widest">Premium</div>
          </div>
        </div>
        
        <button 
          @click="handleLogout" 
          class="w-full flex items-center justify-center gap-2 p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl font-black text-[0.75rem] hover:bg-rose-500/20 hover:text-rose-300 transition-all duration-300 active:scale-95 group"
        >
          <LogOut class="w-3.5 h-3.5 transition-transform duration-300 group-hover:-translate-x-1" />
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Mobile Bottom Navigation -->
    <nav class="lg:hidden fixed bottom-[calc(20px+env(safe-area-inset-bottom,0px))] left-6 right-6 h-20 bg-[#140f2d]/90 backdrop-blur-3xl border border-white/10 rounded-[2.5rem] z-[100] flex items-center justify-around px-2 shadow-[0_20px_50px_rgba(0,0,0,0.5)]">
      <Link 
        v-for="item in navItems" 
        :key="item.path" 
        :href="item.path"
        class="flex flex-col items-center justify-center gap-1.5 flex-1 h-full rounded-2xl transition-all duration-300"
        :class="{ 'text-violet-400 bg-violet-500/10': $page.url.startsWith(item.path) }"
      >
        <component :is="item.icon" class="w-6 h-6" />
        <span class="text-[0.65rem] font-black uppercase tracking-widest">{{ item.name }}</span>
      </Link>
      
      <!-- Global Cart Trigger -->
      <button @click="triggerOpenCart" class="flex flex-col items-center justify-center gap-1.5 flex-1 h-full rounded-2xl relative">
        <div class="relative">
          <ShoppingBag class="w-6 h-6" />
          <div v-if="cartCount > 0" class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white w-4 h-4 rounded-full text-[0.6rem] font-black flex items-center justify-center border-2 border-[#140f2d]">
            {{ cartCount }}
          </div>
        </div>
        <span class="text-[0.65rem] font-black uppercase tracking-widest">Cart</span>
      </button>

      <Link 
        href="/customer/profile"
        class="flex flex-col items-center justify-center gap-1.5 flex-1 h-full rounded-2xl transition-all duration-300"
        :class="{ 'text-violet-400 bg-violet-500/10': $page.url.startsWith('/customer/profile') }"
      >
        <User class="w-6 h-6" />
        <span class="text-[0.65rem] font-black uppercase tracking-widest">Profile</span>
      </Link>
    </nav>

    <!-- Global Floating Cart Button (Hidden on mobile if nav has cart) -->
    <button 
      @click="triggerOpenCart"
      class="hidden lg:flex fixed bottom-12 right-12 w-20 h-20 bg-violet-600 rounded-[2rem] items-center justify-center text-white cursor-pointer shadow-[0_25px_50px_-10px_rgba(139,92,246,0.5)] border border-white/20 transition-all duration-500 hover:scale-110 hover:-translate-y-2 active:scale-95 z-[100] group"
    >
      <ShoppingCart class="w-8 h-8 group-hover:scale-110 transition-transform" />
      <div v-if="cartCount > 0" class="absolute -top-2 -right-2 bg-rose-500 text-white w-8 h-8 rounded-2xl text-[0.8rem] font-black flex items-center justify-center border-4 border-[#0c0616] animate-pulse">
        {{ cartCount }}
      </div>
    </button>

    <!-- Mobile Floating Chatbot Spacer -->
    <div class="lg:hidden h-10 w-full"></div>

    <!-- Main Content Area -->
    <main class="flex-1 relative lg:h-screen lg:overflow-y-auto p-4 md:p-8 lg:p-12 pb-32 lg:pb-12 main-content-glass smooth-scroll-container">
      <!-- Background Ambient Glows -->
      <div class="fixed top-[-10%] right-[-10%] w-[40%] h-[40%] bg-violet-600/5 blur-[120px] pointer-events-none"></div>
      <div class="fixed bottom-[-10%] left-[-10%] w-[30%] h-[30%] bg-indigo-600/5 blur-[120px] pointer-events-none"></div>
      
      <div class="relative z-10">
        <slot></slot>
      </div>
    </main>
    <Chatbot />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { 
  LayoutDashboard, 
  ShoppingBag, 
  LogOut,
  Gem,
  User,
  Search,
  ShoppingCart
} from 'lucide-vue-next';
import Chatbot from '../components/Chatbot.vue';

const username = ref(localStorage.getItem('username') || 'Bocana Ilog');
const cartCount = ref(parseInt(localStorage.getItem('cartCount') || '0'));

const navItems = [
  { name: 'Shop', path: '/customer/dashboard', icon: LayoutDashboard },
  { name: 'Orders', path: '/customer/orders', icon: ShoppingBag }
];

const triggerOpenCart = () => {
  // Emit a global event or use a custom event listener
  window.dispatchEvent(new CustomEvent('open-customer-cart'));
};

const updateCartCount = () => {
  cartCount.value = parseInt(localStorage.getItem('cartCount') || '0');
};

onMounted(() => {
  window.addEventListener('cart-updated', updateCartCount);
  window.addEventListener('storage', updateCartCount);
});

onUnmounted(() => {
  window.removeEventListener('cart-updated', updateCartCount);
  window.removeEventListener('storage', updateCartCount);
});

const handleLogout = () => {
  localStorage.removeItem('isLoggedIn');
  localStorage.removeItem('userRole');
  localStorage.removeItem('username');
  window.location.href = (window.BASE_URL || '/') + 'logout';
};
</script>

<style scoped>
.modern-navy-gradient {
  background: linear-gradient(135deg, #020617 0%, #0f172a 50%, #1e1b4b 100%);
  position: relative;
}

.modern-navy-gradient::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 0% 0%, rgba(30, 58, 138, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 100% 100%, rgba(67, 56, 202, 0.05) 0%, transparent 50%);
  pointer-events: none;
}

.main-content-glass {
  background: rgba(255, 255, 255, 0.02);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.smooth-scroll-container {
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior-y: contain;
  backface-visibility: hidden;
  transform: translate3d(0,0,0);
  will-change: scroll-position;
}

.active-nav-item {
  background: rgba(255, 255, 255, 0.05);
  color: white !important;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 
    0 10px 15px -3px rgba(0, 0, 0, 0.1),
    0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Animations */
@keyframes slideInLeft {
  from { opacity: 0; transform: translateX(-20px); }
  to { opacity: 1; transform: translateX(0); }
}

@keyframes slideInTop {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideInBottom {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.animate-slide-in-left { animation: slideInLeft 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.animate-slide-in-top { animation: slideInTop 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.animate-slide-in-bottom { animation: slideInBottom 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.animate-fade-in { animation: fadeIn 1s ease forwards; }

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: rgba(139, 92, 246, 0.2);
  border-radius: 20px;
  border: 2px solid transparent;
  background-clip: content-box;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(139, 92, 246, 0.4);
  border: 1px solid transparent;
  background-clip: content-box;
}
</style>
