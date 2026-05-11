<template>
  <AdminLayout>
    <div class="flex-1 flex flex-col space-y-4 md:space-y-8 min-h-0">
      <div class="mt-8 md:mt-0">
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white to-sky-400 bg-clip-text text-transparent">Activity Monitor</h1>
        <p class="text-white/50 text-xs md:text-sm font-medium mt-1 md:mt-2">Clean, professional tracking of system interactions.</p>
      </div>

      <!-- Content Area -->
      <div class="flex-1 flex flex-col min-h-0">
        <!-- Desktop Table (Hidden on Mobile) -->
        <GlassCard customClass="hidden md:flex overflow-hidden border-white/[0.08] !p-0 flex-1 flex flex-col min-h-0">
          <div class="overflow-x-auto overflow-y-auto max-h-[70vh] md:max-h-[calc(100vh-300px)] scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
            <table class="w-full text-left border-collapse">
              <thead class="sticky top-0 z-10 bg-[#1a1a1a] backdrop-blur-md">
                <tr class="bg-white/5 border-b border-white/10">
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">TIME</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">USER</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest hidden md:table-cell">IP ADDRESS</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest hidden lg:table-cell">DEVICE</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest text-center">STATUS</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest">EVENT</th>
                  <th class="px-8 py-5 text-[0.7rem] font-black text-white/40 uppercase tracking-widest hidden sm:table-cell text-right">LOCATION</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/[0.05]">
                <tr v-for="log in logs" :key="log.id" class="hover:bg-white/[0.02] transition-colors group border-b border-white/5 last:border-0">
                  <td class="px-8 py-6">
                    <div class="text-sm font-bold text-white">{{ formatTime(log.created_at) }}</div>
                    <div class="text-[10px] text-white/30 uppercase font-black tracking-widest">{{ formatDate(log.created_at) }}</div>
                  </td>
                  <td class="px-8 py-6">
                    <router-link v-if="log.user_id" :to="`/admin/activity/user/${log.user_id}`" class="text-sky-400 hover:text-indigo-400 font-black transition-colors block">
                      <span class="text-sm text-slate-400 font-medium">{{ log.user_identity }}</span>
                    </router-link>
                    <span v-else class="text-white/30 font-bold italic opacity-70">Guest User</span>
                  </td>
                  <td class="px-8 py-6 hidden md:table-cell">
                    <code class="px-3 py-1 bg-violet-500/10 text-violet-400 rounded-lg text-[10px] font-black border border-violet-500/20">
                      {{ log.ip_address || '0.0.0.0' }}
                    </code>
                  </td>
                  <td class="px-8 py-6 hidden lg:table-cell">
                    <div class="flex items-center gap-3 text-white/40 text-[11px] font-bold" :title="log.device">
                      <component :is="getDeviceIcon(log.device)" class="w-4 h-4 text-violet-400/60 shrink-0" />
                      <span class="truncate max-w-[120px]">{{ log.device || 'Unknown' }}</span>
                    </div>
                  </td>
                  <td class="px-8 py-6 text-center">
                    <div v-if="log.user_id" class="flex items-center justify-center">
                      <div 
                        class="w-2.5 h-2.5 rounded-full" 
                        :class="isOnline(log.last_active) ? 'bg-emerald-500 shadow-[0_0_10px_#10b981] animate-pulse' : 'bg-slate-600'"
                        :title="isOnline(log.last_active) ? 'Active' : 'Offline'"
                      ></div>
                    </div>
                    <span v-else class="text-[9px] font-black text-white/10 tracking-widest uppercase italic">Non-member</span>
                  </td>
                  <td class="px-8 py-6">
                    <div class="flex items-center gap-2 whitespace-nowrap">
                      <span class="px-2 py-0.5 bg-white/10 text-white text-[9px] font-black rounded uppercase tracking-tighter">
                        {{ parseEvent(log.event).action }}
                      </span>
                      <span class="text-xs font-bold text-slate-300 uppercase tracking-wide">
                        {{ parseEvent(log.event).target }}
                      </span>
                    </div>
                  </td>
                  <td class="px-8 py-6 hidden sm:table-cell text-right">
                    <div class="flex items-center justify-end gap-2 text-[10px] text-white/40 font-black uppercase tracking-widest">
                      <MapPin class="w-3.5 h-3.5 text-rose-500/60" />
                      {{ log.location }}
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </GlassCard>

        <!-- Mobile Card View (Visible on Mobile Only) -->
        <div class="md:hidden space-y-4 pb-20">
          <div v-for="log in logs" :key="log.id" class="p-5 rounded-2xl bg-white/[0.03] border border-white/10 backdrop-blur-md space-y-4">
            <div class="flex justify-between items-start">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-violet-500/10 flex items-center justify-center border border-violet-500/20">
                  <UserCircle class="w-6 h-6 text-violet-400" />
                </div>
                <div>
                  <router-link v-if="log.user_id" :to="`/admin/activity/user/${log.user_id}`" class="block">
                    <span class="text-sm text-slate-400 font-bold">{{ log.user_identity }}</span>
                  </router-link>
                  <span v-else class="text-white/40 font-bold text-sm italic opacity-70">Guest User</span>
                  <div class="text-[9px] text-white/30 font-black tracking-widest uppercase">
                    {{ formatDate(log.created_at) }} • {{ formatTime(log.created_at) }}
                  </div>
                </div>
              </div>
              <div v-if="log.user_id" class="flex items-center">
                <div 
                  class="w-2 h-2 rounded-full" 
                  :class="isOnline(log.last_active) ? 'bg-emerald-500 shadow-[0_0_8px_#10b981] animate-pulse' : 'bg-slate-600'"
                ></div>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <div class="flex items-center gap-2 whitespace-nowrap">
                <span class="px-2 py-0.5 bg-white/10 text-white text-[8px] font-black rounded uppercase tracking-tighter">
                  {{ parseEvent(log.event).action }}
                </span>
                <span class="text-[10px] font-bold text-slate-300 uppercase tracking-wide">
                  {{ parseEvent(log.event).target }}
                </span>
              </div>
              <div class="flex-1 h-px bg-white/5"></div>
              <div class="flex items-center gap-1.5 text-[9px] text-white/40 font-black uppercase tracking-widest">
                <MapPin class="w-3 h-3 text-rose-500/60" />
                {{ log.location }}
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div class="p-2 bg-white/[0.02] rounded-lg border border-white/5">
                <div class="text-[8px] text-white/20 font-black uppercase tracking-widest mb-1">IP Address</div>
                <code class="text-[9px] text-violet-400 font-mono">{{ log.ip_address || '0.0.0.0' }}</code>
              </div>
              <div class="p-2 bg-white/[0.02] rounded-lg border border-white/5 flex items-center gap-3">
                <div class="flex-1 min-w-0">
                  <div class="text-[8px] text-white/20 font-black uppercase tracking-widest mb-1">Device</div>
                  <div class="text-[9px] text-white/40 font-bold truncate">{{ log.device || 'Unknown' }}</div>
                </div>
                <component :is="getDeviceIcon(log.device)" class="w-4 h-4 text-violet-400/60 shrink-0" />
              </div>
            </div>
          </div>

          <div v-if="logs.length === 0" class="py-20 text-center">
            <div class="text-white/10 flex flex-col items-center gap-4">
              <Ghost class="w-12 h-12 opacity-5" />
              <p class="italic text-base">No logs recorded.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Laptop, MapPin, UserCircle, Ghost, Monitor, Smartphone, Tablet } from 'lucide-vue-next';
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

const parseEvent = (eventStr) => {
  if (!eventStr) return { action: 'UNKNOWN', target: '' };
  const parts = eventStr.split(' ');
  const action = parts[0].toUpperCase();
  const target = parts.slice(1).join(' ');
  return { action, target };
};

const getDeviceIcon = (deviceStr) => {
  const d = deviceStr?.toLowerCase() || '';
  if (d.includes('windows') || d.includes('mac') || d.includes('linux')) return Monitor;
  if (d.includes('android') || d.includes('iphone')) return Smartphone;
  if (d.includes('ipad') || d.includes('tablet')) return Tablet;
  return Laptop;
};

const fetchLogs = async () => {
  try {
    const response = await axios.get('/api/admin/activity');
    const data = response.data.data || response.data;
    logs.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Failed to fetch activity logs:', error);
    logs.value = [];
  }
};

onMounted(() => {
  fetchLogs();
});
</script>
