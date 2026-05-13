<template>
  <AdminLayout :username="username">
    <div class="space-y-8">
      <div>
        <h1 class="text-3xl font-bold text-white">Financial Ledger 📈</h1>
        <p class="text-white/60">Real-time transaction logs and revenue records.</p>
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
        <button @click="exportCSV" class="w-full md:w-auto flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold transition-all">
          <Download class="w-4 h-4" /> Export CSV
        </button>
      </div>

      <GlassCard customClass="overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-white/5 border-b border-white/10">
                <th class="px-6 py-4 font-semibold text-white/70">TRANSACTION CODE</th>
                <th class="px-6 py-4 font-semibold text-white/70">DATE & TIME</th>
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
                <td class="px-6 py-4 text-white/80 max-w-xs truncate" :title="record.items_summary">
                  {{ record.items_summary }}
                </td>
                <td class="px-6 py-4">
                  <strong class="text-xl text-emerald-400">₱{{ formatNumber(record.total_amount) }}</strong>
                </td>
              </tr>
              <tr v-if="filteredSales.length === 0">
                <td colspan="4" class="px-6 py-24 text-center text-white/20 italic">
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
import { Search, Download } from 'lucide-vue-next';
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

const exportCSV = () => {
  window.location.href = `/api/admin/getHistory?export=csv&start_date=${startDate.value}&end_date=${endDate.value}`;
};
</script>
