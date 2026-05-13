<template>
  <CustomerLayout>
    <div class="space-y-12 animate-fade-in">
      
      <!-- Enhanced Hero Banner -->
      <section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-indigo-950/60 via-slate-900/40 to-transparent border border-white/10 p-10 md:p-20 group shadow-2xl backdrop-blur-md">
        <!-- Fish Watermark (Right Aligned, Low Opacity) -->
        <div class="absolute -right-12 top-1/2 -translate-y-1/2 opacity-[0.08] rotate-12 group-hover:rotate-6 group-hover:scale-110 transition-all duration-1000 pointer-events-none">
          <Fish class="w-[20rem] h-[20rem] text-cyan-400" />
        </div>
        
        <!-- Ambient Hero Glow -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-cyan-600/10 blur-[100px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl space-y-6">
          <h1 class="text-5xl md:text-7xl font-black text-white tracking-tighter leading-[1.1]">
            <span class="font-extrabold text-white/90">{{ greeting }},</span> <br/>
            <span class="text-gradient-cyan-white">{{ username }}!</span>
          </h1>
          <p class="text-white/60 text-xl md:text-2xl font-semibold leading-relaxed max-w-xl">
            Ready for some fresh seafood today? <br class="hidden md:block"/> Check out our best catch below.
          </p>
        </div>
      </section>

      <!-- Section Header -->
      <header class="flex items-center justify-between px-2">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-cyan-600/10 rounded-[1.5rem] flex items-center justify-center border border-cyan-500/20 shadow-[0_0_20px_rgba(34,211,238,0.1)]">
            <Gem class="text-cyan-400 w-7 h-7" />
          </div>
          <div class="space-y-1">
            <h2 class="text-3xl font-black text-white tracking-tight">Available Seafood</h2>
            <p class="text-white/40 text-[0.65rem] font-black uppercase tracking-[0.2em]">Premium Selection • Fresh Daily</p>
          </div>
        </div>
      </header>

      <!-- Responsive Product Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">
        <article 
          v-for="product in products" 
          :key="product.id" 
          class="group relative flex flex-col bg-white/[0.03] backdrop-blur-md border border-white/10 rounded-2xl overflow-hidden transition-all duration-300 hover:scale-[1.02] hover:border-cyan-500/30 hover:shadow-[0_20px_60px_-15px_rgba(34,211,238,0.15)]"
          :class="{ 'opacity-50 grayscale pointer-events-none': product.current_stock <= 0 || product.is_available == 0 }"
        >
          <!-- Product Image Container -->
          <div class="aspect-[4/5] w-full overflow-hidden relative">
            <img 
              :src="getImageUrl(product.image)" 
              @error="handleImageError"
              class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
              :alt="product.name"
            />
            <!-- Image Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#020617]/90 via-transparent to-transparent opacity-60"></div>
            
            <!-- Sold Out Badge -->
            <div v-if="product.current_stock <= 0" class="absolute inset-0 flex items-center justify-center bg-black/60 z-10">
              <span class="px-4 py-2 border-2 border-white/30 rounded-xl font-black text-white tracking-widest uppercase text-sm bg-black/40 backdrop-blur-md">Sold Out</span>
            </div>

            <!-- Floating Badge -->
            <div class="absolute top-3 right-3 px-3 py-1.5 bg-black/40 backdrop-blur-md border border-white/10 rounded-full flex items-center gap-2">
              <Star class="w-3 h-3 text-amber-400 fill-amber-400" />
              <span class="text-[0.65rem] font-black">{{ product.real_rating || '5.0' }}</span>
            </div>
          </div>

          <!-- Product Content -->
          <div class="p-5 md:p-6 flex flex-col gap-4 flex-1">
            <div class="space-y-1">
              <h3 class="text-sm md:text-lg font-bold text-white line-clamp-2 min-h-[2.5rem] group-hover:text-cyan-300 transition-colors duration-300">
                {{ product.name }}
              </h3>
              
              <div class="flex items-baseline gap-2">
                <span class="text-lg md:text-2xl font-black text-cyan-400 tracking-tighter">₱{{ formatNumber(product.selling_price) }}</span>
                <span class="text-white/20 text-[0.6rem] font-black uppercase tracking-widest">/ {{ product.unit || 'kg' }}</span>
              </div>

              <div class="flex items-center gap-2 pt-1">
                <div class="flex items-center gap-1.5 text-white/40 text-[0.6rem] font-black uppercase tracking-widest">
                  <ShoppingBag class="w-3 h-3" />
                  <span>{{ product.real_sold_count || 0 }} Sold</span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-2 mt-auto">
              <button 
                v-if="product.is_available != 0 && product.current_stock > 0"
                @click="buyNow(product)"
                class="w-full bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-black py-3 rounded-xl text-xs transition-all duration-300 active:scale-95 flex items-center justify-center gap-2 shadow-lg shadow-cyan-900/20"
              >
                <Zap class="w-3.5 h-3.5 fill-current" />
                <span>Buy Now</span>
              </button>

              <button 
                v-if="product.is_available != 0 && product.current_stock > 0"
                @click="addToCart(product)"
                class="w-full bg-white/5 border border-white/10 text-white/60 font-black py-3 rounded-xl text-xs hover:bg-white hover:text-[#020617] hover:border-white transition-all duration-300 active:scale-95 flex items-center justify-center gap-2 group/btn"
              >
                <Plus class="w-3.5 h-3.5 transition-transform duration-300 group-hover/btn:rotate-90" />
                <span>Add to Cart</span>
              </button>

              <div v-if="product.is_available == 0" class="w-full bg-rose-500/10 border border-rose-500/20 text-rose-400 font-black py-3 rounded-xl text-[0.65rem] uppercase tracking-widest text-center flex items-center justify-center gap-2">
                <Ban class="w-3 h-3" />
                Not Available
              </div>
            </div>
          </div>
        </article>

        <!-- Empty State -->
        <div v-if="products.length === 0" class="col-span-full py-40 text-center animate-fade-in">
          <div class="inline-flex items-center justify-center w-24 h-24 bg-white/[0.02] rounded-full mb-8 border border-white/5">
            <Fish class="w-12 h-12 text-white/10" />
          </div>
          <h3 class="text-2xl font-black text-white mb-3">No products available</h3>
          <p class="text-white/30 font-medium">Please check back later for our fresh catch.</p>
        </div>
      </div>
    </div>

    <!-- Checkout Modal -->
    <div v-if="showCheckoutModal" class="fixed inset-0 z-[1000] flex items-end sm:items-center justify-center bg-slate-950/80 backdrop-blur-md animate-fade-in p-0 sm:p-4 pointer-events-auto">
      <div class="relative w-full max-w-2xl bg-[#0c0f22]/90 sm:bg-white/[0.03] backdrop-blur-2xl border-t sm:border border-white/10 rounded-t-[2.5rem] sm:rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col h-[92vh] sm:h-auto sm:max-h-[85vh] transition-all duration-500">
        <!-- Modal Header -->
        <div class="p-6 sm:p-8 border-b border-white/5 flex items-center justify-between sticky top-0 bg-transparent z-10 backdrop-blur-md">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 flex items-center justify-center border border-cyan-500/20 shadow-lg shadow-cyan-500/5">
              <component :is="currentStepIcon" class="w-6 h-6 text-cyan-400" />
            </div>
            <div>
              <h3 class="text-xl font-black text-white leading-none tracking-tight">{{ currentStepTitle }}</h3>
              <div class="flex items-center gap-2 mt-1.5">
                <div class="flex gap-1">
                  <div v-for="step in 3" :key="step" class="w-4 h-1 rounded-full transition-all duration-500" :class="step <= currentStep ? 'bg-cyan-400' : 'bg-white/10'"></div>
                </div>
                <p class="text-[0.6rem] text-white/30 font-black uppercase tracking-[0.2em]">Step {{ currentStep }} of 3</p>
              </div>
            </div>
          </div>
          <button @click="closeCheckoutModal" class="w-11 h-11 rounded-2xl bg-white/5 hover:bg-rose-500/10 hover:text-rose-400 text-white/40 flex items-center justify-center transition-all duration-300 border border-white/5 hover:border-rose-500/20">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Content -->
        <div class="flex-1 overflow-y-auto p-6 sm:p-8 custom-scrollbar space-y-8">
          <!-- Step 1: Cart Confirmation -->
          <div v-if="currentStep === 1" class="space-y-8 animate-slide-in-right">
            <div class="space-y-4">
              <div v-for="item in cartItems" :key="item.id" class="p-5 bg-white/[0.03] border border-white/5 rounded-[2rem] flex items-center gap-5 group transition-all duration-300 hover:bg-white/[0.05] hover:border-white/10">
                <div class="relative w-20 h-20 flex-shrink-0">
                  <img :src="getImageUrl(item.image)" class="w-full h-full rounded-2xl object-cover border border-white/10 shadow-lg" />
                  <div class="absolute -top-2 -right-2 w-7 h-7 bg-cyan-500 text-slate-950 rounded-full flex items-center justify-center text-[0.7rem] font-black border-2 border-[#0c0f22]">
                    {{ item.quantity }}
                  </div>
                </div>
                
                <div class="flex-1 min-w-0 py-1">
                  <h4 class="text-base font-black text-white truncate mb-2">{{ item.name }}</h4>
                  <div class="flex items-center gap-4">
                    <div class="flex items-center bg-white/5 rounded-xl border border-white/10 p-1">
                      <button @click="updateQty(item.id, -1)" class="w-8 h-8 flex items-center justify-center hover:bg-white/10 rounded-lg text-white/40 hover:text-white transition-all">
                        <Minus class="w-4 h-4" />
                      </button>
                      <span class="w-8 text-center text-sm font-black text-white">{{ item.quantity }}</span>
                      <button @click="updateQty(item.id, 1)" class="w-8 h-8 flex items-center justify-center hover:bg-white/10 rounded-lg text-white/40 hover:text-white transition-all">
                        <Plus class="w-4 h-4" />
                      </button>
                    </div>
                    <span class="text-[0.75rem] text-white/30 font-bold tracking-tight">₱{{ formatNumber(item.price) }} / {{ item.unit || 'kg' }}</span>
                  </div>
                </div>
                
                <div class="text-right flex flex-col justify-between items-end h-20 py-1">
                  <div class="text-lg font-black text-cyan-400 tracking-tighter">₱{{ formatNumber(item.price * item.quantity) }}</div>
                  <button @click="removeFromCart(item.id)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-500/10 text-rose-500/40 hover:text-rose-500 hover:bg-rose-500/20 transition-all duration-300">
                    <Trash2 class="w-4.5 h-4.5" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Order Summary Card -->
            <div class="p-8 bg-white/[0.03] border border-white/10 rounded-[2.5rem] space-y-5 relative overflow-hidden group">
              <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/5 blur-3xl pointer-events-none"></div>
              
              <div class="space-y-4">
                <div class="flex justify-between items-center text-[0.7rem] font-black uppercase tracking-[0.15em] text-white/30">
                  <span>Items Subtotal</span>
                  <span class="text-white">₱{{ formatNumber(cartSubtotal) }}</span>
                </div>
                <div class="flex justify-between items-center text-[0.7rem] font-black uppercase tracking-[0.15em] text-white/30">
                  <span>Estimated Delivery</span>
                  <span class="text-white">₱{{ formatNumber(quote?.shipping_fee || 0) }}</span>
                </div>
                <div v-if="quote?.voucher_discount > 0" class="flex justify-between items-center text-[0.7rem] font-black uppercase tracking-[0.15em] text-amber-400">
                  <span>Voucher applied</span>
                  <span>-₱{{ formatNumber(quote.voucher_discount) }}</span>
                </div>
              </div>
              
              <div class="pt-5 border-t border-white/10 flex justify-between items-center">
                <div class="space-y-0.5">
                  <span class="text-[0.65rem] font-black uppercase tracking-[0.2em] text-white/20">Grand Total</span>
                  <div class="text-3xl font-black text-cyan-400 tracking-tighter shadow-cyan-400/20 drop-shadow-sm">
                    ₱{{ formatNumber(quote?.final_total || cartSubtotal) }}
                  </div>
                </div>
                <div class="px-4 py-2 bg-white/5 border border-white/10 rounded-full text-[0.6rem] font-black text-white/40 uppercase tracking-widest">
                  Secure Checkout
                </div>
              </div>
            </div>
          </div>

          <!-- Step 2: Delivery Details -->
          <div v-if="currentStep === 2" class="space-y-8 animate-slide-in-right">
            <!-- Location Insight Card -->
            <div v-if="ship_to_all !== '1'" class="p-6 bg-cyan-500/5 border border-cyan-500/20 rounded-[2rem] flex items-center gap-5 transition-all hover:bg-cyan-500/10 group">
              <div class="w-14 h-14 rounded-2xl bg-cyan-500/10 flex items-center justify-center flex-shrink-0 border border-cyan-500/20 group-hover:scale-110 transition-transform duration-500">
                <MapPin class="w-7 h-7 text-cyan-400" />
              </div>
              <div class="space-y-1">
                <h4 class="text-sm font-black text-white uppercase tracking-wider">Delivery Coverage</h4>
                <p class="text-xs font-bold text-white/40 leading-relaxed">
                  We currently serve <strong class="text-cyan-400">{{ supportedAreasText }}</strong>.
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="group space-y-2.5">
                <label class="text-[0.65rem] font-black uppercase tracking-widest text-white/30 ml-4 group-focus-within:text-cyan-400 transition-colors">Receiver Name</label>
                <div class="relative">
                  <input v-model="deliveryDetails.name" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm font-bold focus:bg-white/[0.08] focus:border-cyan-500/50 outline-none transition-all placeholder:text-white/10" placeholder="Full name">
                </div>
              </div>
              <div class="group space-y-2.5">
                <label class="text-[0.65rem] font-black uppercase tracking-widest text-white/30 ml-4 group-focus-within:text-cyan-400 transition-colors">Contact Number</label>
                <div class="relative">
                  <input v-model="deliveryDetails.phone" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm font-bold focus:bg-white/[0.08] focus:border-cyan-500/50 outline-none transition-all placeholder:text-white/10" placeholder="09XXXXXXXXX">
                </div>
              </div>
            </div>

            <!-- Auto-detection Section -->
            <div v-if="ship_to_all !== '1'" class="space-y-6">
              <button @click="getLocation" :disabled="isDetectingLocation" class="w-full py-5 bg-white/5 border border-white/10 rounded-[2rem] flex items-center justify-center gap-3 hover:bg-white/10 transition-all active:scale-[0.98] disabled:opacity-50 group border-dashed hover:border-solid hover:border-cyan-500/30">
                <Loader2 v-if="isDetectingLocation" class="w-6 h-6 animate-spin text-cyan-400" />
                <MapPin v-else class="w-6 h-6 text-cyan-400 group-hover:animate-bounce" />
                <span class="text-base font-black tracking-tight">{{ isDetectingLocation ? 'Detecting Location...' : 'Detect Current Location' }}</span>
              </button>
              
              <div v-if="locationError" class="text-xs font-bold text-rose-400 bg-rose-400/5 border border-rose-400/20 p-5 rounded-2xl text-center animate-shake">
                {{ locationError }}
              </div>

              <div class="group space-y-2.5">
                <label class="text-[0.65rem] font-black uppercase tracking-widest text-white/30 ml-4">Verified Barangay / Area</label>
                <input v-model="deliveryDetails.barangay" readonly type="text" class="w-full bg-black/40 border border-white/5 rounded-2xl px-6 py-4 text-sm font-bold text-white/40 cursor-not-allowed outline-none" placeholder="Detection required...">
              </div>

              <div v-if="fullAddress" class="p-6 bg-emerald-500/5 border border-emerald-500/20 rounded-[2rem] flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center flex-shrink-0 border border-emerald-500/20">
                  <CheckCircle class="w-5 h-5 text-emerald-400" />
                </div>
                <div class="space-y-1">
                  <span class="text-[0.6rem] font-black uppercase tracking-widest text-emerald-400">Validated Address</span>
                  <p class="text-xs font-bold text-white leading-relaxed">{{ fullAddress }}</p>
                </div>
              </div>
            </div>

            <!-- Manual Entry (PC/Tablet Improvement: Grid layout) -->
            <div v-else class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group space-y-2.5">
                  <label class="text-[0.65rem] font-black uppercase tracking-widest text-white/30 ml-4 group-focus-within:text-cyan-400 transition-colors">Province / City</label>
                  <input v-model="deliveryDetails.city" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm font-bold focus:bg-white/[0.08] focus:border-cyan-500/50 outline-none transition-all placeholder:text-white/10" placeholder="e.g. Bacolod City">
                </div>
                <div class="group space-y-2.5">
                  <label class="text-[0.65rem] font-black uppercase tracking-widest text-white/30 ml-4 group-focus-within:text-cyan-400 transition-colors">Barangay</label>
                  <input v-model="deliveryDetails.barangay" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm font-bold focus:bg-white/[0.08] focus:border-cyan-500/50 outline-none transition-all placeholder:text-white/10" placeholder="e.g. Villamonte">
                </div>
              </div>
              <div class="group space-y-2.5">
                <label class="text-[0.65rem] font-black uppercase tracking-widest text-white/30 ml-4 group-focus-within:text-cyan-400 transition-colors">Exact Street / Landmarks</label>
                <textarea v-model="deliveryDetails.street" rows="3" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm font-bold focus:bg-white/[0.08] focus:border-cyan-500/50 outline-none transition-all resize-none placeholder:text-white/10" placeholder="House #, Street name, Near Landmark..."></textarea>
              </div>
            </div>
          </div>

          <!-- Step 3: Payment Method -->
          <div v-if="currentStep === 3" class="space-y-8 animate-slide-in-right">
            <div class="space-y-4">
              <label class="text-[0.65rem] font-black uppercase tracking-widest text-white/30 ml-4">Payment Selection</label>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button 
                  @click="paymentMethod = 'COD'"
                  class="p-6 rounded-[2rem] border transition-all duration-500 flex flex-col items-center gap-4 text-center group relative overflow-hidden"
                  :class="paymentMethod === 'COD' ? 'bg-cyan-500/10 border-cyan-500 shadow-xl' : 'bg-white/5 border-white/5 hover:border-white/10'"
                >
                  <div class="w-16 h-16 rounded-[1.5rem] flex items-center justify-center border transition-all duration-500" :class="paymentMethod === 'COD' ? 'bg-cyan-500 text-slate-950 border-cyan-400 scale-110 shadow-lg' : 'bg-white/5 text-white/20 border-white/5'">
                    <CreditCard class="w-8 h-8" />
                  </div>
                  <div class="space-y-1">
                    <h4 class="text-base font-black text-white">COD</h4>
                    <p class="text-[0.6rem] text-white/30 font-black uppercase tracking-widest">Pay on Arrival</p>
                  </div>
                  <div v-if="paymentMethod === 'COD'" class="absolute top-4 right-4 w-6 h-6 rounded-full bg-cyan-500 flex items-center justify-center border-2 border-[#0c0f22] animate-scale-in">
                    <CheckCircle class="w-3.5 h-3.5 text-slate-950" />
                  </div>
                </button>

                <button 
                  @click="paymentMethod = 'GCASH'"
                  class="p-6 rounded-[2rem] border transition-all duration-500 flex flex-col items-center gap-4 text-center group relative overflow-hidden"
                  :class="paymentMethod === 'GCASH' ? 'bg-blue-600/10 border-blue-500 shadow-xl' : 'bg-white/5 border-white/5 hover:border-white/10'"
                >
                  <div class="w-16 h-16 rounded-[1.5rem] flex items-center justify-center border transition-all duration-500" :class="paymentMethod === 'GCASH' ? 'bg-blue-600 text-white border-blue-400 scale-110 shadow-lg' : 'bg-white/5 text-white/20 border-white/5'">
                    <Zap class="w-8 h-8 fill-current" />
                  </div>
                  <div class="space-y-1">
                    <h4 class="text-base font-black text-white">GCash</h4>
                    <p class="text-[0.6rem] text-white/30 font-black uppercase tracking-widest">Digital Transfer</p>
                  </div>
                  <div v-if="paymentMethod === 'GCASH'" class="absolute top-4 right-4 w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center border-2 border-[#0c0f22] animate-scale-in">
                    <CheckCircle class="w-3.5 h-3.5 text-white" />
                  </div>
                </button>
              </div>
            </div>

            <div class="group space-y-2.5">
              <label class="text-[0.65rem] font-black uppercase tracking-widest text-white/30 ml-4 group-focus-within:text-cyan-400 transition-colors">Voucher Code (Optional)</label>
              <div class="flex gap-3">
                <input v-model="voucherCode" type="text" class="flex-1 bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm font-bold focus:bg-white/[0.08] focus:border-cyan-500/50 outline-none transition-all placeholder:text-white/10" placeholder="HAVEACODE?">
                <button @click="fetchQuote" :disabled="isFetchingQuote" class="px-8 bg-white text-slate-950 font-black rounded-2xl text-xs hover:bg-cyan-100 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2">
                  <Loader2 v-if="isFetchingQuote" class="w-4 h-4 animate-spin" />
                  <span v-else>Apply</span>
                </button>
              </div>
              <div v-if="quote?.applied_vouchers?.length" class="flex items-center gap-2 ml-4">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                <p class="text-[0.65rem] text-emerald-400 font-black uppercase tracking-[0.2em]">
                  Applied: {{ quote.applied_vouchers.map(v => v.code).join(', ') }}
                </p>
              </div>
            </div>

            <!-- Order Confirmation Summary -->
            <div class="p-8 bg-white/[0.03] border border-white/10 rounded-[2.5rem] space-y-5 relative overflow-hidden group">
              <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/5 blur-3xl pointer-events-none"></div>
              
              <div class="space-y-4">
                <div class="flex justify-between items-center text-[0.7rem] font-black uppercase tracking-[0.15em] text-white/30">
                  <span>Subtotal</span>
                  <span class="text-white">₱{{ formatNumber(quote?.subtotal || cartSubtotal) }}</span>
                </div>
                <div class="flex justify-between items-center text-[0.7rem] font-black uppercase tracking-[0.15em] text-white/30">
                  <span>Shipping</span>
                  <span class="text-white">₱{{ formatNumber(quote?.shipping_fee || 0) }}</span>
                </div>
                <div v-if="quote?.voucher_discount > 0" class="flex justify-between items-center text-[0.7rem] font-black uppercase tracking-[0.15em] text-amber-400">
                  <span>Discount</span>
                  <span>-₱{{ formatNumber(quote.voucher_discount) }}</span>
                </div>
              </div>
              
              <div class="pt-5 border-t border-white/10 flex justify-between items-center">
                <div class="space-y-0.5">
                  <span class="text-[0.65rem] font-black uppercase tracking-[0.2em] text-white/20">Grand Total</span>
                  <div class="text-4xl font-black text-[#00ff88] tracking-tighter drop-shadow-[0_0_15px_rgba(0,255,136,0.2)]">
                    ₱{{ formatNumber(quote?.final_total || cartSubtotal) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer (Actions) -->
        <div class="p-6 sm:p-8 border-t border-white/5 bg-transparent backdrop-blur-xl sticky bottom-0 z-10 flex gap-4">
          <button 
            @click="prevStep" 
            v-if="currentStep > 1"
            class="w-20 sm:w-28 py-5 bg-white/5 border border-white/10 rounded-[1.5rem] text-white/40 font-black text-xs uppercase tracking-widest hover:bg-white/10 hover:text-white transition-all active:scale-95"
          >
            Back
          </button>
          <button 
            @click="nextStep" 
            :disabled="!canGoNext || isPlacingOrder"
            class="flex-1 py-5 bg-cyan-400 text-slate-950 font-black text-sm rounded-[1.5rem] transition-all duration-500 active:scale-[0.98] flex items-center justify-center gap-3 shadow-2xl shadow-cyan-400/20 disabled:opacity-30 disabled:grayscale disabled:active:scale-100 group overflow-hidden relative"
          >
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            <Loader2 v-if="isPlacingOrder" class="w-5 h-5 animate-spin" />
            <Zap v-else class="w-5 h-5 fill-current" />
            <span class="relative z-10 uppercase tracking-widest">{{ nextStepText }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- GCash Mock UI -->
    <div v-if="showGcashMock" class="fixed inset-0 z-[1100] flex items-center justify-center bg-slate-950/90 backdrop-blur-xl p-6 animate-fade-in">
      <div class="w-full max-w-md bg-blue-600 rounded-[3rem] p-10 text-center space-y-10 shadow-[0_0_100px_rgba(37,99,235,0.2)] border border-white/10 relative overflow-hidden group">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-white/10 blur-3xl rounded-full pointer-events-none"></div>
        
        <div class="space-y-3 relative z-10">
          <div class="w-20 h-20 bg-white rounded-[2rem] flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-blue-900/40">
            <Zap class="w-10 h-10 text-blue-600 fill-current" />
          </div>
          <h2 class="text-4xl font-black text-white tracking-tighter">GCash Pay</h2>
          <p class="text-blue-100/40 font-black uppercase tracking-[0.3em] text-[0.7rem]">Scan to complete order</p>
        </div>
        
        <div class="bg-white p-8 rounded-[2.5rem] inline-block shadow-2xl relative group/qr transition-transform duration-700 hover:scale-105">
          <div class="absolute -inset-6 bg-white/20 blur-3xl rounded-full group-hover/qr:bg-white/40 transition-all duration-1000"></div>
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=TALAbahanSeafoodPaymentMock" class="w-48 h-48 relative z-10" />
        </div>

        <div class="space-y-6 relative z-10">
          <p class="text-blue-50/70 text-sm font-bold leading-relaxed max-w-[280px] mx-auto">
            Scan the QR code above using your GCash app to process the payment.
          </p>
          <div class="flex flex-col gap-4">
            <button @click="confirmGcashPayment" class="w-full py-5 bg-white text-blue-600 font-black rounded-[1.5rem] shadow-2xl hover:bg-blue-50 transition-all active:scale-[0.98] flex items-center justify-center gap-3 group/btn">
              <CheckCircle class="w-6 h-6 group-hover:scale-110 transition-transform" />
              <span class="uppercase tracking-widest text-sm">I Have Paid</span>
            </button>
            <button @click="showGcashMock = false" class="w-full py-5 bg-white/10 text-white/60 font-black rounded-[1.5rem] hover:bg-white/20 hover:text-white transition-all active:scale-[0.98] uppercase tracking-widest text-xs">
              Go Back
            </button>
          </div>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { 
  ShoppingCart, 
  Star, 
  Fish, 
  Zap, 
  Gem,
  Plus,
  ArrowRight,
  Clock,
  ShoppingBag,
  Ban,
  MapPin,
  CreditCard,
  CheckCircle,
  X,
  Trash2,
  Minus,
  Loader2
} from 'lucide-vue-next';
import CustomerLayout from '../../layouts/CustomerLayout.vue';

// Accept products as prop for easier integration
const props = defineProps({
  products: {
    type: Array,
    default: () => []
  },
  username: String,
  shippingLocations: {
    type: Array,
    default: () => []
  },
  ship_to_all: {
    type: String,
    default: '0'
  }
});

const products = ref(props.products);
const username = ref(props.username || localStorage.getItem('username') || 'Bocana Ilog');
const cartCount = ref(parseInt(localStorage.getItem('cartCount') || '0'));
const fallbackImage = 'https://images.unsplash.com/photo-1551248429-40975aa4de74?q=80&w=800&auto=format&fit=crop';

// Checkout State
const showCheckoutModal = ref(false);
const showGcashMock = ref(false);
const currentStep = ref(1);
const cartItems = ref([]);
const paymentMethod = ref('COD');
const voucherCode = ref('');
const isDetectingLocation = ref(false);
const locationError = ref('');
const fullAddress = ref('');
const isFetchingQuote = ref(false);
const isPlacingOrder = ref(false);
const quote = ref(null);

const deliveryDetails = ref({
  name: username.value,
  phone: localStorage.getItem('quick_checkout_phone') || '',
  barangay: localStorage.getItem('quick_checkout_barangay') || '',
  city: '',
  street: ''
});

const greeting = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return "Good Morning";
  if (hour < 18) return "Good Afternoon";
  return "Good Evening";
});

const currentStepIcon = computed(() => {
  if (currentStep.value === 1) return ShoppingBag;
  if (currentStep.value === 2) return MapPin;
  return CreditCard;
});

const currentStepTitle = computed(() => {
  if (currentStep.value === 1) return 'Confirm Order';
  if (currentStep.value === 2) return 'Delivery Details';
  return 'Payment Method';
});

const cartSubtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const supportedAreasText = computed(() => {
  if (props.shippingLocations.length === 0) return 'selected areas';
  const names = props.shippingLocations.slice(0, 3).map(l => l.barangay_name);
  let text = names.join(', ');
  if (props.shippingLocations.length > 3) text += ' and more';
  return text;
});

const canGoNext = computed(() => {
  if (currentStep.value === 1) return cartItems.value.length > 0;
  if (currentStep.value === 2) {
    const { name, phone, barangay } = deliveryDetails.value;
    if (props.ship_to_all === '1') {
      return name && phone && barangay && deliveryDetails.value.city && deliveryDetails.value.street;
    }
    return name && phone && barangay && !locationError.value;
  }
  return paymentMethod.value;
});

const nextStepText = computed(() => {
  if (currentStep.value === 1) return 'Go to Delivery';
  if (currentStep.value === 2) return 'Go to Payment';
  return 'Place Order';
});

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return fallbackImage;
  if (imagePath.startsWith('http')) return imagePath;
  const baseUrl = window.BASE_URL || '';
  const cleanBaseUrl = baseUrl.replace(/\/$/, '');
  const cleanPath = imagePath.replace(/^\//, '').replace(/^uploads\//, '').replace(/^products\//, '');
  return `${cleanBaseUrl}/uploads/products/${cleanPath}`;
};

const handleImageError = (e) => {
  e.target.src = fallbackImage;
};

const updateCartCount = (count) => {
  cartCount.value = count;
  localStorage.setItem('cartCount', count);
  window.dispatchEvent(new Event('cart-updated'));
};

const openCart = () => {
  const savedCart = JSON.parse(localStorage.getItem('cartItems') || '[]');
  if (savedCart.length > 0) {
    cartItems.value = savedCart;
    showCheckoutModal.value = true;
    currentStep.value = 1;
  } else {
    alert('Your cart is empty!');
  }
};

const closeCheckoutModal = () => {
  showCheckoutModal.value = false;
  currentStep.value = 1;
};

const addToCart = (product) => {
  const index = cartItems.value.findIndex(item => item.id === product.id);
  if (index > -1) {
    cartItems.value[index].quantity += 1;
  } else {
    cartItems.value.push({
      id: product.id,
      name: product.name,
      price: product.selling_price,
      image: product.image,
      unit: product.unit,
      quantity: 1
    });
  }
  saveCart();
  updateCartCount(cartItems.value.reduce((sum, item) => sum + item.quantity, 0));
};

const buyNow = (product) => {
  cartItems.value = [{
    id: product.id,
    name: product.name,
    price: product.selling_price,
    image: product.image,
    unit: product.unit,
    quantity: 1
  }];
  saveCart();
  updateCartCount(1);
  showCheckoutModal.value = true;
  currentStep.value = 1;
};

const updateQty = (id, delta) => {
  const index = cartItems.value.findIndex(item => item.id === id);
  if (index === -1) return;
  cartItems.value[index].quantity += delta;
  if (cartItems.value[index].quantity <= 0) {
    cartItems.value.splice(index, 1);
  }
  saveCart();
  updateCartCount(cartItems.value.reduce((sum, item) => sum + item.quantity, 0));
  if (cartItems.value.length === 0) closeCheckoutModal();
  else if (currentStep.value === 3) fetchQuote();
};

const removeFromCart = (id) => {
  const index = cartItems.value.findIndex(item => item.id === id);
  if (index !== -1) {
    cartItems.value.splice(index, 1);
    saveCart();
    updateCartCount(cartItems.value.reduce((sum, item) => sum + item.quantity, 0));
    if (cartItems.value.length === 0) closeCheckoutModal();
    else if (currentStep.value === 3) fetchQuote();
  }
};

const saveCart = () => {
  localStorage.setItem('cartItems', JSON.stringify(cartItems.value));
};

// Scroll Lock Logic
watch(showCheckoutModal, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

const nextStep = () => {
  if (currentStep.value === 1) {
    currentStep.value = 2;
  } else if (currentStep.value === 2) {
    fetchQuote().then(ok => {
      if (ok) currentStep.value = 3;
    });
  } else {
    if (paymentMethod.value === 'GCASH') {
      showGcashMock.value = true;
    } else {
      placeOrder();
    }
  }
};

const prevStep = () => {
  if (currentStep.value > 1) currentStep.value--;
};

const getLocation = () => {
  if (!navigator.geolocation) {
    locationError.value = "Geolocation is not supported by your browser.";
    return;
  }

  isDetectingLocation.value = true;
  locationError.value = '';

  navigator.geolocation.getCurrentPosition(async (position) => {
    const { latitude, longitude } = position.coords;
    try {
      const response = await axios.get(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
      const addr = response.data.address;
      const bgy = addr.quarter || addr.suburb || addr.neighbourhood || addr.village || addr.city_district;
      
      if (bgy) {
        deliveryDetails.value.barangay = bgy;
        deliveryDetails.value.city = addr.city || addr.town || addr.municipality || '';
        deliveryDetails.value.street = addr.road || addr.residential || '';
        fullAddress.value = [deliveryDetails.value.street, bgy, deliveryDetails.value.city, addr.state].filter(Boolean).join(', ');
        validateBarangay(bgy);
      } else {
        locationError.value = "Could not pinpoint your Barangay. Please enter manually if possible.";
      }
    } catch (error) {
      locationError.value = "Failed to detect location details. Please try again.";
    } finally {
      isDetectingLocation.value = false;
    }
  }, (error) => {
    locationError.value = "Location access denied or timed out.";
    isDetectingLocation.value = false;
  });
};

const validateBarangay = async (bgy) => {
  try {
    const response = await axios.post('/customer/validate-location', { barangay: bgy });
    if (response.data.status !== 'success') {
      locationError.value = response.data.message || "We don't deliver to this area yet.";
    } else {
      locationError.value = '';
    }
  } catch (error) {
    console.error('Validation error:', error);
  }
};

const fetchQuote = async () => {
  isFetchingQuote.value = true;
  try {
    const payload = {
      items: cartItems.value.map(item => ({ id: item.id, quantity: item.quantity })),
      payment_method: paymentMethod.value,
      voucher_code: voucherCode.value,
      shipping_details: deliveryDetails.value
    };

    const response = await axios.post('/customer/precheckout', { order_data: JSON.stringify(payload) });
    if (response.data.status === 'success') {
      quote.value = response.data.data;
      return true;
    } else {
      alert(response.data.message);
      return false;
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to validate checkout.');
    return false;
  } finally {
    isFetchingQuote.value = false;
  }
};

const placeOrder = async () => {
  isPlacingOrder.value = true;
  try {
    const payload = {
      items: cartItems.value.map(item => ({ id: item.id, name: item.name, quantity: item.quantity })),
      payment_method: paymentMethod.value,
      voucher_code: voucherCode.value,
      shipping_details: deliveryDetails.value
    };

    const response = await axios.post('/customer/placeOrder', { order_data: JSON.stringify(payload) });
    if (response.data.status === 'success') {
      localStorage.setItem('quick_checkout_phone', deliveryDetails.value.phone);
      localStorage.setItem('quick_checkout_barangay', deliveryDetails.value.barangay);
      alert('Order placed successfully! Transaction: ' + response.data.transaction_code);
      cartItems.value = [];
      saveCart();
      updateCartCount(0);
      router.visit('/customer/orders');
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to place order.');
  } finally {
    isPlacingOrder.value = false;
    showGcashMock.value = false;
    showCheckoutModal.value = false;
  }
};

const confirmGcashPayment = () => {
  alert('GCash Payment Simulated Successfully!');
  placeOrder();
};

const fetchProducts = async () => {
  if (props.products && props.products.length > 0) return;
  
  try {
    const response = await axios.get('/api/customer/dashboard/data');
    products.value = response.data.products || [];
  } catch (error) {
    console.error('Failed to fetch products:', error);
  }
};

onMounted(() => {
  fetchProducts();
  window.addEventListener('open-customer-cart', openCart);
  
  // Load saved cart if any
  const savedCart = JSON.parse(localStorage.getItem('cartItems') || '[]');
  if (savedCart.length > 0) {
    cartItems.value = savedCart;
    updateCartCount(cartItems.value.reduce((sum, item) => sum + item.quantity, 0));
  }
});
</script>

<style scoped>
.text-gradient-cyan-white {
  background: linear-gradient(to right, #22d3ee, #ffffff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(34, 211, 238, 0.2);
  border-radius: 10px;
}
</style>
