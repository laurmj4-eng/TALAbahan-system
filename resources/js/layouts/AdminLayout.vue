<template>
  <div class="flex h-screen bg-[#0f172a] overflow-hidden w-full font-['Plus_Jakarta_Sans',sans-serif]">
    <!-- Mobile Top Bar -->
    <div class="lg:hidden fixed top-0 left-0 right-0 h-11 bg-black/40 backdrop-blur-xl border-b border-white/[0.08] z-[50] flex items-center px-2.5 gap-2">
      <button @click="isSidebarOpen = !isSidebarOpen" class="w-7 h-7 bg-violet-500/20 text-white rounded-lg flex items-center justify-center border border-violet-500/30 cursor-pointer active:scale-95 transition-all">
        <Menu class="w-4 h-4" />
      </button>
      <div class="flex items-center gap-1.5">
        <Gem class="text-[#a855f7] w-4 h-4" />
        <h2 class="text-xs font-bold text-white tracking-wide">Mj Pogi</h2>
      </div>
    </div>

    <!-- Sidebar Overlay (mobile) -->
    <div
      v-if="isSidebarOpen"
      @click="isSidebarOpen = false"
      class="fixed inset-0 z-[99998] bg-black/60 backdrop-blur-[4px] lg:hidden transition-all duration-300 touch-none"
    ></div>

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-[99999] w-[240px] lg:w-[260px] transition-all duration-400 cubic-bezier(0.16,1,0.3,1) lg:static lg:translate-x-0',
        isSidebarOpen ? 'translate-x-0 shadow-[20px_0_50px_rgba(0,0,0,0.5)]' : '-translate-x-full'
      ]"
      class="bg-[#0f172a] border-r border-white/[0.08] flex flex-col min-h-screen"
    >
      <!-- Header -->
      <div class="p-3 lg:p-5 text-center border-b border-white/[0.08] bg-white/[0.02]">
        <div class="flex items-center justify-center gap-2 lg:gap-3 mb-0.5 lg:mb-1">
          <Gem class="text-[#a855f7] w-5 h-5 lg:w-6 lg:h-6 drop-shadow-[0_0_8px_rgba(168,85,247,0.5)]" />
          <h2 class="m-0 text-[1rem] lg:text-[1.2rem] font-bold text-white tracking-wide">Mj Pogi</h2>
        </div>
        <small class="block text-violet-400 font-bold text-[0.6rem] lg:text-[0.7rem] tracking-widest uppercase">Superadmin</small>
      </div>
      
      <!-- Menu -->
      <nav class="flex-1 px-1.5 lg:px-2 py-2.5 lg:py-4 space-y-1.5 lg:space-y-3 overflow-y-auto scrollbar-thin scrollbar-thumb-violet-500/20 scrollbar-track-transparent">
        <div v-for="section in navSections" :key="section.title" class="mb-1.5 lg:mb-3">
          <div class="px-2.5 lg:px-3 pb-1 lg:pb-2 flex items-center gap-1.5 lg:gap-2 text-[0.55rem] lg:text-[0.65rem] font-bold tracking-[1.2px] text-slate-500 uppercase">
            <component :is="section.icon" class="w-2.5 h-2.5 lg:w-3 lg:h-3" />
            <span>{{ section.title }}</span>
          </div>
          
          <ul class="space-y-px lg:space-y-0.5 list-none p-0 m-0">
            <li v-for="item in section.items" :key="item.path">
              <Link
                :href="item.path"
                class="flex items-center gap-2 lg:gap-2.5 px-2.5 lg:px-3 py-2 lg:py-2.5 text-white/70 font-medium text-[0.8rem] lg:text-[0.85rem] rounded-lg lg:rounded-xl border-l-[3px] lg:border-l-4 border-transparent transition-all duration-300 hover:bg-white/5 hover:text-white group relative"
                :class="{ 'bg-white/5 text-white font-semibold border-l-[3px] lg:border-l-4 !border-indigo-500 shadow-lg': $page.url.startsWith(item.path) }"
              >
                <component :is="item.icon" class="w-4 h-4 lg:w-5 text-center transition-all group-hover:scale-110 group-hover:text-[#a855f7]" :class="{ 'text-violet-300': $page.url.startsWith(item.path) }" />
                <span>{{ item.name }}</span>
              </Link>
            </li>
          </ul>
          <div v-if="section.divider" class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent mx-2.5 lg:mx-3 my-2 lg:my-3"></div>
        </div>
      </nav>

      <!-- Footer -->
      <div class="p-2 lg:p-3 bg-black/20 border-t border-white/[0.08]">
        <div class="flex items-center gap-2 lg:gap-2.5 p-2 lg:p-2.5 bg-white/[0.03] rounded-lg lg:rounded-xl mb-2 lg:mb-2.5 border border-white/[0.05]">
          <div class="w-7 h-7 lg:w-9 lg:h-9 rounded-lg lg:rounded-xl bg-violet-500/15 flex items-center justify-center text-base lg:text-xl text-[#a855f7] border border-violet-500/20">
            <UserCircle />
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-[0.7rem] lg:text-[0.8rem] text-white font-semibold truncate">{{ username }}</div>
            <div class="text-[0.55rem] lg:text-[0.7rem] text-violet-400 font-medium">Admin</div>
          </div>
        </div>
        <button @click="handleLogout" class="flex items-center justify-center gap-1.5 lg:gap-2 w-full p-2 lg:p-2.5 bg-rose-500/10 border border-rose-500/20 text-[#fca5a5] rounded-lg lg:rounded-xl font-bold text-[0.75rem] lg:text-[0.85rem] hover:bg-rose-500/20 hover:text-white hover:border-rose-500/40 transition-all active:scale-95 cursor-pointer">
          <LogOut class="w-3.5 h-3.5 lg:w-4 lg:h-4" />
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-0 relative">
      <!-- Background Gradient -->
      <div class="absolute inset-0 z-[-1] bg-gradient-to-br from-[#1e1b4b] via-[#3b0764] to-[#082f49] animate-[gradientBg_15s_ease_infinite] bg-[length:300%_300%]" style="contain: strict; will-change: background-position;"></div>

      <!-- Page Content (pt-11 for mobile top bar, safe-area for gesture nav) -->
      <main class="flex-1 overflow-y-auto pt-13 lg:pt-4 p-2.5 md:p-6 lg:p-8 relative smooth-scroll-container overflow-x-hidden" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        <div class="space-y-4">
          <slot></slot>
        </div>
      </main>
    </div>
    <Chatbot role="admin" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { 
  LayoutDashboard, 
  Package, 
  Users, 
  ShoppingCart, 
  LogOut,
  Menu,
  Gem,
  Compass,
  ListTodo,
  Bolt,
  ShieldCheck,
  UserCircle,
  Fish,
  ClipboardList,
  Shell,
  LineChart,
  Database,
  History,
  MapPin,
  Ticket,
  FileText
} from 'lucide-vue-next';
import Chatbot from '../components/Chatbot.vue';

