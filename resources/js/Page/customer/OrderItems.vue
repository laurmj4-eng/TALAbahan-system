<template>
  <CustomerLayout>
    <div class="space-y-8 pb-20">
      <div class="header-section">
        <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white mb-2">Order Center 📦</h1>
        <p class="text-white/60 font-medium">Browse orders by status — just like a social commerce profile.</p>
      </div>

      <!-- Tabs -->
      <div class="flex flex-wrap gap-3 overflow-x-auto pb-2">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          class="px-6 py-3 rounded-2xl font-extrabold text-sm transition-all flex items-center gap-3 border"
          :class="activeTab === tab.id 
            ? 'bg-violet-600/20 text-white border-violet-500/30 shadow-lg shadow-violet-500/10' 
            : 'bg-white/5 text-white/60 border-white/10 hover:bg-white/10 hover:text-white'"
        >
          <span>{{ tab.label }}</span>
          <span class="px-2 py-0.5 bg-white/10 rounded-lg text-[0.7rem]">{{ counts[tab.id] || 0 }}</span>
        </button>
      </div>

      <!-- Orders List -->
      <div v-if="filteredOrders.length > 0" class="space-y-4">
        <GlassCard 
          v-for="order in filteredOrders" 
          :key="order.id"
          customClass="p-6 border-white/10 hover:border-violet-500/30 hover:bg-white/[0.04] transition-all group"
        >
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1 space-y-2">
              <h3 class="text-xl font-black text-white tracking-wider font-mono">{{ order.transaction_code }}</h3>
              <div class="flex flex-wrap gap-4 text-sm text-white/40 font-medium">
                <span class="flex items-center gap-2">
                  <Calendar class="w-4 h-4" />
                  {{ formatDate(order.created_at) }}
                </span>
                <span class="flex items-center gap-2">
                  <Wallet class="w-4 h-4" />
                  {{ order.payment_method }}
                </span>
              </div>
            </div>

            <div class="flex flex-col items-end gap-4">
              <div class="text-2xl font-black text-[#00ff88]">₱{{ formatNumber(order.total_amount) }}</div>
              <div class="flex flex-wrap items-center gap-3">
                <span 
                  class="px-4 py-1.5 rounded-xl text-[0.7rem] font-black uppercase tracking-widest border"
                  :class="getStatusClass(order.status)"
                >
                  {{ order.status }}
                </span>
                
                <div class="flex gap-2">
                  <button @click="viewDetails(order)" class="p-3 bg-white/5 border border-white/10 rounded-xl text-violet-400 hover:bg-violet-500/20 hover:border-violet-500/40 transition-all flex items-center gap-2">
                    <Eye class="w-4 h-4" />
                    <span class="text-xs font-bold uppercase tracking-widest">Details</span>
                  </button>
                  <button v-if="order.status === 'Processing'" @click="cancelOrder(order.id)" class="p-3 bg-rose-500/5 border border-rose-500/10 rounded-xl text-rose-400 hover:bg-rose-500/20 hover:border-rose-500/40 transition-all">
                    <X class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </GlassCard>
      </div>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center py-32 space-y-6 opacity-20">
        <PackageOpen class="w-24 h-24" />
        <div class="text-center">
          <h2 class="text-2xl font-bold">No orders found</h2>
          <p class="font-medium">No orders match this category yet.</p>
        </div>
        <router-link to="/customer/dashboard" class="px-8 py-3 bg-white text-violet-900 rounded-2xl font-black text-sm hover:bg-violet-50 transition-all">
          Go to Shop
        </router-link>
      </div>

      <!-- Details Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" @click="closeModal"></div>
        <GlassCard customClass="relative w-full max-w-2xl p-8 border-white/20 shadow-2xl overflow-y-auto max-h-[90vh]">
          <div class="flex justify-between items-center mb-8">
            <div>
              <h2 class="text-2xl font-bold text-white mb-1">Order {{ selectedOrder.transaction_code }}</h2>
              <p class="text-white/40 text-sm font-medium">Stage: {{ selectedOrder.stage_label }} | Status: {{ selectedOrder.status }}</p>
            </div>
            <button @click="closeModal" class="text-white/40 hover:text-white transition-colors">
              <X class="w-6 h-6" />
            </button>
          </div>

          <div class="space-y-8">
            <!-- Items List -->
            <div class="space-y-4">
              <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between items-center py-4 border-b border-white/5 last:border-0">
                <div>
                  <div class="text-white font-bold">{{ item.product_name }}</div>
                  <div class="text-xs text-white/40 font-medium">{{ item.quantity }} {{ item.unit }} @ ₱{{ formatNumber(item.unit_price) }}</div>
                </div>
                <div class="text-[#00ff88] font-black">₱{{ formatNumber(item.subtotal) }}</div>
              </div>
            </div>

            <!-- Total -->
            <div class="pt-6 border-t border-white/10 flex justify-between items-end">
              <div class="text-sm text-white/40 font-bold uppercase tracking-widest">Total Amount</div>
              <div class="text-3xl font-black text-[#00ff88]">₱{{ formatNumber(selectedOrder.total_amount) }}</div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-3 pt-4">
              <button v-if="selectedOrder.can_pay_now" @click="payNow(selectedOrder.id)" class="px-6 py-3 bg-violet-600 hover:bg-violet-700 text-white rounded-xl font-bold flex items-center gap-2 transition-all">
                <CreditCard class="w-4 h-4" />
                <span>Pay Now</span>
              </button>
              <button v-if="selectedOrder.can_cancel" @click="cancelOrder(selectedOrder.id)" class="px-6 py-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500/20 rounded-xl font-bold transition-all">
                Cancel Order
              </button>
              <button v-if="selectedOrder.can_track" @click="trackOrder(selectedOrder.id)" class="px-6 py-3 bg-sky-500/10 border border-sky-500/20 text-sky-400 hover:bg-sky-500/20 rounded-xl font-bold flex items-center gap-2 transition-all">
                <Truck class="w-4 h-4" />
                <span>Track</span>
              </button>
            </div>
          </div>
        </GlassCard>
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { Calendar, Wallet, Eye, X, PackageOpen, CreditCard, Truck } from 'lucide-vue-next';
import CustomerLayout from '../../layouts/CustomerLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const activeTab = ref('all');
const orders = ref([]);
const counts = ref({ all: 0, to_pay: 0, to_ship: 0, to_receive: 0, completed: 0 });
const showModal = ref(false);
const selectedOrder = ref(null);

