<template>
  <DeveloperLayout>
    <div class="space-y-6 md:space-y-8">
      <!-- Header -->
      <div>
        <h1 class="text-xl md:text-[3rem] font-extrabold tracking-tight bg-gradient-to-r from-white to-cyan-400 bg-clip-text text-transparent leading-tight mb-1">
          User Management
        </h1>
        <p class="text-xs md:text-[1.1rem] font-medium text-white/50">
          Create, edit, and manage all system users.
        </p>
      </div>

      <!-- Add User Form -->
      <div class="p-4 md:p-[30px] rounded-xl md:rounded-[24px] border border-white/[0.08] bg-slate-900/40">
        <h3 class="text-base md:text-lg font-bold text-white mb-4 md:mb-6">Create New User</h3>
        <form @submit.prevent="saveUser" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Username</label>
            <input
              v-model="addForm.username"
              type="text"
              placeholder="Username..."
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/[0.08] rounded-xl text-white text-sm focus:outline-none focus:border-cyan-500/50 transition-colors"
            />
          </div>
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Email</label>
            <input
              v-model="addForm.email"
              type="email"
              placeholder="Email address..."
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/[0.08] rounded-xl text-white text-sm focus:outline-none focus:border-cyan-500/50 transition-colors"
            />
          </div>
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Password</label>
            <input
              v-model="addForm.password"
              type="password"
              placeholder="••••••••"
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/[0.08] rounded-xl text-white text-sm focus:outline-none focus:border-cyan-500/50 transition-colors"
            />
          </div>
          <div class="space-y-1.5">
            <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Role</label>
            <select
              v-model="addForm.role"
              required
              class="w-full px-4 py-3 bg-white/5 border border-white/[0.08] rounded-xl text-white text-sm focus:outline-none focus:border-cyan-500/50 transition-colors appearance-none"
            >
              <option value="admin" class="bg-slate-900">Admin</option>
              <option value="staff" class="bg-slate-900">Staff</option>
              <option value="customer" class="bg-slate-900">Customer</option>
              <option value="developer" class="bg-slate-900">Developer</option>
            </select>
          </div>
          <div class="md:col-span-4">
            <button
              type="submit"
              :disabled="isSubmitting"
              class="w-full md:w-auto px-6 py-3 md:px-8 md:py-3.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl font-bold shadow-lg shadow-cyan-500/20 transition-all active:scale-[0.98] disabled:opacity-50 text-sm md:text-base"
            >
              {{ isSubmitting ? 'Creating...' : 'Create User' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Filter Tabs -->
      <div class="flex flex-wrap gap-2">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="activeTab = tab.value"
          :class="activeTab === tab.value ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-400' : 'bg-white/[0.05] border-white/[0.1] text-white/60 hover:text-white'"
          class="px-3 py-1.5 rounded-lg border text-xs font-bold uppercase tracking-wider transition-all"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Users Table -->
      <div class="overflow-x-auto rounded-xl md:rounded-2xl border border-white/[0.08] bg-slate-900/60">
        <table class="w-full text-left text-xs md:text-sm">
          <thead>
            <tr class="border-b border-white/[0.08] text-white/50 font-bold uppercase tracking-wider">
              <th class="p-2 md:p-3">Username</th>
              <th class="p-2 md:p-3">Email</th>
              <th class="p-2 md:p-3">Role</th>
              <th class="p-2 md:p-3 hidden md:table-cell">Last Active</th>
              <th class="p-2 md:p-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in filteredUsers" :key="user.id" class="border-b border-white/[0.05] hover:bg-white/[0.03] transition-colors">
              <td class="p-2 md:p-3 font-semibold text-white">{{ user.username }}</td>
              <td class="p-2 md:p-3 text-white/60">{{ user.email }}</td>
              <td class="p-2 md:p-3">
                <span
                  class="px-2 py-0.5 rounded-full text-[0.6rem] font-black tracking-wider uppercase"
                  :class="roleBadge(user.role)"
                >
                  {{ user.role }}
                </span>
              </td>
              <td class="p-2 md:p-3 hidden md:table-cell text-white/40 text-[0.65rem]">{{ formatTime(user.last_active) }}</td>
              <td class="p-2 md:p-3 text-right">
                <div class="flex justify-end gap-1.5">
                  <button @click="openEditModal(user)" class="w-7 h-7 flex items-center justify-center hover:bg-white/10 rounded-lg transition-all active:scale-90">
                    <Edit class="w-3.5 h-3.5 text-white/40" />
                  </button>
                  <button @click="deleteUser(user.id)" class="w-7 h-7 flex items-center justify-center hover:bg-rose-500/20 rounded-lg transition-all active:scale-90">
                    <Trash2 class="w-3.5 h-3.5 text-white/40 hover:text-rose-400" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!filteredUsers.length">
              <td colspan="5" class="p-8 text-center text-white/30 italic">No users found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Edit Modal -->
      <div v-if="editingUser" class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/80">
        <div class="w-full sm:max-w-md p-6 sm:p-8 rounded-t-3xl sm:rounded-2xl border border-white/[0.08] bg-slate-900 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg md:text-xl font-bold text-white">Edit User</h2>
            <button @click="editingUser = null" class="p-2 hover:bg-white/10 rounded-full transition-colors">
              <X class="w-5 h-5 text-white" />
            </button>
          </div>

          <form @submit.prevent="updateUser" class="space-y-4">
            <div class="space-y-1.5">
              <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Username</label>
              <input
                v-model="editForm.username"
                type="text"
                required
                class="w-full px-4 py-3 bg-white/5 border border-white/[0.08] rounded-xl text-white text-sm focus:outline-none focus:border-cyan-500/50 transition-colors"
              />
            </div>
            <div class="space-y-1.5">
              <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Email</label>
              <input
                v-model="editForm.email"
                type="email"
                required
                class="w-full px-4 py-3 bg-white/5 border border-white/[0.08] rounded-xl text-white text-sm focus:outline-none focus:border-cyan-500/50 transition-colors"
              />
            </div>
            <div class="space-y-1.5">
              <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">New Password <span class="text-white/30 font-normal normal-case">(optional)</span></label>
              <input
                v-model="editForm.password"
                type="password"
                placeholder="Leave blank to keep current"
                class="w-full px-4 py-3 bg-white/5 border border-white/[0.08] rounded-xl text-white text-sm focus:outline-none focus:border-cyan-500/50 transition-colors"
              />
            </div>
            <div class="space-y-1.5">
              <label class="text-[0.65rem] font-black text-white/40 uppercase tracking-widest">Role</label>
              <select
                v-model="editForm.role"
                required
                class="w-full px-4 py-3 bg-white/5 border border-white/[0.08] rounded-xl text-white text-sm focus:outline-none focus:border-cyan-500/50 transition-colors appearance-none"
              >
                <option value="admin" class="bg-slate-900">Admin</option>
                <option value="staff" class="bg-slate-900">Staff</option>
                <option value="customer" class="bg-slate-900">Customer</option>
                <option value="developer" class="bg-slate-900">Developer</option>
              </select>
            </div>

            <button
              type="submit"
              :disabled="isSubmitting"
              class="w-full py-3.5 bg-gradient-to-r from-cyan-600 to-teal-600 hover:from-cyan-500 hover:to-teal-500 text-white rounded-xl font-bold shadow-lg shadow-cyan-500/20 transition-all active:scale-[0.98] disabled:opacity-50"
            >
              {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </DeveloperLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { Edit, Trash2, X } from 'lucide-vue-next';
import DeveloperLayout from '../../layouts/DeveloperLayout.vue';

const users = ref([]);
const editingUser = ref(null);
const isSubmitting = ref(false);
const activeTab = ref('all');

const tabs = [
  { label: 'All', value: 'all' },
  { label: 'Admin', value: 'admin' },
  { label: 'Staff', value: 'staff' },
  { label: 'Customer', value: 'customer' },
  { label: 'Developer', value: 'developer' },
];

const addForm = ref({
  username: '',
  email: '',
  password: '',
  role: 'customer',
});

const editForm = ref({
  id: '',
  username: '',
  email: '',
  password: '',
  role: 'customer',
});

const filteredUsers = computed(() => {
  if (activeTab.value === 'all') return users.value;
  return users.value.filter(u => u.role === activeTab.value);
});

const roleBadge = (role) => {
  const r = role?.toLowerCase();
  if (r === 'admin') return 'bg-rose-500/20 text-rose-400 border border-rose-500/30';
  if (r === 'staff') return 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30';
  if (r === 'customer') return 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
  if (r === 'developer') return 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30';
  return 'bg-slate-500/20 text-slate-400';
};

const formatTime = (ts) => {
  if (!ts) return '—';
  const d = new Date(ts);
  const now = new Date();
  const diff = (now - d) / 1000;
  if (diff < 60) return 'Just now';
  if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
  if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
  return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const fetchUsers = async () => {
  try {
    const res = await axios.get('/api/developer/users');
    if (res.data?.status === 'success') {
      users.value = res.data.data || [];
    }
  } catch (err) {
    console.error('Failed to fetch users:', err);
  }
};

const saveUser = async () => {
  isSubmitting.value = true;
  try {
    const formData = new FormData();
    for (const key in addForm.value) {
      formData.append(key, addForm.value[key]);
    }

    const res = await axios.post('/api/developer/users/save', formData);
    if (res.data?.status === 'success') {
      fetchUsers();
      addForm.value = { username: '', email: '', password: '', role: 'customer' };
    } else {
      alert(res.data?.message || 'Failed to create user');
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to create user');
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

    const res = await axios.post('/api/developer/users/update', formData);
    if (res.data?.status === 'success') {
      fetchUsers();
      editingUser.value = null;
    } else {
      alert(res.data?.message || 'Failed to update user');
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update user');
  } finally {
    isSubmitting.value = false;
  }
};

const deleteUser = (id) => {
  if (!confirm('Are you sure you want to delete this user?')) return;

  axios.post(`/api/developer/users/delete/${id}`)
    .then(res => {
      if (res.data?.status === 'success') {
        fetchUsers();
      } else {
        alert(res.data?.message);
      }
    })
    .catch(err => {
      alert(err.response?.data?.message || 'Failed to delete user');
    });
};

onMounted(() => {
  fetchUsers();
});
</script>
