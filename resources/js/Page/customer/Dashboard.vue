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
      <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6 md:gap-8">
        <article 
          v-for="product in products" 
          :key="product.id" 
          class="group relative flex flex-col bg-white/[0.04] backdrop-blur-md border border-white/10 rounded-xl sm:rounded-2xl overflow-hidden transition-all duration-500 hover:bg-white/[0.08] hover:border-white/20 hover:-translate-y-1 sm:hover:-translate-y-2 hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.5)]"
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
              <span class="px-2 py-1 sm:px-4 sm:py-2 border sm:border-2 border-white/30 rounded-lg sm:rounded-xl font-black text-white tracking-widest uppercase text-[0.5rem] sm:text-sm bg-black/40 backdrop-blur-md">Sold Out</span>
            </div>

            <!-- Floating Badge -->
            <div class="absolute top-2 right-2 sm:top-3 sm:right-3 px-2 py-1 sm:px-3 sm:py-1.5 bg-black/40 backdrop-blur-md border border-white/10 rounded-full flex items-center gap-1 sm:gap-2">
              <Star class="w-2.5 h-2.5 sm:w-3 h-3 text-amber-400 fill-amber-400" />
              <span class="text-[0.55rem] sm:text-[0.65rem] font-black">{{ product.real_rating || '5.0' }}</span>
            </div>
          </div>

          <!-- Product Content -->
          <div class="p-3 sm:p-5 md:p-6 flex flex-col gap-3 sm:gap-4 flex-1">
            <div class="space-y-0.5 sm:space-y-1">
              <h3 class="text-xs sm:text-sm md:text-lg font-bold text-white line-clamp-2 min-h-[2rem] sm:min-h-[2.5rem] group-hover:text-cyan-300 transition-colors duration-300 leading-tight">
                {{ product.name }}
              </h3>
              
              <div class="flex items-baseline gap-1 sm:gap-2">
                <span class="text-sm sm:text-lg md:text-2xl font-black text-cyan-400 tracking-tighter">₱{{ formatNumber(product.selling_price) }}</span>
                <span class="text-white/20 text-[0.5rem] sm:text-[0.6rem] font-black uppercase tracking-widest">/ {{ product.unit || 'kg' }}</span>
              </div>

              <div class="flex items-center gap-2 pt-0.5">
                <div class="flex items-center gap-1 sm:gap-1.5 text-white/40 text-[0.5rem] sm:text-[0.6rem] font-black uppercase tracking-widest">
                  <ShoppingBag class="w-2.5 h-2.5 sm:w-3 h-3" />
                  <span>{{ product.real_sold_count || 0 }} Sold</span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-1.5 sm:gap-2 mt-auto">
              <button 
                v-if="product.is_available != 0 && product.current_stock > 0"
                @click="buyNow(product)"
                class="w-full bg-cyan-400 text-slate-950 hover:bg-cyan-300 font-black py-2 sm:py-3 rounded-lg sm:rounded-xl text-[0.6rem] sm:text-xs transition-all duration-300 active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2 shadow-lg shadow-cyan-400/10 group/buy"
              >
                <Zap class="w-3 h-3 sm:w-3.5 h-3.5 fill-current transition-transform duration-300 group-hover/buy:scale-110" />
                <span>Buy Now</span>
              </button>

              <button 
                v-if="product.is_available != 0 && product.current_stock > 0"
                @click="addToCart(product)"
                class="w-full bg-white/5 border border-white/10 text-white/60 font-black py-2 sm:py-3 rounded-lg sm:rounded-xl text-[0.6rem] sm:text-xs hover:bg-white hover:text-slate-950 hover:border-white transition-all duration-300 active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2 group/cart"
              >
                <Plus class="w-3 h-3 sm:w-3.5 h-3.5 transition-transform duration-300 group-hover/cart:rotate-90" />
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
    <Teleport to="body">
      <div v-if="showCheckoutModal" class="fixed inset-0 z-[1000] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm animate-fade-in p-0 sm:p-4 pointer-events-auto">
      <div class="relative w-full max-w-2xl bg-[#0c0f22] sm:bg-[#161b33] border-t sm:border border-white/10 rounded-t-[2rem] sm:rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col h-[90vh] sm:h-auto sm:max-h-[85vh] transition-all duration-500">
        <!-- Modal Header -->
        <div class="p-4 sm:p-8 border-b border-white/5 flex items-center justify-between sticky top-0 bg-[#0c0f22] sm:bg-[#161b33] z-10">
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-cyan-500/10 flex items-center justify-center border border-cyan-500/20 shadow-lg shadow-cyan-500/5">
              <component :is="currentStepIcon" class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-400" />
            </div>
            <div>
              <h3 class="text-lg sm:text-xl font-black text-white leading-none tracking-tight">{{ currentStepTitle }}</h3>
              <div class="flex items-center gap-2 mt-1.5">
                <div class="flex gap-1">
                  <div v-for="step in 3" :key="step" class="w-3 h-0.5 sm:w-4 sm:h-1 rounded-full transition-all duration-500" :class="step <= currentStep ? 'bg-cyan-400' : 'bg-white/10'"></div>
                </div>
                <p class="text-[0.55rem] sm:text-[0.6rem] text-white/30 font-black uppercase tracking-[0.2em]">Step {{ currentStep }} of 3</p>
              </div>
            </div>
          </div>
          <button @click="closeCheckoutModal" class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-white/5 hover:bg-rose-500/10 hover:text-rose-400 text-white/40 flex items-center justify-center transition-all duration-300 border border-white/5 hover:border-rose-500/20">
            <X class="w-4 h-4 sm:w-5 sm:h-5" />
          </button>
        </div>

        <!-- Modal Content -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 custom-scrollbar space-y-6 sm:space-y-8">
          <!-- Step 1: Cart Confirmation -->
          <div v-if="currentStep === 1" class="space-y-4 sm:space-y-8 animate-slide-in-right">
            <div class="space-y-2 sm:space-y-4">
              <div v-for="item in cartItems" :key="item.id" class="p-3 sm:p-5 bg-white/[0.03] border border-white/5 rounded-[1.2rem] sm:rounded-[2rem] flex items-center gap-3 sm:gap-5 group transition-all duration-300 hover:bg-white/[0.05] hover:border-white/10">
                <div class="relative w-14 h-14 sm:w-20 sm:h-20 flex-shrink-0">
                  <img :src="getImageUrl(item.image)" class="w-full h-full rounded-lg sm:rounded-2xl object-cover border border-white/10 shadow-lg" />
                  <div class="absolute -top-1 -right-1 w-5 h-5 sm:w-7 sm:h-7 bg-cyan-500 text-slate-950 rounded-full flex items-center justify-center text-[0.55rem] sm:text-[0.7rem] font-black border-2 border-[#0c0f22]">
                    {{ item.quantity }}
                  </div>
                </div>
                
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between gap-2 mb-1">
                    <h4 class="text-xs sm:text-base font-black text-white truncate">{{ item.name }}</h4>
                    <div class="text-sm sm:text-lg font-black text-cyan-400 tracking-tighter">₱{{ formatNumber(item.price * item.quantity) }}</div>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="flex items-center bg-white/5 rounded-lg border border-white/10 p-0.5">
                      <button @click="updateQty(item.id, -1)" class="w-6 h-6 sm:w-8 sm:h-8 flex items-center justify-center hover:bg-white/10 rounded-md text-white/40 hover:text-white transition-all">
                        <Minus class="w-3 sm:w-4 h-3 sm:h-4" />
                      </button>
                      <span class="w-6 sm:w-8 text-center text-[0.65rem] sm:text-sm font-black text-white">{{ item.quantity }}</span>
                      <button @click="updateQty(item.id, 1)" class="w-6 h-6 sm:w-8 sm:h-8 flex items-center justify-center hover:bg-white/10 rounded-md text-white/40 hover:text-white transition-all">
                        <Plus class="w-3 sm:w-4 h-3 sm:h-4" />
                      </button>
                    </div>
                    
                    <button @click="removeFromCart(item.id)" class="w-7 h-7 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg bg-rose-500/10 text-rose-500/40 hover:text-rose-500 hover:bg-rose-500/20 transition-all duration-300">
                      <Trash2 class="w-3.5 h-3.5 sm:w-4.5 sm:h-4.5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Order Summary Card -->
            <div class="p-4 sm:p-8 bg-white/[0.03] border border-white/10 rounded-[1.5rem] sm:rounded-[2.5rem] space-y-3 sm:space-y-5 relative overflow-hidden group">
              <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/5 blur-3xl pointer-events-none"></div>
              
              <div class="space-y-2 sm:space-y-4">
                <div class="flex justify-between items-center text-[0.55rem] sm:text-[0.7rem] font-black uppercase tracking-[0.15em] text-white/30">
                  <span>Items Subtotal</span>
                  <span class="text-white">₱{{ formatNumber(cartSubtotal) }}</span>
                </div>
                <div class="flex justify-between items-center text-[0.55rem] sm:text-[0.7rem] font-black uppercase tracking-[0.15em] text-white/30">
                  <span>Estimated Delivery</span>
                  <span class="text-white">₱{{ formatNumber(quote?.shipping_fee || 0) }}</span>
                </div>
                <div v-if="quote?.voucher_discount > 0" class="flex justify-between items-center text-[0.55rem] sm:text-[0.7rem] font-black uppercase tracking-[0.15em] text-amber-400">
                  <span>Voucher applied</span>
                  <span>-₱{{ formatNumber(quote.voucher_discount) }}</span>
                </div>
              </div>
              
              <div class="pt-3 sm:pt-5 border-t border-white/10 flex justify-between items-center">
                <div class="space-y-0.5">
                  <span class="text-[0.55rem] sm:text-[0.65rem] font-black uppercase tracking-[0.2em] text-white/20">Grand Total</span>
                  <div class="text-xl sm:text-3xl font-black text-cyan-400 tracking-tighter drop-shadow-sm">
                    ₱{{ formatNumber(quote?.final_total || cartSubtotal) }}
                  </div>
                </div>
                <div class="hidden xs:block px-3 py-1.5 sm:px-4 sm:py-2 bg-white/5 border border-white/10 rounded-full text-[0.5rem] sm:text-[0.6rem] font-black text-white/40 uppercase tracking-widest">
                  Secure Checkout
                </div>
              </div>
            </div>
          </div>

          <!-- Step 2: Delivery Details -->
          <div v-if="currentStep === 2" class="space-y-6 sm:space-y-8 animate-slide-in-right">
            <!-- Location Insight Card -->
            <div v-if="ship_to_all !== '1'" class="p-4 sm:p-6 bg-cyan-500/5 border border-cyan-500/20 rounded-[1.5rem] sm:rounded-[2rem] flex items-center gap-4 sm:gap-5 transition-all hover:bg-cyan-500/10 group">
              <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-cyan-500/10 flex items-center justify-center flex-shrink-0 border border-cyan-500/20 group-hover:scale-110 transition-transform duration-500">
                <MapPin class="w-5 h-5 sm:w-7 sm:h-7 text-cyan-400" />
              </div>
              <div class="space-y-1">
                <h4 class="text-xs sm:text-sm font-black text-white uppercase tracking-wider">Delivery Coverage</h4>
                <p class="text-[0.65rem] sm:text-xs font-bold text-white/40 leading-relaxed">
                  We serve <strong class="text-cyan-400">{{ supportedAreasText }}</strong>.
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div class="group space-y-2">
                <label class="text-[0.6rem] font-black uppercase tracking-widest text-white/30 ml-4 group-focus-within:text-cyan-400 transition-colors">Receiver Name</label>
                <div class="relative">
                  <input v-model="deliveryDetails.name" type="text" class="w-full bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl px-5 py-3.5 sm:px-6 sm:py-4 text-xs sm:text-sm font-bold focus:bg-white/[0.08] focus:border-cyan-500/50 outline-none transition-all placeholder:text-white/10" placeholder="Full name">
                </div>
              </div>
              <div class="group space-y-2">
                <label class="text-[0.6rem] font-black uppercase tracking-widest text-white/30 ml-4 group-focus-within:text-cyan-400 transition-colors">Contact Number</label>
                <div class="relative flex gap-2">
                  <div class="relative flex-1">
                    <input v-model="deliveryDetails.phone" :readonly="isPhoneVerified" type="text" class="w-full bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl px-5 py-3.5 sm:px-6 sm:py-4 text-xs sm:text-sm font-bold focus:bg-white/[0.08] focus:border-cyan-500/50 outline-none transition-all placeholder:text-white/10" placeholder="09XXXXXXXXX">
                    <div v-if="isPhoneVerified" class="absolute right-4 top-1/2 -translate-y-1/2 text-emerald-400">
                      <CheckCircle class="w-5 h-5" />
                    </div>
                  </div>
                  <button 
                    v-if="!isPhoneVerified && !showOtpInput"
                    @click="sendVerificationCode" 
                    :disabled="isSendingCode || !deliveryDetails.phone"
                    class="px-4 sm:px-6 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 rounded-xl sm:rounded-2xl font-black text-[0.6rem] sm:text-xs uppercase tracking-widest hover:bg-cyan-500/20 disabled:opacity-50 transition-all whitespace-now8"
                  >
                     <Loader2 v-if="isSendingCode" class="w-4 h-4 animate-spin mx-auto" />
                     <span v-else>Send Code</span>
                   </button>
                 </div>
                 <p class="text-[0.6rem] font-bold text-cyan-400/60 mt-2 ml-1 leading-tight flex items-start gap-2 italic">
                   <Clock class="w-3 h-3 mt-0.5 flex-shrink-0" />
                   <span>Mobile number must be working because we will call you within 10-15 minutes to verify your order.</span>
                 </p>
                 <p v-if="phoneError && !showOtpInput" class="text-[0.65rem] font-bold text-rose-400 mt-2 ml-1">{{ phoneError }}</p>

                 <!-- OTP Verification Section (Inline) -->
                 <div v-if="showOtpInput && !isPhoneVerified" class="mt-4 p-4 bg-white/5 border border-white/10 rounded-xl space-y-3 animate-slide-in-right">
                   <div class="flex items-center gap-2 mb-1">
                     <Zap class="w-3 h-3 text-cyan-400" />
                     <span class="text-[0.6rem] font-black text-white uppercase tracking-wider">Enter 6-Digit Code</span>
                   </div>
                   <div class="flex gap-2">
                      <input v-model="otpCode" type="text" maxlength="6" class="flex-1 bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-xs font-bold focus:border-cyan-500/50 outline-none text-center tracking-[0.3em]" placeholder="000000">
                      <button 
                        @click="verifyOtpCode" 
                        :disabled="isVerifyingCode || !otpCode"
                        class="px-4 bg-cyan-400 text-slate-950 font-black rounded-lg text-[0.6rem] uppercase hover:bg-cyan-300 disabled:opacity-50 transition-all"
                      >
                        <Loader2 v-if="isVerifyingCode" class="w-3 h-3 animate-spin mx-auto" />
                        <span v-else>Verify</span>
                      </button>
                    </div>
                   <p v-if="phoneError" class="text-[0.55rem] font-bold text-rose-400">{{ phoneError }}</p>
                 </div>
               </div>
             </div>

             <!-- Removed Old OTP Section from here -->
 
             <!-- Auto-detection Section -->
            <div v-if="ship_to_all !== '1'" class="space-y-4 sm:space-y-6">
              <button @click="getLocation" :disabled="isDetectingLocation" class="w-full py-4 sm:py-5 bg-white/5 border border-white/10 rounded-[1.5rem] sm:rounded-[2rem] flex items-center justify-center gap-3 hover:bg-white/10 transition-all active:scale-[0.98] disabled:opacity-50 group border-dashed hover:border-solid hover:border-cyan-500/30">
                <Loader2 v-if="isDetectingLocation" class="w-5 h-5 sm:w-6 sm:h-6 animate-spin text-cyan-400" />
                <MapPin v-else class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-400 group-hover:animate-bounce" />
                <span class="text-sm sm:text-base font-black tracking-tight">{{ isDetectingLocation ? 'Detecting...' : 'Detect Current Location' }}</span>
              </button>
              
              <div v-if="locationError" class="text-[0.65rem] sm:text-xs font-bold text-rose-400 bg-rose-400/5 border border-rose-400/20 p-4 sm:p-5 rounded-xl sm:rounded-2xl text-center animate-shake">
                {{ locationError }}
              </div>

              <div class="group space-y-2">
                <label class="text-[0.6rem] font-black uppercase tracking-widest text-white/30 ml-4">Verified Barangay / Area</label>
                <input v-model="deliveryDetails.barangay" readonly type="text" class="w-full bg-black/40 border border-white/5 rounded-xl sm:rounded-2xl px-5 py-3.5 sm:px-6 sm:py-4 text-xs sm:text-sm font-bold text-white/40 cursor-not-allowed outline-none" placeholder="Detection required...">
              </div>
            </div>

            <!-- Manual Entry -->
            <div v-else class="space-y-4 sm:space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div class="group space-y-2">
                  <label class="text-[0.6rem] font-black uppercase tracking-widest text-white/30 ml-4">Province / City</label>
                  <input v-model="deliveryDetails.city" type="text" class="w-full bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl px-5 py-3.5 sm:px-6 sm:py-4 text-xs sm:text-sm font-bold focus:bg-white/[0.08] focus:border-cyan-500/50 outline-none transition-all" placeholder="City">
                </div>
                <div class="group space-y-2">
                  <label class="text-[0.6rem] font-black uppercase tracking-widest text-white/30 ml-4">Barangay</label>
                  <input v-model="deliveryDetails.barangay" type="text" class="w-full bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl px-5 py-3.5 sm:px-6 sm:py-4 text-xs sm:text-sm font-bold focus:bg-white/[0.08] focus:border-cyan-500/50 outline-none transition-all" placeholder="Barangay">
                </div>
              </div>
              <div class="group space-y-2">
                <label class="text-[0.6rem] font-black uppercase tracking-widest text-white/30 ml-4">Exact Street / Landmarks</label>
                <textarea v-model="deliveryDetails.street" rows="2" class="w-full bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl px-5 py-3.5 sm:px-6 sm:py-4 text-xs sm:text-sm font-bold focus:bg-white/[0.08] focus:border-cyan-500/50 outline-none transition-all resize-none" placeholder="House #, Street name, Near Landmark..."></textarea>
              </div>
            </div>
          </div>

          <!-- Step 3: Payment Method -->
          <div v-if="currentStep === 3" class="space-y-6 sm:space-y-8 animate-slide-in-right">
            <div class="space-y-4">
              <label class="text-[0.6rem] font-black uppercase tracking-widest text-white/30 ml-4">Payment Selection</label>
              
              <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <button 
                  @click="paymentMethod = 'COD'"
                  class="p-4 sm:p-6 rounded-[1.5rem] sm:rounded-[2rem] border transition-all duration-500 flex flex-col items-center gap-3 sm:gap-4 text-center group relative overflow-hidden"
                  :class="paymentMethod === 'COD' ? 'bg-cyan-500/10 border-cyan-500 shadow-xl' : 'bg-white/5 border-white/5 hover:border-white/10'"
                >
                  <div class="w-10 h-10 sm:w-16 sm:h-16 rounded-xl sm:rounded-[1.5rem] flex items-center justify-center border transition-all duration-500" :class="paymentMethod === 'COD' ? 'bg-cyan-500 text-slate-950 border-cyan-400 scale-110 shadow-lg' : 'bg-white/5 text-white/20 border-white/5'">
                    <CreditCard class="w-5 h-5 sm:w-8 sm:h-8" />
                  </div>
                  <div class="space-y-0.5">
                    <h4 class="text-xs sm:text-base font-black text-white">COD</h4>
                    <p class="text-[0.5rem] sm:text-[0.6rem] text-white/30 font-black uppercase tracking-widest">Pay on Arrival</p>
                  </div>
                </button>

                <button 
                  @click="paymentMethod = 'GCASH'"
                  class="p-4 sm:p-6 rounded-[1.5rem] sm:rounded-[2rem] border transition-all duration-500 flex flex-col items-center gap-3 sm:gap-4 text-center group relative overflow-hidden"
                  :class="paymentMethod === 'GCASH' ? 'bg-blue-600/10 border-blue-500 shadow-xl' : 'bg-white/5 border-white/5 hover:border-white/10'"
                >
                  <div class="w-10 h-10 sm:w-16 sm:h-16 rounded-xl sm:rounded-[1.5rem] flex items-center justify-center border transition-all duration-500" :class="paymentMethod === 'GCASH' ? 'bg-blue-600 text-white border-blue-400 scale-110 shadow-lg' : 'bg-white/5 text-white/20 border-white/5'">
                    <Zap class="w-5 h-5 sm:w-8 sm:h-8 fill-current" />
                  </div>
                  <div class="space-y-0.5">
                    <h4 class="text-xs sm:text-base font-black text-white">GCash</h4>
                    <p class="text-[0.5rem] sm:text-[0.6rem] text-white/30 font-black uppercase tracking-widest">Digital Pay</p>
                  </div>
                </button>
              </div>
            </div>

            <div class="group space-y-2">
              <label class="text-[0.6rem] font-black uppercase tracking-widest text-white/30 ml-4 group-focus-within:text-cyan-400 transition-colors">Voucher Code (Optional)</label>
              <div class="flex gap-2 sm:gap-3">
                <input v-model="voucherCode" type="text" class="flex-1 bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl px-5 py-3.5 sm:px-6 sm:py-4 text-xs sm:text-sm font-bold outline-none transition-all placeholder:text-white/10" placeholder="CODE">
                <button @click="fetchQuote" :disabled="isFetchingQuote" class="px-5 sm:px-8 bg-white text-slate-950 font-black rounded-xl sm:rounded-2xl text-[0.65rem] sm:text-xs hover:bg-cyan-100 transition-all active:scale-95 disabled:opacity-50">
                  <Loader2 v-if="isFetchingQuote" class="w-3.5 h-3.5 animate-spin" />
                  <span v-else>Apply</span>
                </button>
              </div>
            </div>

            <!-- Order Confirmation Summary -->
            <div class="p-6 sm:p-8 bg-white/[0.03] border border-white/10 rounded-[1.5rem] sm:rounded-[2.5rem] space-y-4 sm:space-y-5 relative overflow-hidden group">
              <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/5 blur-3xl pointer-events-none"></div>
              
              <div class="space-y-3 sm:space-y-4">
                <div class="flex justify-between items-center text-[0.55rem] sm:text-[0.7rem] font-black uppercase tracking-[0.15em] text-white/30">
                  <span>Subtotal</span>
                  <span class="text-white">₱{{ formatNumber(quote?.subtotal || cartSubtotal) }}</span>
                </div>
                <div class="flex justify-between items-center text-[0.55rem] sm:text-[0.7rem] font-black uppercase tracking-[0.15em] text-white/30">
                  <span>Shipping</span>
                  <span class="text-white">₱{{ formatNumber(quote?.shipping_fee || 0) }}</span>
                </div>
              </div>
              
              <div class="pt-4 sm:pt-5 border-t border-white/10 flex justify-between items-center">
                <div class="space-y-0.5">
                  <span class="text-[0.55rem] sm:text-[0.65rem] font-black uppercase tracking-[0.2em] text-white/20">Grand Total</span>
                  <div class="text-2xl sm:text-4xl font-black text-[#00ff88] tracking-tighter drop-shadow-[0_0_15px_rgba(0,255,136,0.2)]">
                    ₱{{ formatNumber(quote?.final_total || cartSubtotal) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer (Actions) -->
        <div class="p-4 sm:p-8 border-t border-white/5 bg-transparent backdrop-blur-xl sticky bottom-0 z-10 flex gap-3 sm:gap-4">
          <button 
            v-if="currentStep === 1"
            @click="closeCheckoutModal"
            class="w-16 sm:w-28 py-4 sm:py-5 bg-rose-500/10 border border-rose-500/20 rounded-xl sm:rounded-[1.5rem] text-rose-500 font-black text-[0.6rem] sm:text-xs uppercase tracking-widest hover:bg-rose-500/20 transition-all active:scale-95"
          >
            Cancel
          </button>
          <button 
            v-else
            @click="prevStep" 
            class="w-16 sm:w-28 py-4 sm:py-5 bg-white/5 border border-white/10 rounded-xl sm:rounded-[1.5rem] text-white/40 font-black text-[0.6rem] sm:text-xs uppercase tracking-widest hover:bg-white/10 hover:text-white transition-all active:scale-95"
          >
            Back
          </button>
          <button 
            @click="nextStep" 
            :disabled="!canGoNext || isPlacingOrder"
            class="flex-1 py-4 sm:py-5 bg-cyan-400 text-slate-950 font-black text-xs sm:text-sm rounded-xl sm:rounded-[1.5rem] transition-all duration-500 active:scale-[0.98] flex items-center justify-center gap-2 sm:gap-3 shadow-2xl shadow-cyan-400/20 disabled:opacity-30 disabled:grayscale disabled:active:scale-100 group overflow-hidden relative"
          >
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            <Loader2 v-if="isPlacingOrder" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" />
            <Zap v-else class="w-4 h-4 sm:w-5 sm:h-5 fill-current" />
            <span class="relative z-10 uppercase tracking-widest">{{ nextStepText }}</span>
          </button>
        </div>
      </div>
    </div>
    </Teleport>

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

    <!-- Simulated OTP container -->
    <div id="otp-container"></div>
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

// Phone Verification State
const isPhoneVerified = ref(false);
const isSendingCode = ref(false);
const isVerifyingCode = ref(false);
const otpCode = ref('');
const phoneError = ref('');
const showOtpInput = ref(false);

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
    
    // Check if phone is verified
    if (!isPhoneVerified.value) return false;

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

// Phone Verification Functions
const sendVerificationCode = async () => {
  if (!deliveryDetails.value.phone || deliveryDetails.value.phone.length < 10) {
    phoneError.value = "Please enter a valid phone number.";
    return;
  }

  isSendingCode.value = true;
  phoneError.value = '';
  
  // Simulated Demo Logic
  setTimeout(() => {
    showOtpInput.value = true;
    isSendingCode.value = false;
    alert("Demo Mode: Use code '1' or '19828' to verify.");
  }, 800);
};

const verifyOtpCode = async () => {
  if (!otpCode.value) {
    phoneError.value = "Please enter the code.";
    return;
  }

  isVerifyingCode.value = true;
  phoneError.value = '';

  // Demo validation: accept '1' or '19828'
  setTimeout(() => {
    if (otpCode.value === '1' || otpCode.value === '19828') {
      isPhoneVerified.value = true;
      showOtpInput.value = false;
      alert("Phone number verified successfully (Demo Mode)!");
    } else {
      phoneError.value = "Invalid code. Try '1' or '19828'.";
    }
    isVerifyingCode.value = false;
  }, 800);
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

  const options = {
    enableHighAccuracy: true,
    timeout: 10000,
    maximumAge: 0
  };

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
        locationError.value = "Could not pinpoint your Barangay. Please enter manually.";
      }
    } catch (error) {
      locationError.value = "Failed to detect location details. Please try again.";
    } finally {
      isDetectingLocation.value = false;
    }
  }, (error) => {
    let msg = "Location detection failed.";
    switch(error.code) {
      case error.PERMISSION_DENIED:
        msg = "Please allow location access in your browser settings.";
        break;
      case error.POSITION_UNAVAILABLE:
        msg = "Location information is unavailable.";
        break;
      case error.TIMEOUT:
        msg = "Location request timed out. Please try again.";
        break;
    }
    locationError.value = msg;
    isDetectingLocation.value = false;
  }, options);
};

