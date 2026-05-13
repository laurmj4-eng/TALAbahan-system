<template>
  <div class="p-6 md:p-8 space-y-8">
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                <div class="text-white/60 text-sm max-w-md truncate font-medium italic" :title="sale.items_summary">
                  {{ sale.items_summary }}
                </div>
              </td>
              <td class="px-8 py-6 text-right">
                <div class="text-xl font-black text-emerald-400">₱{{ formatNumber(sale.total_amount) }}</div>
              </td>
            </tr>
            <tr v-if="sales.length === 0">
              <td colspan="4" class="px-8 py-32 text-center">
                <div class="flex flex-col items-center gap-4 opacity-10">
                  <Loader2 v-if="loading" class="w-12 h-12 animate-spin" />
                  <History v-else class="w-16 h-16" />
                  <p class="font-bold text-lg italic">{{ loading ? 'Loading sales data...' : 'No sales records found.' }}</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </GlassCard>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ChevronLeft, TrendingUp, DollarSign, BarChart3, Loader2, History } from 'lucide-vue-next';
import GlassCard from '../../components/GlassCard.vue';

const sales = ref([]);
const loading = ref(true);

const fetchSalesHistory = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/getHistory'); // Staff uses admin history endpoint
    if (Array.isArray(response.data)) {
      sales.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch sales history:', error);
  } finally {
    loading.value = false;
  }
};

const totalRevenue = computed(() => {
  return sales.value.reduce((sum, s) => sum + parseFloat(s.total_amount || 0), 0);
});

const averageSale = computed(() => {
  return sales.value.length > 0 ? totalRevenue.value / sales.value.length : 0;
});

const stats = computed(() => [
  { label: 'Total Transactions', value: sales.value.length, icon: TrendingUp, iconColor: 'text-indigo-400' },
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

onMounted(fetchSalesHistory);
</script>
