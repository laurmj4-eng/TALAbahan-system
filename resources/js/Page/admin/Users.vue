<template>
  <AdminLayout>
    <div class="flex-1 flex flex-col space-y-6 md:space-y-8 min-h-0 overflow-x-hidden">
      <!-- Hero Header -->
      <div class="px-1">
        <h1 class="text-[2.25rem] font-[800] tracking-tight bg-gradient-to-r from-white to-white/60 bg-clip-text text-transparent leading-tight">
          System Architecture Database
        </h1>
        <p class="text-white/50 mt-2 text-sm">Append, modify, or terminate entity access securely.</p>
      </div>

      <!-- Add User Form -->
      <GlassCard customClass="p-4 md:p-8">
        <h3 class="text-lg font-bold text-white mb-4 md:mb-6">Add New Entity</h3>
        <form @submit.prevent="saveUser" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Username</label>
            <input 
              v-model="addForm.username" 
              type="text" 
              placeholder="Username..." 
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-[16px] focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Email</label>
            <input 
              v-model="addForm.email" 
              type="email" 
              placeholder="Email address..." 
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-[16px] focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Password</label>
            <input 
              v-model="addForm.password" 
              type="password" 
              placeholder="••••••••" 
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-[16px] focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Role</label>
            <select 
              v-model="addForm.role"
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-[16px] focus:outline-none focus:border-indigo-500/50 transition-colors appearance-none"
            >
              <option value="admin" class="bg-slate-900">Admin</option>
              <option value="staff" class="bg-slate-900">Staff</option>
              <option value="customer" class="bg-slate-900">Customer</option>
            </select>
          </div>
          <div class="md:col-span-4">
            <button 
              type="submit"
              :disabled="isSubmitting"
              class="w-full md:w-auto md:ml-auto flex justify-center px-6 py-3 md:px-8 md:py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98] disabled:opacity-50 touch-manipulation text-sm md:text-base"
            >
              {{ isSubmitting ? 'EXECUTING...' : 'ADD ENTITY+' }}
            </button>
          </div>
        </form>
      </GlassCard>

      <!-- Users Table -->
      <GlassCard customClass="overflow-hidden flex-1 flex flex-col min-h-0 !p-0">
        <div class="overflow-x-auto overflow-y-auto max-h-[60vh] md:max-h-[calc(100dvh-450px)] scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
          <table class="w-full text-left" style="table-layout: fixed;">
            <colgroup>
              <col class="w-[80px] md:w-auto">
              <col>
              <col class="w-[80px] md:w-[100px]">
              <col class="w-[80px] md:w-[100px]">
            </colgroup>
            <thead class="sticky top-0 z-10 bg-[#0f172a]">
              <tr class="bg-white/5 border-b border-white/10">
                <th class="px-3 md:px-6 py-4 text-[0.65rem] font-black text-white/40 uppercase tracking-widest">ID</th>
                <th class="px-3 md:px-6 py-4 text-[0.65rem] font-black text-white/40 uppercase tracking-widest">EMAIL</th>
                <th class="px-3 md:px-6 py-4 text-[0.65rem] font-black text-white/40 uppercase tracking-widest">ROLE</th>
                <th class="px-3 md:px-6 py-4 text-[0.65rem] font-black text-white/40 uppercase tracking-widest text-right">ACTIONS</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="user in users" :key="user.id" class="hover:bg-white/[0.03] transition-colors group">
                <td class="px-3 md:px-6 py-4 md:py-5">
                  <strong class="text-white font-bold text-sm truncate block">{{ user.username }}</strong>
                </td>
                <td class="px-3 md:px-6 py-4 md:py-5 text-white/50 text-sm overflow-hidden text-ellipsis whitespace-nowrap">
                  {{ user.email }}
                </td>
                <td class="px-3 md:px-6 py-4 md:py-5">
                  <span 
                    class="px-2 md:px-3 py-1 rounded-full text-[0.55rem] md:text-[10px] font-black tracking-widest uppercase whitespace-nowrap"
                    :class="getRoleClass(user.role)"
                  >
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-3 md:px-6 py-4 md:py-5 text-right">
                  <div class="flex justify-end gap-1.5">
                    <button @click="openEditModal(user)" class="w-8 h-8 flex items-center justify-center hover:bg-white/10 rounded-lg transition-all active:scale-90 touch-manipulation">
                      <Edit class="w-4 h-4 text-white/40 group-hover:text-white" />
                    </button>
                    <button @click="deleteUser(user.id)" class="w-8 h-8 flex items-center justify-center hover:bg-red-500/20 rounded-lg transition-all active:scale-90 touch-manipulation">
                      <Trash2 class="w-4 h-4 text-white/40 group-hover:text-red-400" />
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
    <div v-if="editingUser" class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/80 ">
      <GlassCard customClass="w-full sm:max-w-md p-6 sm:p-8 relative rounded-t-3xl sm:rounded-2xl max-h-[90vh] overflow-y-auto">
        <button @click="editingUser = null" class="absolute top-4 right-4 sm:top-6 sm:right-6 p-2 hover:bg-white/10 rounded-full transition-colors active:scale-90 touch-manipulation">
          <X class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
        </button>

        <h2 class="text-xl sm:text-2xl font-bold text-white mb-6 sm:mb-8">Override Protocol</h2>

        <form @submit.prevent="updateUser" class="space-y-4 sm:space-y-6">
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Node Identity</label>
            <input 
              v-model="editForm.username" 
              type="text" 
              required
              class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white text-[16px] focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Transmission Vector</label>
            <input 
              v-model="editForm.email" 
              type="email" 
              required
              class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white text-[16px] focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">New Password (Optional)</label>
            <input 
              v-model="editForm.password" 
              type="password" 
              placeholder="Leave blank to keep current"
              class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white text-[16px] focus:outline-none focus:border-indigo-500/50 transition-colors"
            >
          </div>
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Clearance Array</label>
            <select 
              v-model="editForm.role"
              required
              class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white text-[16px] focus:outline-none focus:border-indigo-500/50 transition-colors appearance-none"
            >
              <option value="admin" class="bg-slate-900">Administrator</option>
              <option value="staff" class="bg-slate-900">Staff Command</option>
              <option value="customer" class="bg-slate-900">Customer Standard</option>
            </select>
          </div>

          <button 
            type="submit"
            :disabled="isSubmitting"
            class="w-full py-4 bg-gradient-to-r from-indigo-600 to-violet-700 hover:from-indigo-500 hover:to-violet-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98] disabled:opacity-50 touch-manipulation"
          >
            {{ isSubmitting ? 'EXECUTING...' : 'EXECUTE UPDATE' }}
          </button>
        </form>
      </GlassCard>
    </div>

    <!-- Atmosphere Glow -->
    <div class="fixed top-[-10%] right-[-10%] w-[40%] h-[40%] bg-violet-600/5 blur-[120px] rounded-full pointer-events-none z-[-1]"></div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Edit, Trash2, X } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';
