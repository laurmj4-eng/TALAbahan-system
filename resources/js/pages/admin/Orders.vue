<template>
  <AdminLayout>
    <div class="flex-1 flex flex-col space-y-8 min-h-0">
      <!-- Header -->
      <div class="flex justify-between items-end">
        <div>
          <h1 class="text-[2.5rem] font-extrabold tracking-tight bg-gradient-to-r from-white to-violet-400 bg-clip-text text-transparent leading-tight flex items-center gap-4">
            Customer Orders
            <span class="px-3 py-1 bg-violet-500/20 text-violet-400 border border-violet-500/30 rounded-xl text-sm font-black tracking-widest uppercase">Order</span>
          </h1>
          <p class="text-white/50 font-medium">Monitor and oversee seafood fulfillment operations.</p>
        </div>
      </div>

      <!-- Table -->
      <GlassCard customClass="overflow-hidden border-white/[0.08] !p-0 flex-1 flex flex-col min-h-0">
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
              <tr v-for="order in orders" :key="order.id" class="hover:bg-white/[0.02] transition-colors group">
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
                    <button @click="viewOrderDetails(order)" class="p-3 bg-white/[0.05] border border-white/10 rounded-xl hover:bg-white hover:text-black transition-all group/btn shadow-md" title="View Items">
                      <ReceiptText class="w-4 h-4 text-white/40 group-hover/btn:text-black" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="orders.length === 0">
                <td colspan="7" class="px-8 py-32 text-center">
                  <div class="text-white/10 flex flex-col items-center gap-4">
                    <Ghost class="w-16 h-16 opacity-5" />
                    <p class="italic text-lg">No orders found in fulfillment queue.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </GlassCard>
    </div>

    <!-- Modal logic remains the same but with enhanced glass styling... -->
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { 
  Truck, ReceiptText, Ghost, Check, Truck as TruckIcon, CheckCheck
} from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const orders = ref([]);
const statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];

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
    const response = await axios.get('/api/admin/orders');
    orders.value = response.data.data || response.data;
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

    const response = await axios.post('/api/admin/orders/updateStatus', formData);
    if (response.data.status === 'success') {
      order.status = newStatus;
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Update status failed:', error);
  }
};

onMounted(fetchOrders);
</script>
