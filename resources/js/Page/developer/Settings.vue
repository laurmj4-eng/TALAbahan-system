<template>
  <DeveloperLayout>
    <div class="space-y-6 md:space-y-10">
      <!-- Header -->
      <div>
        <h1 class="text-xl md:text-[3rem] font-extrabold tracking-tight bg-gradient-to-r from-white to-cyan-400 bg-clip-text text-transparent leading-tight mb-1">
          Dev Settings
        </h1>
        <p class="text-xs md:text-[1.1rem] font-medium text-white/50 mb-1">
          Update your developer account credentials.
        </p>
      </div>

      <!-- Profile Form -->
      <div class="p-4 md:p-[30px] rounded-xl md:rounded-[24px] border border-white/[0.08] bg-slate-900/40 max-w-lg">
        <div class="space-y-4 md:space-y-6">
          <div>
            <label class="block text-xs md:text-sm font-bold text-white/70 mb-1 md:mb-2">Email</label>
            <input
              :value="email"
              type="email"
              disabled
              class="w-full px-3 py-2 md:px-4 md:py-2.5 bg-white/[0.03] border border-white/[0.06] rounded-lg md:rounded-xl text-white/50 text-xs md:text-sm cursor-not-allowed"
            />
            <p class="text-[0.6rem] md:text-[0.7rem] text-white/30 mt-1">Email cannot be changed.</p>
          </div>

          <div>
            <label class="block text-xs md:text-sm font-bold text-white/70 mb-1 md:mb-2">Username</label>
            <input
              v-model="form.username"
              type="text"
              placeholder="Enter username..."
              class="w-full px-3 py-2 md:px-4 md:py-2.5 bg-white/[0.05] border border-white/[0.1] rounded-lg md:rounded-xl text-white text-xs md:text-sm placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition-all"
            />
          </div>

          <hr class="border-white/[0.08]" />

          <div>
            <label class="block text-xs md:text-sm font-bold text-white/70 mb-1 md:mb-2">Current Password</label>
            <input
              v-model="form.current_password"
              type="password"
              placeholder="Enter current password..."
              class="w-full px-3 py-2 md:px-4 md:py-2.5 bg-white/[0.05] border border-white/[0.1] rounded-lg md:rounded-xl text-white text-xs md:text-sm placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition-all"
            />
          </div>

          <div>
            <label class="block text-xs md:text-sm font-bold text-white/70 mb-1 md:mb-2">New Password</label>
            <input
              v-model="form.new_password"
              type="password"
              placeholder="Leave blank to keep current"
              class="w-full px-3 py-2 md:px-4 md:py-2.5 bg-white/[0.05] border border-white/[0.1] rounded-lg md:rounded-xl text-white text-xs md:text-sm placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition-all"
            />
          </div>

          <div>
            <label class="block text-xs md:text-sm font-bold text-white/70 mb-1 md:mb-2">Confirm New Password</label>
            <input
              v-model="form.confirm_password"
              type="password"
              placeholder="Re-enter new password"
              class="w-full px-3 py-2 md:px-4 md:py-2.5 bg-white/[0.05] border border-white/[0.1] rounded-lg md:rounded-xl text-white text-xs md:text-sm placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition-all"
            />
          </div>

          <button
            @click="saveProfile"
            :disabled="saving || !form.username.trim() || !form.current_password"
            :class="[
              saving ? 'opacity-60 cursor-wait' : 'hover:bg-cyan-500 hover:text-black hover:scale-[1.02] active:scale-95',
              !form.username.trim() || !form.current_password ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
            ]"
            class="w-full flex items-center justify-center gap-2 px-5 py-2.5 md:px-6 md:py-3 bg-cyan-500/20 border border-cyan-500/30 text-cyan-400 font-extrabold rounded-xl md:rounded-2xl transition-all text-xs md:text-sm shadow-lg shadow-cyan-500/10"
          >
            <Loader v-if="saving" class="w-4 h-4 animate-spin" />
            <span>{{ saving ? 'Saving...' : 'Save Changes' }}</span>
          </button>

          <!-- Result alert -->
          <div
            v-if="result"
            :class="result.success ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-rose-500/10 border-rose-500/20 text-rose-400'"
            class="p-3 md:p-4 rounded-xl border text-xs md:text-sm font-bold"
          >
            {{ result.message }}
          </div>
        </div>
      </div>
    </div>
  </DeveloperLayout>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { Loader } from 'lucide-vue-next';
import DeveloperLayout from '../../layouts/DeveloperLayout.vue';

const props = defineProps({
  username: { type: String, default: '' },
  email: { type: String, default: '' },
});

const form = ref({
  username: props.username,
  current_password: '',
  new_password: '',
  confirm_password: '',
});

const saving = ref(false);
const result = ref(null);

const saveProfile = async () => {
  if (saving.value) return;
  if (!form.value.username.trim() || !form.value.current_password) return;

  if (form.value.new_password && form.value.new_password !== form.value.confirm_password) {
    result.value = { success: false, message: 'New passwords do not match.' };
    return;
  }

  saving.value = true;
  result.value = null;

  try {
    const res = await axios.post('/api/developer/update-profile', {
      username: form.value.username.trim(),
      current_password: form.value.current_password,
      new_password: form.value.new_password || '',
    });

    if (res.data?.status === 'success') {
      result.value = { success: true, message: res.data.message || 'Profile updated!' };
      localStorage.setItem('username', form.value.username.trim());
      form.value.current_password = '';
      form.value.new_password = '';
      form.value.confirm_password = '';
    } else {
      result.value = { success: false, message: res.data?.message || 'Update failed.' };
    }
  } catch (err) {
    result.value = {
      success: false,
      message: err.response?.data?.message || err.message || 'Network error.',
    };
  } finally {
    saving.value = false;
  }
};
</script>
