<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="relative bg-[#11131e]/90 border border-white/10 rounded-xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
      <button
        @click="$emit('close')"
        class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <div class="p-6">
        <h3 class="text-xl font-semibold text-white mb-2">Damage Options</h3>
        <p class="text-gray-400 mb-6">
          What would you like to do for Order #{{ orderId }}?
        </p>

        <div class="flex flex-col sm:flex-row gap-3">
          <button
            @click="$emit('confirm-redelivery')"
            class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-medium py-2.5 px-4 rounded-lg transition-colors"
          >
            OK (Issue Free Redelivery)
          </button>
          <button
            @click="$emit('cancel-only')"
            class="flex-1 border border-white/20 hover:border-white/30 text-white font-medium py-2.5 px-4 rounded-lg transition-colors"
          >
            Cancel (No Redelivery)
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  orderId: {
    type: String,
    required: true
  }
});

defineEmits(['close', 'confirm-redelivery', 'cancel-only']);
</script>
