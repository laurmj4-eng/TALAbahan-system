<template>
  <DeveloperLayout>
    <div class="space-y-6 md:space-y-10">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start gap-3 md:gap-4">
        <div>
          <h1 class="text-xl md:text-[3rem] font-extrabold tracking-tight bg-gradient-to-r from-white to-cyan-400 bg-clip-text text-transparent leading-tight mb-1">
            Dev Dashboard
          </h1>
          <p class="text-xs md:text-[1.1rem] font-medium text-white/50 mb-1">
            System health tracking and push notification broadcast console.
          </p>
          <p class="text-[0.7rem] md:text-[0.75rem] text-white/40">Total Devices: <span class="font-mono font-bold text-cyan-400">{{ devices.length }}</span></p>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
        <div class="p-3 md:p-[30px] rounded-xl md:rounded-[24px] border border-white/[0.08] bg-slate-900/40">
          <div class="w-9 h-9 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-500 text-base md:text-xl mb-3 md:mb-5">
            <Smartphone />
          </div>
          <div class="text-xs md:text-[0.9rem] font-bold text-slate-400 uppercase tracking-widest mb-1 md:mb-2">Total App Installs</div>
          <div class="text-lg md:text-[2.5rem] font-black bg-gradient-to-r from-cyan-400 to-teal-400 bg-clip-text text-transparent leading-none tracking-tight">
            {{ analytics.total_installs }}
          </div>
          <div class="text-[0.7rem] md:text-[0.8rem] text-slate-500">{{ analytics.active_devices }} active devices</div>
        </div>

        <div class="p-3 md:p-[30px] rounded-xl md:rounded-[24px] border border-white/[0.08] bg-slate-900/40">
          <div class="w-9 h-9 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 text-base md:text-xl mb-3 md:mb-5">
            <Activity />
          </div>
          <div class="text-xs md:text-[0.9rem] font-bold text-slate-400 uppercase tracking-widest mb-1 md:mb-2">Online Now</div>
          <div class="text-lg md:text-[2.5rem] font-black bg-gradient-to-r from-emerald-400 to-green-400 bg-clip-text text-transparent leading-none tracking-tight">
            {{ analytics.online_now }}
          </div>
          <div class="text-[0.7rem] md:text-[0.8rem] text-slate-500">active in the last 24h</div>
        </div>

        <div class="p-3 md:p-[30px] rounded-xl md:rounded-[24px] border border-white/[0.08] bg-slate-900/40">
          <div class="w-9 h-9 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 text-base md:text-xl mb-3 md:mb-5">
            <Radio />
          </div>
          <div class="text-xs md:text-[0.9rem] font-bold text-slate-400 uppercase tracking-widest mb-1 md:mb-2">Platforms</div>
          <div class="text-lg md:text-[2.5rem] font-black bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent leading-none tracking-tight">
            {{ deviceStats.platforms }}
          </div>
          <div class="text-[0.7rem] md:text-[0.8rem] text-slate-500">unique platforms</div>
        </div>

        <div class="p-3 md:p-[30px] rounded-xl md:rounded-[24px] border border-white/[0.08] bg-slate-900/40">
          <div class="w-9 h-9 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-500 text-base md:text-xl mb-3 md:mb-5">
            <Monitor />
          </div>
          <div class="text-xs md:text-[0.9rem] font-bold text-slate-400 uppercase tracking-widest mb-1 md:mb-2">App Versions</div>
          <div class="text-lg md:text-[2.5rem] font-black bg-gradient-to-r from-amber-400 to-orange-400 bg-clip-text text-transparent leading-none tracking-tight">
            {{ analytics.unique_versions }}
          </div>
          <div class="text-[0.7rem] md:text-[0.8rem] text-slate-500">unique versions</div>
        </div>
      </div>

      <!-- Device Tracking Table -->
      <div class="space-y-3 md:space-y-6">
        <div class="flex items-center gap-2 md:gap-3">
          <Smartphone class="text-cyan-500 w-4 h-4 md:w-6 md:h-6" />
          <h3 class="text-base md:text-xl font-bold text-white tracking-tight">Device Tracking</h3>
        </div>

        <div class="overflow-x-auto rounded-xl md:rounded-2xl border border-white/[0.08] bg-slate-900/60">
          <table class="w-full text-left text-xs md:text-sm">
            <thead>
              <tr class="border-b border-white/[0.08] text-white/50 font-bold uppercase tracking-wider">
                <th class="p-2 md:p-3">User</th>
                <th class="p-2 md:p-3">Device Model</th>
                <th class="p-2 md:p-3">App Version</th>
                <th class="p-2 md:p-3">Status</th>
                <th class="p-2 md:p-3">Last Seen</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="d in devices"
                :key="d.id"
                class="border-b border-white/[0.05] hover:bg-white/[0.03] transition-colors group cursor-pointer"
                @click="copyToken(d.token)"
              >
                <td class="p-2 md:p-3">
                  <div class="font-semibold text-white truncate max-w-[120px]">{{ d.username || '—' }}</div>
                  <div class="text-[0.6rem] text-white/40">{{ d.user_role || 'guest' }}</div>
                </td>
                <td class="p-2 md:p-3 text-white/80 font-mono text-[0.65rem] md:text-xs max-w-[160px] truncate" :title="d.device_model">
                  {{ d.device_model || '—' }}
                </td>
                <td class="p-2 md:p-3 text-white/70 font-mono text-xs whitespace-nowrap">{{ d.app_version || '—' }}</td>
                <td class="p-2 md:p-3 whitespace-nowrap">
                  <span v-if="d.is_online" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.6rem] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                    Online
                  </span>
                  <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.6rem] font-bold bg-slate-500/15 text-slate-400 border border-slate-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 inline-block"></span>
                    Inactive
                  </span>
                  <span v-if="d.is_trusted_admin_device" class="ml-1 px-1.5 py-0.5 rounded text-[0.55rem] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/20">TRUSTED</span>
                </td>
                <td class="p-2 md:p-3 text-white/60 text-[0.65rem] md:text-xs whitespace-nowrap" :title="d.last_connected">
                  {{ formatTime(d.last_connected || d.updated_at) }}
                </td>
              </tr>
              <tr v-if="!devices.length">
                <td colspan="5" class="p-8 text-center text-white/30 italic">No devices registered yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex justify-end">
          <span class="text-[0.6rem] text-white/30 italic">Click a row to copy its device token</span>
        </div>
      </div>

      <!-- Broadcast Console -->
      <div class="space-y-3 md:space-y-6">
        <div class="flex items-center gap-2 md:gap-3">
          <Radio class="text-cyan-500 w-4 h-4 md:w-6 md:h-6" />
          <h3 class="text-base md:text-xl font-bold text-white tracking-tight">Global Push Notification Broadcast</h3>
        </div>

        <div class="p-4 md:p-[30px] rounded-xl md:rounded-[24px] border border-white/[0.08] bg-slate-900/40">
          <div class="space-y-4 md:space-y-6">
            <div>
              <label class="block text-xs md:text-sm font-bold text-white/70 mb-1 md:mb-2">Notification Title</label>
              <input
                v-model="broadcastTitle"
                type="text"
                placeholder="System Broadcast"
                class="w-full px-3 py-2 md:px-4 md:py-2.5 bg-white/[0.05] border border-white/[0.1] rounded-lg md:rounded-xl text-white text-xs md:text-sm placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition-all"
              />
            </div>

            <div>
              <label class="block text-xs md:text-sm font-bold text-white/70 mb-1 md:mb-2">Broadcast Message</label>
              <textarea
                v-model="broadcastBody"
                rows="4"
                placeholder="Type your broadcast message here..."
                class="w-full px-3 py-2 md:px-4 md:py-2.5 bg-white/[0.05] border border-white/[0.1] rounded-lg md:rounded-xl text-white text-xs md:text-sm placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition-all resize-y"
              ></textarea>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 md:gap-4">
              <div class="flex items-center gap-2">
                <label class="text-xs md:text-sm font-bold text-white/70">Target:</label>
                <select
                  v-model="broadcastTarget"
                  class="px-3 py-2 bg-white/[0.05] border border-white/[0.1] rounded-lg text-white text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                >
                  <option value="all">All Devices</option>
                  <option value="admin">Admins</option>
                  <option value="staff">Staff</option>
                  <option value="customer">Customers</option>
                  <option value="trusted">Trusted Admins</option>
                </select>
              </div>

              <button
                @click="sendBroadcast"
                :disabled="sending || !broadcastBody.trim()"
                :class="[
                  sending ? 'opacity-60 cursor-wait' : 'hover:bg-cyan-500 hover:text-black hover:scale-[1.02] active:scale-95',
                  !broadcastBody.trim() ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
                ]"
                class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-2.5 md:px-6 md:py-3 bg-cyan-500/20 border border-cyan-500/30 text-cyan-400 font-extrabold rounded-xl md:rounded-2xl transition-all text-xs md:text-sm shadow-lg shadow-cyan-500/10"
              >
                <Loader v-if="sending" class="w-4 h-4 animate-spin" />
                <Radio v-else class="w-4 h-4" />
                <span>{{ sending ? 'Broadcasting...' : 'Send Broadcast Alert' }}</span>
              </button>
            </div>

            <!-- Result alert -->
            <div
              v-if="broadcastResult"
              :class="broadcastResult.success ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-rose-500/10 border-rose-500/20 text-rose-400'"
              class="p-3 md:p-4 rounded-xl border text-xs md:text-sm font-bold"
            >
              {{ broadcastResult.message }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </DeveloperLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import {
  Smartphone, Monitor, Radio, Activity, Loader
} from 'lucide-vue-next';
import DeveloperLayout from '../../layouts/DeveloperLayout.vue';

const devices = ref([]);
const analytics = ref({
  total_installs: 0,
  active_devices: 0,
  online_now: 0,
  unique_models: 0,
  unique_versions: 0,
});
const broadcastTitle = ref('');
const broadcastBody = ref('');
const broadcastTarget = ref('all');
const sending = ref(false);
const broadcastResult = ref(null);

const deviceStats = computed(() => {
  const list = devices.value;
  const platforms = new Set(list.map(d => d.platform).filter(Boolean)).size;
  return { platforms };
});

const formatTime = (ts) => {
  if (!ts) return '—';
  const d = new Date(ts);
  const now = new Date();
  const diff = (now - d) / 1000;
  if (diff < 60) return 'Just now';
  if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
  if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';

  const yesterday = new Date(now);
  yesterday.setDate(yesterday.getDate() - 1);
  const isYesterday = d.toDateString() === yesterday.toDateString();
  if (isYesterday) {
    return 'Yesterday at ' + d.toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', hour12: true });
  }
  return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' }) + ' at '
    + d.toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', hour12: true });
};

const copyToken = async (token) => {
  if (!token) return;
  try {
    await navigator.clipboard.writeText(token);
  } catch {
    // fallback for older browsers
    const ta = document.createElement('textarea');
    ta.value = token;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
  }
};

const fetchDevices = async () => {
  try {
    const res = await axios.get('/api/developer/devices');
    if (res.data?.status === 'success') {
      devices.value = res.data.devices || [];
      analytics.value = res.data.analytics || analytics.value;
    }
  } catch (err) {
    console.error('Failed to fetch devices:', err);
  }
};

const sendBroadcast = async () => {
  if (sending.value || !broadcastBody.value.trim()) return;
  sending.value = true;
  broadcastResult.value = null;

  try {
    const res = await axios.post('/api/developer/broadcast', {
      title: broadcastTitle.value.trim() || 'System Broadcast',
      body: broadcastBody.value.trim(),
      target: broadcastTarget.value,
    });

    if (res.data?.status === 'success') {
      broadcastResult.value = {
        success: true,
        message: res.data.message || 'Broadcast sent successfully.',
      };
    } else {
      broadcastResult.value = {
        success: false,
        message: res.data?.message || 'Broadcast failed.',
      };
    }
  } catch (err) {
    broadcastResult.value = {
      success: false,
      message: err.response?.data?.message || err.message || 'Network error.',
    };
  } finally {
    sending.value = false;
  }
};

let interval;
onMounted(() => {
  fetchDevices();
  interval = setInterval(fetchDevices, 30000);
});

onUnmounted(() => {
  clearInterval(interval);
});
</script>
