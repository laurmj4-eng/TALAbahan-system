<template>
  <CustomerLayout>
    <div class="space-y-6 pb-24">
      <div class="header-section">
        <h1 class="text-2xl md:text-5xl font-black tracking-tight text-white mb-1">Order Center 📦</h1>
        <p class="text-white/60 font-medium text-sm">Browse orders by status — just like a social commerce profile.</p>
      </div>

      <!-- Tabs -->
      <div class="flex flex-wrap gap-2 overflow-x-auto pb-2">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          class="px-3 py-1.5 md:px-6 md:py-3 rounded-xl md:rounded-2xl font-extrabold text-sm md:text-base transition-all flex items-center gap-2 md:gap-3 border-b-2 border active:scale-95"
          :class="activeTab === tab.id 
            ? 'bg-violet-600/20 text-cyan-400 border-violet-500/30 border-b-cyan-400 shadow-lg shadow-violet-500/10' 
            : 'bg-white/5 text-slate-400 border-white/[0.08] hover:bg-white/10 hover:text-slate-200'"
        >
          <span>{{ tab.label }}</span>
          <span class="px-1.5 py-0.5 bg-white/10 rounded-lg text-[0.6rem] md:text-[0.7rem]">{{ counts[tab.id] || 0 }}</span>
        </button>
      </div>

      <!-- Orders List -->
      <div v-if="filteredOrders.length > 0" class="space-y-3">
        <GlassCard 
          v-for="order in filteredOrders" 
          :key="order.id"
          customClass="p-5 md:p-6 border-white/[0.08] hover:border-violet-500/30 hover:bg-white/[0.04] transition-all group"
        >
          <!-- Damaged in Transit Alert -->
          <div 
            v-if="order.status === 'Cancelled' && order.cancel_reason === 'Damaged in transit'"
            class="mb-6 p-4 bg-rose-500/10 border-2 border-rose-500/30 rounded-xl flex items-start gap-4"
          >
            <div class="p-2 bg-rose-500/20 rounded-lg flex-shrink-0">
              <Ban class="w-5 h-5 text-rose-400" />
            </div>
            <div class="flex-1">
              <div class="font-bold text-rose-400 mb-1">Order Cancelled: Items Damaged in Transit</div>
              <div class="text-sm text-rose-300/70">
                Your order was reported damaged during delivery. A replacement batch or refund is being handled.
              </div>
            </div>
          </div>

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

            <div class="flex flex-col items-end gap-3">
              <div class="text-xl md:text-2xl font-black text-[#00ff88]">₱{{ formatNumber(order.total_amount) }}</div>
              <div class="flex flex-wrap items-center gap-2">
                <span 
                  class="px-3 py-1 md:px-4 md:py-1.5 rounded-lg md:rounded-xl text-[0.6rem] md:text-[0.7rem] font-black uppercase tracking-widest border"
                  :class="getStatusClass(order.status)"
                >
                  {{ order.status }}
                </span>
                
                <div class="flex gap-1.5">
                  <button @click="viewDetails(order)" class="p-2 md:p-3 bg-white/5 border border-white/[0.08] rounded-lg md:rounded-xl text-violet-400 hover:bg-violet-500/20 hover:border-violet-500/40 transition-all flex items-center gap-1.5 active:scale-95">
                    <Eye class="w-3.5 h-3.5 md:w-4 md:h-4" />
                    <span class="text-[0.6rem] md:text-xs font-bold uppercase tracking-widest">Details</span>
                  </button>
                  <div class="relative group">
                    <button 
                      v-if="order.status === 'Pending'" 
                      @click="(() => { if (!order.id) { console.error('Order missing ID field:', order); alert('Error: Order ID not found. Please refresh.'); } else { console.log('Quick cancel - Order object:', order); cancelOrder(order.id); } })()" 
                      class="p-2 md:p-3 bg-rose-500/5 border border-rose-500/10 rounded-lg md:rounded-xl text-rose-400 hover:bg-rose-500/20 hover:border-rose-500/40 transition-all active:scale-95"
                    >
                      <X class="w-3.5 h-3.5 md:w-4 md:h-4" />
                    </button>
                    <button 
                      v-else-if="['Processing', 'Shipped', 'Completed'].includes(order.status)"
                      disabled
                      class="p-2 md:p-3 bg-white/5 border border-white/[0.08] rounded-lg md:rounded-xl text-white/20 cursor-not-allowed"
                    >
                      <X class="w-3.5 h-3.5 md:w-4 md:h-4" />
                    </button>
                    <div 
                      v-if="['Processing', 'Shipped', 'Completed'].includes(order.status)"
                      class="absolute bottom-full mb-2 right-0 px-3 py-2 bg-black border border-white/20 rounded-lg text-xs text-white/80 whitespace-normal break-words max-w-xs opacity-0 group-hover:opacity-100 transition-opacity z-50"
                    >
                      Cooking has started. This order can no longer be cancelled.
                      <div class="absolute top-full right-2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-white/20"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </GlassCard>
      </div>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center py-20 md:py-32 space-y-4 md:space-y-6 opacity-20">
        <PackageOpen class="w-16 h-16 md:w-24 md:h-24" />
        <div class="text-center">
          <h2 class="text-xl md:text-2xl font-bold">No orders found</h2>
          <p class="font-medium text-sm">No orders match this category yet.</p>
        </div>
        <Link href="/customer/dashboard" class="px-5 py-2.5 md:px-8 md:py-3 bg-white text-violet-900 rounded-xl md:rounded-2xl font-black text-sm md:text-base hover:bg-violet-50 transition-all active:scale-95">
          Go to Shop
        </Link>
      </div>

      <!-- Details Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/80 " @click="closeModal"></div>
        <GlassCard customClass="relative w-full max-w-2xl p-5 md:p-8 border-white/20 shadow-2xl overflow-y-auto max-h-[90vh]">
          <div class="flex justify-between items-center mb-6 md:mb-8">
            <div>
              <h2 class="text-xl md:text-2xl font-bold text-white mb-1">Order {{ selectedOrder.transaction_code }}</h2>
              <p class="text-white/40 text-sm md:text-base font-medium">Stage: {{ selectedOrder.stage_label }} | Status: {{ selectedOrder.status }}</p>
            </div>
            <button @click="closeModal" class="text-white/40 hover:text-white transition-colors active:scale-90">
              <X class="w-5 h-5 md:w-6 md:h-6" />
            </button>
          </div>

          <div class="space-y-6 md:space-y-8">
            <!-- Items List -->
            <div class="space-y-3">
              <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between items-center py-3 md:py-4 border-b border-white/5 last:border-0">
                <div>
                  <div class="text-white font-bold text-sm md:text-base">{{ item.product_name }}</div>
                  <div class="text-[0.6rem] md:text-xs text-white/40 font-medium">{{ item.quantity }} {{ item.unit }} @ ₱{{ formatNumber(item.unit_price) }}</div>
                </div>
                <div class="text-[#00ff88] font-black text-sm md:text-base">₱{{ formatNumber(item.subtotal) }}</div>
              </div>
            </div>

            <!-- Total -->
            <div class="pt-4 md:pt-6 border-t border-white/[0.08] flex justify-between items-end">
              <div class="text-sm md:text-base text-white/40 font-bold uppercase tracking-widest">Total Amount</div>
              <div class="text-2xl md:text-3xl font-black text-[#00ff88]">₱{{ formatNumber(selectedOrder.total_amount) }}</div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-2 pt-3 md:pt-4">
              <button v-if="selectedOrder.can_pay_now" @click="payNow(selectedOrder.id)" class="px-4 py-2 md:px-6 md:py-3 bg-violet-600 hover:bg-violet-700 text-white rounded-xl font-bold flex items-center gap-2 transition-all text-sm active:scale-95">
                <CreditCard class="w-4 h-4" />
                <span>Pay Now</span>
              </button>
              <div class="relative group">
                <button 
                  v-if="selectedOrder.can_cancel && selectedOrder.status === 'Pending'" 
                  @click="cancelOrder(selectedOrder.id)" 
                  class="px-4 py-2 md:px-6 md:py-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500/20 rounded-xl font-bold transition-all text-sm active:scale-95"
                >
                  Cancel Order
                </button>
                <button 
                  v-else-if="['Processing', 'Shipped', 'Completed'].includes(selectedOrder.status)" 
                  disabled 
                  class="px-4 py-2 md:px-6 md:py-3 bg-white/5 border border-white/[0.08] text-white/30 rounded-xl font-bold cursor-not-allowed text-sm"
                >
                  Cancel Order
                </button>
                <div 
                  v-if="['Processing', 'Shipped', 'Completed'].includes(selectedOrder.status)"
                  class="absolute bottom-full mb-2 right-0 px-3 py-2 bg-black border border-white/20 rounded-lg text-xs text-white/80 whitespace-normal break-words max-w-xs opacity-0 group-hover:opacity-100 transition-opacity z-50"
                >
                  Cooking has started. This order can no longer be cancelled.
                  <div class="absolute top-full right-2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-white/20"></div>
                </div>
              </div>
              <button v-if="selectedOrder.can_track" @click="trackOrder(selectedOrder.id)" class="px-4 py-2 md:px-6 md:py-3 bg-sky-500/10 border border-sky-500/20 text-sky-400 hover:bg-sky-500/20 rounded-xl font-bold flex items-center gap-2 transition-all text-sm active:scale-95">
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
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Calendar, Wallet, Eye, X, PackageOpen, CreditCard, Truck, Ban } from 'lucide-vue-next';
import CustomerLayout from '../../layouts/CustomerLayout.vue';
import GlassCard from '../../components/GlassCard.vue';
import { runHeavyTaskWithoutBlockingUI } from '../../composables/usePerformance';

