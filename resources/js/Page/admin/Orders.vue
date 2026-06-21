<template>
  <AdminLayout>
    <div class="flex-1 flex flex-col space-y-3 md:space-y-8 min-h-0">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-2 md:mt-0">
        <div class="min-w-0">
          <h1 class="text-lg md:text-[2.5rem] font-extrabold tracking-tight bg-gradient-to-r from-white to-violet-400 bg-clip-text text-transparent leading-tight flex flex-wrap items-center gap-2 md:gap-4">
            Customer Orders
            <span class="px-2 py-0.5 md:px-3 md:py-1 bg-violet-500/20 text-violet-400 border border-violet-500/30 rounded-md md:rounded-xl text-[0.6rem] md:text-sm font-black tracking-widest uppercase">{{ orders.length }}</span>
          </h1>
          <p class="text-white/50 font-medium text-xs md:text-base truncate">Monitor and oversee seafood fulfillment operations.</p>
        </div>
      </div>

      <!-- Content Area -->
      <div class="flex-1 flex flex-col min-h-0">
        <!-- Desktop Table -->
        <GlassCard customClass="hidden md:flex overflow-hidden border-white/[0.08] !p-0 flex-1 flex flex-col min-h-0">
          <div class="overflow-x-auto overflow-y-auto max-h-[70vh] md:max-h-[calc(100vh-320px)] scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
            <table class="w-full text-left border-collapse">
              <thead class="sticky top-0 z-10 bg-[#1a1a1a]">
                <tr class="bg-white/[0.03] border-b border-white/[0.08]">
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Order Info</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Customer</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Payment</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Tracking</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Total</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Status</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/[0.05]">
                <tr v-for="order in orders" :key="order.id" class="hover:bg-white/[0.02] transition-colors group animate-slide-in-right">
                  <td class="px-8 py-6">
                    <div class="font-mono bg-black text-white px-3 py-1.5 rounded-lg border border-white/20 text-xs font-bold inline-block mb-2 shadow-lg">{{ order.transaction_code }}</div>
                    <div class="text-[10px] text-white/30 font-bold tracking-widest uppercase">{{ formatDate(order.created_at) }}</div>
                  </td>
                  <td class="px-8 py-6">
                    <div class="font-bold text-white text-lg">{{ order.customer_name || 'Walk-in' }}</div>
                    <div class="text-xs text-white/40">{{ order.item_count }} items recorded</div>
                  </td>
                  <td class="px-8 py-6">
                    <span class="px-3 py-1 bg-black text-white border border-white/30 rounded-lg text-[10px] font-black tracking-widest uppercase shadow-md">{{ order.payment_method || 'COD' }}</span>
                  </td>
                  <td class="px-8 py-6">
                    <div class="text-sm text-white/70 font-medium">{{ order.courier_name || '-' }}</div>
                    <div class="text-[10px] text-white/30 font-mono tracking-tighter">{{ order.tracking_number || 'NO-TRACKING-ID' }}</div>
                  </td>
                  <td class="px-8 py-6">
                    <span class="text-xl font-black text-emerald-400">₱{{ formatNumber(order.total_amount) }}</span>
                  </td>
                  <td class="px-8 py-6">
                    <div v-if="order.status === 'Cancelled'" class="flex items-center">
                      <span v-if="isCancelledByCustomer(order)" class="px-4 py-2 bg-red-600 text-white border-2 border-red-700 rounded-xl text-[0.7rem] font-black tracking-widest uppercase shadow-lg shadow-red-500/30 animate-pulse">⚠ CUSTOMER CANCELLED</span>
                      <span v-else class="px-4 py-2 bg-gray-600 text-white border-2 border-gray-700 rounded-xl text-[0.7rem] font-black tracking-widest uppercase">Cancelled by Admin</span>
                    </div>
                    <select v-else v-model="order.status" @change="updateStatus(order, $event.target.value)" class="bg-black text-white border-2 border-white rounded-xl px-4 py-2 text-[0.7rem] font-black tracking-widest uppercase cursor-pointer focus:outline-none transition-all hover:bg-white hover:text-black" :class="getStatusSelectClass(order.status)">
                      <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                    </select>
                  </td>
                  <td class="px-8 py-6 text-right">
                    <div class="flex justify-end items-center gap-3">
                      <button v-if="order.status === 'Shipped'" @click="cancelDamagedInTransit(order)" class="flex items-center gap-2 bg-rose-500/20 text-rose-400 border-2 border-rose-500/50 rounded-xl px-5 py-2 text-[0.7rem] font-black tracking-widest uppercase hover:bg-rose-500 hover:text-white transition-all shadow-lg active:scale-95"><Ban class="w-4 h-4" /> Damaged</button>
                      <button v-if="getNextAction(order.status)" @click="updateStatus(order, getNextAction(order.status).next)" class="flex items-center gap-2 bg-black text-white border-2 border-white rounded-xl px-5 py-2 text-[0.7rem] font-black tracking-widest uppercase hover:bg-white hover:text-black transition-all shadow-lg active:scale-95"><component :is="getNextAction(order.status).icon" class="w-4 h-4" /> {{ getNextAction(order.status).label }}</button>
                      <button @click="editTracking(order)" class="p-3 bg-white/[0.05] border border-white/[0.08] rounded-xl hover:bg-white hover:text-black transition-all group/btn shadow-md" title="Update Tracking"><Truck class="w-4 h-4 text-white/40 group-hover/btn:text-black" /></button>
                      <button @click="viewOrderDetails(order)" class="p-3 bg-white/[0.05] border border-white/[0.08] rounded-xl hover:bg-white hover:text-black transition-all group/btn shadow-md" title="View Items"><ReceiptText class="w-4 h-4 text-white/40 group-hover/btn:text-black" /></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </GlassCard>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-2.5 pb-4">
          <div v-for="order in orders" :key="order.id" class="relative bg-white/[0.04] rounded-xl border border-white/[0.08] overflow-hidden p-3 space-y-2">
            <!-- Row 1: Code + Amount -->
            <div class="flex items-center justify-between gap-2">
              <span class="font-mono text-violet-400 text-xs font-bold truncate min-w-0 flex-1">{{ order.transaction_code }}</span>
              <span class="text-emerald-400 text-sm font-black shrink-0">₱{{ formatNumber(order.total_amount) }}</span>
            </div>
            <!-- Row 2: Customer + Payment + Date -->
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="text-white text-xs font-semibold truncate">{{ order.customer_name || 'Walk-in' }}</span>
                <span class="text-white/30 text-[0.65rem] shrink-0">{{ order.item_count }} items</span>
              </div>
              <span class="px-1.5 py-0.5 bg-white/5 text-white/50 border border-white/10 rounded text-[0.6rem] font-bold uppercase shrink-0">{{ order.payment_method || 'COD' }}</span>
            </div>
            <!-- Row 3: Date -->
            <div class="text-[0.65rem] text-white/30">{{ formatDateShort(order.created_at) }}</div>
            <!-- Row 4: Tracking (if exists) -->
            <div v-if="order.tracking_number || order.courier_name" class="flex items-center gap-1.5 text-[0.65rem] text-white/30">
              <Truck class="w-3 h-3 shrink-0" />
              <span class="truncate">{{ order.courier_name || '-' }}</span>
              <span v-if="order.tracking_number" class="font-mono text-white/20 truncate">· {{ order.tracking_number }}</span>
            </div>
            <!-- Row 5: Status -->
            <div>
              <div v-if="order.status === 'Cancelled'" class="w-full">
                <span v-if="isCancelledByCustomer(order)" class="w-full block text-center px-2 py-1.5 bg-red-500/20 text-red-400 border border-red-500/30 rounded-lg text-xs font-bold uppercase">⚠ Customer Cancelled</span>
                <span v-else class="w-full block text-center px-2 py-1.5 bg-white/5 text-white/30 border border-white/10 rounded-lg text-xs font-bold uppercase">Cancelled by Admin</span>
              </div>
              <select v-else v-model="order.status" @change="updateStatus(order, $event.target.value)" class="w-full bg-black/40 text-white border border-white/15 rounded-lg px-2.5 py-2 text-xs font-bold uppercase focus:outline-none" :class="getStatusSelectClassMobile(order.status)">
                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
              </select>
            </div>
            <!-- Row 6: Actions -->
            <div class="flex items-center gap-2 pt-1">
              <button v-if="order.status === 'Shipped'" @click="cancelDamagedInTransit(order)" class="flex items-center gap-1 px-2.5 py-1.5 bg-rose-500/15 text-rose-400 border border-rose-500/30 rounded-lg text-xs font-bold uppercase active:scale-95 transition-all">
                <Ban class="w-3 h-3" /> Damaged
              </button>
              <button v-if="getNextAction(order.status)" @click="updateStatus(order, getNextAction(order.status).next)" class="flex items-center gap-1 px-2.5 py-1.5 bg-white/10 text-white border border-white/20 rounded-lg text-xs font-bold uppercase active:scale-95 transition-all">
                <component :is="getNextAction(order.status).icon" class="w-3 h-3" /> {{ getNextAction(order.status).label }}
              </button>
              <div class="flex-1"></div>
              <button @click="editTracking(order)" class="p-2 bg-white/5 border border-white/10 rounded-lg active:scale-95 transition-all" title="Tracking">
                <Truck class="w-4 h-4 text-white/40" />
              </button>
              <button @click="viewOrderDetails(order)" class="p-2 bg-white/5 border border-white/10 rounded-lg active:scale-95 transition-all" title="Details">
                <ReceiptText class="w-4 h-4 text-white/40" />
              </button>
            </div>
          </div>

          <div v-if="orders.length === 0" class="py-16 text-center">
            <div class="text-white/10 flex flex-col items-center gap-3">
              <Ghost class="w-8 h-8 opacity-5" />
              <p class="italic text-xs">No orders found.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Damage Modal -->
    <Teleport to="body">
      <div v-if="showDamageModal" class="!z-[999999] fixed inset-0 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="showDamageModal = false"></div>
        <div class="bg-[#11131e]/95 border border-white/[0.08] p-4 sm:p-6 rounded-xl shadow-2xl w-full max-w-[92%] sm:max-w-md text-white relative">
          <button @click="showDamageModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
          <h3 class="text-base sm:text-lg font-semibold mb-1">Damage Options</h3>
          <p class="text-xs sm:text-sm text-gray-400 mb-4">Order #<span class="break-all">{{ selectedOrder?.transaction_code }}</span></p>
          <div class="flex flex-col gap-2 sm:flex-row sm:justify-end sm:gap-3">
            <button @click="handleCancelNoRedelivery" class="w-full py-2.5 sm:py-2.5 sm:w-auto border border-white/20 hover:border-white/30 text-white font-medium px-4 rounded-lg transition-colors text-xs sm:text-sm">Cancel (No Redelivery)</button>
            <button @click="handleConfirmRedelivery" class="w-full py-2.5 sm:py-2.5 sm:w-auto bg-emerald-600 hover:bg-emerald-500 text-white font-medium px-4 rounded-lg transition-colors text-xs sm:text-sm">OK (Free Redelivery)</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Truck, ReceiptText, Ghost, Check, Truck as TruckIcon, CheckCheck, Ban } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';
