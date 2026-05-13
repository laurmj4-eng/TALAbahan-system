<template>
  <div class="p-6 md:p-8 space-y-8">
    <div class="header flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white mb-2">📦 Product Inventory</h1>
        <p class="text-white/50 font-medium">Monitor stock levels and manage fresh seafood products.</p>
      </div>
      <Link href="/staff/dashboard" class="px-6 py-3 bg-white/5 border border-white/10 rounded-2xl font-bold text-white hover:bg-white/10 transition-all flex items-center gap-2">
        <ChevronLeft class="w-5 h-5" />
        <span>Dashboard</span>
      </Link>
    </div>

    <GlassCard customClass="p-8 border-white/10">
      <div class="flex flex-col md:flex-row gap-4 mb-8">
        <div class="flex-1 relative group">
          <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20 group-focus-within:text-indigo-400 transition-colors" />
          <input 
            v-model="searchQuery" 
            type="text" 
            class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 pl-12 pr-6 text-white placeholder-white/20 focus:outline-none focus:border-indigo-500/50 transition-all"
            placeholder="Search products by name or category..."
          />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="text-[0.7rem] font-black text-white/40 uppercase tracking-[0.2em] border-b border-white/5">
              <th class="px-6 py-4">Picture</th>
              <th class="px-6 py-4">Product Name</th>
              <th class="px-6 py-4 text-right">Cost</th>
              <th class="px-6 py-4 text-right">Selling</th>
              <th class="px-6 py-4 text-center">Stock Level</th>
              <th class="px-6 py-4 text-center">Unit</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="product in filteredProducts" :key="product.id" class="group hover:bg-white/[0.02] transition-all">
              <td class="px-6 py-6">
                <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 overflow-hidden flex items-center justify-center group-hover:border-indigo-500/30 transition-all">
                  <img v-if="product.image" :src="'/uploads/products/' + product.image" class="w-full h-full object-cover" />
                  <Fish v-else class="w-8 h-8 opacity-20" />
                </div>
              </td>
              <td class="px-6 py-6">
                <div class="font-bold text-white text-lg group-hover:text-indigo-300 transition-colors">{{ product.name }}</div>
                <div class="text-xs text-white/30 mt-1 uppercase tracking-widest font-black">{{ product.category || 'Seafood' }}</div>
              </td>
              <td class="px-6 py-6 text-right font-mono text-white/40 italic">₱{{ formatNumber(product.cost_price) }}</td>
              <td class="px-6 py-6 text-right font-black text-emerald-400 text-lg">₱{{ formatNumber(product.selling_price) }}</td>
              <td class="px-6 py-6 text-center">
                <div class="flex flex-col items-center gap-2">
                  <span class="text-xl font-black" :class="parseInt(product.current_stock) <= 5 ? 'text-rose-400 animate-pulse' : 'text-white'">
                    {{ product.current_stock }}
                  </span>
                  <span 
                    class="px-2 py-0.5 rounded-md text-[0.6rem] font-black uppercase tracking-widest border"
                    :class="parseInt(product.current_stock) <= 5 ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'"
                  >
                    {{ parseInt(product.current_stock) <= 5 ? 'Low Stock' : 'In Stock' }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-6 text-center">
                <span class="px-3 py-1 bg-white/5 border border-white/10 rounded-lg text-xs font-bold text-white/60 uppercase tracking-widest">{{ product.unit }}</span>
              </td>
            </tr>
            <tr v-if="filteredProducts.length === 0">
              <td colspan="6" class="px-6 py-32 text-center">
                <div class="flex flex-col items-center gap-4 opacity-10">
                  <Loader2 v-if="loading" class="w-12 h-12 animate-spin" />
                  <Ghost v-else class="w-16 h-16" />
                  <p class="font-bold text-lg italic">{{ loading ? 'Loading inventory...' : 'No products found.' }}</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </GlassCard>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ChevronLeft, Search, Fish, Loader2, Ghost } from 'lucide-vue-next';
import GlassCard from '../../components/GlassCard.vue';

const products = ref([]);
const loading = ref(true);
const searchQuery = ref('');

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/getProducts'); // Staff uses same list endpoint
    if (response.data.status === 'success') {
      products.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to fetch products:', error);
  } finally {
    loading.value = false;
  }
};

const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value;
  const q = searchQuery.value.toLowerCase();
  return products.value.filter(p => 
    p.name.toLowerCase().includes(q) || 
    (p.category && p.category.toLowerCase().includes(q))
  );
});

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

onMounted(fetchProducts);
</script>
