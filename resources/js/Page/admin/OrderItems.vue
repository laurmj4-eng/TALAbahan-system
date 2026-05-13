<template>
  <AdminLayout>
    <div class="space-y-8">
      <div>
        <h1 class="text-3xl font-bold text-white">Order Items 🧾</h1>
        <p class="text-white/60">Line-by-line product receipts from customer orders.</p>
      </div>

      <GlassCard customClass="overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-white/5 border-b border-white/10">
                <th class="px-6 py-4 font-semibold text-white/70">TXN CODE</th>
                <th class="px-6 py-4 font-semibold text-white/70">CUSTOMER</th>
                <th class="px-6 py-4 font-semibold text-white/70">PRODUCT</th>
                <th class="px-6 py-4 font-semibold text-white/70 text-center">QTY</th>
                <th class="px-6 py-4 font-semibold text-white/70">UNIT PRICE</th>
                <th class="px-6 py-4 font-semibold text-white/70">SUBTOTAL</th>
                <th class="px-6 py-4 font-semibold text-white/70">STATUS</th>
                <th class="px-6 py-4 font-semibold text-white/70">DATE</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="item in orderItems" :key="item.id" class="hover:bg-white/5 transition-colors group">
                <td class="px-6 py-4">
                  <span class="font-bold text-indigo-400">{{ item.transaction_code || '-' }}</span>
                </td>
                <td class="px-6 py-4 text-white/80">
                  {{ item.customer_name || 'Walk-in Customer' }}
                </td>
                <td class="px-6 py-4 text-white/80">
                  {{ item.product_name || '-' }}
                </td>
                <td class="px-6 py-4 text-center text-white/80">
                  {{ item.quantity || 0 }} {{ item.unit || '' }}
                </td>
                <td class="px-6 py-4 font-mono text-white/80">
                  ₱{{ formatNumber(item.unit_price) }}
                </td>
                <td class="px-6 py-4 font-mono font-bold text-white">
                  ₱{{ formatNumber(item.subtotal) }}
                </td>
                <td class="px-6 py-4">
                  <span 
                    class="px-2 py-1 rounded text-[10px] font-black tracking-widest uppercase"
                    :class="getStatusClass(item.status)"
                  >
                    {{ item.status || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-white/40">
                  {{ formatDate(item.created_at) }}
                </td>
              </tr>
              <tr v-if="orderItems.length === 0">
                <td colspan="8" class="px-6 py-12 text-center text-white/20 italic">
                  No order line items recorded yet.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </GlassCard>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const orderItems = ref([]);

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

const getStatusClass = (status) => {
  const s = status?.toLowerCase();
  if (s === 'completed') return 'bg-green-500/20 text-green-400 border border-green-500/30';
  if (s === 'pending') return 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
  if (s === 'cancelled') return 'bg-red-500/20 text-red-400 border border-red-500/30';
  return 'bg-white/5 text-white/40 border border-white/10';
};

const fetchOrderItems = async () => {
  try {
    // Note: Adjust API endpoint as needed
    const response = await axios.get('/api/admin/orders/items');
    orderItems.value = response.data.data || response.data;
  } catch (error) {
    console.error('Failed to fetch order items:', error);
  }
};

onMounted(() => {
  fetchOrderItems();
});
</script>
