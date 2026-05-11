<template>
  <div :class="['bg-white/[0.05] backdrop-blur-[16px] border border-white/10 shadow-[0_4px_30px_rgba(0,0,0,0.1)] rounded-2xl', customClass]">
    <slot></slot>
  </div>
</template>

<script setup>
defineProps({
  customClass: {
    type: String,
    default: ''
  }
});
</script>

<style scoped>
</style>
