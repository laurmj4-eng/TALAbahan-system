<template>
  <CustomerLayout>
    <div class="space-y-8 pb-20">
      <div class="profile-header flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="profile-title">
          <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white mb-2">Profile</h1>
          <p class="text-white/60 font-medium">Welcome, <strong class="text-violet-400">{{ username }}</strong>. Manage your purchases quickly.</p>
        </div>
        <div class="profile-actions flex flex-wrap gap-3">
          <router-link to="/customer/dashboard" class="btn-soft px-6 py-3 bg-white/5 border border-white/10 rounded-2xl font-bold text-white hover:bg-white/10 transition-all flex items-center gap-2">
            <Store class="w-5 h-5 text-violet-400" />
            <span>Back to Shop</span>
          </router-link>
          <router-link to="/customer/orders" class="btn-soft px-6 py-3 bg-white/5 border border-white/10 rounded-2xl font-bold text-white hover:bg-white/10 transition-all flex items-center gap-2">
            <ClipboardList class="w-5 h-5 text-violet-400" />
            <span>View All Orders</span>
          </router-link>
          <button @click="handleLogout" class="btn-soft px-6 py-3 bg-rose-500/10 border border-rose-500/20 rounded-2xl font-bold text-rose-400 hover:bg-rose-500/20 transition-all flex items-center gap-2">
            <LogOut class="w-5 h-5" />
            <span>Logout</span>
          </button>
        </div>
      </div>

      <GlassCard customClass="p-8 border-white/10 shadow-2xl">
        <div class="purchases-header flex items-center justify-between mb-8">
          <h3 class="text-2xl font-bold text-white flex items-center gap-3">
            <ShoppingBag class="w-7 h-7 text-violet-400" />
            <span>My Purchases</span>
          </h3>
          <small class="text-white/40 font-medium hidden md:block">Tap a badge to jump to that order stage.</small>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <router-link 
            v-for="badge in badges" 
            :key="badge.tab"
            :to="'/customer/orders?tab=' + badge.tab"
            class="group relative flex items-center justify-between p-6 bg-white/5 border border-white/10 rounded-3xl hover:bg-white/10 hover:border-violet-500/40 transition-all overflow-hidden"
          >
            <span class="absolute top-3 right-3 px-3 py-1 bg-white/5 border border-white/10 rounded-full text-[0.65rem] font-black uppercase tracking-widest text-white/60">{{ badge.pill }}</span>
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-2xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center">
                <component :is="badge.icon" class="w-6 h-6 text-violet-400" />
              </div>
              <div>
                <div class="font-black text-white text-lg leading-tight">{{ badge.label }}</div>
                <div class="text-xs text-white/40 font-medium">{{ badge.hint }}</div>
              </div>
            </div>
            <div class="text-3xl font-black text-white ml-4 badge-bounce">{{ counts[badge.tab] || 0 }}</div>
          </router-link>
        </div>
      </GlassCard>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { Store, ClipboardList, LogOut, ShoppingBag, CreditCard, Package, Truck, CheckCircle } from 'lucide-vue-next';
import CustomerLayout from '../../layouts/CustomerLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

const router = useRouter();
const username = ref('Customer');
const counts = ref({
  to_pay: 0,
  to_ship: 0,
  to_receive: 0,
  completed: 0
});

const badges = [
  { tab: 'to_pay', label: 'Payment', hint: 'Complete checkout', pill: 'To Pay', icon: CreditCard },
  { tab: 'to_ship', label: 'Preparing', hint: 'Seller processing', pill: 'To Ship', icon: Package },
  { tab: 'to_receive', label: 'Shipping', hint: 'Track delivery', pill: 'To Receive', icon: Truck },
  { tab: 'completed', label: 'History', hint: 'Review & reorder', pill: 'Completed', icon: CheckCircle }
];

const fetchProfileData = async () => {
  try {
    const response = await axios.get('/api/customer/dashboard/data');
    if (response.data.status === 'success') {
      username.value = response.data.username || 'Customer';
      counts.value = response.data.orderCounts || {
        to_pay: 0,
        to_ship: 0,
        to_receive: 0,
        completed: 0
      };
    }
  } catch (error) {
    console.error('Failed to fetch profile data:', error);
  }
};

const handleLogout = () => {
  localStorage.removeItem('isLoggedIn');
  localStorage.removeItem('userRole');
  window.location.href = '/logout';
};

onMounted(fetchProfileData);
</script>

<style scoped>
@keyframes badgeBounce {
  0% { transform: scale(1); }
  100% { transform: scale(1.1) translateY(-2px); }
}

.badge-bounce {
  animation: badgeBounce 0.6s cubic-bezier(0.36, 0, 0.66, -0.56) alternate infinite;
  animation-iteration-count: 2;
}
</style>
