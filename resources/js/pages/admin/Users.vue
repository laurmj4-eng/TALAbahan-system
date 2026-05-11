<template>
  <AdminLayout>
    <div class="space-y-8">
      <div>
        <h1 class="text-3xl font-bold text-white">System Architecture Database</h1>
        <p class="text-white/60 mt-2">Append, modify, or terminate entity access securely.</p>
      </div>

      <!-- Add User Form -->
      <GlassCard customClass="p-8">
        <h3 class="text-lg font-bold text-white mb-6">Add New Entity</h3>
        <form @submit.prevent="saveUser" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Node Identity (Username)</label>
            <input 
              v-model="addForm.username" 
              type="text" 
              placeholder="Username..." 
              required
              class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Transmission Vector (Email)</label>
            <input 
              v-model="addForm.email" 
              type="email" 
              placeholder="Email address..." 
              required
              class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Clearance Key (Password)</label>
            <input 
              v-model="addForm.password" 
              type="password" 
              placeholder="••••••••" 
              required
              class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Clearance Array (Role)</label>
            <select 
              v-model="addForm.role"
              required
              class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
              <option value="admin">Admin</option>
              <option value="staff">Staff Member</option>
              <option value="customer">Customer</option>
            </select>
          </div>
          <div class="md:col-span-4 flex justify-end">
            <button 
              type="submit"
              :disabled="isSubmitting"
              class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-95 disabled:opacity-50"
            >
              {{ isSubmitting ? 'EXECUTING...' : 'ADD ENTITY+' }}
            </button>
          </div>
        </form>
      </GlassCard>

      <!-- Users Table -->
      <GlassCard customClass="overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-white/5 border-b border-white/10">
                <th class="px-6 py-4 font-semibold text-white/70">ID</th>
                <th class="px-6 py-4 font-semibold text-white/70">EMAIL ADDRESS</th>
                <th class="px-6 py-4 font-semibold text-white/70">ROLE</th>
                <th class="px-6 py-4 font-semibold text-white/70 text-right">ACTIONS</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="user in users" :key="user.id" class="hover:bg-white/5 transition-colors group">
                <td class="px-6 py-4">
                  <strong class="text-white font-bold">{{ user.username }}</strong>
                </td>
                <td class="px-6 py-4 text-white/60">
                  {{ user.email }}
                </td>
                <td class="px-6 py-4">
                  <span 
                    class="px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase"
                    :class="getRoleClass(user.role)"
                  >
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="openEditModal(user)" class="p-2 hover:bg-white/10 rounded-xl transition-all group/btn">
                      <Edit class="w-4 h-4 text-white/40 group-hover/btn:text-white" />
                    </button>
                    <button @click="deleteUser(user.id)" class="p-2 hover:bg-red-500/20 rounded-xl transition-all group/btn">
                      <Trash2 class="w-4 h-4 text-white/40 group-hover/btn:text-red-400" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="users.length === 0">
                <td colspan="4" class="px-6 py-24 text-center text-white/20 italic">
                  No records found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </GlassCard>
    </div>

    <!-- Edit Modal -->
    <div v-if="editingUser" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
      <GlassCard customClass="w-full max-w-md p-8 relative">
        <button @click="editingUser = null" class="absolute top-6 right-6 p-2 hover:bg-white/10 rounded-full transition-colors">
          <X class="w-6 h-6 text-white" />
        </button>

        <h2 class="text-2xl font-bold text-white mb-8">Override Protocol</h2>

        <form @submit.prevent="updateUser" class="space-y-6">
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Node Identity</label>
            <input 
              v-model="editForm.username" 
              type="text" 
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Transmission Vector</label>
            <input 
              v-model="editForm.email" 
              type="email" 
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">New Clearance Key (Optional)</label>
            <input 
              v-model="editForm.password" 
              type="password" 
              placeholder="Leave blank to keep current password"
              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-2">
            <label class="text-xs font-black text-white/40 uppercase tracking-widest">Clearance Array</label>
            <select 
              v-model="editForm.role"
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
              <option value="admin">Administrator</option>
              <option value="staff">Staff Command</option>
              <option value="customer">Customer Standard</option>
            </select>
          </div>

          <button 
            type="submit"
            :disabled="isSubmitting"
            class="w-full py-4 bg-gradient-to-r from-indigo-600 to-violet-700 hover:from-indigo-500 hover:to-violet-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-95 disabled:opacity-50"
          >
            {{ isSubmitting ? 'EXECUTING...' : 'EXECUTE UPDATE' }}
          </button>
        </form>
      </GlassCard>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Edit, Trash2, X } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const users = ref([]);
const editingUser = ref(null);
const isSubmitting = ref(false);

const addForm = ref({
  username: '',
  email: '',
  password: '',
  role: 'customer'
});

const editForm = ref({
  id: '',
  username: '',
  email: '',
  password: '',
  role: 'customer'
});

const getRoleClass = (role) => {
  const r = role?.toLowerCase();
  if (r === 'admin') return 'bg-rose-500/20 text-rose-400 border border-rose-500/30';
  if (r === 'staff') return 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30';
  return 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
};

const fetchUsers = async () => {
  try {
    const response = await axios.get('/api/admin/users'); // Use existing route if possible or create new
    users.value = response.data.data || response.data;
  } catch (error) {
    console.error('Failed to fetch users:', error);
  }
};

const saveUser = async () => {
  isSubmitting.value = true;
  try {
    const formData = new FormData();
    for (const key in addForm.value) {
      formData.append(key, addForm.value[key]);
    }
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    const response = await axios.post('/admin/saveUser', formData);
    if (response.data.status === 'success') {
      fetchUsers();
      addForm.value = { username: '', email: '', password: '', role: 'customer' };
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Failed to save user:', error);
    alert('Failed to save user');
  } finally {
    isSubmitting.value = false;
  }
};

const openEditModal = (user) => {
  editingUser.value = user;
  editForm.value = { ...user, password: '' };
};

const updateUser = async () => {
  isSubmitting.value = true;
  try {
    const formData = new FormData();
    for (const key in editForm.value) {
      formData.append(key, editForm.value[key]);
    }
    if (window.CSRF_TOKEN_NAME) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    const response = await axios.post('/admin/updateUser', formData);
    if (response.data.status === 'success') {
      fetchUsers();
      editingUser.value = null;
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    console.error('Failed to update user:', error);
    alert('Failed to update user');
  } finally {
    isSubmitting.value = false;
  }
};

const deleteUser = async (id) => {
  if (!confirm('Confirm Termination?')) return;
  try {
    const response = await axios.get(`/admin/deleteUser/${id}`);
    if (response.data.status === 'success' || response.status === 200) {
      fetchUsers();
    }
  } catch (error) {
    console.error('Failed to delete user:', error);
  }
};

onMounted(() => {
  fetchUsers();
});
</script>
