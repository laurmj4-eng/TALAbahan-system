<template>
  <AdminLayout>
    <div class="space-y-8">
      <div class="flex justify-between items-end">
        <div>
          <h1 class="text-3xl font-bold text-white">Shipping Locations 📍</h1>
          <p class="text-white/60 mt-2">Manage specific barangays and places allowed for delivery.</p>
        </div>
        <button @click="isAddModalOpen = true" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 transition-all">
          <Plus class="w-4 h-4 inline-block mr-2" /> Add Location
        </button>
      </div>

      <!-- Global Shipping Toggle -->
      <div class="flex items-center gap-6 bg-white/5 p-6 rounded-2xl border border-white/10">
        <span class="font-bold text-white">Ship to All Locations (Global)</span>
        <label class="relative inline-flex items-center cursor-pointer group">
          <input type="checkbox" v-model="isGlobalShipping" @change="toggleGlobalShipping" class="sr-only peer">
          <div class="w-12 h-6 bg-white/10 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-500"></div>
        </label>
        <span class="text-sm font-medium" :class="isGlobalShipping ? 'text-violet-400' : 'text-white/40'">
          Currently: {{ isGlobalShipping ? 'ON (Shipping to all)' : 'OFF (Specific locations only)' }}
        </span>
      </div>

      <GlassCard customClass="overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-white/5 border-b border-white/10">
                <th class="px-6 py-4 font-semibold text-white/70">BARANGAY NAME</th>
                <th class="px-6 py-4 font-semibold text-white/70">CITY / MUNICIPALITY</th>
                <th class="px-6 py-4 font-semibold text-white/70">STATUS</th>
                <th class="px-6 py-4 font-semibold text-white/70 text-right">ACTIONS</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="location in locations" :key="location.id" class="hover:bg-white/5 transition-colors group">
                <td class="px-6 py-4">
                  <strong class="text-white font-bold">{{ location.barangay_name }}</strong>
                </td>
                <td class="px-6 py-4 text-white/60">
                  {{ location.city_municipality }}
                </td>
                <td class="px-6 py-4">
                  <span 
                    class="px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase"
                    :class="parseInt(location.is_active) ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30'"
                  >
                    {{ parseInt(location.is_active) ? 'Shippable' : 'Not Shippable' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="openEditModal(location)" class="p-2 hover:bg-white/10 rounded-xl transition-all group/btn">
                      <Edit class="w-4 h-4 text-white/40 group-hover/btn:text-white" />
                    </button>
                    <button @click="deleteLocation(location.id)" class="p-2 hover:bg-red-500/20 rounded-xl transition-all group/btn">
                      <Trash2 class="w-4 h-4 text-white/40 group-hover/btn:text-red-400" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="locations.length === 0">
                <td colspan="4" class="px-6 py-24 text-center text-white/20 italic">
                  No shipping locations found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </GlassCard>
    </div>

    <!-- Modals -->
    <div v-if="isAddModalOpen || editingLocation" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
      <GlassCard customClass="w-full max-w-md p-8 relative">
        <button @click="closeModals" class="absolute top-6 right-6 p-2 hover:bg-white/10 rounded-full transition-colors">
          <X class="w-6 h-6 text-white" />
        </button>

        <h2 class="text-2xl font-bold text-white mb-8">{{ editingLocation ? 'Edit Location' : 'Add New Location' }}</h2>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Barangay Name</label>
            <input 
              v-model="form.barangay_name" 
              type="text" 
              placeholder="e.g. Villamonte" 
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">City / Municipality</label>
            <input 
              v-model="form.city_municipality" 
              type="text" 
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div v-if="editingLocation" class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Status</label>
            <select 
              v-model="form.is_active"
              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
              <option value="1">Shippable</option>
              <option value="0">Not Shippable</option>
            </select>
          </div>

          <button 
            type="submit"
            :disabled="isSubmitting"
            class="w-full py-4 bg-gradient-to-r from-indigo-600 to-violet-700 hover:from-indigo-500 hover:to-violet-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-95 disabled:opacity-50"
          >
            {{ isSubmitting ? 'SAVING...' : (editingLocation ? 'UPDATE LOCATION' : 'ADD SHIPPING LOCATION') }}
          </button>
        </form>
      </GlassCard>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Plus, Edit, Trash2, X } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const locations = ref([]);
const isGlobalShipping = ref(false);
const isAddModalOpen = ref(false);
const editingLocation = ref(null);
const isSubmitting = ref(false);

const form = ref({
  barangay_name: '',
  city_municipality: 'Bacolod City',
  is_active: '1'
});

const fetchLocations = async () => {
  try {
    const response = await axios.get('/api/admin/shipping'); // You'll need this endpoint
    locations.value = response.data.locations;
    isGlobalShipping.value = response.data.ship_to_all === '1';
  } catch (error) {
    console.error('Failed to fetch locations:', error);
  }
};

const closeModals = () => {
  isAddModalOpen.value = false;
  editingLocation.value = null;
  form.value = {
    barangay_name: '',
    city_municipality: 'Bacolod City',
    is_active: '1'
  };
};

const openEditModal = (location) => {
  editingLocation.value = location;
  form.value = { ...location };
  // is_active might come as number, convert to string for select
  form.value.is_active = String(location.is_active);
};

const handleSubmit = async () => {
  isSubmitting.value = true;
  try {
    const formData = new FormData();
    for (const key in form.value) {
      formData.append(key, form.value[key]);
    }
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    const endpoint = editingLocation.value ? '/admin/shipping/update' : '/admin/shipping/store';
    const response = await axios.post(endpoint, formData);
    
    if (response.data.status === 'success') {
      fetchLocations();
      closeModals();
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Failed to save location:', error);
    alert('Failed to save location');
  } finally {
    isSubmitting.value = false;
  }
};

const deleteLocation = async (id) => {
  if (!confirm('Are you sure you want to delete this shipping location?')) return;
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    const response = await axios.post('/admin/shipping/delete', formData);
    if (response.data.status === 'success') {
      fetchLocations();
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Failed to delete location:', error);
    alert('Failed to delete location');
  }
};

const toggleGlobalShipping = async () => {
  try {
    const formData = new FormData();
    formData.append('ship_to_all', isGlobalShipping.value ? '1' : '0');
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    const response = await axios.post('/admin/shipping/updateGlobal', formData);
    if (response.data.status !== 'success') {
      alert(response.data.message);
      isGlobalShipping.value = !isGlobalShipping.value;
    }
  } catch (error) {
    console.error('Failed to toggle global shipping:', error);
    alert('Failed to toggle global shipping');
    isGlobalShipping.value = !isGlobalShipping.value;
  }
};

onMounted(() => {
  fetchLocations();
});
</script>
