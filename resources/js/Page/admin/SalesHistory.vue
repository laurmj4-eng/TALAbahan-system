<template>
  <AdminLayout :username="username">
    <div class="space-y-3 md:space-y-8">
      <!-- Header -->
      <div class="overflow-hidden">
        <h1 class="text-lg md:text-3xl font-bold text-white truncate">Financial Ledger 📈</h1>
        <p class="text-white/60 text-[0.65rem] md:text-base truncate">Real-time transaction logs and revenue records.</p>
      </div>

      <!-- Daily Summary Card -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-6">
        <GlassCard customClass="p-2.5 md:p-6 border-l-4 border-l-emerald-500">
          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0 flex-1">
              <p class="text-white/40 text-[0.55rem] md:text-xs font-black uppercase tracking-wider mb-0.5">Revenue Today</p>
              <h3 class="text-base md:text-3xl font-black text-white truncate">₱{{ formatNumber(totalRevenueToday) }}</h3>
            </div>
            <div class="bg-emerald-500/20 p-1.5 md:p-3 rounded-lg shrink-0">
              <TrendingUp class="w-3.5 h-3.5 md:w-6 md:h-6 text-emerald-400" />
            </div>
          </div>
        </GlassCard>
      </div>

      <!-- Search & Filter Bar -->
      <div class="bg-white/5 p-2.5 md:p-6 rounded-xl md:rounded-2xl border border-white/[0.08] space-y-2.5 md:space-y-4">
        <!-- Search + Export Row -->
        <div class="flex gap-2">
          <div class="relative flex-1 min-w-0">
            <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-white/40" />
            <input 
              v-model="searchQuery"
              type="text" 
              id="transaction-search"
              placeholder="Search..." 
              class="w-full pl-8 pr-2 py-1.5 bg-black/30 border border-white/[0.08] rounded-lg text-xs text-white focus:outline-none focus:border-indigo-500/50"
            >
          </div>
          <div class="relative group shrink-0">
            <button class="flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[0.65rem] font-bold transition-all">
              <Download class="w-3 h-3" /> Export
            </button>
            <div class="absolute right-0 mt-2 w-40 bg-gray-900 border border-white/[0.08] rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
              <button @click="exportData('csv')" class="w-full text-left px-3 py-2 text-[0.65rem] text-white hover:bg-white/10 flex items-center gap-2 transition-colors active:scale-[0.98] touch-manipulation">
                <FileText class="w-3 h-3 text-emerald-400" /> CSV
              </button>
              <button @click="exportData('pdf')" class="w-full text-left px-3 py-2 text-[0.65rem] text-white hover:bg-white/10 flex items-center gap-2 transition-colors active:scale-[0.98] touch-manipulation">
                <FileDown class="w-3 h-3 text-rose-400" /> PDF
              </button>
              <button @click="exportData('word')" class="w-full text-left px-3 py-2 text-[0.65rem] text-white hover:bg-white/10 flex items-center gap-2 transition-colors active:scale-[0.98] touch-manipulation">
                <FileCode class="w-3 h-3 text-blue-400" /> Word
              </button>
            </div>
          </div>
        </div>

        <!-- Status Filter (Mobile) -->
        <div class="flex gap-1.5 overflow-x-auto no-scrollbar pb-0.5 md:hidden">
          <button
            v-for="opt in statusOptions"
            :key="opt.value"
            @click="statusFilter = opt.value"
            :class="['status-filter-btn', { active: statusFilter === opt.value }]"
          >
            {{ opt.label }}
          </button>
        </div>

        <!-- Sort + Date (Mobile) -->
        <div class="flex gap-2 md:hidden">
          <select 
            v-model="sortBy" 
            class="flex-1 min-w-0 bg-black/30 border border-white/[0.08] rounded-lg px-2 py-1.5 text-[0.65rem] text-white focus:outline-none focus:border-indigo-500/50"
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
      <GlassCard customClass="overflow-hidden responsive-table-to-cards">
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
                    <div class="absolute bottom-full left-0 mb-2 w-64 p-3 bg-gray-900 border border-white/[0.08] rounded-xl shadow-2xl opacity-0 invisible group-hover/items:opacity-100 group-hover/items:visible transition-all z-50">
                      <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-2 border-b border-white/[0.08] pb-1">Items List</p>
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

        <!-- Mobile Cards -->
        <div class="md:hidden p-2 space-y-2">
          <div v-if="filteredSales.length === 0" class="py-16 text-center text-white/20 italic text-xs">
            {{ isLoading ? 'Loading...' : 'No transactions found.' }}
          </div>
          <div
            v-for="record in paginatedSales"
            :key="'card-' + record.id"
            @click="toggleCard(record.id)"
            class="mobile-card relative bg-white/[0.03] rounded-lg p-2.5 border border-white/[0.08] transition-all duration-200 active:scale-[0.98] cursor-pointer overflow-hidden"
            :class="{ 'is-expanded': expandedCards.has(record.id) }"
          >
            <!-- Row 1: Code + Revenue + Status -->
            <div class="flex items-center justify-between gap-2 mb-1">
              <span class="text-violet-400 text-[0.7rem] font-bold truncate min-w-0 flex-1">{{ record.transaction_code }}</span>
              <span class="text-emerald-400 text-sm font-black shrink-0">₱{{ formatNumber(record.total_amount) }}</span>
            </div>
            <!-- Row 2: Status + Date -->
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-1.5 min-w-0 flex-1">
                <span class="px-1.5 py-px rounded-full text-[0.5rem] font-bold uppercase shrink-0"
                  :class="getStatusClass(record.status || 'completed')">
                  {{ record.status || 'Completed' }}
                </span>
                <span class="text-[0.5rem] text-white/30 truncate">{{ formatDateShort(record.created_at) }}</span>
              </div>
              <span class="text-white/20 text-xs shrink-0" :class="{ '!text-indigo-400': expandedCards.has(record.id) }">›</span>
            </div>

            <!-- Expanded Content -->
            <div v-if="expandedCards.has(record.id)" class="mt-2 pt-2 border-t border-white/[0.08] space-y-1.5">
              <!-- Customer -->
              <div class="bg-indigo-500/10 rounded-lg p-1.5">
                <span class="text-[0.5rem] font-bold uppercase text-white/40 block">Customer</span>
                <p class="text-[0.7rem] text-indigo-300 font-semibold leading-tight">{{ getCustomerDisplay(record) }}</p>
                <span v-if="record.user_id" class="text-[0.45rem] text-indigo-400 font-bold uppercase">Registered</span>
                <span v-else-if="record.customer_alias" class="text-[0.45rem] text-emerald-400 font-bold uppercase">Walk-in</span>
                <span v-else class="text-[0.45rem] text-white/30 font-bold uppercase">Walk-in</span>
              </div>
              <!-- Items -->
              <div>
                <span class="text-[0.5rem] font-bold uppercase text-white/40 block">Items ({{ record.items_summary.split(',').length }})</span>
                <ul class="mt-0.5 space-y-px">
                  <li v-for="(item, idx) in record.items_summary.split(',')" :key="idx" class="text-[0.6rem] text-white/60 flex items-start gap-1 leading-tight">
                    <span class="text-indigo-400 shrink-0 mt-px">•</span>
                    <span class="break-all min-w-0">{{ item.trim() }}</span>
                  </li>
                </ul>
              </div>
              <!-- Actions -->
              <div class="flex gap-1.5 pt-1.5 border-t border-white/[0.08]">
                <button class="flex-1 px-2 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-[0.6rem] font-bold transition-colors active:scale-95">
                  Edit
                </button>
                <button class="flex-1 px-2 py-1 bg-rose-600/20 hover:bg-rose-600/40 text-rose-400 rounded text-[0.6rem] font-bold transition-colors border border-rose-500/30 active:scale-95">
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
          class="px-3 py-1.5 bg-white/5 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed text-white rounded-lg text-[0.65rem] font-bold transition-all border border-white/[0.08] active:scale-95 touch-manipulation"
        >
          ‹ Prev
        </button>
        <span class="text-white/50 text-[0.65rem] font-bold">{{ currentPage }}/{{ totalPages }}</span>
        <button 
          @click="nextPage" 
          :disabled="currentPage === totalPages"
          class="px-3 py-1.5 bg-white/5 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed text-white rounded-lg text-[0.65rem] font-bold transition-all border border-white/[0.08] active:scale-95 touch-manipulation"
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
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatDateShort = (dateStr) => {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getStatusClass = (status) => {
  const classes = {
    'completed': 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
    'paid': 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
    'pending': 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
    'processing': 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
    'cancelled': 'bg-rose-500/20 text-rose-400 border border-rose-500/30',
    'refunded': 'bg-purple-500/20 text-purple-400 border border-purple-500/30'
  };
  return classes[status?.toLowerCase()] || 'bg-white/10 text-white/60 border border-white/[0.08]';
};

const fetchSales = () => {
  isLoading.value = true;
  runHeavyTaskWithoutBlockingUI(async () => {
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
  });
};

const filteredSales = computed(() => {
  let result = localSales.value;
  
  if (statusFilter.value !== 'all') {
    result = result.filter(s => 
      (s.status || 'completed').toLowerCase() === statusFilter.value
    );
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
      case 'newest':
        return new Date(b.created_at) - new Date(a.created_at);
      case 'oldest':
        return new Date(a.created_at) - new Date(b.created_at);
      case 'highest':
        return parseFloat(b.total_amount) - parseFloat(a.total_amount);
      case 'lowest':
        return parseFloat(a.total_amount) - parseFloat(b.total_amount);
      case 'alpha':
        return (a.transaction_code || '').localeCompare(b.transaction_code || '');
      default:
        return 0;
    }
  });
  
  return result;
});

const paginatedSales = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredSales.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredSales.value.length / itemsPerPage));

const goToPage = (page) => {
  currentPage.value = page;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const prevPage = () => {
  if (currentPage.value > 1) goToPage(currentPage.value - 1);
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) goToPage(currentPage.value + 1);
};

watch([searchQuery, statusFilter, sortBy], () => {
  currentPage.value = 1;
});

const exportData = (type) => {
  window.location.href = `/api/admin/getHistory?export=${type}&start_date=${startDate.value}&end_date=${endDate.value}`;
};
</script>

<style scoped>
@media (max-width: 768px) {
  .responsive-table-to-cards {
    position: relative;
  }
}

.mobile-card {
  position: relative;
  overflow: hidden;
}

.status-filter-btn {
  padding: 0.25rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.55rem;
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
