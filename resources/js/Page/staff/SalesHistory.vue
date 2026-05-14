<template>
  <StaffLayout :username="username">
    <div class="space-y-8">
      <div class="header flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight bg-gradient-to-r from-white to-emerald-400 bg-clip-text text-transparent">💰 Sales History</h1>
          <p class="text-white/50 font-medium mt-2">Track daily revenue and historical transaction data.</p>
        </div>
        <Link href="/staff/dashboard" class="px-6 py-3 bg-white/5 border border-white/10 rounded-2xl font-bold text-white hover:bg-white/10 transition-all flex items-center gap-2">
          <ChevronLeft class="w-5 h-5" />
          <span>Dashboard</span>
        </Link>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <GlassCard v-for="stat in stats" :key="stat.label" customClass="p-8 border-white/10 flex flex-col items-center text-center group hover:bg-white/[0.04] transition-all">
          <div class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <component :is="stat.icon" class="w-7 h-7" :class="stat.iconColor" />
          </div>
          <div class="text-[0.65rem] font-black text-white/40 uppercase tracking-[0.2em] mb-1">{{ stat.label }}</div>
          <div class="text-3xl font-black text-white">{{ stat.value }}</div>
        </GlassCard>
      </div>

      <GlassCard customClass="overflow-hidden border-white/10">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="text-[0.7rem] font-black text-white/40 uppercase tracking-[0.2em] border-b border-white/5">
                <th class="px-8 py-5">Transaction Code</th>
                <th class="px-8 py-5">Date & Time</th>
                <th class="px-8 py-5">Customer</th>
                <th class="px-8 py-5">Items Summary</th>
                <th class="px-8 py-5 text-right">Revenue</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="sale in sales" :key="sale.id" class="group hover:bg-white/[0.02] transition-all">
                <td class="px-8 py-6">
                  <span class="font-mono font-bold text-violet-400">{{ sale.transaction_code }}</span>
                </td>
                <td class="px-8 py-6">
                  <div class="text-white font-medium">{{ formatDate(sale.created_at) }}</div>
                </td>
                <td class="px-8 py-6">
                  <div class="flex flex-col">
                    <span class="text-white font-medium">{{ getCustomerDisplay(sale) }}</span>
                    <span v-if="sale.user_id" class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Registered User</span>
                    <span v-else-if="sale.customer_alias" class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">Walk-in / {{ sale.customer_name }}</span>
                    <span v-else class="text-[10px] text-white/30 font-bold uppercase tracking-wider">Walk-in</span>
                  </div>
                </td>
                <td class="px-8 py-6">
                  <div class="group/items relative inline-block cursor-help">
                    <span class="bg-white/10 px-2 py-1 rounded text-xs font-bold text-white/40 group-hover/items:bg-indigo-500/20 group-hover/items:text-indigo-300 transition-colors">
                      {{ sale.items_summary.split(',').length }} Items
                    </span>
                    <div class="absolute bottom-full left-0 mb-2 w-64 p-3 bg-gray-900 border border-white/10 rounded-xl shadow-2xl opacity-0 invisible group-hover/items:opacity-100 group-hover/items:visible transition-all z-50 text-left">
                      <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-2 border-b border-white/10 pb-1 italic">Purchased Items</p>
                      <ul class="space-y-1">
                        <li v-for="(item, idx) in sale.items_summary.split(',')" :key="idx" class="text-xs text-white/70 flex items-start gap-2">
                          <span class="text-indigo-400">•</span> {{ item.trim() }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </td>
                <td class="px-8 py-6 text-right">
                  <div class="text-xl font-black text-emerald-400">₱{{ formatNumber(sale.total_amount) }}</div>
                </td>
              </tr>
              <tr v-if="sales.length === 0">
                <td colspan="5" class="px-8 py-32 text-center">
                  <div class="flex flex-col items-center gap-4 opacity-10">
                    <HistoryIcon class="w-16 h-16" />
                    <p class="font-bold text-lg italic">No sales records found.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </GlassCard>
    </div>
  </StaffLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, TrendingUp, DollarSign, BarChart3, History as HistoryIcon } from 'lucide-vue-next';
import GlassCard from '../../components/GlassCard.vue';
import StaffLayout from '../../layouts/StaffLayout.vue';

const props = defineProps({
  username: String,
  sales: Array
});

const totalRevenueToday = computed(() => {
  const today = new Date().toISOString().split('T')[0];
  return props.sales
    .filter(s => s.created_at && s.created_at.startsWith(today))
    .reduce((sum, s) => sum + parseFloat(s.total_amount || 0), 0);
});

const getCustomerDisplay = (record) => {
  if (record.user_id) return record.customer_name;
  return record.customer_alias || record.customer_name || 'Walk-in Customer';
};

const totalRevenue = computed(() => {
  return props.sales.reduce((sum, s) => sum + parseFloat(s.total_amount || 0), 0);
});

const averageSale = computed(() => {
  return props.sales.length > 0 ? totalRevenue.value / props.sales.length : 0;
});

const stats = computed(() => [
  { label: 'Today\'s Revenue', value: '₱' + formatNumber(totalRevenueToday.value), icon: TrendingUp, iconColor: 'text-emerald-400' },
  { label: 'Total Transactions', value: props.sales.length, icon: HistoryIcon, iconColor: 'text-indigo-400' },
  { label: 'Total Revenue', value: '₱' + formatNumber(totalRevenue.value), icon: DollarSign, iconColor: 'text-emerald-400' },
  { label: 'Average Sale', value: '₱' + formatNumber(averageSale.value), icon: BarChart3, iconColor: 'text-violet-400' }
]);

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
