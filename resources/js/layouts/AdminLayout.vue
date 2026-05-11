<template>
  <div class="flex min-h-screen lg:h-screen bg-[#0f172a] lg:overflow-hidden font-['Plus_Jakarta_Sans',sans-serif]">
    <!-- Mobile Top Bar -->
    <div class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-black/40 backdrop-blur-xl border-b border-white/10 z-[50] flex items-center px-4 gap-4">
      <button @click="isSidebarOpen = !isSidebarOpen" class="w-10 h-10 bg-violet-500/20 text-white rounded-lg flex items-center justify-center border border-violet-500/30 cursor-pointer active:scale-95 transition-all">
        <Menu class="w-6 h-6" />
      </button>
      <div class="flex items-center gap-3">
        <Gem class="text-[#a855f7] w-6 h-6" />
        <h2 class="text-lg font-bold text-white tracking-wide">Mj Pogi</h2>
      </div>
    </div>

    <!-- Sidebar Overlay -->
    <div
      v-if="isSidebarOpen"
      @click="isSidebarOpen = false"
      class="fixed inset-0 z-[99998] bg-black/60 backdrop-blur-[4px] lg:hidden transition-all duration-300 touch-none"
    ></div>

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-[99999] w-[260px] transition-all duration-400 cubic-bezier(0.16,1,0.3,1) lg:static lg:translate-x-0',
        isSidebarOpen ? 'translate-x-0 shadow-[20px_0_50px_rgba(0,0,0,0.5)]' : '-translate-x-full'
      ]"
      class="bg-[#0f172a] border-r border-white/10 flex flex-col min-h-screen"
    >
      <!-- Header -->
      <div class="p-7 px-5 text-center border-b border-white/10 bg-white/[0.02]">
        <div class="flex items-center justify-center gap-3 mb-1.5">
          <Gem class="text-[#a855f7] w-6 h-6 drop-shadow-[0_0_8px_rgba(168,85,247,0.5)]" />
          <h2 class="m-0 text-[1.3rem] font-bold text-white tracking-wide">Mj Pogi</h2>
        </div>
        <small class="block text-violet-400 font-bold text-[0.75rem] tracking-widest uppercase">Superadmin</small>
      </div>
      
      <!-- Menu -->
      <nav class="flex-1 px-2 py-5 space-y-4 overflow-y-auto scrollbar-thin scrollbar-thumb-violet-500/20 scrollbar-track-transparent">
        <div v-for="section in navSections" :key="section.title" class="mb-4">
          <div class="px-4 pb-3 flex items-center gap-2 text-[0.7rem] font-bold tracking-[1.2px] text-slate-500 uppercase">
            <component :is="section.icon" class="w-3.5 h-3.5" />
            <span>{{ section.title }}</span>
          </div>
          
          <ul class="space-y-1 list-none p-0 m-0">
            <li v-for="item in section.items" :key="item.path">
              <router-link
                :to="item.path"
                class="flex items-center gap-3 px-4 py-3 text-white/70 font-medium text-[0.9rem] rounded-xl border-l-4 border-transparent transition-all duration-300 hover:bg-white/5 hover:text-white group relative"
                active-class="bg-white/5 text-white font-semibold border-l-4 !border-indigo-500 shadow-lg"
              >
                <component :is="item.icon" class="w-6 text-center transition-all group-hover:scale-110 group-hover:text-[#a855f7] group-[.router-link-active]:text-violet-300" />
                <span>{{ item.name }}</span>
              </router-link>
            </li>
          </ul>
          <div v-if="section.divider" class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent mx-3 my-4"></div>
        </div>
      </nav>

      <!-- Footer -->
      <div class="p-4 bg-black/20 border-t border-white/[0.08]">
        <div class="flex items-center gap-3 p-3 bg-white/[0.03] rounded-xl mb-3 border border-white/[0.05]">
          <div class="w-10 h-10 rounded-xl bg-violet-500/15 flex items-center justify-center text-2xl text-[#a855f7] border border-violet-500/20">
            <UserCircle />
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-[0.9rem] text-white font-semibold truncate">{{ username }}</div>
            <div class="text-[0.75rem] text-violet-400 font-medium">Admin</div>
          </div>
        </div>
        <button @click="handleLogout" class="flex items-center justify-center gap-2.5 w-full p-3 bg-rose-500/10 border border-rose-500/20 text-[#fca5a5] rounded-xl font-bold text-[0.9rem] hover:bg-rose-500/20 hover:text-white hover:border-rose-500/40 hover:-translate-y-0.5 transition-all shadow-lg active:scale-95 cursor-pointer">
          <LogOut class="w-4 h-4" />
          <span>Logout System</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col relative">
      <!-- Background Gradient (Matching header.php) -->
      <div class="absolute inset-0 z-[-1] bg-gradient-to-br from-[#1e1b4b] via-[#3b0764] to-[#082f49] animate-[gradientBg_15s_ease_infinite] bg-[length:300%_300%]"></div>

      <!-- Page Content -->
      <main class="flex-1 lg:overflow-y-auto pt-20 lg:pt-10 p-4 md:p-6 lg:p-10 relative">
        <div class="space-y-4">
          <slot></slot>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
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
  Shrimp,
  LineChart,
  Database,
  History,
  MapPin,
  Ticket,
  FileText
} from 'lucide-vue-next';

const router = useRouter();
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
      { name: 'Seafood POS', path: '/admin/pos', icon: Shrimp },
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
  router.push('/login');
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
