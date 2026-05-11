<template>
  <AdminLayout>
    <div class="flex-1 flex flex-col space-y-8 min-h-0">
      <div>
        <h1 class="text-3xl font-bold text-white">Activity Monitor</h1>
        <p class="text-white/60 mt-2">Clean, professional tracking of system interactions.</p>
      </div>

      <GlassCard customClass="overflow-hidden flex-1 flex flex-col min-h-0">
        <div class="overflow-x-auto overflow-y-auto max-h-[70vh] md:max-h-[calc(100vh-300px)] scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
          <table class="w-full text-left border-collapse">
            <thead class="sticky top-0 z-10 bg-[#1a1a1a] backdrop-blur-md">
              <tr class="bg-white/5 border-b border-white/10">
                <th class="px-6 py-4 font-semibold text-white/70">TIME</th>
                <th class="px-6 py-4 font-semibold text-white/70">USER</th>
                <th class="px-6 py-4 font-semibold text-white/70 hidden md:table-cell">IP ADDRESS</th>
                <th class="px-6 py-4 font-semibold text-white/70 hidden lg:table-cell">DEVICE</th>
                <th class="px-6 py-4 font-semibold text-white/70">STATUS</th>
                <th class="px-6 py-4 font-semibold text-white/70">EVENT</th>
                <th class="px-6 py-4 font-semibold text-white/70 hidden sm:table-cell">LOCATION</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="log in logs" :key="log.id" class="hover:bg-white/5 transition-colors group">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-white">{{ formatTime(log.created_at) }}</div>
                  <div class="text-[10px] text-white/40 uppercase font-bold tracking-widest">{{ formatDate(log.created_at) }}</div>
                </td>
                <td class="px-6 py-4">
                  <router-link v-if="log.user_id" :to="`/admin/activity/user/${log.user_id}`" class="text-sky-400 hover:text-indigo-400 font-bold transition-colors">
                    {{ log.user_identity }}
                  </router-link>
                  <span v-else class="text-white/40">Guest</span>
                </td>
                <td class="px-6 py-4 hidden md:table-cell">
                  <code class="px-2 py-1 bg-violet-500/10 text-violet-400 rounded text-xs border border-violet-500/20">
                    {{ log.ip_address || '0.0.0.0' }}
                  </code>
                </td>
                <td class="px-6 py-4 hidden lg:table-cell">
                  <div class="flex items-center gap-2 text-white/40 text-[10px] truncate max-w-[150px]" :title="log.device">
                    <Laptop class="w-3 h-3 shrink-0" />
                    {{ log.device || 'Unknown' }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="log.user_id" class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full" :class="isOnline(log.last_active) ? 'bg-emerald-500 animate-pulse' : 'bg-white/20'"></div>
                    <span 
                      class="text-[10px] font-black tracking-widest uppercase"
                      :class="isOnline(log.last_active) ? 'text-emerald-400' : 'text-white/30'"
                    >
                      {{ isOnline(log.last_active) ? 'Active' : 'Offline' }}
                    </span>
                  </div>
                  <span v-else class="text-[10px] font-black text-white/20 tracking-widest uppercase">N/A</span>
                </td>
                <td class="px-6 py-4">
                  <span class="px-3 py-1 bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 rounded-lg text-xs font-medium">
                    {{ log.event }}
                  </span>
                </td>
                <td class="px-6 py-4 hidden sm:table-cell">
                  <div class="flex items-center gap-2 text-[10px] text-white/50 font-bold uppercase tracking-widest">
                    <MapPin class="w-3 h-3 text-rose-500" />
                    {{ log.location }}
                  </div>
                </td>
              </tr>
              <tr v-if="logs.length === 0">
                <td colspan="7" class="px-6 py-24 text-center text-white/20 italic">
                  No logs found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </GlassCard>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Laptop, MapPin } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const logs = ref([]);

const formatTime = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
};

const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-PH', { month: 'short', day: '2-digit' });
};

const isOnline = (lastActive) => {
  if (!lastActive) return false;
  const last = new Date(lastActive).getTime();
  const now = new Date().getTime();
  return (now - last) < 300000; // 5 minutes
};

const fetchLogs = async () => {
  try {
    const response = await axios.get('/api/admin/activity'); // You'll need this endpoint
    logs.value = response.data.data || response.data;
  } catch (error) {
    console.error('Failed to fetch activity logs:', error);
  }
};

onMounted(() => {
  fetchLogs();
});
</script>
