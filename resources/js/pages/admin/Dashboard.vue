<template>
  <AdminLayout>
    <div class="space-y-10">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start gap-6">
        <div>
          <h1 class="text-3xl md:text-[3rem] font-extrabold tracking-tight bg-gradient-to-r from-white to-violet-400 bg-clip-text text-transparent leading-tight mb-2">
            Welcome back, {{ username }}!
          </h1>
          <p class="text-sm md:text-[1.1rem] font-medium text-white/50 mb-1">
            Manage products, monitor sales, and track order activity in one clean owner dashboard.
          </p>
          <p class="text-[0.8rem] text-white/40">Server Time: <span class="font-mono">{{ serverTime }}</span></p>
        </div>
        <button @click="printReport" class="flex items-center gap-3 px-6 py-3 bg-white/[0.05] backdrop-blur-[16px] border border-white/10 rounded-2xl hover:bg-white/10 hover:border-white/20 transition-all group shadow-lg shadow-indigo-500/20">
          <Printer class="w-5 h-5 text-blue-400 group-hover:scale-110 transition-transform" />
          <span class="font-bold">Print Daily Report</span>
        </button>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Today's Sales -->
        <div class="group p-[30px] rounded-[24px] border border-white/10 bg-slate-900/40 backdrop-blur-xl transition-all duration-400 hover:scale-[1.02] hover:border-emerald-500/40 hover:bg-slate-900/60 hover:shadow-indigo-500/20 overflow-hidden relative opacity-0 animate-fade-in-up delay-100">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.05] to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
          <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 text-xl mb-5">
            <Coins />
          </div>
          <div class="text-[0.9rem] font-bold text-slate-400 uppercase tracking-widest mb-2">Today's Sales</div>
          <div class="text-[2.5rem] font-black bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent leading-none tracking-tight mb-2">
            ₱{{ formatNumber(cards.today_sales) }}
            <span v-if="cards.sales_growth !== undefined" :class="cards.sales_growth >= 0 ? 'text-emerald-400' : 'text-rose-400'" class="text-sm font-semibold ml-2">
              {{ cards.sales_growth >= 0 ? '↑' : '↓' }} {{ Math.abs(cards.sales_growth) }}%
            </span>
          </div>
          <div class="text-[0.8rem] text-slate-500">Final total for today</div>
        </div>

        <!-- Net Profit -->
        <div class="group p-[30px] rounded-[24px] border border-white/10 bg-slate-900/40 backdrop-blur-xl transition-all duration-400 hover:scale-[1.02] hover:border-violet-500/40 hover:bg-slate-900/60 hover:shadow-indigo-500/20 overflow-hidden relative opacity-0 animate-fade-in-up delay-200">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.05] to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
          <div class="w-12 h-12 rounded-xl bg-violet-500/10 flex items-center justify-center text-violet-500 text-xl mb-5">
            <PieChart />
          </div>
          <div class="text-[0.9rem] font-bold text-slate-400 uppercase tracking-widest mb-2">Net Profit</div>
          <div class="text-[2.5rem] font-black bg-gradient-to-r from-violet-400 to-indigo-400 bg-clip-text text-transparent leading-none tracking-tight mb-2">
            ₱{{ formatNumber(cards.today_profit) }}
          </div>
          <div class="text-[0.8rem] text-slate-500">Earnings after cost</div>
        </div>

        <!-- Profit Margin -->
        <div class="group p-[30px] rounded-[24px] border border-white/10 bg-slate-900/40 backdrop-blur-xl transition-all duration-400 hover:scale-[1.02] hover:border-amber-500/40 hover:bg-slate-900/60 hover:shadow-indigo-500/20 overflow-hidden relative opacity-0 animate-fade-in-up delay-300">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.05] to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
          <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-500 text-xl mb-5">
            <Percent />
          </div>
          <div class="text-[0.9rem] font-bold text-slate-400 uppercase tracking-widest mb-2">Profit Margin</div>
          <div class="text-[2.5rem] font-black bg-gradient-to-r from-amber-400 to-orange-400 bg-clip-text text-transparent leading-none tracking-tight mb-2">
            {{ cards.profit_margin }}%
          </div>
          <div class="text-[0.8rem] text-slate-500">Profit to sales ratio</div>
        </div>

        <!-- Today's Orders -->
        <div class="group p-[30px] rounded-[24px] border border-white/10 bg-slate-900/40 backdrop-blur-xl transition-all duration-400 hover:scale-[1.02] hover:border-blue-500/40 hover:bg-slate-900/60 hover:shadow-indigo-500/20 overflow-hidden relative opacity-0 animate-fade-in-up delay-400">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.05] to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
          <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 text-xl mb-5">
            <ShoppingCart />
          </div>
          <div class="text-[0.9rem] font-bold text-slate-400 uppercase tracking-widest mb-2">Today's Orders</div>
          <div class="text-[2.5rem] font-black bg-gradient-to-r from-blue-400 to-sky-400 bg-clip-text text-transparent leading-none tracking-tight mb-2">
            {{ cards.today_orders }}
          </div>
          <div class="text-[0.8rem] text-slate-500">Transactions processed</div>
        </div>
      </div>

      <!-- Alerts -->
      <div v-if="cards.stale_orders_count > 0" class="flex items-center justify-between p-6 bg-orange-500/10 backdrop-blur-xl border border-orange-500/20 rounded-2xl">
        <div class="flex items-center gap-5">
          <AlertTriangle class="w-8 h-8 text-orange-400" />
          <div>
            <h4 class="font-extrabold text-orange-400 text-lg">Action Required: {{ cards.stale_orders_count }} Stale Orders</h4>
            <p class="text-sm text-slate-400">Some orders have been pending for more than 24 hours. Please review and update their status.</p>
          </div>
        </div>
        <router-link to="/admin/orders" class="px-6 py-2.5 bg-orange-500 text-black text-sm font-extrabold rounded-xl hover:bg-orange-400 transition-all active:scale-95 shadow-lg shadow-orange-500/20">
          View Orders
        </router-link>
      </div>

      <!-- Performance Overview -->
      <div class="space-y-6">
        <div class="flex items-center gap-3">
          <LineChart class="text-violet-500 w-6 h-6" />
          <h3 class="text-xl font-bold text-white tracking-tight">Performance Overview</h3>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Chart -->
          <GlassCard customClass="lg:col-span-2 p-[30px] rounded-[24px]">
            <div class="mb-8">
              <h2 class="text-2xl font-bold text-white mb-2">7-Day Sales Trend</h2>
              <p class="text-white/40 text-sm">Connected to order history for live owner insights.</p>
            </div>
            <div class="h-[350px] relative">
              <canvas ref="chartCanvas"></canvas>
            </div>
          </GlassCard>

          <!-- Top Products -->
          <div class="p-[30px] rounded-[24px] bg-white/[0.03] border border-white/10 backdrop-blur-[16px]">
            <div class="flex items-center gap-3 mb-8">
              <Trophy class="text-amber-500 w-5 h-5" />
              <h4 class="text-lg font-bold text-indigo-300">Top Products (30d)</h4>
            </div>
            <div v-if="topProducts.length" class="space-y-6">
              <div v-for="(product, index) in topProducts" :key="index" class="flex items-center justify-between group">
                <div class="flex items-center gap-4">
                  <span class="text-[0.8rem] font-extrabold text-white/20 group-hover:text-white/40 transition-colors">#{{ index + 1 }}</span>
                  <span class="font-bold text-white/90 group-hover:text-white transition-colors">{{ product.product_name }}</span>
                </div>
                <span class="text-[0.85rem] font-bold text-emerald-400">{{ product.total_sold }} sold</span>
              </div>
            </div>
            <div v-else class="text-center py-12 text-white/20 italic text-sm">
              No sales data yet.
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="space-y-6">
        <div class="flex items-center gap-3">
          <Zap class="text-violet-500 w-6 h-6" />
          <h3 class="text-xl font-bold text-white tracking-tight">Quick Management</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
          <router-link v-for="action in quickActions" :key="action.name" :to="action.path" class="group p-6 rounded-[24px] bg-white/[0.02] border border-white/10 backdrop-blur-[16px] flex flex-col items-center justify-center gap-4 transition-all duration-300 hover:-translate-y-2 hover:bg-violet-500/10 hover:border-violet-500/30 hover:shadow-[0_15px_30px_rgba(0,0,0,0.3)]">
            <component :is="action.icon" :class="action.color" class="w-8 h-8 group-hover:scale-110 group-hover:-rotate-6 transition-all duration-300" />
            <span class="font-bold text-[1.1rem] tracking-tight group-hover:text-violet-400 transition-colors">{{ action.name }}</span>
          </router-link>
        </div>
      </div>

      <!-- Activity Feed -->
      <div class="space-y-6">
        <div class="flex items-center gap-3">
          <Activity class="text-violet-500 w-6 h-6" />
          <h3 class="text-xl font-bold text-white tracking-tight">Live Activity Feed</h3>
        </div>
        <GlassCard customClass="p-[25px] rounded-[24px]">
          <div v-if="activities.length" class="space-y-4">
            <div v-for="(act, index) in activities" :key="index" class="flex items-center justify-between pb-4 border-b border-white/[0.05] last:border-0 last:pb-0 group">
              <div class="flex items-center gap-5">
                <div :style="{ backgroundColor: act.color + '20', color: act.color }" class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-transform group-hover:scale-110">
                  <i :class="['fas', act.icon]"></i>
                </div>
                <div>
                  <h5 class="font-bold text-[0.95rem] text-white group-hover:text-violet-300 transition-colors">{{ act.title }}</h5>
                  <p class="text-[0.85rem] text-white/50">{{ act.desc }}</p>
                </div>
              </div>
              <div class="text-right">
                <span class="text-[0.75rem] text-white/30 font-bold uppercase tracking-widest">{{ formatTime(act.time) }}</span>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-12 text-white/20">
            <Ghost class="w-12 h-12 mx-auto mb-4 opacity-10" />
            <p class="italic">No recent activity recorded.</p>
          </div>
        </GlassCard>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';
