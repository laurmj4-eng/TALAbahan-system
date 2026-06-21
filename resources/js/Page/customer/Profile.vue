<template>
  <CustomerLayout>
    <div class="space-y-6 pb-24">
      <div class="profile-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="profile-title">
          <h1 class="text-3xl md:text-5xl font-black tracking-tight text-white mb-1">Profile</h1>
          <p class="text-white/60 font-medium text-sm">Welcome, <strong class="text-violet-400">{{ username }}</strong>. Manage your purchases quickly.</p>
        </div>
        <div class="profile-actions flex flex-wrap gap-2">
          <Link href="/customer/dashboard" class="btn-soft px-4 py-2 md:px-6 md:py-3 bg-white/5 border border-white/[0.08] rounded-xl md:rounded-2xl font-bold text-white hover:bg-white/10 transition-all flex items-center gap-2 text-sm active:scale-95">
            <Store class="w-4.5 h-4.5 md:w-5 md:h-5 text-violet-400" />
            <span>Back to Shop</span>
          </Link>
          <Link href="/customer/orders" class="btn-soft px-4 py-2 md:px-6 md:py-3 bg-white/5 border border-white/[0.08] rounded-xl md:rounded-2xl font-bold text-white hover:bg-white/10 transition-all flex items-center gap-2 text-sm active:scale-95">
            <ClipboardList class="w-4.5 h-4.5 md:w-5 md:h-5 text-violet-400" />
            <span>View All Orders</span>
          </Link>
          <button @click="handleLogout" class="btn-soft px-4 py-2 md:px-6 md:py-3 bg-rose-500/10 border border-rose-500/20 rounded-xl md:rounded-2xl font-bold text-rose-400 hover:bg-rose-500/20 transition-all flex items-center gap-2 text-sm active:scale-95">
            <LogOut class="w-4.5 h-4.5 md:w-5 md:h-5" />
            <span>Logout</span>
          </button>
        </div>
      </div>

      <GlassCard customClass="p-4 md:p-8 border-white/[0.08] shadow-2xl">
        <div class="purchases-header flex items-center justify-between mb-4 md:mb-8">
          <h3 class="text-lg md:text-2xl font-bold text-white flex items-center gap-2 md:gap-3">
            <ShoppingBag class="w-5 h-5 md:w-7 md:h-7 text-violet-400" />
            <span>My Purchases</span>
          </h3>
          <small class="text-white/40 font-medium hidden md:block">Tap a badge to jump to that order stage.</small>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
          <Link 
            v-for="badge in badges" 
            :key="badge.tab"
            :href="'/customer/orders?tab=' + badge.tab"
            class="group relative flex items-center justify-between p-3 md:p-6 bg-white/5 border border-white/[0.08] rounded-xl md:rounded-3xl hover:bg-white/10 hover:border-violet-500/40 transition-all overflow-hidden active:scale-95"
          >
            <span class="absolute top-2 right-2 md:top-3 md:right-3 px-2 py-0.5 md:px-3 md:py-1 bg-white/5 border border-white/[0.08] rounded-full text-[0.5rem] md:text-[0.65rem] font-black uppercase tracking-widest text-white/60">{{ badge.pill }}</span>
            <div class="flex items-center gap-2 md:gap-4">
              <div class="w-8 h-8 md:w-12 md:h-12 rounded-xl md:rounded-2xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center">
                <component :is="badge.icon" class="w-4 h-4 md:w-6 md:h-6 text-violet-400" />
              </div>
              <div>
                <div class="font-black text-white text-sm md:text-lg leading-tight">{{ badge.label }}</div>
                <div class="text-[0.6rem] md:text-xs text-white/40 font-medium hidden sm:block">{{ badge.hint }}</div>
              </div>
            </div>
            <div class="text-xl md:text-3xl font-black text-white ml-2 md:ml-4 badge-bounce">{{ counts[badge.tab] || 0 }}</div>
          </Link>
        </div>
      </GlassCard>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Store, ClipboardList, LogOut, ShoppingBag, CreditCard, Package, Truck, CheckCircle } from 'lucide-vue-next';
import CustomerLayout from '../../layouts/CustomerLayout.vue';
import GlassCard from '../../components/GlassCard.vue';

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
  window.location.href = (window.BASE_URL || '/') + 'logout';
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
