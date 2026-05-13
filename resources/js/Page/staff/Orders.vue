<template>
  <div class="p-6 md:p-8 space-y-8">
    <div class="header flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight bg-gradient-to-r from-white to-violet-400 bg-clip-text text-transparent">Order Fulfillment</h1>
        <p class="text-white/50 font-medium mt-2">Manage and process your fresh seafood deliveries.</p>
      </div>
      <router-link to="/staff/dashboard" class="px-6 py-3 bg-white/5 border border-white/10 rounded-2xl font-bold text-white hover:bg-white/10 transition-all flex items-center gap-2">
        <ChevronLeft class="w-5 h-5" />
        <span>Dashboard</span>
      </router-link>
    </div>

    <GlassCard customClass="overflow-hidden border-white/10">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-separate border-spacing-y-2 px-4">
          <thead>
            <tr class="text-[0.7rem] font-black text-white/40 uppercase tracking-[0.15em]">
              <th class="px-6 py-4">Transaction</th>
              <th class="px-6 py-4">Customer</th>
              <th class="px-6 py-4 text-center">Payment</th>
              <th class="px-6 py-4 text-right">Amount</th>
              <th class="px-6 py-4 text-center">Status</th>
              <th class="px-6 py-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in orders" :key="order.id" class="bg-white/[0.03] hover:bg-white/[0.06] transition-all group">
              <td class="px-6 py-5 rounded-l-2xl">
                <span class="font-mono font-bold text-indigo-400 bg-indigo-500/10 px-3 py-1 rounded-lg border border-indigo-500/20">{{ order.transaction_code }}</span>
              </td>
              <td class="px-6 py-5">
                <div class="font-bold text-white">{{ order.customer_name || 'Walk-in' }}</div>
                <div class="text-[0.7rem] text-white/30 font-medium mt-1">{{ formatDate(order.created_at) }}</div>
              </td>
              <td class="px-6 py-5 text-center">
                <span class="px-3 py-1 bg-slate-900 border border-white/10 rounded-lg text-[0.65rem] font-black uppercase tracking-widest text-white/60">
                  {{ order.payment_method }}
                </span>
              </td>
              <td class="px-6 py-5 text-right font-black text-emerald-400">
                ₱{{ formatNumber(order.total_amount) }}
              </td>
              <td class="px-6 py-5 text-center">
                <span 
                  class="px-4 py-1.5 rounded-xl text-[0.7rem] font-black uppercase tracking-widest border"
                  :class="getStatusClass(order.status)"
                >
                  {{ order.status }}
                </span>
              </td>
              <td class="px-6 py-5 text-right rounded-r-2xl">
                <div class="flex justify-end gap-2">
                  <button v-if="getNextStatus(order.status)" @click="updateStatus(order.id, getNextStatus(order.status))" class="px-4 py-2 bg-white text-slate-950 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-50 transition-all flex items-center gap-2">
                    <component :is="getStatusIcon(order.status)" class="w-3.5 h-3.5" />
                    <span>{{ getStatusActionLabel(order.status) }}</span>
                  </button>
                  <button @click="viewDetails(order)" class="w-10 h-10 bg-white/5 border border-white/10 rounded-xl flex items-center justify-center text-white/60 hover:text-white hover:bg-white/10 transition-all">
                    <Eye class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="orders.length === 0">
              <td colspan="6" class="px-6 py-32 text-center">
                <div class="flex flex-col items-center gap-4 opacity-10">
                  <Loader2 v-if="loading" class="w-12 h-12 animate-spin" />
                  <PackageOpen v-else class="w-16 h-16" />
                  <p class="font-bold text-lg italic">{{ loading ? 'Loading orders...' : 'No orders found.' }}</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </GlassCard>

    <!-- Details Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" @click="closeModal"></div>
      <GlassCard customClass="relative w-full max-w-2xl p-8 border-white/20 shadow-2xl overflow-y-auto max-h-[90vh]">
        <div class="flex justify-between items-center mb-8 border-b border-white/10 pb-6">
          <h2 class="text-3xl font-black text-white tracking-tight">Order Details</h2>
          <button @click="closeModal" class="text-white/40 hover:text-white transition-colors">
            <X class="w-7 h-7" />
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div class="p-6 bg-white/5 border border-white/10 rounded-2xl">
            <div class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest mb-2">Customer</div>
            <div class="text-xl font-bold text-white">{{ selectedOrder.customer_name || 'Walk-in Customer' }}</div>
            <div class="text-sm text-white/40 font-medium mt-1">{{ formatDate(selectedOrder.created_at) }}</div>
          </div>
          <div class="p-6 bg-white/5 border border-white/10 rounded-2xl">
            <div class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest mb-2">Total Amount</div>
            <div class="text-3xl font-black text-emerald-400">₱{{ formatNumber(selectedOrder.total_amount) }}</div>
          </div>
        </div>

        <div class="overflow-x-auto mb-8">
          <table class="w-full text-left">
            <thead>
              <tr class="text-[0.65rem] font-black text-white/30 uppercase tracking-widest border-b border-white/5">
                <th class="py-4">Product</th>
                <th class="py-4 text-right">Price</th>
                <th class="py-4 text-center">Qty</th>
                <th class="py-4 text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="item in selectedOrder.items" :key="item.id">
                <td class="py-4 font-bold text-white">{{ item.product_name }}</td>
                <td class="py-4 text-right text-white/60">₱{{ formatNumber(item.unit_price) }}</td>
                <td class="py-4 text-center text-white/40">{{ item.quantity }} {{ item.unit }}</td>
                <td class="py-4 text-right font-bold text-white">₱{{ formatNumber(item.subtotal) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-white/10">
          <button @click="closeModal" class="px-8 py-3 bg-white/5 border border-white/10 rounded-xl font-bold text-white hover:bg-white/10 transition-all">
            Close
          </button>
        </div>
      </GlassCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ChevronLeft, Eye, X, PackageOpen, Loader2, Flame, Truck, CheckCheck } from 'lucide-vue-next';
import GlassCard from '../../components/GlassCard.vue';

const orders = ref([]);
const loading = ref(true);
const showModal = ref(false);
const selectedOrder = ref(null);

const fetchOrders = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/staff/getOrders');
    if (response.data.status === 'success') {
      orders.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to fetch orders:', error);
  } finally {
    loading.value = false;
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
  return 'bg-white/5 text-white/40 border-white/10';
};

const getNextStatus = (status) => {
  if (status === 'Pending') return 'Processing';
  if (status === 'Processing') return 'Shipped';
  if (status === 'Shipped') return 'Completed';
  return null;
};

const getStatusIcon = (status) => {
  if (status === 'Pending') return Flame;
  if (status === 'Processing') return Truck;
  if (status === 'Shipped') return CheckCheck;
  return null;
};

const getStatusActionLabel = (status) => {
  if (status === 'Pending') return 'Process';
  if (status === 'Processing') return 'Ship';
  if (status === 'Shipped') return 'Complete';
  return '';
};

const updateStatus = async (id, newStatus) => {
  try {
    const response = await axios.post('/api/staff/updateOrderStatus', {
      id,
      status: newStatus,
      [window.CSRF_TOKEN_NAME]: window.CSRF_HASH
    });
    if (response.data.status === 'success') {
      await fetchOrders();
      if (response.data.token) window.CSRF_HASH = response.data.token;
    }
  } catch (error) {
    console.error('Update status failed:', error);
  }
};

const viewDetails = (order) => {
  selectedOrder.value = order;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedOrder.value = null;
};

onMounted(fetchOrders);
</script>
