<template>
  <div class="flex h-screen bg-[#0f172a] overflow-hidden w-full font-['Plus_Jakarta_Sans',sans-serif]">
    <!-- Mobile Top Bar -->
    <div class="lg:hidden fixed top-0 left-0 right-0 h-12 bg-black/40 backdrop-blur-xl border-b border-white/[0.08] z-[50] flex items-center px-3 gap-2">
      <button @click="isSidebarOpen = !isSidebarOpen" class="w-8 h-8 bg-cyan-500/20 text-white rounded-lg flex items-center justify-center border border-cyan-500/30 cursor-pointer active:scale-95 transition-all">
        <Menu class="w-4 h-4" />
      </button>
      <div class="flex items-center gap-2">
        <Terminal class="text-[#22d3ee] w-5 h-5" />
        <h2 class="text-sm font-bold text-white tracking-wide">Dev Console</h2>
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
          <Terminal class="text-[#22d3ee] w-5 h-5 lg:w-6 lg:h-6 drop-shadow-[0_0_8px_rgba(34,211,238,0.5)]" />
          <h2 class="m-0 text-[1rem] lg:text-[1.2rem] font-bold text-white tracking-wide">Dev Console</h2>
        </div>
        <small class="block text-cyan-400 font-bold text-[0.65rem] lg:text-[0.7rem] tracking-widest uppercase">Developer</small>
      </div>

      <!-- Menu -->
      <nav class="flex-1 px-2 lg:px-2 py-3 lg:py-4 space-y-2 lg:space-y-3 overflow-y-auto scrollbar-thin scrollbar-thumb-cyan-500/20 scrollbar-track-transparent">
        <div v-for="section in navSections" :key="section.title" class="mb-2 lg:mb-3">
          <div class="px-3 pb-2 flex items-center gap-2 text-[0.6rem] lg:text-[0.65rem] font-bold tracking-[1.2px] text-slate-500 uppercase">
            <component :is="section.icon" class="w-3 h-3 lg:w-3 lg:h-3" />
            <span>{{ section.title }}</span>
          </div>

          <ul class="space-y-px lg:space-y-0.5 list-none p-0 m-0">
            <li v-for="item in section.items" :key="item.path">
              <Link
                :href="item.path"
                class="flex items-center gap-2.5 px-3 py-2.5 text-white/70 font-medium text-[0.8rem] lg:text-[0.85rem] rounded-lg lg:rounded-xl border-l-[3px] lg:border-l-4 border-transparent transition-all duration-300 hover:bg-white/5 hover:text-white group relative"
                :class="{ 'bg-white/5 text-white font-semibold border-l-[3px] lg:border-l-4 !border-cyan-500 shadow-lg': $page.url.startsWith(item.path) }"
              >
                <component :is="item.icon" class="w-4 h-4 lg:w-5 text-center transition-all group-hover:scale-110 group-hover:text-[#22d3ee]" :class="{ 'text-cyan-300': $page.url.startsWith(item.path) }" />
                <span>{{ item.name }}</span>
              </Link>
            </li>
          </ul>
          <div v-if="section.divider" class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent mx-3 my-2 lg:my-3"></div>
        </div>
      </nav>

      <!-- Footer -->
      <div class="p-2.5 lg:p-3 bg-black/20 border-t border-white/[0.08]">
        <div class="flex items-center gap-2 lg:gap-2.5 p-2 lg:p-2.5 bg-white/[0.03] rounded-lg lg:rounded-xl mb-2 lg:mb-2.5 border border-white/[0.05]">
          <div class="w-8 h-8 lg:w-9 lg:h-9 rounded-lg lg:rounded-xl bg-cyan-500/15 flex items-center justify-center text-lg lg:text-xl text-[#22d3ee] border border-cyan-500/20">
            <UserCircle />
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-[0.75rem] lg:text-[0.8rem] text-white font-semibold truncate">{{ username }}</div>
            <div class="text-[0.6rem] lg:text-[0.7rem] text-cyan-400 font-medium">Developer</div>
          </div>
        </div>
        <button @click="handleLogout" class="flex items-center justify-center gap-2 lg:gap-2 w-full p-2.5 lg:p-2.5 bg-rose-500/10 border border-rose-500/20 text-[#fca5a5] rounded-lg lg:rounded-xl font-bold text-[0.8rem] lg:text-[0.85rem] hover:bg-rose-500/20 hover:text-white hover:border-rose-500/40 transition-all active:scale-95 cursor-pointer">
          <LogOut class="w-4 h-4 lg:w-4 lg:h-4" />
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-0 relative w-full max-w-full">
      <div class="absolute inset-0 z-[-1] bg-gradient-to-br from-[#0f172a] via-[#164e63] to-[#0e7490] animate-[gradientBg_15s_ease_infinite] bg-[length:300%_300%]" style="contain: strict; will-change: background-position;"></div>

      <main class="flex-1 overflow-y-auto pt-14 lg:pt-4 px-3 pb-4 md:px-6 lg:px-8 md:p-6 lg:p-8 relative smooth-scroll-container overflow-x-hidden" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        <div class="space-y-4">
          <slot></slot>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
  LayoutDashboard,
  LogOut,
  Menu,
  Terminal,
  UserCircle,
  Radio,
  Monitor,
  Smartphone
} from 'lucide-vue-next';

const isSidebarOpen = ref(false);
const username = ref(localStorage.getItem('username') || 'Developer');

const navSections = [
  {
    title: 'System',
    icon: Monitor,
    items: [
      { name: 'Dashboard', path: '/developer/dashboard', icon: LayoutDashboard }
    ],
    divider: true
  },
  {
    title: 'Monitor',
    icon: Radio,
    items: [
      { name: 'Device Tracking', path: '/developer/dashboard', icon: Smartphone }
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

.smooth-scroll-container {
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior-y: contain;
  backface-visibility: hidden;
  transform: translate3d(0,0,0);
  will-change: transform;
}

::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: rgba(34, 211, 238, 0.2);
  border-radius: 20px;
  border: 2px solid transparent;
  background-clip: content-box;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(34, 211, 238, 0.4);
  border: 1px solid transparent;
  background-clip: content-box;
}

.scrollbar-thin::-webkit-scrollbar {
  width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: rgba(34, 211, 238, 0.2);
  border-radius: 10px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
</style>
