<template>
  <AdminLayout :username="username">
    <div class="space-y-8">
      <div>
        <h1 class="text-3xl font-bold text-white">Financial Ledger 📈</h1>
        <p class="text-white/60">Real-time transaction logs and revenue records.</p>
      </div>

      <!-- Daily Summary Card -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <GlassCard customClass="p-6 border-l-4 border-l-emerald-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-white/40 text-xs font-black uppercase tracking-widest mb-1">Total Revenue Today</p>
              <h3 class="text-3xl font-black text-white">₱{{ formatNumber(totalRevenueToday) }}</h3>
            </div>
            <div class="bg-emerald-500/20 p-3 rounded-xl">
              <TrendingUp class="w-6 h-6 text-emerald-400" />
            </div>
          </div>
        </GlassCard>
      </div>

      <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white/5 p-6 rounded-2xl border border-white/10">
        <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
          <div class="relative w-full md:w-64">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/40" />
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search transactions..." 
              class="w-full pl-10 pr-4 py-2 bg-black/30 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50"
            >
          </div>
          <div class="flex items-center gap-2">
            <input 
              v-model="startDate"
              type="date" 
              class="bg-black/30 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500/50"
            >
            <span class="text-white/40">to</span>
            <input 
              v-model="endDate"
              type="date" 
              class="bg-black/30 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500/50"
            >
            <button @click="fetchSales" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all">
              Filter
            </button>
          </div>
        </div>

        <div class="relative group">
          <button class="w-full md:w-auto flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold transition-all">
            <Download class="w-4 h-4" /> Export Data
          </button>
          <div class="absolute right-0 mt-2 w-48 bg-gray-900 border border-white/10 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
            <button @click="exportData('csv')" class="w-full text-left px-4 py-3 text-sm text-white hover:bg-white/10 flex items-center gap-3 transition-colors">
              <FileText class="w-4 h-4 text-emerald-400" /> Export CSV
            </button>
            <button @click="exportData('pdf')" class="w-full text-left px-4 py-3 text-sm text-white hover:bg-white/10 flex items-center gap-3 transition-colors">
              <FileDown class="w-4 h-4 text-rose-400" /> Export PDF
            </button>
            <button @click="exportData('word')" class="w-full text-left px-4 py-3 text-sm text-white hover:bg-white/10 flex items-center gap-3 transition-colors">
              <FileCode class="w-4 h-4 text-blue-400" /> Export Word
            </button>
          </div>
        </div>
      </div>

      <GlassCard customClass="overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-white/5 border-b border-white/10">
                <th class="px-6 py-4 font-semibold text-white/70">TRANSACTION CODE</th>
                <th class="px-6 py-4 font-semibold text-white/70">DATE & TIME</th>
                <th class="px-6 py-4 font-semibold text-white/70">CUSTOMER</th>
                <th class="px-6 py-4 font-semibold text-white/70">ITEMS PURCHASED</th>
                <th class="px-6 py-4 font-semibold text-white/70">TOTAL REVENUE</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="record in filteredSales" :key="record.id" class="hover:bg-white/5 transition-colors group">
                <td class="px-6 py-4">
                  <strong class="text-violet-400 tracking-widest">{{ record.transaction_code }}</strong>
                </td>
                <td class="px-6 py-4 text-sm text-white/60">
                  {{ formatDate(record.created_at) }}
                </td>
                <td class="px-6 py-4 text-sm">
                  <div class="flex flex-col">
                    <span class="text-white font-medium">{{ getCustomerDisplay(record) }}</span>
                    <span v-if="record.user_id" class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Registered User</span>
                    <span v-else-if="record.customer_alias" class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">Walk-in / {{ record.customer_name }}</span>
                    <span v-else class="text-[10px] text-white/30 font-bold uppercase tracking-wider">Walk-in</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-white/80 max-w-xs">
                  <div class="group/items relative inline-block cursor-help">
                    <span class="bg-white/10 px-2 py-1 rounded text-xs font-bold text-white/70 group-hover/items:bg-indigo-500/20 group-hover/items:text-indigo-300 transition-colors">
                      {{ record.items_summary.split(',').length }} Items
                    </span>
                    <div class="absolute bottom-full left-0 mb-2 w-64 p-3 bg-gray-900 border border-white/10 rounded-xl shadow-2xl opacity-0 invisible group-hover/items:opacity-100 group-hover/items:visible transition-all z-50">
                      <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-2 border-b border-white/10 pb-1">Items List</p>
                      <ul class="space-y-1">
                        <li v-for="(item, idx) in record.items_summary.split(',')" :key="idx" class="text-xs text-white/70 flex items-start gap-2">
                          <span class="text-indigo-400">•</span> {{ item.trim() }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <strong class="text-xl text-emerald-400">₱{{ formatNumber(record.total_amount) }}</strong>
                </td>
              </tr>
              <tr v-if="filteredSales.length === 0">
                <td colspan="5" class="px-6 py-24 text-center text-white/20 italic">
                  {{ isLoading ? 'Loading financial data...' : 'No transactions found in ledger.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </GlassCard>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { Search, Download, FileText, FileDown, FileCode, TrendingUp } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const props = defineProps({
  username: String,
  sales: Array
});

const localSales = ref([...props.sales]);
const searchQuery = ref('');
const startDate = ref('');
const endDate = ref('');
const isLoading = ref(false);

watch(() => props.sales, (newSales) => {
  localSales.value = [...newSales];
}, { deep: true });

const totalRevenueToday = computed(() => {
  const today = new Date().toISOString().split('T')[0];
  return localSales.value
    .filter(s => s.created_at && s.created_at.startsWith(today))
    .reduce((sum, s) => sum + parseFloat(s.total_amount || 0), 0);
});

const getCustomerDisplay = (record) => {
  if (record.user_id) return record.customer_name;
  return record.customer_alias || record.customer_name || 'Walk-in Customer';
};

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const fetchSales = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/admin/getHistory', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value
      }
    });
    if (Array.isArray(response.data)) {
      localSales.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch sales history:', error);
  } finally {
    isLoading.value = false;
  }
};

const filteredSales = computed(() => {
  if (!searchQuery.value) return localSales.value;
  const q = searchQuery.value.toLowerCase();
  return localSales.value.filter(s => 
    s.transaction_code.toLowerCase().includes(q) || 
    (s.items_summary && s.items_summary.toLowerCase().includes(q))
  );
});

const exportData = (type) => {
  window.location.href = `/api/admin/getHistory?export=${type}&start_date=${startDate.value}&end_date=${endDate.value}`;
};
</script>