import { runHeavyTaskWithoutBlockingUI } from '../../composables/usePerformance';

const orders = ref([]);
const statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
const showDamageModal = ref(false);
const selectedOrder = ref(null);

const formatNumber = (num) => parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleString('en-PH', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });
};

const formatDateShort = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getStatusSelectClass = (status) => {
  const s = status?.toLowerCase();
  if (s === 'completed') return 'border-emerald-500 text-emerald-500 hover:!bg-emerald-500 hover:!text-white';
  if (s === 'pending') return 'border-amber-500 text-amber-500 hover:!bg-amber-500 hover:!text-white';
  if (s === 'cancelled') return 'border-rose-500 text-rose-500 hover:!bg-rose-500 hover:!text-white';
  if (s === 'processing') return 'border-sky-500 text-sky-500 hover:!bg-sky-500 hover:!text-white';
  if (s === 'shipped') return 'border-indigo-500 text-indigo-500 hover:!bg-indigo-500 hover:!text-white';
  return 'border-white';
};

const getStatusSelectClassMobile = (status) => {
  const s = status?.toLowerCase();
  if (s === 'completed') return 'text-emerald-400 border-emerald-500/30';
  if (s === 'pending') return 'text-amber-400 border-amber-500/30';
  if (s === 'cancelled') return 'text-rose-400 border-rose-500/30';
  if (s === 'processing') return 'text-sky-400 border-sky-500/30';
  if (s === 'shipped') return 'text-indigo-400 border-indigo-500/30';
  return '';
};

