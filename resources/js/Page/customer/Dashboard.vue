<template>
  <CustomerLayout>
    <div class="space-y-12 animate-fade-in">
      
      <!-- Enhanced Hero Banner -->
      <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-br from-violet-950/40 via-indigo-950/20 to-transparent border border-white/5 p-10 md:p-20 group shadow-2xl">
        <!-- Fish Watermark (Right Aligned, Low Opacity) -->
        <div class="absolute -right-12 top-1/2 -translate-y-1/2 opacity-[0.05] rotate-12 group-hover:rotate-6 group-hover:scale-110 transition-all duration-1000 pointer-events-none">
          <Fish class="w-[20rem] h-[20rem] text-white" />
        </div>
        
        <!-- Ambient Hero Glow -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-violet-600/10 blur-[100px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl space-y-6">
          <h1 class="text-5xl md:text-7xl font-black text-white tracking-tighter leading-[1.1]">
            {{ greeting }}, <br/>
            <span class="text-gradient-purple-white">{{ username }}!</span>
          </h1>
          <p class="text-white/50 text-xl md:text-2xl font-medium leading-relaxed max-w-xl">
            Ready for some fresh seafood today? <br class="hidden md:block"/> Check out our best catch below.
          </p>
          
          <div class="pt-4 flex items-center gap-6">
            <button class="px-8 py-4 bg-white text-[#0c0616] font-black rounded-2xl hover:bg-violet-100 transition-all active:scale-95 flex items-center gap-3">
              <span>Explore Catch</span>
              <ArrowRight class="w-5 h-5" />
            </button>
            <div class="flex -space-x-3">
              <div v-for="i in 3" :key="i" class="w-10 h-10 rounded-full border-2 border-[#0c0616] bg-violet-900/40 backdrop-blur-md flex items-center justify-center text-[0.6rem] font-bold">
                {{ i }}k+
              </div>
            </div>
            <span class="text-white/30 text-xs font-bold uppercase tracking-widest">Happy Customers</span>
          </div>
        </div>
      </section>

      <!-- Section Header -->
      <header class="flex items-center justify-between px-2">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-violet-600/10 rounded-[1.5rem] flex items-center justify-center border border-violet-500/20 shadow-[0_0_20px_rgba(139,92,246,0.1)]">
            <Gem class="text-violet-400 w-7 h-7" />
          </div>
          <div class="space-y-1">
            <h2 class="text-3xl font-black text-white tracking-tight">Available Seafood</h2>
            <p class="text-white/30 text-xs font-bold uppercase tracking-[0.2em]">Premium Selection • Fresh Daily</p>
          </div>
        </div>
        
        <div class="hidden md:flex items-center gap-2 p-1.5 bg-white/5 border border-white/10 rounded-2xl backdrop-blur-xl">
          <button class="px-4 py-2 bg-violet-600 rounded-xl text-xs font-black uppercase tracking-widest transition-all">All</button>
          <button class="px-4 py-2 hover:bg-white/5 rounded-xl text-xs font-black uppercase tracking-widest text-white/40 transition-all">Shellfish</button>
          <button class="px-4 py-2 hover:bg-white/5 rounded-xl text-xs font-black uppercase tracking-widest text-white/40 transition-all">Fish</button>
        </div>
      </header>

      <!-- Responsive Product Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">
        <article 
          v-for="product in products" 
          :key="product.id" 
          class="group relative flex flex-col bg-white/[0.02] backdrop-blur-3xl border border-white/5 rounded-[2.5rem] overflow-hidden transition-all duration-700 hover:scale-105 hover:border-violet-500/30 hover:shadow-[0_20px_60px_-15px_rgba(139,92,246,0.2)]"
        >
          <!-- Product Image Container -->
          <div class="aspect-square w-full overflow-hidden relative">
            <img 
              :src="getImageUrl(product.image)" 
              @error="handleImageError"
              class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
              :alt="product.name"
            />
            <!-- Image Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#0c0616]/80 via-transparent to-transparent opacity-60"></div>
            
            <!-- Floating Badge -->
            <div class="absolute top-5 right-5 px-4 py-2 bg-black/40 backdrop-blur-md border border-white/10 rounded-full flex items-center gap-2">
              <Star class="w-3 h-3 text-amber-400 fill-amber-400" />
              <span class="text-[0.7rem] font-black">{{ product.real_rating || '5.0' }}</span>
            </div>
          </div>

          <!-- Product Content -->
          <div class="p-8 flex flex-col gap-6 flex-1">
            <div class="space-y-2">
              <h3 class="text-xl font-bold text-white lowercase group-hover:text-violet-300 transition-colors">
                {{ product.name }}
              </h3>
              
              <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-[#00ff88] tracking-tighter">₱{{ formatNumber(product.selling_price) }}</span>
                <span class="text-white/20 text-[0.65rem] font-black uppercase tracking-widest">/ {{ product.unit || 'kg' }}</span>
              </div>

              <div class="flex items-center gap-4 pt-2">
                <div class="flex items-center gap-2 text-white/40 text-[0.65rem] font-black uppercase tracking-widest">
                  <ShoppingBag class="w-3.5 h-3.5" />
                  <span>{{ product.real_sold_count || 0 }} Sold</span>
                </div>
                <div class="w-1 h-1 rounded-full bg-white/10"></div>
                <div class="flex items-center gap-2 text-violet-400/60 text-[0.65rem] font-black uppercase tracking-widest">
                  <Clock class="w-3.5 h-3.5" />
                  <span>Fresh</span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3 mt-auto">
              <button 
                @click="buyNow(product)"
                class="w-full bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-black py-4 rounded-2xl transition-all duration-300 active:scale-95 flex items-center justify-center gap-3 shadow-lg shadow-violet-900/20"
              >
                <Zap class="w-4 h-4 fill-current" />
                <span>Buy Now</span>
              </button>
              
              <button 
                @click="addToCart(product)"
                class="w-full bg-transparent border border-white/10 text-white/60 font-black py-4 rounded-2xl hover:bg-white hover:text-[#0c0616] hover:border-white transition-all duration-500 active:scale-95 flex items-center justify-center gap-3 group/btn"
              >
                <Plus class="w-4 h-4 transition-transform group-hover/btn:rotate-90" />
                <span>Add to Cart</span>
              </button>
            </div>
          </div>
        </article>

        <!-- Empty State -->
        <div v-if="products.length === 0" class="col-span-full py-40 text-center animate-fade-in">
          <div class="inline-flex items-center justify-center w-24 h-24 bg-white/[0.02] rounded-full mb-8 border border-white/5">
            <Fish class="w-12 h-12 text-white/10" />
          </div>
          <h3 class="text-2xl font-black text-white mb-3">No products available</h3>
          <p class="text-white/30 font-medium">Please check back later for our fresh catch.</p>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { 
  ShoppingCart, 
  Star, 
  Fish, 
  Zap, 
  Gem,
  Plus,
  ArrowRight,
  Clock,
  ShoppingBag
} from 'lucide-vue-next';
import CustomerLayout from '../../layouts/CustomerLayout.vue';

// Accept products as prop for easier integration
const props = defineProps({
  initialProducts: {
    type: Array,
    default: () => []
  }
});

const products = ref(props.initialProducts);
const username = ref(localStorage.getItem('username') || 'Bocana Ilog');
const cartCount = ref(parseInt(localStorage.getItem('cartCount') || '0'));
const fallbackImage = 'https://images.unsplash.com/photo-1551248429-40975aa4de74?q=80&w=800&auto=format&fit=crop';

const greeting = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return "Good Morning";
  if (hour < 18) return "Good Afternoon";
  return "Good Evening";
});

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return fallbackImage;
  if (imagePath.startsWith('http')) return imagePath;
  const baseUrl = window.BASE_URL || '';
  const cleanBaseUrl = baseUrl.replace(/\/$/, '');
  const cleanPath = imagePath.replace(/^\//, '').replace(/^uploads\//, '').replace(/^products\//, '');
  return `${cleanBaseUrl}/uploads/products/${cleanPath}`;
};

const handleImageError = (e) => {
  e.target.src = fallbackImage;
};

const updateCartCount = (count) => {
  cartCount.value = count;
  localStorage.setItem('cartCount', count);
  window.dispatchEvent(new Event('cart-updated'));
};

const openCart = () => {
  // Logic to open cart modal
  console.log('Opening cart modal...');
};

const fetchProducts = async () => {
  // If products are already passed via props, use them
  if (props.initialProducts.length > 0) {
    products.value = props.initialProducts;
    return;
  }
  
  try {
    const response = await axios.get('/api/customer/dashboard/data');
    products.value = response.data.products || [];
  } catch (error) {
    console.error('Failed to fetch products:', error);
  }
};

const buyNow = (product) => {
  console.log('Buy now:', product.name);
};

const addToCart = (product) => {
  cartCount.value++;
  updateCartCount(cartCount.value);
};

onMounted(() => {
  fetchProducts();
  window.addEventListener('open-customer-cart', openCart);
  
  // Initial sync
  updateCartCount(cartCount.value);
});
</script>

<style scoped>
.text-gradient-purple-white {
  background: linear-gradient(to right, #a78bfa, #ffffff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fadeIn 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
