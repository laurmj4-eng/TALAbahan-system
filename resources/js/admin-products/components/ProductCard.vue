<template>
  <div class="bg-[#1a1a1a]/60 backdrop-blur-md border border-white/10 rounded-2xl overflow-hidden flex flex-col shadow-lg transition-all hover:scale-[1.02] hover:shadow-xl">
    <div class="relative h-48">
      <img 
        :src="getImageUrl(product.image)" 
        :alt="product.name"
        class="w-full h-full object-cover"
      />
      <div 
        class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold shadow-lg backdrop-blur-md"
        :class="product.is_available == 1 ? 'bg-green-500/80 text-white' : 'bg-red-500/80 text-white'"
      >
        {{ product.is_available == 1 ? 'LIVE' : 'HIDDEN' }}
      </div>
      <div 
        v-if="product.current_stock > 0"
        class="absolute top-3 right-3 bg-blue-500/80 backdrop-blur-md text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg"
      >
        In Stock
      </div>
      <div 
        v-else
        class="absolute top-3 right-3 bg-red-500/80 backdrop-blur-md text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg"
      >
        Out of Stock
      </div>
    </div>
    
    <div class="p-4 flex-grow">
      <h3 class="text-lg font-semibold text-white truncate">{{ product.name }}</h3>
      <div class="mt-2 space-y-1">
        <div class="flex justify-between text-sm">
          <span class="text-gray-400">Stock:</span>
          <span class="text-white font-medium">{{ product.current_stock }} {{ product.unit }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-gray-400">Cost:</span>
          <span class="text-white font-medium">₱{{ formatPrice(product.cost_price) }}</span>
        </div>
        <div class="flex justify-between text-lg font-bold text-blue-400 mt-2">
          <span>Price:</span>
          <span>₱{{ formatPrice(product.selling_price) }}</span>
        </div>
      </div>
    </div>

    <div class="flex border-t border-white/5 bg-white/5">
      <div class="flex-1 flex items-center justify-center py-4 border-r border-white/5 gap-3 px-2">
        <div class="w-12 flex justify-center shrink-0">
          <ProductToggle 
            :model-value="product.is_available" 
            @toggle="$emit('toggle-status', product)"
          />
        </div>
        <button 
          @click="$emit('edit', product)"
          class="text-sm font-semibold text-white hover:text-blue-400 transition-colors uppercase tracking-wider"
        >
          Edit
        </button>
      </div>
      <button 
        @click="$emit('delete', product)"
        class="flex-1 py-4 text-sm font-semibold text-red-400 hover:bg-red-500/10 transition-colors uppercase tracking-wider"
      >
        Delete
      </button>
    </div>
  </div>
</template>

<script setup>
import ProductToggle from './ProductToggle.vue';

defineProps({
  product: {
    type: Object,
    required: true
  }
});

defineEmits(['edit', 'delete', 'toggle-status']);

const getImageUrl = (image) => {
  const base = window.BASE_URL || '/';
  return image ? `${base.replace(/\/$/, '')}/uploads/products/${image}` : `${base.replace(/\/$/, '')}/images/logo.png`;
};

const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('en-US', { minimumFractionDigits: 2 });
};
</script>