const validateBarangay = async (bgy) => {
  try {
    const csrfName = document.querySelector('meta[name="csrf-name"]')?.content || 'csrf_test_name';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.content || 'X-CSRF-TOKEN';

    const postData = { barangay: bgy };
    if (csrfName && csrfToken) postData[csrfName] = csrfToken;

    const headers = {};
    if (csrfToken) headers[csrfHeader] = csrfToken;

    const response = await axios.post('/customer/validate-location', postData, { headers });
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

    // Use URLSearchParams for standard form-data POST
    const params = new URLSearchParams();
    params.append('order_data', JSON.stringify(payload));

    const response = await axios.post('/customer/precheckout', params);
    
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

    // Use URLSearchParams for standard form-data POST
    const params = new URLSearchParams();
    params.append('order_data', JSON.stringify(payload));

    const response = await axios.post('/customer/placeOrder', params);

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

  // Global Axios interceptor for CSRF tokens
  axios.interceptors.request.use(config => {
    const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.content || 'X-CSRF-TOKEN';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const csrfName = document.querySelector('meta[name="csrf-name"]')?.content || 'csrf_test_name';
    
    // Ensure AJAX header is sent
    config.headers['X-Requested-With'] = 'XMLHttpRequest';
    
    if (csrfToken && (config.method === 'post' || config.method === 'put' || config.method === 'delete')) {
      config.headers[csrfHeader] = csrfToken;
      
      // If using URLSearchParams, also inject CSRF into the body for redundancy
      if (config.data instanceof URLSearchParams) {
        config.data.set(csrfName, csrfToken);
      }
    }
    return config;
  });

  // Global Axios response interceptor to update CSRF tokens
  axios.interceptors.response.use(response => {
    const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.content || 'X-CSRF-TOKEN';
    const newToken = response.headers[csrfHeader.toLowerCase()] || response.data?.token;
    if (newToken) {
      const meta = document.querySelector('meta[name="csrf-token"]');
      if (meta) meta.content = newToken;
    }
    return response;
  });

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
