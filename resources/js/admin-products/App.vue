<template>
  <div class="p-4 md:p-8 min-h-screen w-full">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h2 class="text-2xl md:text-3xl font-bold text-white">Daily Seafood Inventory 🐟</h2>
        <p class="text-gray-400 mt-1">Manage and update your seafood inventory items below.</p>
      </div>
      <button 
        @click="openAddModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl transition-all flex items-center gap-2 shadow-lg shadow-blue-900/20"
      >
        <span class="text-xl">+</span> Add Product
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
    </div>

    <!-- Mobile Grid (Hidden on desktop) -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:hidden">
      <ProductCard 
        v-for="product in products" 
        :key="product.id"
        :product="product"
        @edit="openEditModal"
        @delete="confirmDelete"
        @toggle-status="handleToggleStatus"
      />
    </div>

    <!-- Desktop Table (Hidden on mobile) -->
    <div v-if="!loading" class="hidden md:block overflow-x-auto bg-[#1a1a1a]/60 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-white/10 bg-white/5">
            <th class="px-6 py-4 text-sm font-semibold text-gray-300">PICTURE</th>
            <th class="px-6 py-4 text-sm font-semibold text-gray-300">PRODUCT NAME</th>
            <th class="px-6 py-4 text-sm font-semibold text-gray-300">COST PRICE</th>
            <th class="px-6 py-4 text-sm font-semibold text-gray-300">SELLING PRICE</th>
            <th class="px-6 py-4 text-sm font-semibold text-gray-300">STOCK LEVEL</th>
            <th class="px-6 py-4 text-sm font-semibold text-gray-300">STATUS</th>
            <th class="px-6 py-4 text-sm font-semibold text-gray-300">LIVE STATUS</th>
            <th class="px-6 py-4 text-sm font-semibold text-gray-300 sticky right-0 bg-[#1a1a1a]/80 backdrop-blur-md border-l border-white/10">ACTIONS</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="product in products" :key="product.id" class="hover:bg-white/[0.05] transition-colors group">
            <td class="px-6 py-4">
              <img :src="getImageUrl(product.image)" class="w-12 h-12 rounded-lg object-cover border border-white/10 shadow-sm" />
            </td>
            <td class="px-6 py-4">
              <div class="text-white font-medium">{{ product.name }}</div>
            </td>
            <td class="px-6 py-4 text-gray-400">₱{{ formatPrice(product.cost_price) }}</td>
            <td class="px-6 py-4 text-white font-semibold">₱{{ formatPrice(product.selling_price) }}</td>
            <td class="px-6 py-4">
              <span class="text-white">{{ product.current_stock }}</span>
              <span class="text-gray-500 text-xs ml-1">{{ product.unit }}</span>
            </td>
            <td class="px-6 py-4">
              <span 
                v-if="product.current_stock > 0"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-500 border border-green-500/20 backdrop-blur-sm"
              >
                In Stock
              </span>
              <span 
                v-else
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-500 border border-red-500/20 backdrop-blur-sm"
              >
                Out of Stock
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-12 flex justify-center">
                  <ProductToggle 
                    :model-value="product.is_available" 
                    @toggle="handleToggleStatus(product)"
                  />
                </div>
                <span 
                  :class="product.is_available == 1 ? 'text-green-500' : 'text-gray-500'"
                  class="text-xs font-bold uppercase tracking-wider"
                >
                  {{ product.is_available == 1 ? 'LIVE' : 'HIDDEN' }}
                </span>
              </div>
            </td>
            <td class="px-6 py-4 sticky right-0 bg-[#1a1a1a]/80 backdrop-blur-md border-l border-white/10">
              <div class="flex gap-2">
                <button 
                  @click="openEditModal(product)"
                  class="p-2 text-blue-400 hover:bg-blue-400/10 rounded-lg transition-colors"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button 
                  @click="confirmDelete(product)"
                  class="p-2 text-red-400 hover:bg-red-400/10 rounded-lg transition-colors"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Product Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
      <div class="bg-[#1a1a1a]/90 backdrop-blur-xl border border-white/10 w-full max-w-2xl rounded-3xl overflow-hidden shadow-2xl">
        <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center bg-white/5">
          <h3 class="text-xl font-bold text-white">{{ isEditing ? 'Edit Product' : 'Add New Product' }}</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-white transition-colors p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="handleSubmit" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Product Name</label>
                <input 
                  v-model="form.name" 
                  type="text" 
                  required
                  class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-blue-500 transition-colors"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Unit (e.g., kg, pcs)</label>
                <input 
                  v-model="form.unit" 
                  type="text" 
                  required
                  class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-blue-500 transition-colors"
                />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-400 mb-1">Cost Price</label>
                  <div class="relative">
                    <span class="absolute left-4 top-2 text-gray-500">₱</span>
                    <input 
                      v-model="form.cost_price" 
                      type="number" 
                      step="0.01" 
                      required
                      class="w-full bg-white/5 border border-white/10 rounded-xl pl-8 pr-4 py-2 text-white focus:outline-none focus:border-blue-500 transition-colors"
                    />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-400 mb-1">Selling Price</label>
                  <div class="relative">
                    <span class="absolute left-4 top-2 text-gray-500">₱</span>
                    <input 
                      v-model="form.selling_price" 
                      type="number" 
                      step="0.01" 
                      required
                      class="w-full bg-white/5 border border-white/10 rounded-xl pl-8 pr-4 py-2 text-white focus:outline-none focus:border-blue-500 transition-colors"
                    />
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">
                  {{ isEditing ? 'Current Stock' : 'Initial Stock' }}
                </label>
                <input 
                  v-model="form.quantity" 
                  type="number" 
                  step="0.01" 
                  required
                  class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-blue-500 transition-colors"
                />
              </div>
            </div>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Product Image</label>
                <div 
                  class="relative group border-2 border-dashed border-white/10 rounded-2xl aspect-square flex flex-col items-center justify-center overflow-hidden hover:border-blue-500/50 transition-colors"
                  @click="$refs.fileInput.click()"
                >
                  <img 
                    v-if="imagePreview" 
                    :src="imagePreview" 
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-500 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Click to upload image</p>
                  </div>
                  <input 
                    ref="fileInput" 
                    type="file" 
                    class="hidden" 
                    accept="image/*"
                    @change="handleImageChange"
                  />
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-8 flex gap-4">
            <button 
              type="button" 
              @click="showModal = false"
              class="flex-1 bg-white/5 hover:bg-white/10 text-white font-semibold py-3 rounded-xl transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="submitting"
              class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-800 text-white font-semibold py-3 rounded-xl transition-all shadow-lg shadow-blue-900/20"
            >
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Product' : 'Add Product') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import ProductCard from './components/ProductCard.vue';
import ProductToggle from './components/ProductToggle.vue';

