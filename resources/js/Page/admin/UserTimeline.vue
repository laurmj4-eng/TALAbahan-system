<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start gap-6">
        <div>
          <Link href="/admin/activity" class="inline-flex items-center gap-2 px-4 py-2 bg-violet-500/10 backdrop-blur-md border border-violet-500/20 rounded-xl text-white/80 font-semibold text-sm hover:bg-violet-500/20 hover:border-violet-500/40 hover:-translate-x-1 transition-all group mb-4">
            <ArrowLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
            <span>Back to Monitor</span>
          </Link>
          <h2 class="text-[2.2rem] font-bold text-white leading-tight">User Timeline: {{ user.username }}</h2>
          <p class="text-white/60">Detailed activity history for this identity node.</p>
        </div>

        <div class="w-full md:w-auto p-5 rounded-2xl bg-white/[0.03] border border-white/10 backdrop-blur-md text-right">
          <div class="text-[0.8rem] text-white/40 uppercase font-black tracking-widest mb-1">Current Status</div>
          <div :class="isOnline ? 'text-emerald-400' : 'text-rose-500'" class="font-black text-lg">
            {{ isOnline ? 'ONLINE' : 'OFFLINE' }}
          </div>
          <div class="text-[0.7rem] text-white/30 font-bold uppercase tracking-widest mt-1">
            Last seen: {{ user.last_active || 'Never' }}
          </div>
        </div>
      </div>

      <!-- Timeline Container -->
      <div class="relative pl-10 mt-10 before:content-[''] before:absolute before:left-[49px] before:top-0 before:bottom-0 before:w-0.5 before:bg-white/10">
        <div v-if="logs.length > 0">
          <template v-for="(group, date) in groupedLogs" :key="date">
            <div class="relative mb-8 -ml-[14px] z-10">
              <span class="bg-violet-600 text-white px-5 py-1.5 rounded-full text-xs font-black tracking-widest uppercase shadow-[0_4px_15px_rgba(112,0,255,0.3)]">
                {{ date }}
              </span>
            </div>

            <div v-for="log in group" :key="log.id" class="relative mb-8 group">
              <div class="absolute left-[-3px] top-4 w-5 h-5 bg-slate-900 border-[3px] border-violet-600 rounded-full flex items-center justify-center z-10 shadow-[0_0_10px_rgba(112,0,255,0.5)]">
                <div class="w-1.5 h-1.5 bg-violet-600 rounded-full"></div>
              </div>

              <div class="ml-10 p-6 rounded-2xl border border-white/10 bg-white/[0.02] backdrop-blur-md transition-all duration-300 group-hover:border-violet-500/40 group-hover:translate-x-1">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-white/[0.05]">
                  <span class="text-sky-400 font-black text-sm">{{ formatTime(log.created_at) }}</span>
                  <span class="text-white font-bold">{{ log.event }}</span>
                </div>
                <div class="flex flex-wrap gap-6 text-[0.85rem] text-white/50">
                  <div class="flex items-center gap-2">
                    <Laptop class="w-4 h-4 text-violet-400" />
                    <span>{{ log.device }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <MapPin class="w-4 h-4 text-rose-500" />
                    <span>{{ log.location }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <Network class="w-4 h-4 text-sky-400" />
                    <span class="font-mono">{{ log.ip_address }}</span>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
        <div v-else class="text-center py-24 text-white/30 italic">
          No activity recorded for this user.
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ArrowLeft, Laptop, MapPin, Network } from 'lucide-vue-next';
import AdminLayout from '../../layouts/AdminLayout.vue';

const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

const user = ref({});
const logs = ref([]);

const isOnline = computed(() => {
  if (!user.value.last_active) return false;
  const last = new Date(user.value.last_active).getTime();
  const now = new Date().getTime();
  return (now - last) < 300000; // 5 minutes
});

const groupedLogs = computed(() => {
  const groups = {};
  logs.value.forEach(log => {
    const date = new Date(log.created_at).toLocaleDateString('en-PH', { month: 'long', day: '2-digit', year: 'numeric' });
    if (!groups[date]) groups[date] = [];
    groups[date].push(log);
  });
  return groups;
});

const formatTime = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true });
};

const fetchData = async () => {
  try {
    const response = await axios.get(`/api/admin/activity/user/${props.id}`);
    user.value = response.data.user || {};
    logs.value = Array.isArray(response.data.logs) ? response.data.logs : [];
  } catch (error) {
    console.error('Failed to fetch user timeline:', error);
    user.value = {};
    logs.value = [];
  }
};

onMounted(fetchData);
</script>