import { runHeavyTaskWithoutBlockingUI } from '../../composables/usePerformance';

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
    const response = await axios.get('/api/admin/users');
    const data = response.data.data || response.data;
    users.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Failed to fetch users:', error);
    users.value = [];
  }
};

const saveUser = () => {
  isSubmitting.value = true;
  runHeavyTaskWithoutBlockingUI(async () => {
    try {
      const formData = new FormData();
      for (const key in addForm.value) {
        formData.append(key, addForm.value[key]);
      }
      if (window.CSRF_TOKEN_NAME) {
        formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
      }

      const response = await axios.post('/api/admin/users/save', formData);
      if (response.data.status === 'success') {
        fetchUsers();
        addForm.value = { username: '', email: '', password: '', role: 'customer' };
      } else {
        alert(response.data.message);
      }
    } catch (error) {
      console.error('Failed to save user:', error);
      alert(error.response?.data?.message || 'Failed to save user');
    } finally {
      isSubmitting.value = false;
    }
  });
};

const openEditModal = (user) => {
  editingUser.value = user;
  editForm.value = { ...user, password: '' };
};

const updateUser = () => {
  isSubmitting.value = true;
  runHeavyTaskWithoutBlockingUI(async () => {
    try {
      const formData = new FormData();
      for (const key in editForm.value) {
        formData.append(key, editForm.value[key]);
      }
      if (window.CSRF_TOKEN_NAME) {
        formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
      }

      const response = await axios.post('/api/admin/users/update', formData);
      if (response.data.status === 'success') {
        fetchUsers();
        editingUser.value = null;
      } else {
        alert(response.data.message);
      }
    } catch (error) {
      console.error('Failed to update user:', error);
      alert(error.response?.data?.message || 'Failed to update user');
    } finally {
      isSubmitting.value = false;
    }
  });
};

const deleteUser = (id) => {
  if (!confirm('Are you sure you want to terminate this node?')) return;

  runHeavyTaskWithoutBlockingUI(async () => {
    try {
      const response = await axios.post(`/api/admin/users/delete/${id}`);
      if (response.data.status === 'success') {
        fetchUsers();
      } else {
        alert(response.data.message);
      }
    } catch (error) {
      console.error('Failed to delete user:', error);
      alert(error.response?.data?.message || 'Failed to delete user');
    }
  });
};

onMounted(() => {
  fetchUsers();
});
</script>
