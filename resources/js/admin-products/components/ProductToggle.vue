<template>
  <button 
    @click.stop="$emit('toggle')"
    :disabled="disabled"
    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 focus:ring-offset-[#1a1a1a]"
    :class="modelValue ? 'bg-green-500' : 'bg-gray-600'"
  >
    <span class="sr-only">Toggle availability</span>
    <span
      aria-hidden="true"
      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
      :class="modelValue ? 'translate-x-5' : 'translate-x-0'"
    ></span>
  </button>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [Boolean, Number],
    required: true
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

defineEmits(['toggle']);
</script>
