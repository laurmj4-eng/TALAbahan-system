<template>
  <StaffLayout :username="username">
    <div class="flex-1 flex flex-col space-y-4 md:space-y-8 min-h-0">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 md:mt-0">
        <div>
          <h1 class="text-2xl md:text-[2.5rem] font-extrabold tracking-tight bg-gradient-to-r from-white to-violet-400 bg-clip-text text-transparent leading-tight flex flex-wrap items-center gap-2 md:gap-4">
            Order Fulfillment
            <span class="px-2 py-0.5 md:px-3 md:py-1 bg-violet-500/20 text-violet-400 border border-violet-500/30 rounded-lg md:rounded-xl text-[10px] md:text-sm font-black tracking-widest uppercase">Staff</span>
          </h1>
          <p class="text-white/50 font-medium text-xs md:text-base">Manage and process your fresh seafood deliveries.</p>
        </div>
        <Link href="/staff/dashboard" class="px-6 py-3 bg-white/5 border border-white/10 rounded-2xl font-bold text-white hover:bg-white/10 transition-all flex items-center gap-2">
          <ChevronLeft class="w-5 h-5" />
          <span>Dashboard</span>
        </Link>
      </div>

      <!-- Content Area -->
      <div class="flex-1 flex flex-col min-h-0">
        <!-- Desktop Table (Hidden on Mobile) -->
        <GlassCard customClass="hidden md:flex overflow-hidden border-white/[0.08] !p-0 flex-1 flex flex-col min-h-0">
          <div class="overflow-x-auto overflow-y-auto max-h-[70vh] md:max-h-[calc(100vh-320px)] scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
            <table class="w-full text-left border-collapse">
              <thead class="sticky top-0 z-10 bg-[#1a1a1a] backdrop-blur-md">
                <tr class="bg-white/[0.03] border-b border-white/10">
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
                <tr v-for="order in localOrders" :key="order.id" class="hover:bg-white/[0.02] transition-colors group animate-slide-in-right">
                  <td class="px-8 py-6">
                    <div class="font-mono bg-black text-white px-3 py-1.5 rounded-lg border border-white/20 text-xs font-bold inline-block mb-2 shadow-lg">
                      {{ order.transaction_code }}
                    </div>
                    <div class="text-[10px] text-white/30 font-bold tracking-widest uppercase">
                      {{ formatDate(order.created_at) }}
                    </div>
                  </td>
                  <td class="px-8 py-6">
                    <div class="font-bold text-white text-lg">{{ order.customer_name || 'Walk-in' }}</div>
                    <div class="text-xs text-white/40">{{ order.item_count }} items recorded</div>
                  </td>
                  <td class="px-8 py-6">
                    <span class="px-3 py-1 bg-black text-white border border-white/30 rounded-lg text-[10px] font-black tracking-widest uppercase shadow-md">
                      {{ order.payment_method || 'COD' }}
                    </span>
                  </td>
                  <td class="px-8 py-6">
                    <div class="text-sm text-white/70 font-medium">{{ order.courier_name || '-' }}</div>
                    <div class="text-[10px] text-white/30 font-mono tracking-tighter">{{ order.tracking_number || 'NO-TRACKING-ID' }}</div>
                  </td>
                  <td class="px-8 py-6">
                    <span class="text-xl font-black text-emerald-400">₱{{ formatNumber(order.total_amount) }}</span>
                  </td>
                  <td class="px-8 py-6">
                    <select 
                      v-model="order.status" 
                      @change="updateStatus(order, $event.target.value)"
                      class="bg-black text-white border-2 border-white rounded-xl px-4 py-2 text-[0.7rem] font-black tracking-widest uppercase cursor-pointer focus:outline-none transition-all hover:bg-white hover:text-black"
                      :class="getStatusSelectClass(order.status)"
                    >
                      <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                    </select>
                  </td>
                  <td class="px-8 py-6 text-right">
                    <div class="flex justify-end items-center gap-3">
                      <button 
                        v-if="getNextAction(order.status)"
                        @click="updateStatus(order, getNextAction(order.status).next)"
                        class="flex items-center gap-2 bg-black text-white border-2 border-white rounded-xl px-5 py-2 text-[0.7rem] font-black tracking-widest uppercase hover:bg-white hover:text-black transition-all shadow-lg active:scale-95"
                      >
                        <component :is="getNextAction(order.status).icon" class="w-4 h-4" />
                        {{ getNextAction(order.status).label }}
                      </button>
                      <button @click="editTracking(order)" class="p-3 bg-white/[0.05] border border-white/10 rounded-xl hover:bg-white hover:text-black transition-all group/btn shadow-md" title="Update Tracking">
                        <Truck class="w-4 h-4 text-white/40 group-hover/btn:text-black" />
                      </button>
                      <button @click="viewDetails(order)" class="p-3 bg-white/[0.05] border border-white/10 rounded-xl hover:bg-white hover:text-black transition-all group/btn shadow-md" title="View Items">
                        <ReceiptText class="w-4 h-4 text-white/40 group-hover/btn:text-black" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </GlassCard>

        <!-- Mobile Card View (Visible on Mobile Only) -->
        <div class="md:hidden space-y-4 pb-20">
          <div v-for="order in localOrders" :key="order.id" class="p-4 rounded-2xl bg-white/[0.03] border border-white/10 backdrop-blur-md space-y-4">
            <div class="flex justify-between items-start">
              <div>
                <div class="font-mono bg-black text-white px-2 py-1 rounded-lg border border-white/20 text-[10px] font-bold inline-block mb-1 shadow-lg">
                  {{ order.transaction_code }}
                </div>
                <div class="text-[9px] text-white/30 font-bold tracking-widest uppercase">
                  {{ formatDate(order.created_at) }}
                </div>
              </div>
              <span class="text-lg font-black text-emerald-400">₱{{ formatNumber(order.total_amount) }}</span>
            </div>

            <div class="flex justify-between items-center">
              <div>
                <div class="font-bold text-white text-base">{{ order.customer_name || 'Walk-in' }}</div>
                <div class="text-[10px] text-white/40">{{ order.item_count }} items recorded</div>
              </div>
              <span class="px-2 py-0.5 bg-black text-white border border-white/30 rounded-lg text-[9px] font-black tracking-widest uppercase">
                {{ order.payment_method || 'COD' }}
              </span>
            </div>

            <div class="p-3 bg-white/[0.02] rounded-xl border border-white/5 space-y-1">
              <div class="text-[10px] text-white/40 font-bold uppercase tracking-widest">Tracking Info</div>
              <div class="flex justify-between items-center">
                <span class="text-xs text-white/70">{{ order.courier_name || 'No courier' }}</span>
                <span class="text-[9px] text-white/30 font-mono">{{ order.tracking_number || 'NO-TRACKING' }}</span>
              </div>
            </div>

            <div class="flex flex-col gap-3">
              <select 
                v-model="order.status" 
                @change="updateStatus(order, $event.target.value)"
                class="w-full bg-black text-white border-2 border-white rounded-xl px-4 py-2.5 text-[0.7rem] font-black tracking-widest uppercase focus:outline-none"
                :class="getStatusSelectClass(order.status)"
              >
                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
              </select>

              <div class="flex gap-2">
                <button 
                  v-if="getNextAction(order.status)"
                  @click="updateStatus(order, getNextAction(order.status).next)"
                  class="flex-1 flex items-center justify-center gap-2 bg-white text-black border-2 border-white rounded-xl px-4 py-2.5 text-[0.7rem] font-black tracking-widest uppercase active:scale-95 transition-all"
                >
                  <component :is="getNextAction(order.status).icon" class="w-4 h-4" />
                  {{ getNextAction(order.status).label }}
                </button>
                <button @click="editTracking(order)" class="p-3 bg-white/[0.05] border border-white/10 rounded-xl active:scale-95 transition-all">
                  <Truck class="w-4 h-4 text-white/60" />
                </button>
                <button @click="viewDetails(order)" class="p-3 bg-white/[0.05] border border-white/10 rounded-xl active:scale-95 transition-all">
                  <ReceiptText class="w-4 h-4 text-white/60" />
                </button>
              </div>
            </div>
          </div>

          <div v-if="localOrders.length === 0" class="py-20 text-center">
            <div class="text-white/10 flex flex-col items-center gap-4">
              <PackageOpen class="w-12 h-12 opacity-5" />
              <p class="italic text-base">No orders found.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

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

        <div v-if="selectedOrder.items && selectedOrder.items.length > 0" class="overflow-x-auto mb-8">
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
        <div v-else class="py-12 text-center text-white/20 font-medium italic">
          No items found for this order.
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-white/10">
          <button @click="closeModal" class="px-8 py-3 bg-white/5 border border-white/10 rounded-xl font-bold text-white hover:bg-white/10 transition-all">
            Close
          </button>
        </div>
      </GlassCard>
    </div>
  </StaffLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { 
  ChevronLeft, Eye, X, PackageOpen, Check, Truck as TruckIcon, CheckCheck, Truck, ReceiptText
} from 'lucide-vue-next';
import GlassCard from '../../components/GlassCard.vue';
import StaffLayout from '../../layouts/StaffLayout.vue';

