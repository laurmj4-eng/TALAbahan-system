<template>
  <AdminLayout :username="username">
    <div class="space-y-3 md:space-y-8">
      <div>
        <h1 class="text-lg md:text-3xl font-bold text-white">Financial Ledger</h1>
        <p class="text-white/60 text-xs md:text-base">Real-time transaction logs and revenue records.</p>
      </div>

      <!-- Daily Summary Card -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-6">
        <GlassCard customClass="p-3 md:p-6 border-l-4 border-l-emerald-500">
          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0 flex-1">
              <p class="text-white/40 text-xs font-black uppercase tracking-wider mb-0.5">Revenue Today</p>
              <h3 class="text-xl md:text-3xl font-black text-white">₱{{ formatNumber(totalRevenueToday) }}</h3>
            </div>
            <div class="bg-emerald-500/20 p-2 md:p-3 rounded-lg shrink-0">
              <TrendingUp class="w-5 h-5 md:w-6 md:h-6 text-emerald-400" />
            </div>
          </div>
        </GlassCard>
      </div>

      <!-- Search & Filter Bar -->
      <div class="w-full max-w-full bg-white/5 p-3 md:p-6 rounded-xl md:rounded-2xl border border-white/[0.08] space-y-3">
        <div class="flex gap-2">
          <div class="relative flex-1 min-w-0">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/40" />
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search..." 
              class="w-full pl-9 pr-3 py-2.5 bg-black/30 border border-white/[0.08] rounded-lg text-xs text-white focus:outline-none focus:border-indigo-500/50"
            >
          </div>
          <div class="relative group shrink-0">
            <button class="flex items-center gap-1.5 px-3 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all">
              <Download class="w-3.5 h-3.5" /> Export
            </button>
            <div class="absolute right-0 mt-2 w-40 bg-gray-900 border border-white/[0.08] rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
              <button @click="exportData('csv')" class="w-full text-left px-3 py-2.5 text-xs text-white hover:bg-white/10 flex items-center gap-2 transition-colors active:scale-[0.98] touch-manipulation">
                <FileText class="w-3.5 h-3.5 text-emerald-400" /> CSV
              </button>
              <button @click="exportData('pdf')" class="w-full text-left px-3 py-2.5 text-xs text-white hover:bg-white/10 flex items-center gap-2 transition-colors active:scale-[0.98] touch-manipulation">
                <FileDown class="w-3.5 h-3.5 text-rose-400" /> PDF
              </button>
              <button @click="exportData('word')" class="w-full text-left px-3 py-2.5 text-xs text-white hover:bg-white/10 flex items-center gap-2 transition-colors active:scale-[0.98] touch-manipulation">
                <FileCode class="w-3.5 h-3.5 text-blue-400" /> Word
              </button>
            </div>
          </div>
        </div>

        <!-- Status Filter (Mobile) -->
        <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1 md:hidden w-full max-w-full">
          <button
            v-for="opt in statusOptions"
            :key="opt.value"
            @click="statusFilter = opt.value"
            :class="['status-filter-btn', { active: statusFilter === opt.value }]"
          >
            {{ opt.label }}
          </button>
        </div>

        <!-- Sort (Mobile) -->
        <div class="md:hidden w-full">
          <select 
            v-model="sortBy" 
            class="w-full bg-black/30 border border-white/[0.08] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500/50"
          >
            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Desktop Date Filter -->
        <div class="hidden md:flex items-center gap-2">
          <input 
            v-model="startDate"
            type="date" 
            class="bg-black/30 border border-white/[0.08] rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500/50"
          >
          <span class="text-white/40">to</span>
          <input 
            v-model="endDate"
            type="date" 
            class="bg-black/30 border border-white/[0.08] rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500/50"
          >
          <button @click="fetchSales" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all">
            Filter
          </button>
        </div>
      </div>

      <!-- Sales Cards / Table -->
      <GlassCard customClass="responsive-table-to-cards w-full max-w-full overflow-hidden">
        <!-- Desktop Table -->
        <div class="overflow-x-auto hidden md:block">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-white/5 border-b border-white/[0.08]">
                <th class="px-6 py-4 font-semibold text-white/70">TRANSACTION CODE</th>
                <th class="px-6 py-4 font-semibold text-white/70">DATE & TIME</th>
                <th class="px-6 py-4 font-semibold text-white/70">CUSTOMER</th>
                <th class="px-6 py-4 font-semibold text-white/70">ITEMS PURCHASED</th>
                <th class="px-6 py-4 font-semibold text-white/70">TOTAL REVENUE</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="record in paginatedSales" :key="record.id" class="hover:bg-white/5 transition-colors group">
                <td class="px-6 py-4">
                  <strong class="text-violet-400 tracking-widest">{{ record.transaction_code }}</strong>
                </td>
                <td class="px-6 py-4 text-sm text-white/60">{{ formatDate(record.created_at) }}</td>
                <td class="px-6 py-4 text-sm">
                  <span class="text-white font-medium">{{ getCustomerDisplay(record) }}</span>
                </td>
                <td class="px-6 py-4 text-white/80">
                  <span class="bg-white/10 px-2 py-1 rounded text-xs font-bold text-white/70">{{ record.items_summary.split(',').length }} Items</span>
                </td>
                <td class="px-6 py-4">
                  <strong class="text-xl text-emerald-400">₱{{ formatNumber(record.total_amount) }}</strong>
                </td>
              </tr>
              <tr v-if="filteredSales.length === 0">
                <td colspan="5" class="px-6 py-24 text-center text-white/20 italic">
                  {{ isLoading ? 'Loading...' : 'No transactions found.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden p-3 space-y-2.5">
          <div v-if="filteredSales.length === 0" class="py-16 text-center text-white/20 italic text-xs">
            {{ isLoading ? 'Loading...' : 'No transactions found.' }}
          </div>
          <div
            v-for="record in paginatedSales"
            :key="'card-' + record.id"
            @click="toggleCard(record.id)"
            class="mobile-card relative bg-white/[0.03] rounded-xl p-2.5 border border-white/[0.08] transition-all duration-200 active:scale-[0.98] cursor-pointer"
          >
            <div class="flex flex-col gap-1.5">
              <div class="flex items-center justify-between">
                <span class="text-violet-400 text-[0.75rem] font-bold truncate pr-2">{{ record.transaction_code }}</span>
                <span class="text-white/30 text-xs leading-none shrink-0" :class="{ '!text-indigo-400 transform rotate-90': expandedCards.has(record.id) }">›</span>
              </div>
              
              <div class="flex flex-wrap items-center gap-2">
                 <span class="text-emerald-400 text-[0.85rem] font-black">₱{{ formatNumber(record.total_amount) }}</span>
                 <span class="px-2 py-0.5 rounded-full text-[0.55rem] font-bold uppercase tracking-wider"
                  :class="getStatusClass(record.status || 'completed')">
                  {{ record.status || 'Completed' }}
                 </span>
              </div>

              <span class="text-[0.65rem] text-white/40 truncate">{{ formatDateShort(record.created_at) }}</span>
            </div>

            <!-- Expanded Content -->
            <div v-if="expandedCards.has(record.id)" class="mt-2.5 pt-2.5 border-t border-white/[0.08] space-y-2.5">
              <div class="bg-indigo-500/10 rounded-lg p-2.5">
                <span class="text-xs font-bold uppercase text-white/40 block mb-0.5">Customer</span>
                <p class="text-xs text-indigo-300 font-semibold">{{ getCustomerDisplay(record) }}</p>
                <span v-if="record.user_id" class="text-[0.65rem] text-indigo-400 font-bold uppercase">Registered</span>
                <span v-else-if="record.customer_alias" class="text-[0.65rem] text-emerald-400 font-bold uppercase">Walk-in</span>
                <span v-else class="text-[0.65rem] text-white/30 font-bold uppercase">Walk-in</span>
              </div>
              <div>
                <span class="text-xs font-bold uppercase text-white/40 block mb-0.5">Items ({{ record.items_summary.split(',').length }})</span>
                <ul class="space-y-0.5">
                  <li v-for="(item, idx) in record.items_summary.split(',')" :key="idx" class="text-[0.7rem] text-white/60 flex items-start gap-1.5 leading-tight">
                    <span class="text-indigo-400 shrink-0 mt-0.5">•</span>
                    <span class="break-words min-w-0">{{ item.trim() }}</span>
                  </li>
                </ul>
              </div>
              <div class="flex gap-2 pt-2 border-t border-white/[0.08]">
                <button class="flex-1 px-2 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-colors active:scale-95">
                  Edit
                </button>
                <button class="flex-1 px-2 py-1.5 bg-rose-600/20 hover:bg-rose-600/40 text-rose-400 rounded-lg text-xs font-bold transition-colors border border-rose-500/30 active:scale-95">
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </GlassCard>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex items-center justify-center gap-2">
        <button 
          @click="prevPage" 
          :disabled="currentPage === 1"
          class="px-3 py-1.5 bg-white/5 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed text-white rounded-lg text-xs font-bold transition-all border border-white/[0.08] active:scale-95 touch-manipulation"
        >
          ‹ Prev
        </button>
        <span class="text-white/50 text-xs font-bold">{{ currentPage }}/{{ totalPages }}</span>
        <button 
          @click="nextPage" 
          :disabled="currentPage === totalPages"
          class="px-3 py-1.5 bg-white/5 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed text-white rounded-lg text-xs font-bold transition-all border border-white/[0.08] active:scale-95 touch-manipulation"
        >
          Next ›
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { Search, Download, FileText, FileDown, FileCode, TrendingUp } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';
import { runHeavyTaskWithoutBlockingUI } from '../../composables/usePerformance';

const props = defineProps({
  username: String,
  sales: Array
});

const localSales = ref([...props.sales]);
const searchQuery = ref('');
const startDate = ref('');
const endDate = ref('');
const isLoading = ref(false);
const statusFilter = ref('all');
const sortBy = ref('newest');
const currentPage = ref(1);
const itemsPerPage = 10;

const statusOptions = [
  { value: 'all', label: 'All' },
  { value: 'completed', label: 'Completed' },
  { value: 'paid', label: 'Paid' },
  { value: 'pending', label: 'Pending' },
  { value: 'processing', label: 'Processing' },
  { value: 'cancelled', label: 'Cancelled' }
];

const sortOptions = [
  { value: 'newest', label: 'Newest First' },
  { value: 'oldest', label: 'Oldest First' },
  { value: 'highest', label: 'Highest Revenue' },
  { value: 'lowest', label: 'Lowest Revenue' },
  { value: 'alpha', label: 'A-Z' }
];

const expandedCards = ref(new Set());

const toggleCard = (id) => {
  if (expandedCards.value.has(id)) {
    expandedCards.value.delete(id);
  } else {
    expandedCards.value.add(id);
  }
};

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
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatDateShort = (dateStr) => {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getStatusClass = (status) => {
  const classes = {
    'completed': 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
    'paid': 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
    'pending': 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
    'processing': 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
    'cancelled': 'bg-rose-500/20 text-rose-400 border border-rose-500/30',
  };
  return classes[status?.toLowerCase()] || 'bg-white/10 text-white/60 border border-white/[0.08]';
};

const fetchSales = () => {
  isLoading.value = true;
  runHeavyTaskWithoutBlockingUI(async () => {
    try {
      const response = await axios.get('/api/admin/getHistory', {
        params: { start_date: startDate.value, end_date: endDate.value }
      });
      if (Array.isArray(response.data)) {
        localSales.value = response.data;
      }
    } catch (error) {
      console.error('Failed to fetch sales history:', error);
    } finally {
      isLoading.value = false;
    }
  });
};

const filteredSales = computed(() => {
  let result = localSales.value;
  
  if (statusFilter.value !== 'all') {
    result = result.filter(s => (s.status || 'completed').toLowerCase() === statusFilter.value);
  }
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(s => 
      s.transaction_code.toLowerCase().includes(q) || 
      (s.items_summary && s.items_summary.toLowerCase().includes(q)) ||
      (s.customer_name && s.customer_name.toLowerCase().includes(q))
    );
  }
  
  result = [...result].sort((a, b) => {
    switch (sortBy.value) {
      case 'newest': return new Date(b.created_at) - new Date(a.created_at);
      case 'oldest': return new Date(a.created_at) - new Date(b.created_at);
      case 'highest': return parseFloat(b.total_amount) - parseFloat(a.total_amount);
      case 'lowest': return parseFloat(a.total_amount) - parseFloat(b.total_amount);
      case 'alpha': return (a.transaction_code || '').localeCompare(b.transaction_code || '');
      default: return 0;
    }
  });
  
  return result;
});

const paginatedSales = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredSales.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() => Math.ceil(filteredSales.value.length / itemsPerPage));

const goToPage = (page) => {
  currentPage.value = page;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const prevPage = () => { if (currentPage.value > 1) goToPage(currentPage.value - 1); };
const nextPage = () => { if (currentPage.value < totalPages.value) goToPage(currentPage.value + 1); };

watch([searchQuery, statusFilter, sortBy], () => { currentPage.value = 1; });

const exportData = (type) => {
  window.location.href = `/api/admin/getHistory?export=${type}&start_date=${startDate.value}&end_date=${endDate.value}`;
};
</script>

<style scoped>
.mobile-card { position: relative; overflow: hidden; }

.status-filter-btn {
  padding: 0.4rem 0.85rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.6);
  white-space: nowrap;
  transition: all 0.2s;
  touch-action: manipulation;
}

.status-filter-btn.active {
  background: rgba(99, 102, 241, 0.2);
  border-color: rgba(99, 102, 241, 0.5);
  color: #818cf8;
}

.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