const isSidebarOpen = ref(false);
const username = ref(localStorage.getItem('username') || 'Admin');

const navSections = [
  {
    title: 'Navigation',
    icon: Compass,
    items: [
      { name: 'Dashboard', path: '/admin/dashboard', icon: LayoutDashboard }
    ],
    divider: true
  },
  {
    title: 'Management',
    icon: ListTodo,
    items: [
      { name: 'Products', path: '/admin/products', icon: Fish },
      { name: 'Orders', path: '/admin/orders', icon: ClipboardList }
    ],
    divider: true
  },
  {
    title: 'Quick Access',
    icon: Bolt,
    items: [
      { name: 'Seafood POS', path: '/admin/pos', icon: Shell },
      { name: 'Sales', path: '/admin/sales', icon: LineChart }
    ],
    divider: true
  },
  {
    title: 'Admin',
    icon: ShieldCheck,
    items: [
      { name: 'Database', path: '/admin/users', icon: Database },
      { name: 'Activity Log', path: '/admin/activity', icon: History },
      { name: 'Shipping', path: '/admin/shipping', icon: MapPin },
      { name: 'Vouchers', path: '/admin/vouchers', icon: Ticket }
    ]
  }
];

const handleLogout = () => {
  localStorage.removeItem('isLoggedIn');
  localStorage.removeItem('userRole');
  localStorage.removeItem('username');
  window.location.href = (window.BASE_URL || '/') + 'logout';
};
</script>

<style>
@keyframes gradientBg {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

@keyframes pulse-subtle {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.05); }
}

@keyframes slideInRight {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-pulse-subtle {
  animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-fade-in-up {
  animation: fadeInUp 0.7s ease-out forwards;
}

.animate-slide-in-right {
  animation: slideInRight 0.5s ease-out forwards;
}

.smooth-scroll-container {
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior-y: contain;
  backface-visibility: hidden;
  transform: translate3d(0,0,0);
  will-change: transform;
}

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

/* Staggered delays for stat cards */
.delay-100 { animation-delay: 100ms; }
.delay-200 { animation-delay: 200ms; }
.delay-300 { animation-delay: 300ms; }
.delay-400 { animation-delay: 400ms; }

/* Custom Scrollbar */
.scrollbar-thin::-webkit-scrollbar {
  width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: rgba(168, 85, 247, 0.2);
  border-radius: 10px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
</style>