const isCancelledByCustomer = (order) => {
  if (order.status !== 'Cancelled') return false;
  return (order.cancel_reason || '').toLowerCase().includes('customer');
};

const getNextAction = (status) => {
  if (status === 'Pending') return { next: 'Processing', label: 'Process', icon: Check };
  if (status === 'Processing') return { next: 'Shipped', label: 'Ship', icon: TruckIcon };
  if (status === 'Shipped') return { next: 'Completed', label: 'Complete', icon: CheckCheck };
  return null;
};

const fetchOrders = async () => {
  try {
    const response = await axios.get('/api/admin/orders');
    const data = response.data.data || response.data;
    orders.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Failed to fetch orders:', error);
    orders.value = [];
  }
};

const updateStatus = (order, newStatus) => {
  if (!confirm(`Transition order to ${newStatus}?`)) return;
  runHeavyTaskWithoutBlockingUI(async () => {
    try {
      const formData = new FormData();
      formData.append('id', order.id);
      formData.append('status', newStatus);
      if (window.CSRF_TOKEN_NAME) formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
      const response = await axios.post('/api/admin/orders/updateStatus', formData);
      if (response.data.status === 'success') {
        order.status = newStatus;
      } else {
        alert(response.data.message);
      }
    } catch (error) {
      console.error('Update status failed:', error);
    }
  });
};

