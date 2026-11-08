<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import { openCartDrawer, openLoginModal, openMobileNav } from '@/composables/store/useStoreUi'
import { cartCount, addToCart } from '@/composables/store/useCart'
import { categories as fallbackCategories } from '@/data/store'
import SearchListing from '@/Components/store/SearchListing.vue'

const page = usePage()
const store = computed(() => page.props.store || {})
const storeName = computed(() => store.value.name || 'MediMart')
const storeLogo = computed(() => store.value.logo_url)
const storePhone = computed(() => store.value.phone || '09610-016778')
const storeEmail = computed(() => store.value.email || 'support@medimart.com')
const customer = computed(() => page.props.auth?.customer)

const currencySymbol = computed(() => (store.value?.currency === 'USD' ? '$' : '৳'))

const categories = computed(() => {
    return page.props.categories && page.props.categories.length > 0
        ? page.props.categories
        : fallbackCategories
})

function handleAccountClick() {
    if (customer.value) {
        router.visit('/dashboard')
    } else {
        openLoginModal()
    }
}

// ══════════════════════════════════════════
// DYNAMIC LIVE SEARCH STATE & HANDLERS
// ══════════════════════════════════════════
const searchQuery = ref('')
const searchLoading = ref(false)
const searchResults = ref([])
const isSearchOpen = ref(false)
const hasSearched = ref(false)
const activeIndex = ref(-1)

const desktopSearchRef = ref(null)
const mobileSearchRef = ref(null)

let debounceTimer = null

function performSearch() {
    clearTimeout(debounceTimer)
    const q = searchQuery.value.trim()
    if (!q) {
        searchResults.value = []
        isSearchOpen.value = false
        hasSearched.value = false
        searchLoading.value = false
        activeIndex.value = -1
        return
    }

    isSearchOpen.value = true
    searchLoading.value = true
    activeIndex.value = -1

    debounceTimer = setTimeout(() => {
        axios.get('/api/search/products', { params: { q } })
            .then((res) => {
                searchResults.value = res.data.products || []
                hasSearched.value = true
            })
            .catch(() => {
                searchResults.value = []
                hasSearched.value = true
            })
            .finally(() => {
                searchLoading.value = false
            })
    }, 250)
}

function onSearchFocus() {
    if (searchQuery.value.trim().length > 0) {
        isSearchOpen.value = true
    }
}

function clearSearch() {
    searchQuery.value = ''
    searchResults.value = []
    isSearchOpen.value = false
    hasSearched.value = false
    activeIndex.value = -1
}

function closeSearch() {
    isSearchOpen.value = false
    activeIndex.value = -1
}

function navigateResults(direction) {
    if (!isSearchOpen.value || searchResults.value.length === 0) return
    const max = searchResults.value.length - 1
    let next = activeIndex.value + direction
    if (next < 0) next = max
    if (next > max) next = 0
    activeIndex.value = next
}

function selectActiveResult() {
    if (activeIndex.value >= 0 && searchResults.value[activeIndex.value]) {
        handleProductClick(searchResults.value[activeIndex.value])
    } else if (searchResults.value.length > 0) {
        handleProductClick(searchResults.value[0])
    }
}

function handleQuickAdd(product) {
    addToCart(product)
    window.dispatchEvent(new CustomEvent('medimart:toast', {
        detail: `🛒 Added ${product.name} to cart!`
    }))
}

function handleProductClick(product) {
    addToCart(product)
    openCartDrawer()
    closeSearch()
    window.dispatchEvent(new CustomEvent('medimart:toast', {
        detail: `🛒 Added ${product.name} to cart!`
    }))
}