const activeTab = ref('all');
const orders = ref([]);
const counts = ref({ all: 0, to_pay: 0, to_ship: 0, to_receive: 0, completed: 0, cancelled: 0 });
const showModal = ref(false);
const selectedOrder = ref(null);

const tabs = [
  { id: 'all', label: 'All' },
  { id: 'to_pay', label: 'To Pay' },
  { id: 'to_ship', label: 'To Ship' },
  { id: 'to_receive', label: 'To Receive' },
  { id: 'completed', label: 'Completed' },
  { id: 'cancelled', label: 'Cancelled' }
];

const filteredOrders = computed(() => {
  if (activeTab.value === 'all') return orders.value;
  return orders.value.filter(o => {
    const status = o.status.toLowerCase();
    if (activeTab.value === 'to_pay') return status === 'pending';
    if (activeTab.value === 'to_ship') return status === 'processing';
    if (activeTab.value === 'to_receive') return status === 'shipped';
    if (activeTab.value === 'completed') return status === 'completed';
    if (activeTab.value === 'cancelled') return status === 'cancelled';
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
  return 'bg-white/5 text-white/40 border-white/[0.08]';
};

const viewDetails = async (order) => {
  try {
    const response = await axios.get(`/api/customer/order-details/${order.id}`);
    if (response.data.status === 'success') {
      selectedOrder.value = {
        ...order,
        ...response.data.data,
        id: order.id, // Preserve the original order ID
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

const cancelOrder = (orderId) => {
  if (!confirm('Are you sure you want to cancel this order?')) return;
  
  const actualId = parseInt(orderId, 10);
  console.log('Attempting to cancel order ID:', orderId, 'Parsed:', actualId, 'Type:', typeof orderId);
  
  if (!actualId || actualId === 0 || isNaN(actualId)) {
    alert('Error: Invalid order ID. Order ID must be a valid number. Please refresh the page and try again.');
    console.error('Cancel request aborted: orderId is invalid', { original: orderId, parsed: actualId });
    return;
  }
  
  runHeavyTaskWithoutBlockingUI(async () => {
    try {
      const response = await axios.post('/api/customer/cancel-order', { 
        id: actualId,
        [window.CSRF_TOKEN_NAME]: window.CSRF_HASH 
      });
      if (response.data.status === 'success') {
        alert('Order cancelled successfully!');
        await fetchOrders();
        closeModal();
        if (response.data.token) window.CSRF_HASH = response.data.token;
      } else {
        alert(`Cancellation failed: ${response.data.message || 'Unknown error'}`);
        console.error('Cancel error response:', response.data);
      }
    } catch (error) {
      console.error('Cancel failed:', error);
      alert(`Error: ${error.response?.data?.message || error.message || 'Connection error'}`);
    }
  });
};

const payNow = (id) => {
  runHeavyTaskWithoutBlockingUI(async () => {
    try {
      const response = await axios.post('/api/customer/pay-now', { 
        id,
        [window.CSRF_TOKEN_NAME]: window.CSRF_HASH 
      });
      if (response.data.status === 'success') {
        alert('Payment successful!');
        await fetchOrders();
        closeModal();
        if (response.data.token) window.CSRF_HASH = response.data.token;
      }
    } catch (error) {
      alert(error.response?.data?.message || 'Payment failed');
    }
  });
};

const trackOrder = async (id) => {
  try {
    const response = await axios.get(`/api/customer/tracking/${id}`);
    if (response.data.status === 'success') {
      const tracking = response.data.data;
      alert(`Tracking Number: ${tracking.tracking_number || 'Pending'}\nCourier: ${tracking.courier_name || 'Processing'}`);
    }
  } catch (error) {
    console.error('Tracking failed:', error);
  }
};

onMounted(fetchOrders);
</script>