import { 
  Printer, Coins, PieChart, Percent, ShoppingCart, 
  AlertTriangle, Trophy, Zap, Activity, Ghost,
  Fish, FileText, Users, Boxes, LineChart
} from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const username = ref('Admin');
const serverTime = ref('');
const cards = ref({
  today_sales: 0,
  today_profit: 0,
  profit_margin: 0,
  today_orders: 0,
  sales_growth: 0,
  stale_orders_count: 0
});
const topProducts = ref([]);
const activities = ref([]);
const chartCanvas = ref(null);
let chartInstance = null;

const quickActions = [
  { name: 'Seafood POS', path: '/admin/pos', icon: ShoppingCart, color: 'text-violet-400' },
  { name: 'Inventory', path: '/admin/products', icon: Fish, color: 'text-blue-400' },
  { name: 'Sales Ledger', path: '/admin/sales', icon: FileText, color: 'text-emerald-400' },
  { name: 'Orders', path: '/admin/orders', icon: Boxes, color: 'text-indigo-400' },
  { name: 'Users', path: '/admin/users', icon: Users, color: 'text-amber-400' },
];

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatTime = (timeStr) => {
  const date = new Date(timeStr);
  return date.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true });
};

const printReport = () => {
  window.print();
};

const initChart = (data) => {
  if (chartInstance) chartInstance.destroy();
  if (!chartCanvas.value) return;

  const ctx = chartCanvas.value.getContext('2d');
  const isAllZero = data.sales.every(val => val === 0);

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.labels,
      datasets: [{
        label: 'Sales Revenue',
        data: data.sales,
        borderColor: isAllZero ? 'rgba(255,255,255,0.1)' : '#10b981',
        backgroundColor: isAllZero ? 'transparent' : 'rgba(16, 185, 129, 0.15)',
        fill: true,
        tension: 0.4,
        pointRadius: isAllZero ? 0 : 4,
        pointHoverRadius: 6,
        borderWidth: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(15, 23, 42, 0.9)',
          titleFont: { size: 14, weight: 'bold' },
          bodyFont: { size: 13 },
          padding: 12,
          cornerRadius: 12,
          callbacks: {
            label: (context) => `₱${formatNumber(context.parsed.y)}`
          }
        }
      },
      scales: {
        x: { 
          grid: { color: 'rgba(255,255,255,0.05)' }, 
          ticks: { color: 'rgba(255,255,255,0.4)', font: { weight: '600' } } 
        },
        y: { 
          beginAtZero: true,
          grid: { color: 'rgba(255,255,255,0.05)' },
          ticks: { 
            color: 'rgba(255,255,255,0.4)',
            font: { weight: '600' },
            callback: (val) => '₱' + val
          }
        }
      }
    }
  });
};

