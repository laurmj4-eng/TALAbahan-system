<template>
  <StaffLayout :username="username">
    <div class="space-y-8">
      <div class="header-section">
        <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white mb-2">Staff Portal 🏛️</h1>
        <p class="text-white/60 font-medium">Welcome back, {{ username }}! Here's an overview of the business today.</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <GlassCard v-for="stat in dashboardStats" :key="stat.label" customClass="p-6 border-white/10 flex flex-col items-center text-center group hover:bg-white/[0.04] transition-all">
          <div class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <component :is="stat.icon" class="w-6 h-6" :class="stat.iconColor" />
          </div>
          <div class="text-[0.6rem] font-black text-white/40 uppercase tracking-[0.2em] mb-1">{{ stat.label }}</div>
          <div class="text-2xl font-black text-white">{{ stat.value }}</div>
        </GlassCard>
      </div>

      <!-- Quick Navigation -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <Link href="/staff/pos" class="group block h-full">
          <GlassCard customClass="p-8 border-white/10 hover:border-violet-500/30 hover:bg-white/[0.04] transition-all h-full flex flex-col items-center justify-center text-center space-y-4">
            <div class="w-20 h-20 rounded-[2rem] bg-violet-500/10 border border-violet-500/20 flex items-center justify-center group-hover:rotate-6 transition-transform">
              <ShoppingCart class="w-10 h-10 text-violet-400" />
            </div>
            <div>
              <h3 class="text-2xl font-black text-white group-hover:text-violet-300 transition-colors">Seafood POS</h3>
              <p class="text-white/40 font-medium text-sm mt-1">Direct terminal for walk-in orders.</p>
            </div>
          </GlassCard>
        </Link>

        <Link href="/staff/orders" class="group block h-full">
          <GlassCard customClass="p-8 border-white/10 hover:border-indigo-500/30 hover:bg-white/[0.04] transition-all h-full flex flex-col items-center justify-center text-center space-y-4">
            <div class="w-20 h-20 rounded-[2rem] bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center group-hover:rotate-6 transition-transform">
              <ClipboardList class="w-10 h-10 text-indigo-400" />
            </div>
            <div>
              <h3 class="text-2xl font-black text-white group-hover:text-indigo-300 transition-colors">Manage Orders</h3>
              <p class="text-white/40 font-medium text-sm mt-1">Fulfill pending deliveries and updates.</p>
            </div>
          </GlassCard>
        </Link>

        <Link href="/staff/products" class="group block h-full">
          <GlassCard customClass="p-8 border-white/10 hover:border-emerald-500/30 hover:bg-white/[0.04] transition-all h-full flex flex-col items-center justify-center text-center space-y-4">
            <div class="w-20 h-20 rounded-[2rem] bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center group-hover:-rotate-6 transition-transform">
              <Package class="w-10 h-10 text-emerald-400" />
            </div>
            <div>
              <h3 class="text-2xl font-black text-white group-hover:text-emerald-300 transition-colors">Inventory</h3>
              <p class="text-white/40 font-medium text-sm mt-1">Check stock levels and availability.</p>
            </div>
          </GlassCard>
        </Link>

        <Link href="/staff/salesHistory" class="group block h-full">
          <GlassCard customClass="p-8 border-white/10 hover:border-sky-500/30 hover:bg-white/[0.04] transition-all h-full flex flex-col items-center justify-center text-center space-y-4">
            <div class="w-20 h-20 rounded-[2rem] bg-sky-500/10 border border-sky-500/20 flex items-center justify-center group-hover:rotate-12 transition-transform">
              <BarChart3 class="w-10 h-10 text-sky-400" />
            </div>
            <div>
              <h3 class="text-2xl font-black text-white group-hover:text-sky-300 transition-colors">Sales History</h3>
              <p class="text-white/40 font-medium text-sm mt-1">Review past transactions and revenue.</p>
            </div>
          </GlassCard>
        </Link>
      </div>

      <!-- Recent Activity Placeholder -->
      <GlassCard customClass="p-8 border-white/10">
        <div class="flex items-center justify-between mb-8">
          <h3 class="text-2xl font-bold text-white flex items-center gap-3">
            <Activity class="w-6 h-6 text-indigo-400" />
            <span>System Status</span>
          </h3>
          <div class="flex items-center gap-2 bg-emerald-500/10 text-emerald-400 px-4 py-1.5 rounded-full text-[0.65rem] font-black uppercase tracking-widest border border-emerald-500/20">
            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
            Operational
          </div>
        </div>
        <div class="py-12 text-center text-white/20">
          <p class="italic font-medium">No alerts or notifications at this time.</p>
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
