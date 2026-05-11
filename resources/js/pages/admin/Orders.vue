<template>
  <AdminLayout>
    <div class="flex-1 flex flex-col space-y-4 md:space-y-8 min-h-0">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 md:mt-0">
        <div>
          <h1 class="text-2xl md:text-[2.5rem] font-extrabold tracking-tight bg-gradient-to-r from-white to-violet-400 bg-clip-text text-transparent leading-tight flex flex-wrap items-center gap-2 md:gap-4">
            Customer Orders
            <span class="px-2 py-0.5 md:px-3 md:py-1 bg-violet-500/20 text-violet-400 border border-violet-500/30 rounded-lg md:rounded-xl text-[10px] md:text-sm font-black tracking-widest uppercase">Order</span>
          </h1>
          <p class="text-white/50 font-medium text-xs md:text-base">Monitor and oversee seafood fulfillment operations.</p>
        </div>
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
                <tr v-for="order in orders" :key="order.id" class="hover:bg-white/[0.02] transition-colors group animate-slide-in-right">
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
              </tbody>
            </table>
          </div>
        </GlassCard>

        <!-- Mobile Card View (Visible on Mobile Only) -->
        <div class="md:hidden space-y-4 pb-20">
          <div v-for="order in orders" :key="order.id" class="p-4 rounded-2xl bg-white/[0.03] border border-white/10 backdrop-blur-md space-y-4">
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
                <button @click="viewOrderDetails(order)" class="p-3 bg-white/[0.05] border border-white/10 rounded-xl active:scale-95 transition-all">
                  <ReceiptText class="w-4 h-4 text-white/60" />
                </button>
              </div>
            </div>
          </div>

          <div v-if="orders.length === 0" class="py-20 text-center">
            <div class="text-white/10 flex flex-col items-center gap-4">
              <Ghost class="w-12 h-12 opacity-5" />
              <p class="italic text-base">No orders found.</p>
            </div>
          </div>
        </div>
      </div>
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
    const data = response.data.data || response.data;
    orders.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Failed to fetch orders:', error);
    orders.value = [];
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

const editTracking = (order) => {
  const tracking = prompt('Enter tracking number:', order.tracking_number || '');
  if (tracking === null) return;
  const courier = prompt('Enter courier name:', order.courier_name || '');
  if (courier === null) return;

  const formData = new FormData();
  formData.append('id', order.id);
  formData.append('tracking_number', tracking);
  formData.append('courier_name', courier);
  if (window.CSRF_TOKEN_NAME) formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);

  axios.post('/api/admin/orders/updateTracking', formData)
    .then(res => {
      if (res.data.status === 'success') {
        order.tracking_number = tracking;
        order.courier_name = courier;
      }
    })
    .catch(err => console.error(err));
};

const viewOrderDetails = (order) => {
  // For now, let's just alert the items count or simple info
  // You might want to implement a proper modal later
  alert(`Order ${order.transaction_code}\nCustomer: ${order.customer_name}\nTotal: ₱${formatNumber(order.total_amount)}`);
};

onMounted(fetchOrders);
</script>
