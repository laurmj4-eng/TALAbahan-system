<template>
  <StaffLayout :username="username">
    <div class="space-y-6 md:space-y-8">
      <div class="header-section" v-once>
        <h1 class="text-2xl md:text-5xl font-black tracking-tight text-white mb-1">Staff Portal 🏛️</h1>
        <p class="text-white/60 font-medium text-sm">Welcome back, {{ username }}! Here's an overview of the business today.</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
        <GlassCard v-for="stat in dashboardStats" :key="stat.label" customClass="p-4 md:p-6 border-white/10 flex flex-col items-center text-center group hover:bg-white/[0.04] transition-all">
          <div class="w-9 h-9 md:w-12 md:h-12 rounded-xl md:rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mb-3 md:mb-4 group-hover:scale-110 transition-transform">
            <component :is="stat.icon" class="w-5 h-5 md:w-6 md:h-6" :class="stat.iconColor" />
          </div>
          <div class="text-[0.5rem] md:text-[0.6rem] font-black text-white/40 uppercase tracking-[0.2em] mb-0.5 md:mb-1">{{ stat.label }}</div>
          <div class="text-xl md:text-2xl font-black text-white">{{ stat.value }}</div>
        </GlassCard>
      </div>

      <!-- Quick Navigation -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6" v-once>
        <Link href="/staff/pos" class="group block h-full">
          <GlassCard customClass="p-4 md:p-8 border-white/10 hover:border-violet-500/30 hover:bg-white/[0.04] transition-all h-full flex flex-col items-center justify-center text-center space-y-2 md:space-y-4">
            <div class="w-12 h-12 md:w-20 md:h-20 rounded-xl md:rounded-[2rem] bg-violet-500/10 border border-violet-500/20 flex items-center justify-center group-hover:rotate-6 transition-transform">
              <ShoppingCart class="w-6 h-6 md:w-10 md:h-10 text-violet-400" />
            </div>
            <div>
              <h3 class="text-base md:text-2xl font-black text-white group-hover:text-violet-300 transition-colors">Seafood POS</h3>
              <p class="text-white/40 font-medium text-[0.6rem] md:text-sm mt-0.5 md:mt-1 hidden sm:block">Direct terminal for walk-in orders.</p>
            </div>
          </GlassCard>
        </Link>

        <Link href="/staff/orders" class="group block h-full">
          <GlassCard customClass="p-4 md:p-8 border-white/10 hover:border-indigo-500/30 hover:bg-white/[0.04] transition-all h-full flex flex-col items-center justify-center text-center space-y-2 md:space-y-4">
            <div class="w-12 h-12 md:w-20 md:h-20 rounded-xl md:rounded-[2rem] bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center group-hover:rotate-6 transition-transform">
              <ClipboardList class="w-6 h-6 md:w-10 md:h-10 text-indigo-400" />
            </div>
            <div>
              <h3 class="text-base md:text-2xl font-black text-white group-hover:text-indigo-300 transition-colors">Manage Orders</h3>
              <p class="text-white/40 font-medium text-[0.6rem] md:text-sm mt-0.5 md:mt-1 hidden sm:block">Fulfill pending deliveries and updates.</p>
            </div>
          </GlassCard>
        </Link>

        <Link href="/staff/products" class="group block h-full">
          <GlassCard customClass="p-4 md:p-8 border-white/10 hover:border-emerald-500/30 hover:bg-white/[0.04] transition-all h-full flex flex-col items-center justify-center text-center space-y-2 md:space-y-4">
            <div class="w-12 h-12 md:w-20 md:h-20 rounded-xl md:rounded-[2rem] bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center group-hover:-rotate-6 transition-transform">
              <Package class="w-6 h-6 md:w-10 md:h-10 text-emerald-400" />
            </div>
            <div>
              <h3 class="text-base md:text-2xl font-black text-white group-hover:text-emerald-300 transition-colors">Inventory</h3>
              <p class="text-white/40 font-medium text-[0.6rem] md:text-sm mt-0.5 md:mt-1 hidden sm:block">Check stock levels and availability.</p>
            </div>
          </GlassCard>
        </Link>

        <Link href="/staff/salesHistory" class="group block h-full">
          <GlassCard customClass="p-4 md:p-8 border-white/10 hover:border-sky-500/30 hover:bg-white/[0.04] transition-all h-full flex flex-col items-center justify-center text-center space-y-2 md:space-y-4">
            <div class="w-12 h-12 md:w-20 md:h-20 rounded-xl md:rounded-[2rem] bg-sky-500/10 border border-sky-500/20 flex items-center justify-center group-hover:rotate-12 transition-transform">
              <BarChart3 class="w-6 h-6 md:w-10 md:h-10 text-sky-400" />
            </div>
            <div>
              <h3 class="text-base md:text-2xl font-black text-white group-hover:text-sky-300 transition-colors">Sales History</h3>
              <p class="text-white/40 font-medium text-[0.6rem] md:text-sm mt-0.5 md:mt-1 hidden sm:block">Review past transactions and revenue.</p>
            </div>
          </GlassCard>
        </Link>
      </div>

      <!-- Recent Activity Placeholder -->
      <GlassCard customClass="p-4 md:p-8 border-white/10">
        <div class="flex items-center justify-between mb-4 md:mb-8">
          <h3 class="text-lg md:text-2xl font-bold text-white flex items-center gap-2 md:gap-3">
            <Activity class="w-5 h-5 md:w-6 md:h-6 text-indigo-400" />
            <span>System Status</span>
          </h3>
          <div class="flex items-center gap-1.5 md:gap-2 bg-emerald-500/10 text-emerald-400 px-3 py-1 md:px-4 md:py-1.5 rounded-full text-[0.5rem] md:text-[0.65rem] font-black uppercase tracking-widest border border-emerald-500/20">
            <div class="w-1.5 h-1.5 md:w-2 md:h-2 bg-emerald-500 rounded-full animate-pulse"></div>
            Operational
          </div>
        </div>
        <div class="py-8 md:py-12 text-center text-white/20">
          <p class="italic font-medium text-sm">No alerts or notifications at this time.</p>
        </div>
      </GlassCard>
    </div>
  </StaffLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ShoppingCart, Package, BarChart3, Activity, Clock, PackageCheck, AlertCircle, ShoppingBag } from 'lucide-vue-next';
import GlassCard from '../../components/GlassCard.vue';
import StaffLayout from '../../layouts/StaffLayout.vue';

const props = defineProps({
  username: String,
  cards: Object,
  chart: Object
});

const dashboardStats = computed(() => [
  { 
    label: 'Today\'s Orders', 
    value: props.cards?.today_orders || '0', 
    icon: ShoppingBag, 
    iconColor: 'text-indigo-400' 
  },
  { 
    label: 'Total Products', 
    value: props.cards?.total_products || '0', 
    icon: Package, 
    iconColor: 'text-sky-400' 
  },
  { 
    label: 'Low Stock', 
    value: props.cards?.low_stock_count || '0', 
    icon: AlertCircle, 
    iconColor: 'text-amber-400' 
  },
  { 
    label: 'Out of Stock', 
    value: props.cards?.out_of_stock || '0', 
    icon: Clock, 
    iconColor: 'text-rose-400' 
  }
]);
</script>
