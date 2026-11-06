<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { openCartDrawer, openLoginModal, openMobileNav } from '@/composables/store/useStoreUi'
import { cartCount } from '@/composables/store/useCart'
import { categories as fallbackCategories } from '@/data/store'

const page = usePage()
const store = computed(() => page.props.store || {})
const storeName = computed(() => store.value.name || 'MediMart')
const storeLogo = computed(() => store.value.logo_url)
const storePhone = computed(() => store.value.phone || '09610-016778')

const categories = computed(() => {
    return page.props.categories && page.props.categories.length > 0
        ? page.props.categories
        : fallbackCategories
})
</script>

<template>
    <!-- ════════════════════════════════════════
         TOP BAR
    ════════════════════════════════════════ -->
    <div class="bg-primary text-white text-sm py-2 hidden sm:block">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between">
            <p class="flex items-center gap-1.5">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Free delivery on orders over ৳500 | Fast doorstep express delivery
            </p>
            <div class="flex items-center gap-5">
                <a href="#" class="hover:text-white/80 transition-colors">Become a Seller</a>
                <a href="#" class="hover:text-white/80 transition-colors">Track Order</a>
                <a href="#" class="hover:text-white/80 transition-colors">Help</a>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════
         NAVBAR
    ════════════════════════════════════════ -->
    <nav class="sticky top-0 z-50 bg-white nav-shadow">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Main nav row -->
            <div class="flex items-center gap-2 sm:gap-3 lg:gap-6 h-16 lg:h-20">

                <!-- Hamburger (mobile/tablet only) -->
                <button
                    type="button"
                    class="lg:hidden shrink-0 w-9 h-9 flex items-center justify-center text-gray-700 -ml-1.5"
                    aria-label="Open navigation menu"
                    @click="openMobileNav"
                >
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>

                <!-- Logo -->
                <a href="/" class="flex items-center gap-2 shrink-0">
                    <img
                        v-if="storeLogo"
                        :src="storeLogo"
                        :alt="storeName"
                        class="h-9 lg:h-11 w-auto max-w-[170px] object-contain rounded-lg"
                    />
                    <template v-else>
                        <div class="w-9 h-9 lg:w-11 lg:h-11 rounded-lg bg-primary flex items-center justify-center shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="lg:w-5 lg:h-5">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="text-lg sm:text-xl lg:text-2xl font-extrabold text-primary whitespace-nowrap">{{ storeName }}</span>
                    </template>
                </a>

                <!-- Search (desktop inline) -->
                <div class="hidden lg:flex flex-1 max-w-2xl">
                    <div class="relative flex w-full">
                        <select
                            class="shrink-0 border border-r-0 border-gray-200 rounded-l-xl px-4 py-3.5 text-base bg-gray-50 text-gray-600 focus:outline-none max-w-[150px] truncate"
                        >
                            <option value="">All</option>
                            <option v-for="cat in categories" :key="cat.slug || cat.name" :value="cat.slug">
                                {{ cat.name }}
                            </option>
                        </select>
                        <input
                            type="text"
                            placeholder="Search medicines, brands & more..."
                            class="flex-1 min-w-0 border border-gray-200 px-5 py-3.5 text-base focus:outline-none focus:border-primary transition-colors"
                        />
                        <button
                            type="button"
                            aria-label="Search products"
                            class="bg-primary hover:bg-primary2 text-white px-6 py-3.5 rounded-r-xl transition-colors"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                                <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Spacer to push icons right on mobile -->
                <div class="flex-1 lg:hidden"></div>

                <!-- Right actions -->
                <div class="flex items-center gap-0 xs:gap-0.5 sm:gap-1 lg:gap-4 shrink-0">

                    <!-- Upload Rx -->
                    <a
                        href="#upload-prescription"
                        class="hidden lg:flex items-center gap-2 border border-primary text-primary text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-primarylt transition-colors shrink-0"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Upload Rx
                    </a>

                    <!-- Account -->
                    <button
                        type="button"
                        class="flex flex-col items-center justify-center w-10 sm:w-11 lg:w-auto px-0 sm:px-2 lg:px-4 py-2 text-gray-600 hover:text-primary transition-colors shrink-0"
                        @click="openLoginModal"
                    >
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" class="sm:w-[22px] sm:h-[22px] lg:w-6 lg:h-6">
                            <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                            <path d="M4 20c0-4 3.58-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span class="hidden lg:block text-[12px] mt-0.5 font-medium">Account</span>
                    </button>

                    <!-- Orders -->
                    <button
                        type="button"
                        class="hidden sm:flex flex-col items-center justify-center w-11 lg:w-auto px-0 lg:px-4 py-2 text-gray-600 hover:text-primary transition-colors shrink-0"
                        @click="openLoginModal"
                    >
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="lg:w-6 lg:h-6">
                            <path d="M20 7h-3V6a4 4 0 00-8 0v1H6a1 1 0 00-1 1v11a2 2 0 002 2h10a2 2 0 002-2V8a1 1 0 00-1-1zM11 6a1 1 0 012 0v1h-2V6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="hidden lg:block text-[12px] mt-0.5 font-medium">Orders</span>
                    </button>

                    <!-- Cart button -->
                    <button
                        type="button"
                        class="relative flex flex-col items-center justify-center w-11 sm:w-12 lg:w-auto px-0 sm:px-2 lg:px-4 py-2 text-gray-600 hover:text-primary transition-colors shrink-0"
                        aria-label="View shopping cart"
                        @click="openCartDrawer"
                    >
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="sm:w-[23px] sm:h-[23px] lg:w-6 lg:h-6">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3 6h18M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="hidden lg:block text-[12px] mt-0.5 font-medium">Cart</span>
                        <span
                            id="cart-badge"
                            class="absolute top-0.5 right-1 sm:right-1.5 w-[1.125rem] h-[1.125rem] sm:w-5 sm:h-5 bg-accent text-white text-[10px] sm:text-[11px] font-bold rounded-full flex items-center justify-center"
                        >{{ cartCount }}</span>
                    </button>

                </div>
            </div>

            <!-- Search (mobile/tablet, below main row) -->
            <div class="lg:hidden pb-3">
                <div class="relative flex w-full">
                    <input
                        type="text"
                        placeholder="Search medicines, brands & more..."
                        class="flex-1 min-w-0 border border-gray-200 rounded-l-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition-colors"
                    />
                    <button
                        type="button"
                        aria-label="Search"
                        class="bg-primary hover:bg-primary2 text-white px-4 py-2.5 rounded-r-xl transition-colors shrink-0"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                            <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Category nav strip -->
            <div class="flex items-center border-t border-gray-100">
                <div class="flex items-center gap-0 overflow-x-auto no-scrollbar">
