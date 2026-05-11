<template>
  <AdminLayout>
    <div class="space-y-8">
      <h2 class="text-3xl font-bold text-white">TALAbahan Terminal</h2>
      
      <div class="flex flex-col lg:flex-row gap-8 items-start">
        <!-- Products Grid -->
        <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-6">
          <GlassCard 
            v-for="product in products" 
            :key="product.id" 
            @click="addToCart(product)"
            customClass="p-6 text-center cursor-pointer hover:bg-white/10 hover:border-indigo-500/50 transition-all group"
          >
            <span class="text-5xl block mb-4 group-hover:scale-110 transition-transform">{{ product.icon || '🐟' }}</span>
            <div class="font-bold text-white mb-2">{{ product.name }}</div>
            <div class="text-emerald-400 font-black text-lg">₱{{ formatNumber(product.price) }}</div>
          </GlassCard>
          <div v-if="products.length === 0" class="col-span-full py-24 text-center text-white/20 italic">
            Loading products...
          </div>
        </div>

        <!-- Cart Sidebar -->
        <GlassCard customClass="w-full lg:w-96 p-8 sticky top-8 border-white/10 bg-black/40">
          <div class="text-xl font-bold text-white mb-6 border-b border-white/10 pb-4">Current Order</div>
          
          <!-- Customer Selection -->
          <div class="mb-6">
            <select 
              v-model="customerName"
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
              <option value="Walk-in Customer">Walk-in Customer</option>
              <option v-for="user in customers" :key="user.id" :value="user.username">{{ user.username }}</option>
            </select>
          </div>

          <!-- Cart Items -->
          <div class="min-h-[200px] max-h-[400px] overflow-y-auto mb-6 space-y-4">
            <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-full text-white/20 italic py-12">
              <ShoppingCart class="w-12 h-12 mb-4 opacity-10" />
              <p>Select items to add to cart</p>
            </div>
            <div v-for="item in cart" :key="item.id" class="flex justify-between items-center py-3 border-b border-white/5 last:border-0">
              <div class="flex-1">
                <div class="font-bold text-sm text-white">{{ item.icon }} {{ item.name }}</div>
                <div class="text-xs text-emerald-400">₱{{ formatNumber(item.price) }}</div>
              </div>
              <div class="flex items-center gap-3">
                <button @click="changeQty(item, -1)" class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-red-500/20 rounded-lg text-white transition-colors">-</button>
                <span class="font-bold text-white w-4 text-center">{{ item.qty }}</span>
                <button @click="changeQty(item, 1)" class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-indigo-500/20 rounded-lg text-white transition-colors">+</button>
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

    <!-- Receipt Modal -->
    <div v-if="receipt" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md">
      <div class="bg-white text-slate-900 w-full max-w-sm p-8 rounded-sm font-mono shadow-2xl">
        <div class="text-center border-b-2 border-dashed border-slate-200 pb-6 mb-6">
          <h1 class="text-2xl font-black tracking-widest">TALAbahan</h1>
          <p class="text-[10px] mt-1 text-slate-500 uppercase">Seafood & Grill Terminal</p>
          <p class="text-[10px] text-slate-400 mt-1">{{ receipt.date }}</p>
        </div>
        
        <div class="space-y-2 mb-6">
          <div v-for="item in receipt.items" :key="item.id" class="flex justify-between text-xs">
            <span>{{ item.quantity }}x {{ item.product_name }}</span>
            <span>₱{{ formatNumber(item.subtotal) }}</span>
          </div>
        </div>

        <div class="border-t border-dashed border-slate-200 pt-4 space-y-2">
          <div class="flex justify-between font-bold">
            <span>TOTAL</span>
            <span>₱{{ formatNumber(receipt.total_amount) }}</span>
          </div>
          <div v-if="receipt.discount > 0" class="flex justify-between text-slate-500 text-xs italic">
            <span>DISCOUNT</span>
            <span>-₱{{ formatNumber(receipt.discount) }}</span>
          </div>
        </div>

        <div class="border-t border-dashed border-slate-200 mt-6 pt-4">
          <div class="flex justify-between text-[10px] text-slate-400 uppercase tracking-widest">
            <span>TRANSACTION</span>
            <span class="font-bold text-slate-600">{{ receipt.transaction_code }}</span>
          </div>
        </div>

        <div class="text-center mt-8 pt-6 border-t-2 border-dashed border-slate-200">
          <p class="text-xs font-bold uppercase tracking-widest">Thank you for dining!</p>
          <p class="text-[10px] text-slate-400 mt-1">Visit us again soon 🌊</p>
        </div>

        <button @click="receipt = null" class="w-full mt-8 py-3 bg-slate-900 text-white font-bold text-xs uppercase tracking-widest hover:bg-slate-800 transition-colors">
          Close & New Order
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { ShoppingCart } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const products = ref([]);
const customers = ref([]);
const cart = ref([]);
const customerName = ref('Walk-in Customer');
const voucherCode = ref('');
const voucherStatus = ref(null);
const isProcessing = ref(false);
const receipt = ref(null);

const subtotal = computed(() => cart.value.reduce((sum, item) => sum + (item.price * item.qty), 0));
const discount = ref(0);
const tax = computed(() => subtotal.value * 0.12);
const total = computed(() => subtotal.value + tax.value - discount.value);

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
    formData.append('items', JSON.stringify(cart.value));
    formData.append('voucher_code', voucherCode.value);
    formData.append('customer_name', customerName.value);
    
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    const response = await axios.post('/api/admin/checkout', formData);
    if (response.data.status === 'success') {
      receipt.value = response.data.data;
      cart.value = [];
      voucherCode.value = '';
      voucherStatus.value = null;
      discount.value = 0;
    } else {
      alert(response.data.message || 'Checkout failed');
    }
  } catch (error) {
    console.error('Checkout error:', error);
    alert('Checkout error');
  } finally {
    isProcessing.value = false;
  }
};

const fetchData = async () => {
  try {
    const [pRes, uRes] = await Promise.all([
      axios.get('/api/admin/getProducts'),
      axios.get('/api/admin/users')
    ]);
    products.value = pRes.data;
    customers.value = uRes.data.filter(u => u.role === 'customer');
  } catch (error) {
    console.error('Failed to fetch POS data:', error);
  }
};

onMounted(() => {
  fetchData();
});
</script>
