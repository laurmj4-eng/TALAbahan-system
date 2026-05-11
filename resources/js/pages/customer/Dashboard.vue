<template>
  <div class="min-h-screen pb-12">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 bg-white/10 backdrop-blur-xl border-b border-white/20">
      <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
            <span class="text-indigo-900 font-black text-xl">T</span>
          </div>
          <h1 class="text-2xl font-black tracking-tighter text-white drop-shadow-md">TALAbahan</h1>
        </div>
        
        <nav class="hidden md:flex items-center space-x-2">
          <router-link 
            to="/customer/dashboard" 
            class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all hover:bg-white/20 hover:text-white"
            active-class="bg-white/20 text-white shadow-inner"
          >
            Shop
          </router-link>
          <router-link 
            to="/customer/orders" 
            class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all hover:bg-white/20 text-white/70 hover:text-white"
            active-class="bg-white/20 text-white shadow-inner"
          >
            My Orders
          </router-link>
          <router-link 
            to="/customer/profile" 
            class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all hover:bg-white/20 text-white/70 hover:text-white"
            active-class="bg-white/20 text-white shadow-inner"
          >
            Profile
          </router-link>
          <button 
            @click="handleLogout" 
            class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all hover:bg-red-500/30 text-red-300"
          >
            Logout
          </button>
        </nav>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
      <!-- Glass Main Container -->
      <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-[2.5rem] p-8 md:p-12 shadow-2xl">
        <div class="mb-12">
          <h2 class="text-5xl font-black mb-4 tracking-tight text-white">Fresh Selections</h2>
          <p class="text-white/60 text-xl font-medium max-w-2xl">Premium quality seafood and local favorites delivered directly to your doorstep with care.</p>
        </div>

        <!-- Responsive Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div 
            v-for="product in products" 
            :key="product.id" 
            class="group relative flex flex-col h-[520px] bg-white/10 backdrop-blur-lg border border-white/20 rounded-[2rem] overflow-hidden transition-all duration-500 hover:translate-y-[-12px] hover:shadow-[0_20px_50px_rgba(79,70,229,0.3)] hover:border-white/40"
          >
            <!-- 50/50 Split: Image Top -->
            <div class="h-1/2 w-full overflow-hidden relative bg-slate-800">
              <img 
                :src="getImageUrl(product.image)" 
                @error="handleImageError"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                :alt="product.name"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-60 group-hover:opacity-40 transition-opacity duration-300"></div>
              
              <!-- Floating Price Badge -->
              <div class="absolute top-4 right-4 bg-white/20 backdrop-blur-md border border-white/30 px-4 py-2 rounded-full shadow-lg">
                <span class="text-xl font-black text-white">₱{{ product.price }}</span>
              </div>
            </div>

            <!-- 50/50 Split: Info Bottom -->
            <div class="h-1/2 p-8 flex flex-col justify-between bg-gradient-to-b from-white/5 to-transparent">
              <div>
                <h3 class="text-2xl font-extrabold text-white mb-3 group-hover:text-indigo-300 transition-colors">{{ product.name }}</h3>
                <p class="text-white/60 text-sm line-clamp-2 leading-relaxed mb-6 font-medium italic">"{{ product.description || 'No description available for this delicious item.' }}"</p>
                
                <div class="flex items-center space-x-6">
                  <div class="flex items-center bg-white/5 px-3 py-1.5 rounded-lg border border-white/10">
                    <Star class="w-4 h-4 text-yellow-400 mr-2 fill-yellow-400" />
                    <span class="text-white font-bold text-sm">{{ product.rating || '4.8' }}</span>
                  </div>
                  <div class="flex items-center bg-white/5 px-3 py-1.5 rounded-lg border border-white/10">
                    <ShoppingBag class="w-4 h-4 text-indigo-400 mr-2" />
                    <span class="text-white/70 font-bold text-xs uppercase tracking-tighter">{{ product.sold_count || '250+' }} SOLD</span>
                  </div>
                </div>
              </div>

              <button 
                @click="addToCart(product)"
                class="w-full mt-6 bg-white text-indigo-950 font-black py-4 rounded-2xl hover:bg-indigo-500 hover:text-white transition-all duration-300 active:scale-95 flex items-center justify-center space-x-3 shadow-xl shadow-indigo-500/10"
              >
                <ShoppingCart class="w-5 h-5" />
                <span>Add to Cart</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ShoppingCart, Star, ShoppingBag } from 'lucide-vue-next';

const router = useRouter();
const products = ref([]);
const fallbackImage = 'https://images.unsplash.com/photo-1551248429-40975aa4de74?q=80&w=800&auto=format&fit=crop';

const getImageUrl = (imagePath) => {
  if (!imagePath) return fallbackImage;
  if (imagePath.startsWith('http')) return imagePath;
  
  const baseUrl = window.BASE_URL || '';
  // According to instructions: base_url('uploads/') + image name
  const cleanBaseUrl = baseUrl.replace(/\/$/, '');
  const cleanPath = imagePath.replace(/^\//, '').replace(/^uploads\//, '');
  
  return `${cleanBaseUrl}/uploads/${cleanPath}`;
};

const handleImageError = (e) => {
  e.target.src = fallbackImage;
};

const fetchProducts = async () => {
  try {
    const response = await axios.get('/api/customer/dashboard/data');
    products.value = response.data.products;
  } catch (error) {
    console.error('Failed to fetch products:', error);
  }
};

const addToCart = (product) => {
  console.log('Added to cart:', product.name);
};

const handleLogout = () => {
  localStorage.removeItem('isLoggedIn');
  localStorage.removeItem('userRole');
  router.push('/login');
};

onMounted(fetchProducts);
</script>