function handleClickOutside(event) {
    const isInsideDesktop = desktopSearchRef.value && desktopSearchRef.value.contains(event.target)
    const isInsideMobile = mobileSearchRef.value && mobileSearchRef.value.contains(event.target)
    if (!isInsideDesktop && !isInsideMobile) {
        closeSearch()
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
    clearTimeout(debounceTimer)
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
                <a
                    v-if="storeEmail"
                    :href="`mailto:${storeEmail}`"
                    class="hover:text-white/80 transition-colors flex items-center gap-1.5"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2"/>
                        <path d="M22 6l-10 7L2 6" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    {{ storeEmail }}
                </a>
                <span class="text-white/40">|</span>
                <span class="flex items-center gap-1.5 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Pharmacy Open 24/7
                </span>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════
         MAIN HEADER
    ════════════════════════════════════════ -->
    <nav class="bg-white sticky top-0 z-40 border-b border-gray-100 shadow-xs">
        <div class="max-w-7xl mx-auto px-2 xs:px-3 sm:px-4">

            <!-- Main Row -->
            <div class="flex items-center justify-between gap-1 xs:gap-1.5 sm:gap-2 lg:gap-8 h-[60px] sm:h-[68px] lg:h-[76px]">

                <!-- Left: Hamburger (mobile/tablet only) + Logo -->
                <div class="flex items-center gap-1 xs:gap-1.5 sm:gap-3 shrink-0">
                    <button
                        type="button"
                        class="lg:hidden p-1 sm:p-2 -ml-1 text-gray-700 hover:text-primary transition-colors shrink-0 cursor-pointer"
                        aria-label="Open mobile menu"
                        @click="openMobileNav"
                    >
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="xs:w-6 xs:h-6">
                            <path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>

                    <!-- Logo: dynamic image if available, else stylized initials/name -->
                    <a href="/" class="flex items-center gap-2 group shrink-0">
                        <img
                            v-if="storeLogo"
                            :src="storeLogo"
                            :alt="storeName"
                            class="h-8 sm:h-9 lg:h-10 w-auto max-w-[120px] sm:max-w-[140px] lg:max-w-[170px] object-contain rounded-lg"
                        />
                        <template v-else>
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-primary flex items-center justify-center text-white font-extrabold text-base sm:text-lg shadow-sm group-hover:scale-105 transition-transform">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <span class="text-xl sm:text-2xl font-extrabold text-primary tracking-tight">
                                {{ storeName }}
                            </span>
                        </template>
                    </a>
                </div>

                <!-- Desktop Search (Fluid & Interactive with Dropdown) -->
                <div class="hidden lg:flex flex-1 max-w-xl" ref="desktopSearchRef">
                    <div class="relative flex w-full">
                        <div class="relative flex-1 flex items-center min-w-0">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search medicines, health products, brands..."
                                @input="performSearch"
                                @focus="onSearchFocus"
                                @keydown.esc="closeSearch"
                                @keydown.down.prevent="navigateResults(1)"
                                @keydown.up.prevent="navigateResults(-1)"
                                @keydown.enter.prevent="selectActiveResult"
                                class="w-full border border-gray-200 rounded-l-xl pl-4 pr-10 py-3.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all bg-white"
                            />
                            <!-- Spinner or Clear button -->
                            <div class="absolute right-3 flex items-center">
                                <svg v-if="searchLoading" class="animate-spin h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <button
                                    v-else-if="searchQuery"
                                    type="button"
                                    @click="clearSearch"
                                    class="text-gray-400 hover:text-gray-600 p-1 cursor-pointer rounded-full transition-colors"
                                    title="Clear search"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                </button>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="performSearch"
                            aria-label="Search products"
                            class="bg-primary hover:bg-primary2 text-white px-6 py-3.5 rounded-r-xl transition-colors cursor-pointer shrink-0 flex items-center justify-center shadow-xs"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                                <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>

                        <!-- Dynamic Search Dropdown (Desktop) -->
                        <SearchListing
                            v-if="isSearchOpen"
                            :results="searchResults"
                            :loading="searchLoading"
                            :query="searchQuery"
                            :has-searched="hasSearched"
                            :currency-symbol="currencySymbol"
                            :active-index="activeIndex"
                            @select="handleProductClick"
                            @add="handleQuickAdd"
                            @close="closeSearch"
                            @hover="idx => activeIndex = idx"
                        />
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
                        class="flex flex-col items-center justify-center w-10 sm:w-11 lg:w-auto px-0 sm:px-2 lg:px-4 py-2 text-gray-600 hover:text-primary transition-colors shrink-0 cursor-pointer"
                        @click="handleAccountClick"
                    >
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" class="sm:w-[22px] sm:h-[22px] lg:w-6 lg:h-6" :class="customer ? 'text-primary' : ''">
                            <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                            <path d="M4 20c0-4 3.58-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span class="hidden lg:block text-[12px] mt-0.5 font-medium truncate max-w-[75px]">
                            {{ customer ? (customer.first_name || 'Account') : 'Account' }}
                        </span>
                    </button>

                    <!-- Orders -->
                    <button
                        type="button"
                        class="hidden sm:flex flex-col items-center justify-center w-11 lg:w-auto px-0 lg:px-4 py-2 text-gray-600 hover:text-primary transition-colors shrink-0 cursor-pointer"
                        @click="handleAccountClick"
                    >
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="lg:w-6 lg:h-6">
                            <path d="M20 7h-3V6a4 4 0 00-8 0v1H6a1 1 0 00-1 1v11a2 2 0 002 2h10a2 2 0 002-2V8a1 1 0 00-1-1zM11 6a1 1 0 012 0v1h-2V6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="hidden lg:block text-[12px] mt-0.5 font-medium">Orders</span>
                    </button>

                    <!-- Cart button -->
                    <button
                        type="button"
                        class="relative flex flex-col items-center justify-center w-11 sm:w-12 lg:w-auto px-0 sm:px-2 lg:px-4 py-2 text-gray-600 hover:text-primary transition-colors shrink-0 cursor-pointer"
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

            <!-- Search (mobile/tablet, below main row with screen responsiveness) -->
            <div class="lg:hidden pb-3 relative" ref="mobileSearchRef">
                <div class="relative flex w-full">
                    <div class="relative flex-1 flex items-center min-w-0">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search medicines, brands & more..."
                            @input="performSearch"
                            @focus="onSearchFocus"
                            @keydown.esc="closeSearch"
                            @keydown.down.prevent="navigateResults(1)"
                            @keydown.up.prevent="navigateResults(-1)"
                            @keydown.enter.prevent="selectActiveResult"
                            class="w-full min-w-0 border border-gray-200 rounded-l-xl pl-3.5 pr-8 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all bg-white"
                        />
                        <!-- Spinner or Clear button -->
                        <div class="absolute right-2.5 flex items-center">
                            <svg v-if="searchLoading" class="animate-spin h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <button
                                v-else-if="searchQuery"
                                type="button"
                                @click="clearSearch"
                                class="text-gray-400 hover:text-gray-600 p-0.5 cursor-pointer rounded-full transition-colors"
                                title="Clear search"
                            >
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="performSearch"
                        aria-label="Search"
                        class="bg-primary hover:bg-primary2 text-white px-4 py-2.5 rounded-r-xl transition-colors shrink-0 cursor-pointer shadow-xs flex items-center justify-center"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                            <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <!-- Dynamic Search Dropdown (Mobile) -->
                <SearchListing
                    v-if="isSearchOpen"
                    :results="searchResults"
                    :loading="searchLoading"
                    :query="searchQuery"
                    :has-searched="hasSearched"
                    :currency-symbol="currencySymbol"
                    :active-index="activeIndex"
                    @select="handleProductClick"
                    @add="handleQuickAdd"
                    @close="closeSearch"
                    @hover="idx => activeIndex = idx"
                />
            </div>

            <!-- Category nav strip -->
            <div class="flex items-center border-t border-gray-100">
                <div class="flex items-center gap-0 overflow-x-auto no-scrollbar">
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