const editTracking = (order) => {
  const tracking = prompt('Enter tracking number:', order.tracking_number || '');
  if (tracking === null) return;
  const courier = prompt('Enter courier name:', order.courier_name || '');
  if (courier === null) return;
  runHeavyTaskWithoutBlockingUI(() => {
    const formData = new FormData();
    formData.append('id', order.id);
    formData.append('tracking_number', tracking);
    formData.append('courier_name', courier);
    if (window.CSRF_TOKEN_NAME) formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    axios.post('/api/admin/orders/updateTracking', formData)
      .then(res => { if (res.data.status === 'success') { order.tracking_number = tracking; order.courier_name = courier; } })
      .catch(err => console.error(err));
  });
};

const viewOrderDetails = (order) => {
  alert(`Order ${order.transaction_code}\nCustomer: ${order.customer_name}\nTotal: ₱${formatNumber(order.total_amount)}`);
};

const cancelDamagedInTransit = (order) => {
  selectedOrder.value = order;
  showDamageModal.value = true;
};

const handleCancelNoRedelivery = () => {
  if (!selectedOrder.value) return;
  runHeavyTaskWithoutBlockingUI(async () => {
    try {
      const formData = new FormData();
      formData.append('id', selectedOrder.value.id);
      formData.append('issue_redelivery', '0');
      if (window.CSRF_TOKEN_NAME) formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
      const response = await axios.post('/api/admin/orders/cancelDamagedInTransit', formData);
      if (response.data.status === 'success') { alert(response.data.message); await fetchOrders(); }
      else { alert(response.data.message || 'Failed to cancel order'); }
    } catch (error) {
      console.error('Cancel damaged order failed:', error);
      alert(error.response?.data?.message || 'Failed to cancel order.');
    } finally {
      showDamageModal.value = false;
      selectedOrder.value = null;
    }
  });
};

const handleConfirmRedelivery = () => {
  if (!selectedOrder.value) return;
  runHeavyTaskWithoutBlockingUI(async () => {
    try {
      const formData = new FormData();
      formData.append('id', selectedOrder.value.id);
      formData.append('issue_redelivery', '1');
      if (window.CSRF_TOKEN_NAME) formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
      const response = await axios.post('/api/admin/orders/cancelDamagedInTransit', formData);
      if (response.data.status === 'success') { alert(response.data.message); await fetchOrders(); }
      else { alert(response.data.message || 'Failed to cancel order'); }
    } catch (error) {
      console.error('Cancel damaged order failed:', error);
      alert(error.response?.data?.message || 'Failed to cancel order.');
    } finally {
      showDamageModal.value = false;
      selectedOrder.value = null;
    }
  });
};

onMounted(fetchOrders);
</script>
