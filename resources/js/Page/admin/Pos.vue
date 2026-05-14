<template>
  <AdminLayout :username="username">
    <div class="space-y-8">
      <h2 class="text-3xl font-bold text-white">TALAbahan Terminal</h2>
      
      <div class="flex flex-col lg:flex-row gap-8 items-start">
        <!-- Products Grid -->
        <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-6">
          <GlassCard 
            v-for="product in products" 
            :key="product.id" 
            @click="addToCart(product)"
            customClass="p-0 overflow-hidden cursor-pointer hover:ring-2 hover:ring-indigo-500/50 transition-all group flex flex-col h-full"
          >
            <!-- Product Image -->
            <div class="h-40 overflow-hidden relative bg-white/5">
              <img 
                v-if="product.image" 
                :src="getImageUrl(product.image)" 
                :alt="product.name"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-4xl opacity-50">
                {{ product.icon || '🐟' }}
              </div>
              <!-- Quick Add Badge -->
              <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <span class="bg-white text-slate-900 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider scale-90 group-hover:scale-100 transition-transform">Add to Cart</span>
              </div>
            </div>

            <!-- Product Info -->
            <div class="p-4 flex flex-col flex-1">
              <div class="font-bold text-white mb-1 truncate text-sm md:text-base">{{ product.name }}</div>
              <div class="flex items-center justify-between mt-auto">
                <span class="text-xs text-white/40 uppercase tracking-widest font-bold">{{ product.unit || 'piece' }}</span>
                <div class="text-emerald-400 font-black text-base">₱{{ formatNumber(product.selling_price || product.price) }}</div>
              </div>
            </div>
          </GlassCard>
          <div v-if="products.length === 0" class="col-span-full py-24 text-center text-white/20 italic">
            No products available.
          </div>
        </div>

        <!-- Cart Sidebar -->
        <GlassCard customClass="w-full lg:w-96 p-8 sticky top-8 border-white/10 bg-black/40 shadow-2xl backdrop-blur-2xl">
          <div class="text-xl font-bold text-white mb-6 border-b border-white/10 pb-4 flex items-center gap-2">
            <ShoppingCart class="w-5 h-5 text-indigo-400" />
            <span>Current Order</span>
          </div>
          
          <!-- Customer Selection -->
          <div class="mb-6">
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-white/40 mb-2">Select Customer</label>
            <select 
              v-model="customerName"
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500/50 transition-colors text-sm font-medium"
            >
              <option value="Walk-in Customer" class="bg-slate-900">Walk-in Customer</option>
              <option v-for="user in customers" :key="user.id" :value="user.username" class="bg-slate-900">{{ user.username }}</option>
            </select>
          </div>

          <!-- Cart Items -->
          <div class="min-h-[200px] max-h-[400px] overflow-y-auto mb-6 pr-2 custom-scrollbar">
            <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-[200px] text-white/20 italic">
              <ShoppingCart class="w-12 h-12 mb-4 opacity-10" />
              <p class="text-xs">Your cart is empty</p>
            </div>
            <div v-for="item in cart" :key="item.id" class="group flex justify-between items-center py-4 border-b border-white/5 last:border-0 hover:bg-white/[0.02] -mx-2 px-2 rounded-lg transition-colors">
              <div class="flex-1 min-w-0 pr-4">
                <div class="font-bold text-sm text-white truncate">{{ item.name }}</div>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-emerald-400 font-bold">₱{{ formatNumber(item.selling_price || item.price) }}</span>
                  <span class="text-[10px] text-white/30 font-medium">/ {{ item.unit || 'piece' }}</span>
                </div>
              </div>
              <div class="flex items-center bg-white/5 rounded-xl p-1 gap-1">
                <button @click="changeQty(item, -1)" class="w-7 h-7 flex items-center justify-center hover:bg-rose-500/20 rounded-lg text-white transition-all active:scale-90">-</button>
                <span class="font-black text-white text-xs w-6 text-center">{{ item.qty }}</span>
                <button @click="changeQty(item, 1)" class="w-7 h-7 flex items-center justify-center hover:bg-emerald-500/20 rounded-lg text-white transition-all active:scale-90">+</button>
              </div>
            </div>
          </div>

          <!-- Summary -->
          <div class="border-t-2 border-white/10 pt-6 space-y-3">
            <div class="flex justify-between text-sm text-white/60">
              <span>Subtotal</span>
              <span>₱{{ formatNumber(subtotal) }}</span>
            </div>
            <div v-if="discount > 0" class="flex justify-between text-sm text-emerald-400">
              <span>Discount</span>
              <span>-₱{{ formatNumber(discount) }}</span>
            </div>
            <div class="flex justify-between text-sm text-white/60">
              <span>Tax (12%)</span>
              <span>₱{{ formatNumber(tax) }}</span>
            </div>
            <div class="flex justify-between text-2xl font-black text-white pt-2">
              <span>Total</span>
              <span>₱{{ formatNumber(total) }}</span>
            </div>

            <!-- Voucher -->
            <div class="pt-6 border-t border-dashed border-white/10">
              <div class="flex gap-2">
                <input 
                  v-model="voucherCode"
                  type="text" 
                  placeholder="Voucher Code"
                  class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500/50"
                >
                <button @click="applyVoucher" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all">APPLY</button>
              </div>
              <p v-if="voucherStatus" :class="voucherStatus.type === 'success' ? 'text-emerald-400' : 'text-red-400'" class="text-[10px] mt-2 font-bold uppercase tracking-widest">
                {{ voucherStatus.message }}
              </p>
            </div>

            <button 
              @click="processCheckout"
              :disabled="cart.length === 0 || isProcessing"
              class="w-full mt-6 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-lg shadow-lg shadow-emerald-500/20 transition-all active:scale-95"
            >
              {{ isProcessing ? 'PROCESSING...' : 'PROCESS PAYMENT' }}
            </button>
          </div>
        </GlassCard>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(139, 92, 246, 0.2);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(139, 92, 246, 0.4);
}
</style>

