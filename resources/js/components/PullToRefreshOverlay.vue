<template>
  <div
    v-show="visible || refreshing"
    class="fixed top-0 left-1/2 -translate-x-1/2 z-[9999] pointer-events-none"
    :style="overlayStyle"
  >
    <div class="w-11 h-11 bg-white rounded-full shadow-xl flex items-center justify-center ring-1 ring-black/5">
      <div
        class="w-5 h-5 rounded-full border-2 border-t-transparent animate-spin"
        :class="spinnerColor"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePullToRefresh } from '../composables/usePullToRefresh'

const { pullY, visible, refreshing, activated } = usePullToRefresh()

const overlayStyle = computed(() => ({
  transform: `translate(calc(-50%), ${pullY.value}px)`,
  transition: refreshing.value ? 'none' : 'transform 0.3s ease-out',
}))

const spinnerColor = computed(() =>
  activated.value || refreshing.value
    ? 'border-cyan-500'
    : 'border-gray-300'
)
</script>
