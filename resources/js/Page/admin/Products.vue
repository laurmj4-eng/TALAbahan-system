<template>
  <AdminLayout>
    <div class="flex-1 flex flex-col space-y-6 md:space-y-8 min-h-0">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-6">
        <div class="flex-1">
          <h1 class="text-3xl md:text-[2.5rem] font-extrabold tracking-tight bg-gradient-to-r from-white to-sky-400 bg-clip-text text-transparent leading-tight">
            Inventory Management
          </h1>
          <p class="text-white/50 font-medium text-sm md:text-base">Add, modify, or toggle visibility of seafood products.</p>
        </div>
        <button @click="openAddModal" class="w-full md:w-auto px-8 py-4 md:py-3 bg-violet-600 hover:bg-violet-700 text-white rounded-2xl font-bold shadow-lg shadow-violet-500/20 transition-all active:scale-95 flex items-center justify-center gap-2">
          <Plus class="w-5 h-5" />
          <span>Add Product</span>
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">
        <div class="p-6 rounded-[24px] bg-white/[0.03] border border-white/10 backdrop-blur-md">
          <div class="text-white/40 text-[0.7rem] font-black uppercase tracking-widest mb-1">Live Items</div>
          <div class="text-3xl font-black text-emerald-400">{{ liveItemsCount }}</div>
        </div>
        <div class="p-6 rounded-[24px] bg-white/[0.03] border border-white/10 backdrop-blur-md">
          <div class="text-white/40 text-[0.7rem] font-black uppercase tracking-widest mb-1">Total Products</div>
          <div class="text-3xl font-black text-sky-400">{{ products.length }}</div>
        </div>
      </div>

      <!-- Table -->
      <GlassCard customClass="overflow-hidden border-white/[0.08] flex-1 flex flex-col min-h-0">
        <div class="overflow-x-auto overflow-y-auto max-h-[60vh] md:max-h-[calc(100vh-420px)] scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
          <!-- Desktop Table (Hidden on Mobile) -->
          <table class="hidden md:table w-full text-left border-collapse">
            <thead class="sticky top-0 z-10 bg-[#1a1a1a] backdrop-blur-md">
              <tr class="bg-white/[0.02] border-b border-white/10">
                <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Product Node</th>
                <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Category</th>
                <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Price Point</th>
                <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest text-center">Visibility</th>
                <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/[0.05]">
              <tr v-for="product in products" :key="product.id" class="hover:bg-white/[0.02] transition-colors group animate-slide-in-right">
                <td class="px-8 py-6">
                  <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/[0.05] border border-white/10 overflow-hidden flex items-center justify-center p-1 group-hover:border-violet-500/30 transition-colors">
                      <img 
                        v-if="product.image" 
                        :src="getImageUrl(product.image)" 
                        class="w-full h-full object-cover rounded-xl"
                        @error="(e) => e.target.src = '/images/placeholder.png'"
                      >
                      <div v-else class="text-2xl opacity-20">🐟</div>
                    </div>
                    <div>
                      <div class="font-bold text-white text-lg group-hover:text-violet-300 transition-colors">{{ product.name }}</div>
                      <div class="text-[0.7rem] text-white/30 font-mono">{{ product.sku || 'SKU-PENDING' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-8 py-6">
                  <span class="px-3 py-1 bg-white/[0.05] border border-white/10 rounded-lg text-[0.7rem] font-bold text-white/60 uppercase tracking-wider">
                    {{ product.category || 'Seafood' }}
                  </span>
                </td>
                <td class="px-8 py-6 font-black text-emerald-400 text-lg">
                  ₱{{ formatNumber(product.selling_price) }}
                </td>
                <td class="px-8 py-6 text-center">
                  <button 
                    @click="toggleStatus(product)"
                    class="relative inline-flex items-center cursor-pointer transition-all active:scale-90"
                  >
                    <div 
                      :class="parseInt(product.is_available) === 1 ? 'bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.4)] animate-pulse-subtle' : 'bg-rose-500 shadow-[0_0_15px_rgba(244,63,94,0.4)]'"
                      class="px-4 py-1.5 rounded-full text-[0.65rem] font-black text-white uppercase tracking-widest border border-white/20"
                    >
                      {{ parseInt(product.is_available) === 1 ? 'LIVE' : 'HIDDEN' }}
                    </div>
                  </button>
                </td>
                <td class="px-8 py-6 text-right">
                  <div class="flex justify-end gap-3 transition-opacity">
                    <button @click="openEditModal(product)" class="p-3 bg-white/[0.05] border border-white/10 rounded-xl hover:bg-violet-500/20 hover:border-violet-500/40 hover:text-violet-400 transition-all">
                      <Edit2 class="w-4 h-4" />
                    </button>
                    <button @click="deleteProduct(product.id)" class="p-3 bg-white/[0.05] border border-white/10 rounded-xl hover:bg-rose-500/20 hover:border-rose-500/40 hover:text-rose-400 transition-all">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="products.length === 0">
                <td colspan="6" class="px-8 py-32 text-center">
                  <div class="text-white/10 flex flex-col items-center gap-4">
                    <Ghost class="w-16 h-16 opacity-5" />
                    <p class="italic text-lg">No products found in database.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Mobile List View -->
          <div class="md:hidden divide-y divide-white/[0.05]">
            <div v-for="product in products" :key="product.id" class="p-4 space-y-4">
              <div class="flex gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white/[0.05] border border-white/10 overflow-hidden flex-shrink-0">
                  <img v-if="product.image" :src="getImageUrl(product.image)" class="w-full h-full object-cover">
                  <div v-else class="w-full h-full flex items-center justify-center text-2xl opacity-20">🐟</div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-bold text-white text-lg truncate">{{ product.name }}</div>
                  <div class="text-[10px] text-white/30 font-mono mb-2 uppercase">{{ product.category || 'Seafood' }}</div>
                  <div class="font-black text-emerald-400 text-xl">₱{{ formatNumber(product.selling_price) }}</div>
                </div>
              </div>
              
              <div class="flex justify-between items-center bg-white/[0.02] p-3 rounded-xl border border-white/5">
                <div class="flex items-center gap-2">
                  <span class="text-[10px] text-white/40 font-bold uppercase">Unit:</span>
                  <span class="font-bold text-white">{{ product.unit }}</span>
                </div>
                <button @click="toggleStatus(product)" class="px-3 py-1 rounded-full text-[0.6rem] font-black text-white uppercase tracking-widest border border-white/10" :class="parseInt(product.is_available) === 1 ? 'bg-emerald-500/80' : 'bg-rose-500/80'">
                  {{ parseInt(product.is_available) === 1 ? 'LIVE' : 'HIDDEN' }}
                </button>
              </div>

              <div class="flex gap-2">
                <button @click="openEditModal(product)" class="flex-1 flex items-center justify-center gap-2 p-3 bg-white/[0.05] border border-white/10 rounded-xl text-xs font-bold text-white/70">
                  <Edit2 class="w-4 h-4" /> Edit
                </button>
                <button @click="deleteProduct(product.id)" class="flex-1 flex items-center justify-center gap-2 p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs font-bold text-rose-400">
                  <Trash2 class="w-4 h-4" /> Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </GlassCard>

      <!-- Add/Edit Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" @click="closeModal"></div>
        <GlassCard customClass="relative w-full max-w-lg p-8 border-white/20 shadow-2xl overflow-y-auto max-h-[90vh]">
          <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-white">{{ isEditing ? 'Edit Product' : 'Add New Product' }}</h2>
            <button @click="closeModal" class="text-white/40 hover:text-white transition-colors">
              <X class="w-6 h-6" />
            </button>
          </div>

          <form @submit.prevent="saveProduct" class="space-y-6">
            <div class="space-y-2">
              <label class="text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Product Name</label>
              <input v-model="form.name" type="text" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-violet-500/50 transition-all" placeholder="e.g. Bangus Large" required />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Cost Price (₱)</label>
                <input v-model="form.cost_price" type="number" step="0.01" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-violet-500/50 transition-all" placeholder="0.00" required />
              </div>
              <div class="space-y-2">
                <label class="text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Selling Price (₱)</label>
                <input v-model="form.selling_price" type="number" step="0.01" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-violet-500/50 transition-all" placeholder="0.00" required />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
              <div class="space-y-2">
                <label class="text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Unit</label>
                <select v-model="form.unit" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-violet-500/50 transition-all appearance-none">
                  <option value="Per serving" class="bg-slate-900">Per serving</option>
                  <option value="Isa ka basket" class="bg-slate-900">Isa ka basket</option>
                  <option value="Pila ka bilao" class="bg-slate-900">Pila ka bilao</option>
                </select>
              </div>
            </div>

            <div class="space-y-2">
              <label class="text-[0.7rem] font-black text-white/40 uppercase tracking-widest">Product Image</label>
              <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center overflow-hidden">
                  <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" />
                  <div v-else class="text-3xl opacity-10">📸</div>
                </div>
                <input type="file" @change="handleImageChange" accept="image/*" class="text-sm text-white/40 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-violet-600/20 file:text-violet-400 hover:file:bg-violet-600/30 cursor-pointer" />
              </div>
            </div>

            <button type="submit" :disabled="submitting" class="w-full py-4 bg-violet-600 hover:bg-violet-700 text-white rounded-2xl font-bold shadow-lg shadow-violet-500/20 transition-all active:scale-95 disabled:opacity-50">
              {{ submitting ? 'Processing...' : (isEditing ? 'Update Product' : 'Add Product') }}
            </button>
          </form>
        </GlassCard>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { Plus, Edit2, Trash2, Ghost, X } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const products = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const submitting = ref(false);
const imagePreview = ref(null);
const selectedFile = ref(null);

const form = ref({
  id: null,
  name: '',
  cost_price: '',
  selling_price: '',
  unit: 'Per serving'
});

const liveItemsCount = computed(() => {
  if (!Array.isArray(products.value)) return 0;
  return products.value.filter(p => parseInt(p.is_available) === 1).length;
});

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return '';
  if (imagePath.startsWith('http') || imagePath.startsWith('blob:')) return imagePath;
  const baseUrl = window.BASE_URL || '';
  const cleanBaseUrl = baseUrl.replace(/\/$/, '');
  const cleanPath = imagePath.replace(/^\//, '').replace(/^uploads\//, '').replace(/^products\//, '');
  return `${cleanBaseUrl}/uploads/products/${cleanPath}`;
};

const fetchProducts = async () => {
  try {
    const response = await axios.get('/api/admin/getProducts');
    products.value = response.data.data || [];
    if (response.data.token) window.CSRF_HASH = response.data.token;
  } catch (error) {
    console.error('Failed to fetch products:', error);
    products.value = [];
  }
};

const openAddModal = () => {
  isEditing.value = false;
  form.value = { id: null, name: '', cost_price: '', selling_price: '', unit: 'Per serving' };
  imagePreview.value = null;
  selectedFile.value = null;
  showModal.value = true;
};

const openEditModal = (product) => {
  isEditing.value = true;
  form.value = {
    id: product.id,
    name: product.name,
    cost_price: product.cost_price,
    selling_price: product.selling_price,
    unit: product.unit || 'Per serving'
  };
  imagePreview.value = product.image ? getImageUrl(product.image) : null;
  selectedFile.value = null;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const handleImageChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const saveProduct = async () => {
  submitting.value = true;
  try {
    const formData = new FormData();
    formData.append('id', form.value.id || '');
    formData.append('name', form.value.name);
    formData.append('cost_price', form.value.cost_price);
    formData.append('selling_price', form.value.selling_price);
    formData.append('unit', form.value.unit);
    
    if (selectedFile.value) {
      formData.append('image', selectedFile.value);
    }
    
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    const url = isEditing.value ? '/api/admin/products/update' : '/api/admin/products/store';
    const response = await axios.post(url, formData);

    if (response.data.status === 'success') {
      await fetchProducts();
      closeModal();
    }
  } catch (error) {
    console.error('Save failed:', error);
    alert(error.response?.data?.message || 'Failed to save product');
  } finally {
    submitting.value = false;
  }
};

const toggleStatus = async (product) => {
  try {
    const newStatus = parseInt(product.is_available) === 1 ? 0 : 1;
    
    // Some routes might be /api/admin/... or /admin/...
    // Based on routes.php, it's /api/admin/products/toggleStatus/(:num)
    // We'll use the API route and handle potential CSRF 403 by including the token
    const response = await axios.post(`/api/admin/products/toggleStatus/${product.id}`, {
      [window.CSRF_TOKEN_NAME]: window.CSRF_HASH
    });
    
    if (response.data.status === 'success') {
      product.is_available = newStatus;
      if (response.data.token) window.CSRF_HASH = response.data.token;
    }
  } catch (error) {
    console.error('Toggle status failed:', error);
    // If we still get a 403, it might be the token is stale, update it if returned
    if (error.response?.data?.token) {
      window.CSRF_HASH = error.response.data.token;
    }
  }
};

const deleteProduct = async (id) => {
  if (!confirm('Destroy this record permanently?')) return;
  try {
    const formData = new FormData();
    formData.append('id', id);
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }
    const response = await axios.post('/api/admin/products/delete', formData);
    if (response.data.status === 'success') {
      fetchProducts();
      if (response.data.token) window.CSRF_HASH = response.data.token;
    }
  } catch (error) {
    console.error('Delete failed:', error);
  }
};

onMounted(fetchProducts);
</script>