const props = defineProps({
  username: String,
  orders: Array
});

const localOrders = ref([...props.orders]);
const statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
const showModal = ref(false);
const selectedOrder = ref(null);

watch(() => props.orders, (newOrders) => {
  localOrders.value = [...newOrders];
}, { deep: true });

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleString('en-PH', { 
    month: 'short', 
    day: '2-digit', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });
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

const getNextAction = (status) => {
  if (status === 'Pending') return { next: 'Processing', label: 'Process', icon: Check };
  if (status === 'Processing') return { next: 'Shipped', label: 'Ship', icon: TruckIcon };
  if (status === 'Shipped') return { next: 'Completed', label: 'Complete', icon: CheckCheck };
  return null;
};

const fetchOrders = async () => {
  try {
    const response = await axios.get('/api/staff/getOrders');
    const data = response.data.data || response.data;
    localOrders.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Failed to fetch orders:', error);
  }
};

const updateStatus = async (order, newStatus) => {
  if (!confirm(`Transition order to ${newStatus}?`)) return;

  try {
    const formData = new FormData();
    formData.append('id', order.id);
    formData.append('status', newStatus);
    
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    const response = await axios.post('/api/staff/updateOrderStatus', formData);
    if (response.data.status === 'success') {
      order.status = newStatus;
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Update status failed:', error);
  }
};

const editTracking = async (order) => {
  const tracking = prompt('Enter tracking number:', order.tracking_number || '');
  if (tracking === null) return;
  
  const courier = prompt('Enter courier name:', order.courier_name || '');
  if (courier === null) return;

  try {
    const formData = new FormData();
    formData.append('id', order.id);
    formData.append('tracking_number', tracking);
    formData.append('courier_name', courier);
    
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    const response = await axios.post('/api/staff/updateTracking', formData);
    if (response.data.status === 'success') {
      order.tracking_number = tracking;
      order.courier_name = courier;
      // If status changed to Shipped automatically by the backend
      if (order.status === 'Processing' && (tracking || courier)) {
        order.status = 'Shipped';
      }
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Tracking update failed:', error);
  }
};

const viewDetails = async (order) => {
  selectedOrder.value = { ...order, items: [] };
  showModal.value = true;
  
  try {
    const response = await axios.get(`/api/staff/getOrderDetail/${order.id}`);
    if (response.data.status === 'success') {
      selectedOrder.value.items = response.data.data.items || [];
    }
  } catch (error) {
    console.error('Failed to fetch order details:', error);
  }
};

const closeModal = () => {
  showModal.value = false;
  selectedOrder.value = null;
};

onMounted(() => {
  // fetchOrders(); // Initial data comes from props, but can refresh if needed
});
</script>
