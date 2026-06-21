<script setup>
import { ref, onMounted, onUnmounted, onBeforeUpdate, nextTick, watch, Teleport } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CustomerLayout from '../../layouts/CustomerLayout.vue';
import gsap from 'gsap';
import ScrollTrigger from 'gsap/ScrollTrigger';
import { ArrowLeft, ShoppingCart, Info, Star, Loader2 } from 'lucide-vue-next';

// Register GSAP Plugin
gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    product: {
        type: Object,
        required: true,
        default: () => ({
            id: 1,
            name: 'TALAbahan Special Bangros',
            description: 'Experience the ultimate seafood delight. A rich, deep flavor profile infused with our signature spices and a hint of smoky essence, crafted to perfection.',
            price: '250.00',
            unit: 'Per serving',
            image: '/images/products/bangros-detail.jpg',
            category: 'Premium',
            prep_style: 'Kinilaw / Grilled',
            flavor_notes: 'Spicy, Citrusy, Savory',
            portion_size: 'Good for 2-3 persons',
            stock_status: 'available'
        })
    }
});

// Refs for GSAP
const headerRef = ref(null);
const headerImgRef = ref(null);
const titleRef = ref(null);
const splitSectionsRef = ref([]);
const stickyBarRef = ref(null);

// Scroll tracking for back button visibility
const isBackButtonVisible = ref(true);
let lastScrollY = 0;

// Collect multiple section refs into array via function ref
const setSplitSectionRef = (el) => {
    if (el && !splitSectionsRef.value.includes(el)) {
        splitSectionsRef.value.push(el);
    }
};

// Clear refs before each update to avoid duplicates
onBeforeUpdate(() => {
    splitSectionsRef.value = [];
});

// Image URL helper — product.image is just a filename from DB
const fallbackImage = 'https://images.unsplash.com/photo-1559847844-5315695dadae?q=80&w=2000&auto=format&fit=crop';
const getImageUrl = (imagePath) => {
    if (!imagePath) return fallbackImage;
    if (imagePath.startsWith('http')) return imagePath;
    const baseUrl = window.BASE_URL || '';
    const cleanBaseUrl = baseUrl.replace(/\/$/, '');
    const cleanPath = imagePath.replace(/^\//, '').replace(/^uploads\//, '').replace(/^products\//, '');
    return `${cleanBaseUrl}/uploads/products/${cleanPath}`;
};

const handleScroll = () => {
    const currentScrollY = window.scrollY;
    // Check both window scroll and main content container scroll
    const mainContent = document.querySelector('.main-content-glass');
    const containerScrollY = mainContent ? mainContent.scrollTop : 0;
    
    const effectiveScrollY = Math.max(currentScrollY, containerScrollY);
    
    if (effectiveScrollY > lastScrollY && effectiveScrollY > 100) {
        isBackButtonVisible.value = false;
    } else {
        isBackButtonVisible.value = true;
    }
    
    lastScrollY = effectiveScrollY;
};

onMounted(() => {
    // Add scroll listeners to both window and main container
    window.addEventListener('scroll', handleScroll, { passive: true });
    const mainContent = document.querySelector('.main-content-glass');
    if (mainContent) {
        mainContent.addEventListener('scroll', handleScroll, { passive: true });
    }
    
    nextTick(() => {
        // 1. Parallax Image Header
        gsap.to(headerImgRef.value, {
            yPercent: 30,
            ease: "none",
            scrollTrigger: {
                trigger: headerRef.value,
                start: "top top",
                end: "bottom top",
                scrub: true,
            }
        });

        // 2. Text Parallax/Masking on Title
        gsap.fromTo(titleRef.value, 
            { y: 100, opacity: 0, clipPath: 'inset(100% 0 0 0)' },
            { 
                y: 0, 
                opacity: 1, 
                clipPath: 'inset(0% 0 0 0)', 
                duration: 1.5, 
                ease: "power4.out",
                delay: 0.2
            }
        );
        
        gsap.to(titleRef.value, {
            y: -100,
            opacity: 0.5,
            scrollTrigger: {
                trigger: headerRef.value,
                start: "top top",
                end: "bottom top",
                scrub: 1,
            }
        });

        // 3. Sequential Scroll Reveals (Split Grid)
        splitSectionsRef.value.forEach((section, i) => {
            const textContent = section.querySelector('.split-text');
            const imageContent = section.querySelector('.split-image');
            
            if(textContent) {
                gsap.fromTo(textContent, 
                    { y: 50, opacity: 0 },
                    {
                        y: 0,
                        opacity: 1,
                        duration: 1,
                        ease: "power3.out",
                        scrollTrigger: {
                            trigger: section,
                            start: "top 80%",
                        }
                    }
                );
            }

            if(imageContent) {
                gsap.fromTo(imageContent, 
                    { scale: 0.9, opacity: 0 },
                    {
                        scale: 1,
                        opacity: 1,
                        duration: 1.2,
                        ease: "power3.out",
                        scrollTrigger: {
                            trigger: section,
                            start: "top 80%",
                        }
                    }
                );
            }
        });

        // 4. Sticky Order Bar Transition
        gsap.fromTo(stickyBarRef.value, 
            { y: 100, opacity: 0 },
            {
                y: 0,
                opacity: 1,
                duration: 0.5,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: headerRef.value,
                    start: "bottom center", // Appears after scrolling past header
                    toggleActions: "play none none reverse"
                }
            }
        );
    });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    const mainContent = document.querySelector('.main-content-glass');
    if (mainContent) {
        mainContent.removeEventListener('scroll', handleScroll);
    }
    ScrollTrigger.getAll().forEach(t => t.kill());
});

