<template>
  <AdminLayout>
    <div class="space-y-8">
      <div class="flex justify-between items-end">
        <div>
          <h1 class="text-3xl font-bold text-white">Voucher Management</h1>
          <p class="text-white/60 mt-2">Create and toggle checkout vouchers without manual SQL.</p>
        </div>
        <button @click="isAddModalOpen = true" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 transition-all">
          <Plus class="w-4 h-4 inline-block mr-2" /> Add Voucher
        </button>
      </div>

      <GlassCard customClass="overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-white/5 border-b border-white/10">
                <th class="px-6 py-4 font-semibold text-white/70">CODE</th>
                <th class="px-6 py-4 font-semibold text-white/70">NAME</th>
                <th class="px-6 py-4 font-semibold text-white/70">SCOPE</th>
                <th class="px-6 py-4 font-semibold text-white/70">DISCOUNT</th>
                <th class="px-6 py-4 font-semibold text-white/70">MINIMUM</th>
                <th class="px-6 py-4 font-semibold text-white/70">PAYMENT LIMIT</th>
                <th class="px-6 py-4 font-semibold text-white/70">STATUS</th>
                <th class="px-6 py-4 font-semibold text-white/70 text-right">ACTION</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="voucher in vouchers" :key="voucher.id" class="hover:bg-white/5 transition-colors group">
                <td class="px-6 py-4">
                  <strong class="text-white font-black tracking-widest">{{ voucher.code }}</strong>
                </td>
                <td class="px-6 py-4 text-white/80">
                  {{ voucher.name }}
                </td>
                <td class="px-6 py-4">
                  <span class="text-[10px] font-black text-white/40 uppercase tracking-widest">{{ voucher.scope }}</span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-emerald-400 font-bold">
                    {{ voucher.discount_type === 'percent' ? formatDiscount(voucher.discount_value) + '%' : '₱' + formatNumber(voucher.discount_value) }}
                  </div>
                  <div v-if="voucher.max_discount" class="text-[10px] text-white/30 uppercase tracking-tighter">
                    Max ₱{{ formatNumber(voucher.max_discount) }}
                  </div>
                </td>
                <td class="px-6 py-4 text-white/60 font-mono text-sm">
                  ₱{{ formatNumber(voucher.min_order_amount) }}
                </td>
                <td class="px-6 py-4 text-white/40 text-xs">
                  {{ voucher.payment_method_limit || 'Any' }}
                </td>
                <td class="px-6 py-4">
                  <span 
                    class="px-2.5 py-1 rounded-lg text-[10px] font-black tracking-widest uppercase"
                    :class="parseInt(voucher.is_active) === 1 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30'"
                  >
                    {{ parseInt(voucher.is_active) === 1 ? 'ACTIVE' : 'INACTIVE' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <button 
                    @click="toggleVoucher(voucher)"
                    class="px-4 py-1.5 bg-white/5 border border-white/10 rounded-xl text-[10px] font-black tracking-widest uppercase hover:bg-white hover:text-black transition-all"
                  >
                    {{ parseInt(voucher.is_active) === 1 ? 'Deactivate' : 'Activate' }}
                  </button>
                </td>
              </tr>
              <tr v-if="vouchers.length === 0">
                <td colspan="8" class="px-6 py-24 text-center text-white/20 italic">
                  No vouchers found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </GlassCard>
    </div>

    <!-- Add Voucher Modal -->
    <div v-if="isAddModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
      <GlassCard customClass="w-full max-w-2xl p-8 relative">
        <button @click="isAddModalOpen = false" class="absolute top-6 right-6 p-2 hover:bg-white/10 rounded-full transition-colors">
          <X class="w-6 h-6 text-white" />
        </button>

        <h2 class="text-2xl font-bold text-white mb-8">Create Voucher</h2>

        <form @submit.prevent="saveVoucher" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Code</label>
            <input v-model="form.code" type="text" placeholder="PLAT40" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors">
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Name</label>
            <input v-model="form.name" type="text" placeholder="Platform 40 Off" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors">
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Scope</label>
            <select v-model="form.scope" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors">
              <option value="platform">Platform</option>
              <option value="shop">Shop</option>
            </select>
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Discount Type</label>
            <select v-model="form.discount_type" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors">
              <option value="fixed">Fixed</option>
              <option value="percent">Percent</option>
            </select>
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Discount Value</label>
            <input v-model="form.discount_value" type="number" min="0.01" step="0.01" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors">
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Max Discount (Optional)</label>
            <input v-model="form.max_discount" type="number" min="0" step="0.01" placeholder="120" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors">
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Min Order Amount</label>
            <input v-model="form.min_order_amount" type="number" min="0" step="0.01" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors">
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Payment Limit (Optional)</label>
            <select v-model="form.payment_method_limit" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors">
              <option value="">Any</option>
              <option value="COD">COD only</option>
              <option value="GCASH">GCash only</option>
            </select>
          </div>

          <button 
            type="submit"
            :disabled="isSubmitting"
            class="md:col-span-2 py-4 bg-gradient-to-r from-indigo-600 to-violet-700 hover:from-indigo-500 hover:to-violet-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-95 disabled:opacity-50"
          >
            {{ isSubmitting ? 'SAVING...' : 'SAVE VOUCHER' }}
          </button>
        </form>
      </GlassCard>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Plus, X } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const vouchers = ref([]);
const isAddModalOpen = ref(false);
const isSubmitting = ref(false);

const form = ref({
  code: '',
  name: '',
  scope: 'platform',
  discount_type: 'fixed',
  discount_value: '',
  max_discount: '',
  min_order_amount: '0',
  payment_method_limit: ''
});

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDiscount = (val) => {
  return parseFloat(val).toString();
};

const fetchVouchers = async () => {
  try {
    const response = await axios.get('/api/admin/vouchers');
    const data = response.data.data || response.data;
    vouchers.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Failed to fetch vouchers:', error);
    vouchers.value = [];
  }
};

const saveVoucher = async () => {
  isSubmitting.value = true;
  try {
    const formData = new FormData();
    for (const key in form.value) {
      formData.append(key, form.value[key]);
    }
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    const response = await axios.post('/admin/vouchers/store', formData);
    if (response.data.status === 'success') {
      fetchVouchers();
      isAddModalOpen.value = false;
      form.value = {
        code: '', name: '', scope: 'platform', discount_type: 'fixed',
        discount_value: '', max_discount: '', min_order_amount: '0',
        payment_method_limit: ''
      };
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Failed to save voucher:', error);
    alert('Failed to save voucher');
  } finally {
    isSubmitting.value = false;
  }
};

const toggleVoucher = async (voucher) => {
  try {
    const formData = new FormData();
    formData.append('id', voucher.id);
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    const response = await axios.post('/admin/vouchers/toggle', formData);
    if (response.data.status === 'success') {
      voucher.is_active = parseInt(voucher.is_active) === 1 ? 0 : 1;
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Failed to toggle voucher:', error);
    alert('Failed to toggle voucher');
  }
};

onMounted(() => {
  fetchVouchers();
});
</script>
