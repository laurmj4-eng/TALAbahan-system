<template>
  <div
    v-show="visible || refreshing"
    class="fixed inset-x-0 z-[9999] flex justify-start pointer-events-none"
    style="top: -56px"
    :style="overlayStyle"
  >
    <div class="mx-auto flex items-center justify-center w-12 h-12 md:w-14 md:h-14 bg-white/90 backdrop-blur rounded-full shadow-2xl ring-1 ring-black/5">
      <div
        class="w-5 h-5 md:w-6 md:h-6 rounded-full border-2 border-t-transparent animate-spin"
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
  transform: `translateY(${pullY.value + 56}px)`,
  transition: refreshing.value ? 'none' : 'transform 0.3s ease-out',
}))

const spinnerColor = computed(() =>
  activated.value || refreshing.value
    ? 'border-cyan-500'
    : 'border-gray-300'
)
</script>