const products = ref([]);
const loading = ref(true);
const submitting = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const imagePreview = ref(null);
const csrfToken = ref('');

const form = ref({
  id: null,
  name: '',
  unit: '',
  cost_price: 0,
  selling_price: 0,
  quantity: 0,
  image: null
});

onMounted(() => {
  axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  fetchProducts();
});

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/admin/products/list');
    if (response.data.status === 'success') {
      products.value = response.data.data;
      csrfToken.value = response.data.token;
    }
  } catch (error) {
    console.error('Error fetching products:', error);
  } finally {
    loading.value = false;
  }
};

const getImageUrl = (image) => {
  return image ? `/uploads/products/${image}` : '/images/logo.png';
};

const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('en-US', { minimumFractionDigits: 2 });
};

const openAddModal = () => {
  isEditing.value = false;
  form.value = {
    id: null,
    name: '',
    unit: '',
    cost_price: 0,
    selling_price: 0,
    quantity: 0,
    image: null
  };
  imagePreview.value = null;
  showModal.value = true;
};

const openEditModal = (product) => {
  isEditing.value = true;
  form.value = {
    id: product.id,
    name: product.name,
    unit: product.unit,
    cost_price: product.cost_price,
    selling_price: product.selling_price,
    quantity: product.current_stock,
    image: null
  };
  imagePreview.value = getImageUrl(product.image);
  showModal.value = true;
};

const handleImageChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.value.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const handleToggleStatus = async (product) => {
  try {
    const formData = new FormData();
    formData.append('csrf_test_name', csrfToken.value);

    const response = await axios.post(`/admin/products/toggleStatus/${product.id}`, formData);
    
    if (response.data.status === 'success') {
      product.is_available = response.data.is_available;
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Error toggling status:', error);
    alert('Failed to update availability status');
  }
};

const handleSubmit = async () => {
  submitting.value = true;
  const formData = new FormData();
  Object.keys(form.value).forEach(key => {
    if (form.value[key] !== null) {
      formData.append(key, form.value[key]);
    }
  });
  
  if (isEditing.value) {
    // For update, we use current_stock field name as expected by the controller
    formData.append('current_stock', form.value.quantity);
  }

  formData.append('csrf_test_name', csrfToken.value);

  const url = isEditing.value ? '/admin/products/update' : '/admin/products/store';

  try {
    const response = await axios.post(url, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    if (response.data.status === 'success') {
      showModal.value = false;
      await fetchProducts();
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Error saving product:', error);
    alert(error.response?.data?.message || 'An error occurred');
  } finally {
    submitting.value = false;
  }
};

const confirmDelete = async (product) => {
  if (confirm(`Are you sure you want to delete ${product.name}?`)) {
    const formData = new FormData();
    formData.append('id', product.id);
    formData.append('csrf_test_name', csrfToken.value);

    try {
      const response = await axios.post('/admin/products/delete', formData);
      if (response.data.status === 'success') {
        await fetchProducts();
      } else {
        alert(response.data.message);
      }
    } catch (error) {
      console.error('Error deleting product:', error);
      alert('An error occurred while deleting the product');
    }
  }
};
</script>

<style scoped>
/* Any component-specific styles */
</style>