<!--                    <a-->
<!--                        href="#all-categories"-->
<!--                        class="flex items-center gap-2 px-5 py-3.5 text-[15px] font-semibold text-white bg-primary whitespace-nowrap shrink-0"-->
<!--                    >-->
<!--                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">-->
<!--                            <path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>-->
<!--                        </svg>-->
<!--                        All Categories-->
<!--                    </a>-->

                    <a
                        v-for="cat in categories"
                        :key="cat.slug || cat.name"
                        :href="cat.slug ? `#category-${cat.slug}` : '#'"
                        class="px-5 py-3.5 text-[15px] text-gray-600 hover:text-primary hover:bg-primarylt transition-colors whitespace-nowrap shrink-0"
                    >
                        {{ cat.icon || '🏷️' }} {{ cat.name }}
                    </a>
                </div>

                <!-- Right-side quick links -->
                <div class="hidden lg:flex items-center gap-6 ml-auto pl-6 pr-1 shrink-0 border-l border-gray-100">
                    <a href="#" class="flex items-center gap-1.5 text-[14px] text-gray-500 hover:text-primary whitespace-nowrap transition-colors">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Become a Seller
                    </a>
                    <a href="#" class="flex items-center gap-1.5 text-[14px] text-gray-500 hover:text-primary whitespace-nowrap transition-colors">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M20 12H4M4 12l6-6M4 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Track Order
                    </a>
                    <a
                        :href="`tel:${storePhone}`"
                        class="flex items-center gap-1.5 text-[14px] font-semibold text-primary whitespace-nowrap transition-colors"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ storePhone }}
                    </a>
                </div>
            </div>

        </div>
    </nav>
</template>