const tabs = [
  { id: 'all', label: 'All' },
  { id: 'to_pay', label: 'To Pay' },
  { id: 'to_ship', label: 'To Ship' },
  { id: 'to_receive', label: 'To Receive' },
  { id: 'completed', label: 'Completed' }
];

const filteredOrders = computed(() => {
  if (activeTab.value === 'all') return orders.value;
  return orders.value.filter(o => {
    const status = o.status.toLowerCase();
    if (activeTab.value === 'to_pay') return status === 'pending';
    if (activeTab.value === 'to_ship') return status === 'processing';
    if (activeTab.value === 'to_receive') return status === 'shipped';
    if (activeTab.value === 'completed') return status === 'completed';
    return true;
  });
});

const fetchOrders = async () => {
  try {
    const response = await axios.get('/api/customer/order-center/data'); // Assuming this endpoint exists or needs to be created
    if (response.data.status === 'success') {
      orders.value = response.data.orders;
      counts.value = response.data.counts;
    }
  } catch (error) {
    console.error('Failed to fetch orders:', error);
  }
};

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

const getStatusClass = (status) => {
  const s = status.toLowerCase();
  if (s === 'pending') return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
  if (s === 'processing') return 'bg-orange-500/10 text-orange-400 border-orange-500/20';
  if (s === 'shipped') return 'bg-sky-500/10 text-sky-400 border-sky-500/20';
  if (s === 'completed') return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
  if (s === 'cancelled') return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
  return 'bg-white/5 text-white/40 border-white/10';
};

const viewDetails = async (order) => {
  try {
    const response = await axios.get(`/api/customer/order-details/${order.id}`);
    if (response.data.status === 'success') {
      selectedOrder.value = {
        ...order,
        ...response.data.data,
        stage_label: response.data.data.lifecycle?.stage_key || 'Order',
        can_pay_now: response.data.data.lifecycle?.actions?.can_pay_now,
        can_cancel: response.data.data.lifecycle?.actions?.can_cancel,
        can_track: response.data.data.lifecycle?.actions?.can_track
      };
      showModal.value = true;
    }
  } catch (error) {
    console.error('Failed to fetch order details:', error);
  }
};

const closeModal = () => {
  showModal.value = false;
  selectedOrder.value = null;
};

const cancelOrder = async (id) => {
  if (!confirm('Are you sure you want to cancel this order?')) return;
  try {
    const response = await axios.post('/api/customer/cancel-order', { 
      id,
      [window.CSRF_TOKEN_NAME]: window.CSRF_HASH 
    });
    if (response.data.status === 'success') {
      await fetchOrders();
      if (response.data.token) window.CSRF_HASH = response.data.token;
    }
  } catch (error) {
    console.error('Cancel failed:', error);
  }
};

onMounted(fetchOrders);
</script>