const fetchData = async () => {
  try {
    const response = await axios.get('/api/admin/dashboard/data');
    const data = response.data || {};
    
    username.value = data.username || 'Admin';
    serverTime.value = data.server_time || '';
    cards.value = data.cards || {
      today_sales: 0,
      today_profit: 0,
      profit_margin: 0,
      today_orders: 0,
      sales_growth: 0,
      stale_orders_count: 0
    };
    topProducts.value = data.top_products || [];
    activities.value = data.activities || [];
    
    if (data.chart) {
      initChart(data.chart);
    }
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error);
  }
};

let interval;
onMounted(() => {
  fetchData();
  interval = setInterval(fetchData, 30000);
});

onUnmounted(() => {
  clearInterval(interval);
});
</script>

<style scoped>
@media print {
  :deep(aside), :deep(header), button, .Zap, .Zap + h3, .grid-cols-5 {
    display: none !important;
  }
  
  :deep(main) {
    padding: 0 !important;
    margin: 0 !important;
    background: white !important;
  }

  .text-white { color: black !important; }
  .bg-gradient-to-r { background: none !important; -webkit-text-fill-color: black !important; color: black !important; }
  .text-white\/50, .text-white\/40, .text-white\/30 { color: #666 !important; }
  
  .glass-card, [class*="border-white/"] { 
    background: white !important; 
    border: 2px solid #000 !important;
    box-shadow: none !important;
    color: black !important;
  }

  .text-emerald-500, .text-violet-500, .text-amber-500, .text-blue-500 {
    color: black !important;
  }

  .rounded-\[24px\] { border-radius: 0 !important; }
}
</style>