<script setup>

import { ref, computed } from 'vue';
import axios from 'axios';
import { ShoppingCart } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const windowObj = window;
const props = defineProps({
  username: String,
  products: Array,
  customers: Array
});

const cart = ref([]);
const customerName = ref('Walk-in Customer');
const voucherCode = ref('');
const voucherStatus = ref(null);
const isProcessing = ref(false);
const receipt = ref(null);

const subtotal = computed(() => cart.value.reduce((sum, item) => sum + ((item.selling_price || item.price) * item.qty), 0));
const discount = ref(0);
const tax = computed(() => subtotal.value * 0.12);
const total = computed(() => subtotal.value + tax.value - discount.value);

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return '';
  if (imagePath.startsWith('http')) return imagePath;
  const baseUrl = window.BASE_URL || '';
  const cleanBaseUrl = baseUrl.replace(/\/$/, '');
  const cleanPath = imagePath.replace(/^\//, '').replace(/^uploads\//, '').replace(/^products\//, '');
  return `${cleanBaseUrl}/uploads/products/${cleanPath}`;
};

const addToCart = (product) => {
  const existing = cart.value.find(item => item.id === product.id);
  if (existing) {
    existing.qty++;
  } else {
    cart.value.push({ ...product, qty: 1 });
  }
};

const changeQty = (item, delta) => {
  item.qty += delta;
  if (item.qty <= 0) {
    cart.value = cart.value.filter(i => i.id !== item.id);
  }
};

const applyVoucher = async () => {
  if (cart.value.length === 0) {
    voucherStatus.value = { type: 'error', message: 'Add items first' };
    return;
  }
  if (!voucherCode.value) {
    voucherStatus.value = { type: 'error', message: 'Enter a code' };
    return;
  }

  // Simplified: In a real app, this would be an API call
  voucherStatus.value = { type: 'success', message: `Code "${voucherCode.value}" applied` };
};

const processCheckout = async () => {
  if (cart.value.length === 0) return;
  isProcessing.value = true;

  try {
    const formData = new FormData();
    formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    formData.append('customer_name', customerName.value);
    formData.append('voucher_code', voucherCode.value);
    formData.append('items', JSON.stringify(cart.value));

    const response = await axios.post('/admin/checkout', formData);
    if (response.data.status === 'success') {
      cart.value = [];
      voucherCode.value = '';
      voucherStatus.value = null;
      discount.value = 0;
      alert(response.data.message || 'Checkout successful!');
    } else {
      alert(response.data.message || 'Checkout failed');
    }
    
    // Update CSRF hash if backend returned a new one
    if (response.data.token) {
      window.CSRF_HASH = response.data.token;
    }
  } catch (error) {
    console.error('Checkout error:', error);
    const msg = error.response?.data?.message || 'Checkout error';
    alert(msg);
    if (error.response?.data?.token) {
      window.CSRF_HASH = error.response.data.token;
    }
  } finally {
    isProcessing.value = false;
  }
};
</script>