const goBack = () => {
    window.history.length > 1 ? router.back() : router.visit('/customer/dashboard');
};

const isProcessing = ref(false);

const buyNow = () => {
    isProcessing.value = true;
    // Implement add to cart / buy now logic
    console.log(`Buying ${props.product.name}`);
    setTimeout(() => {
        isProcessing.value = false;
    }, 400); // simulated instant feedback
};
</script>

<template>
    <CustomerLayout>
        <Head :title="`${product.name} | Details`" />
        
        <div class="min-h-screen text-white selection:bg-cyan-500/30 font-sans overflow-x-hidden" style="background-color: oklch(0.13 0.04 265);">
            
            <!-- Floating Back Button (compact for built-in nav phones) -->
            <Teleport to="body">
                <Transition name="back-button">
                    <Link 
                        v-if="isBackButtonVisible"
                        href="/customer/dashboard" 
                        class="fixed top-14 left-3 sm:top-6 sm:left-6 md:top-12 md:left-12 lg:top-16 lg:left-[240px] z-[9999] flex items-center gap-1.5 sm:gap-3 text-white/60 hover:text-cyan-400 bg-white/[0.04] px-2.5 sm:px-5 py-1.5 sm:py-2.5 rounded-full border border-white/[0.08] hover:border-white/20 hover:bg-white/[0.08] transition-all duration-300 group shadow-lg hover:-translate-x-1"
                    >
                        <span class="inline-block transition-transform duration-300 group-hover:-translate-x-1">
                            <ArrowLeft class="w-3 h-3 sm:w-4 sm:h-4" />
                        </span>
                        <span class="text-[0.5rem] sm:text-xs tracking-widest uppercase font-bold">Back</span>
                    </Link>
                </Transition>
            </Teleport>

            <!-- Parallax Header -->
            <header ref="headerRef" class="relative w-full h-[70vh] sm:h-[80vh] md:h-screen flex items-center justify-center overflow-hidden">
                <!-- Background Image with Parallax -->
                <div 
                    ref="headerImgRef"
                    class="absolute inset-0 w-full h-[130%] -top-[15%] bg-cover bg-center opacity-40 mix-blend-luminosity"
                    :style="`background-image: url('${getImageUrl(product.image)}');`"
                ></div>
                
                <!-- Gradients for depth -->
                <div class="absolute inset-0 bg-gradient-to-b from-[#020617]/80 via-transparent to-[#020617]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(6,182,212,0.15),transparent_70%)]"></div>

                <div class="relative z-10 flex flex-col items-center text-center px-4 max-w-5xl mx-auto">
                    <span class="text-cyan-400 text-[0.6rem] sm:text-sm md:text-base tracking-[0.3em] uppercase mb-4 sm:mb-6 font-bold drop-shadow-[0_0_8px_rgba(6,182,212,0.8)]">
                        Premium Selection
                    </span>
                    <h1 ref="titleRef" class="text-4xl sm:text-5xl md:text-7xl lg:text-8xl font-black text-transparent bg-clip-text bg-gradient-to-b from-white to-white/50 tracking-tighter leading-tight filter drop-shadow-[0_0_30px_rgba(255,255,255,0.1)]">
                        {{ product.name }}
                    </h1>
                </div>
            </header>

            <!-- Content Details - Split Grid Reveal -->
            <main class="relative z-20 pb-40">
                
                <!-- Description Section -->
                <section :ref="setSplitSectionRef" class="max-w-7xl mx-auto px-3 sm:px-6 py-16 sm:py-24 md:py-32 flex flex-col md:flex-row items-center gap-8 sm:gap-12 md:gap-24">
                    <div class="w-full md:w-1/2 split-text">
                        <div class="w-10 sm:w-12 h-1 bg-gradient-to-r from-cyan-500 to-purple-600 mb-6 sm:mb-8 rounded-full shadow-[0_0_10px_rgba(6,182,212,0.5)]"></div>
                        <h2 class="text-2xl sm:text-3xl md:text-5xl font-bold mb-4 sm:mb-6 text-white/90 leading-tight">Crafted for the <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">Connoisseur</span></h2>
                        <p class="text-base sm:text-lg text-white/60 leading-relaxed font-light">
                            {{ product.description }}
                        </p>
                    </div>
                    <div class="w-full md:w-1/2 split-image relative">
                        <div class="absolute inset-0 bg-gradient-to-tr from-cyan-500/20 to-purple-600/20 rounded-xl sm:rounded-2xl blur-2xl"></div>
                        <div class="relative aspect-square md:aspect-[4/3] rounded-xl sm:rounded-2xl overflow-hidden border border-white/[0.08] shadow-2xl bg-white/[0.04]  p-1.5 sm:p-2">
                             <div class="w-full h-full rounded-lg sm:rounded-xl bg-cover bg-center mix-blend-overlay" :style="`background-image: url('${getImageUrl(product.image)}');`"></div>
                        </div>
                    </div>
                </section>

                <!-- Specs Section -->
                <section :ref="setSplitSectionRef" class="max-w-7xl mx-auto px-3 sm:px-6 py-16 sm:py-24 md:py-32 flex flex-col-reverse md:flex-row items-center gap-8 sm:gap-12 md:gap-24">
                    <div class="w-full md:w-1/2 split-image relative grid grid-cols-2 gap-3 sm:gap-4">
                        <div class="aspect-square rounded-xl sm:rounded-2xl bg-white/[0.04] border border-white/[0.08] p-4 sm:p-6 flex flex-col justify-center items-center text-center group hover:border-cyan-500/50 hover:bg-white/[0.08] transition-all duration-500 shadow-[0_4px_30px_rgba(0,0,0,0.5)]">
                            <Star class="w-6 h-6 sm:w-8 sm:h-8 text-cyan-400 mb-3 sm:mb-4 group-hover:scale-110 transition-transform drop-shadow-[0_0_8px_rgba(6,182,212,0.8)]" />
                            <span class="text-[0.6rem] sm:text-xs text-white/40 uppercase tracking-widest mb-1">Preparation</span>
                            <span class="text-xs sm:text-sm font-semibold text-white/90">{{ product.prep_style }}</span>
                        </div>
                        <div class="aspect-square rounded-xl sm:rounded-2xl bg-white/[0.04] border border-white/[0.08] p-4 sm:p-6 flex flex-col justify-center items-center text-center group hover:border-purple-500/50 hover:bg-white/[0.08] transition-all duration-500 shadow-[0_4px_30px_rgba(0,0,0,0.5)] mt-6 sm:mt-8">
                            <Info class="w-6 h-6 sm:w-8 sm:h-8 text-purple-400 mb-3 sm:mb-4 group-hover:scale-110 transition-transform drop-shadow-[0_0_8px_rgba(168,85,247,0.8)]" />
                            <span class="text-[0.6rem] sm:text-xs text-white/40 uppercase tracking-widest mb-1">Flavor Notes</span>
                            <span class="text-xs sm:text-sm font-semibold text-white/90">{{ product.flavor_notes }}</span>
                        </div>
                    </div>
                    <div class="w-full md:w-1/2 split-text md:pl-8 lg:pl-12">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4 sm:mb-6 text-white/90">The Perfect Serving</h2>
                        <ul class="space-y-4 sm:space-y-6">
                            <li class="flex items-start gap-3 sm:gap-4">
                                <div class="w-1.5 h-1.5 rounded-full bg-cyan-400 mt-2 shadow-[0_0_8px_rgba(6,182,212,0.8)]"></div>
                                <div>
                                    <h4 class="text-white/80 font-semibold mb-1 text-sm sm:text-base">Portion Size</h4>
                                    <p class="text-white/50 text-xs sm:text-sm">{{ product.portion_size }}</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3 sm:gap-4">
                                <div class="w-1.5 h-1.5 rounded-full bg-purple-400 mt-2 shadow-[0_0_8px_rgba(168,85,247,0.8)]"></div>
                                <div>
                                    <h4 class="text-white/80 font-semibold mb-1 text-sm sm:text-base">Unit Type</h4>
                                    <p class="text-white/50 text-xs sm:text-sm">{{ product.unit }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </section>
            </main>

            <!-- Sticky Order Bar (compact for built-in nav phones) -->
            <div ref="stickyBarRef" class="fixed bottom-0 left-0 w-full z-50 p-2 sm:p-3 md:p-6 translate-y-full opacity-0 pointer-events-none" style="padding-bottom: env(safe-area-inset-bottom, 8px);">
                <div class="max-w-4xl mx-auto bg-white/[0.04] border border-white/[0.08] rounded-xl sm:rounded-2xl p-2.5 sm:p-4 md:px-8 md:py-5 flex items-center justify-between shadow-[0_-10px_40px_rgba(0,0,0,0.8)] hover:bg-white/[0.08] hover:border-white/20 transition-all duration-500 pointer-events-auto relative overflow-hidden">
                    <!-- Glow effect inside bar -->
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-1/2 h-[1px] bg-gradient-to-r from-transparent via-cyan-500/50 to-transparent"></div>
                    
                    <div class="flex flex-col">
                        <span class="text-[0.5rem] sm:text-xs text-white/50 uppercase tracking-widest font-semibold">{{ product.unit }}</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-cyan-400 font-black text-base sm:text-xl">₱</span>
                            <span class="text-lg sm:text-2xl md:text-3xl font-black text-white">{{ parseFloat(product.price).toFixed(2) }}</span>
                        </div>
                    </div>

                    <button @pointerdown.prevent="buyNow" :disabled="isProcessing" class="relative group overflow-hidden rounded-lg sm:rounded-xl bg-cyan-400 text-slate-950 px-4 sm:px-6 md:px-8 py-2 sm:py-2.5 md:py-4 font-black uppercase tracking-wider text-[0.65rem] sm:text-sm md:text-base transition-all duration-300 active:scale-95 flex items-center gap-1.5 sm:gap-3 touch-manipulation disabled:opacity-70 disabled:cursor-not-allowed shadow-lg shadow-cyan-400/20 hover:bg-cyan-300">
                        <span class="relative z-10">{{ isProcessing ? 'Processing...' : 'Order Now' }}</span>
                        <Loader2 v-if="isProcessing" class="w-3 h-3 sm:w-4 sm:h-4 relative z-10 animate-spin" />
                        <ShoppingCart v-else class="w-3 h-3 sm:w-4 sm:h-4 relative z-10 transition-transform duration-300 group-hover:scale-110" />
                    </button>
                </div>
            </div>

        </div>
    </CustomerLayout>
</template>

<style scoped>
.back-button-enter-active,
.back-button-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.back-button-enter-from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
}

.back-button-leave-to {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
}
</style>
