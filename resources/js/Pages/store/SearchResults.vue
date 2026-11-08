<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import { addToCart } from '@/composables/store/useCart'
import { openCartDrawer } from '@/composables/store/useStoreUi'

const props = defineProps({
    query: {
        type: String,
        default: '',
    },
    products: {
        type: Array,
        default: () => [],
    },
    total: {
        type: Number,
        default: 0,
    },
    categoryFacets: {
        type: Array,
        default: () => [],
    },
    brandFacets: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
})

// Store Currency Helper
const currencySymbol = '৳'

function formatPrice(val) {
    if (!val && val !== 0) return ''
    return currencySymbol + Math.round(Number(val)).toLocaleString()
}

// ══════════════════════════════════════════
// FILTER STATES
// ══════════════════════════════════════════
const searchQuery = ref(props.query || '')
const selectedCategories = ref(props.filters?.categories || [])
const selectedBrands = ref(props.filters?.brands || [])
const minPriceInput = ref(props.filters?.min_price ?? null)
const maxPriceInput = ref(props.filters?.max_price ?? null)
const priceSlider = ref(props.filters?.max_price ?? 3000)
const minRating = ref(null)
const onlyDiscounted = ref(false)
const onlyRx = ref('all') // 'all' | 'rx' | 'otc'
const sortBy = ref(props.filters?.sort || 'relevance')

// Mobile bottom-sheet filter modal
const mobileFilterOpen = ref(false)

function openFilterSheet() {
    mobileFilterOpen.value = true
    document.body.style.overflow = 'hidden'
}

function closeFilterSheet() {
    mobileFilterOpen.value = false
    document.body.style.overflow = ''
}

// Image load error tracking
const failedImages = ref({})
function handleImgError(id) {
    failedImages.value[id] = true
}

// ══════════════════════════════════════════
// RELATED SEARCHES & SUGGESTIONS
// ══════════════════════════════════════════
const suggestedSearches = [
    'Napa Extra',
    'Paracetamol',
    'Vitamin C',
    'Seclo',
    'Azithromycin',
    'Moisturiser',
    'Face Serum',
    'Calcium',
]

// ══════════════════════════════════════════
// CLIENT-SIDE REACTIVE FILTERING & SORTING
// ══════════════════════════════════════════
const filteredProducts = computed(() => {
    let list = [...props.products]

    // Category filter
    if (selectedCategories.value.length > 0) {
        list = list.filter((p) =>
            selectedCategories.value.includes(String(p.category_id))
        )
    }

    // Brand filter
    if (selectedBrands.value.length > 0) {
        list = list.filter((p) =>
            selectedBrands.value.includes(String(p.brand_id))
        )
    }

    // Price filter
    if (minPriceInput.value !== null && minPriceInput.value !== '') {
        list = list.filter((p) => p.price >= Number(minPriceInput.value))
    }
    if (maxPriceInput.value !== null && maxPriceInput.value !== '') {
        list = list.filter((p) => p.price <= Number(maxPriceInput.value))
    }

    // Rating filter
    if (minRating.value !== null) {
        list = list.filter((p) => (p.rating || 5) >= minRating.value)
    }

    // Discount filter
    if (onlyDiscounted.value) {
        list = list.filter(
            (p) => Boolean(p.badge) || (p.original && p.original > p.price)
        )
    }

    // Prescription filter
    if (onlyRx.value === 'rx') {
        list = list.filter((p) => p.requires_prescription)
    } else if (onlyRx.value === 'otc') {
        list = list.filter((p) => !p.requires_prescription)
    }

    // Sorting
    if (sortBy.value === 'price_low_high') {
        list.sort((a, b) => a.price - b.price)
    } else if (sortBy.value === 'price_high_low') {
        list.sort((a, b) => b.price - a.price)
    } else if (sortBy.value === 'newest') {
        list.sort((a, b) => b.id - a.id)
    } else if (sortBy.value === 'rating') {
        list.sort((a, b) => (b.rating || 5) - (a.rating || 5))
    }

    return list
})

// Active filters count for badges
const activeFiltersCount = computed(() => {
    let count = 0
    if (selectedCategories.value.length > 0) count += selectedCategories.value.length
    if (selectedBrands.value.length > 0) count += selectedBrands.value.length
    if (minPriceInput.value !== null && minPriceInput.value !== '') count++
    if (maxPriceInput.value !== null && maxPriceInput.value !== '') count++
    if (minRating.value !== null) count++
    if (onlyDiscounted.value) count++
    if (onlyRx.value !== 'all') count++
    return count
})

function clearAllFilters() {
    selectedCategories.value = []
    selectedBrands.value = []
    minPriceInput.value = null
    maxPriceInput.value = null
    priceSlider.value = 3000
    minRating.value = null
    onlyDiscounted.value = false
    onlyRx.value = 'all'
    sortBy.value = 'relevance'
    currentPage.value = 1
}

function handlePriceSliderChange(e) {
    const val = Number(e.target.value)
    priceSlider.value = val
    maxPriceInput.value = val === 3000 ? null : val
}

// ══════════════════════════════════════════
// PAGINATION
// ══════════════════════════════════════════
const currentPage = ref(1)
const itemsPerPage = 16

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredProducts.value.length / itemsPerPage))
)

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    return filteredProducts.value.slice(start, start + itemsPerPage)
})

function setPage(p) {
    if (p < 1 || p > totalPages.value) return
    currentPage.value = p
    window.scrollTo({ top: 120, behavior: 'smooth' })
}

// Reset page when filters change
watch(
    [
        selectedCategories,
        selectedBrands,
        minPriceInput,
        maxPriceInput,
        minRating,
        onlyDiscounted,
        onlyRx,
        sortBy,
    ],
    () => {
        currentPage.value = 1
    },
    { deep: true }
)

// ══════════════════════════════════════════
// ACTIONS: ADD TO CART
// ══════════════════════════════════════════
function handleQuickAdd(product) {
    addToCart(product)
    window.dispatchEvent(
        new CustomEvent('medimart:toast', {
            detail: `🛒 Added ${product.name} to cart!`,
        })
    )
}

function handleCardClick(product) {
    addToCart(product)
    openCartDrawer()
    window.dispatchEvent(
        new CustomEvent('medimart:toast', {
            detail: `🛒 Added ${product.name} to cart!`,
        })
    )
}

function executeKeywordSearch(term) {
    router.visit('/search?q=' + encodeURIComponent(term))
}
</script>

<template>
    <StoreLayout>
        <Head>
            <title>
                {{ query ? `Search results for "${query}"` : 'Browse All Products' }} — MediMart
            </title>
        </Head>

        <!-- ════════════════════════════════════════
             SEARCH RESULT HEADER & BREADCRUMBS
        ════════════════════════════════════════ -->
        <div class="max-w-7xl mx-auto px-3 sm:px-4 pt-4">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-1.5 text-xs sm:text-[13px] text-gray-400 mb-3">
                <Link href="/" class="hover:text-primary transition-colors">Home</Link>
                <span>/</span>
                <span class="text-gray-600 font-medium">Search results</span>
                <template v-if="query">
                    <span>/</span>
                    <span class="text-primary font-semibold truncate max-w-[140px] sm:max-w-xs">{{ query }}</span>
                </template>
            </nav>

            <!-- Main Results Header Banner -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs">
                <div>
                    <h1 class="text-lg sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                        <template v-if="query">
                            Search results for <span class="text-primary">&ldquo;{{ query }}&rdquo;</span>
                        </template>
                        <template v-else>
                            All Store Products
                        </template>
                    </h1>
                    <p class="text-xs sm:text-[14px] text-gray-500 mt-1">
                        {{ filteredProducts.length }} product{{ filteredProducts.length === 1 ? '' : 's' }} found
                    </p>
                </div>

                <!-- Mobile Filter Trigger Button -->
                <button
                    type="button"
                    @click="openFilterSheet"
                    class="lg:hidden flex items-center justify-center gap-2 border border-gray-200 hover:border-primary text-gray-700 font-semibold text-xs sm:text-sm px-4 py-2.5 rounded-xl shrink-0 transition-colors cursor-pointer shadow-2xs"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M7 12h10M10 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>Filters</span>
                    <span
                        v-if="activeFiltersCount > 0"
                        class="w-5 h-5 rounded-full bg-primary text-white text-[11px] font-bold flex items-center justify-center ml-1"
                    >
                        {{ activeFiltersCount }}
                    </span>
                </button>
            </div>

            <!-- Related Suggestions Strip -->
            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap mt-3">
                <span class="text-xs text-gray-400 font-medium">Popular:</span>
                <button
                    v-for="term in suggestedSearches"
                    :key="term"
                    type="button"
                    @click="executeKeywordSearch(term)"
                    class="text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-full px-2.5 sm:px-3 py-1 hover:border-primary hover:text-primary transition-colors cursor-pointer"
                    :class="query && query.toLowerCase() === term.toLowerCase() ? 'border-primary bg-primarylt text-primary font-bold' : ''"
                >
                    {{ term }}
                </button>
            </div>

            <!-- Active Filters Chip Strip -->
            <div v-if="activeFiltersCount > 0" class="flex items-center gap-2 flex-wrap mt-2.5 pt-2 border-t border-gray-100">
                <span class="text-xs font-semibold text-gray-500">Active filters:</span>

                <button
                    v-for="catId in selectedCategories"
                    :key="'cat-' + catId"
                    type="button"
                    @click="selectedCategories = selectedCategories.filter(c => c !== catId)"
                    class="inline-flex items-center gap-1 text-xs bg-primarylt text-primary border border-primary/20 px-2.5 py-0.5 rounded-full font-medium hover:bg-primary/20 cursor-pointer"
                >
                    <span>{{ categoryFacets.find(c => c.id === catId)?.name || 'Category' }}</span>
                    <span>✕</span>
                </button>

                <button
                    v-for="bId in selectedBrands"
                    :key="'b-' + bId"
                    type="button"
                    @click="selectedBrands = selectedBrands.filter(b => b !== bId)"
                    class="inline-flex items-center gap-1 text-xs bg-primarylt text-primary border border-primary/20 px-2.5 py-0.5 rounded-full font-medium hover:bg-primary/20 cursor-pointer"
                >
                    <span>{{ brandFacets.find(b => b.id === bId)?.name || 'Brand' }}</span>
                    <span>✕</span>
                </button>

                <button
                    v-if="minPriceInput !== null || maxPriceInput !== null"
                    type="button"
                    @click="minPriceInput = null; maxPriceInput = null; priceSlider = 3000"
                    class="inline-flex items-center gap-1 text-xs bg-primarylt text-primary border border-primary/20 px-2.5 py-0.5 rounded-full font-medium hover:bg-primary/20 cursor-pointer"
                >
                    <span>Price: {{ formatPrice(minPriceInput || 0) }} – {{ formatPrice(maxPriceInput || 3000) }}</span>
                    <span>✕</span>
                </button>

                <button
                    v-if="minRating !== null"
                    type="button"
                    @click="minRating = null"
                    class="inline-flex items-center gap-1 text-xs bg-primarylt text-primary border border-primary/20 px-2.5 py-0.5 rounded-full font-medium hover:bg-primary/20 cursor-pointer"
                >
                    <span>{{ minRating }}★ &amp; up</span>
                    <span>✕</span>
                </button>

                <button
                    v-if="onlyDiscounted"
                    type="button"
                    @click="onlyDiscounted = false"
                    class="inline-flex items-center gap-1 text-xs bg-primarylt text-primary border border-primary/20 px-2.5 py-0.5 rounded-full font-medium hover:bg-primary/20 cursor-pointer"
                >
                    <span>Discounted only</span>
                    <span>✕</span>
                </button>

                <button
                    type="button"
                    @click="clearAllFilters"
                    class="text-xs text-red-500 hover:underline font-bold ml-1 cursor-pointer"
                >
                    Reset all
                </button>
            </div>
        </div>

        <!-- ════════════════════════════════════════
             MAIN: SIDEBAR + RESULTS GRID
        ════════════════════════════════════════ -->
        <section class="max-w-7xl mx-auto px-3 sm:px-4 py-4 sm:py-6">
            <div class="flex gap-5 items-start">

                <!-- ── DESKTOP FILTER SIDEBAR ── -->
                <aside class="hidden lg:block w-64 shrink-0">
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 sticky top-24 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div class="flex items-center gap-1.5">
                                <h3 class="text-base font-bold text-gray-900">Filters</h3>
                                <span
                                    v-if="activeFiltersCount > 0"
                                    class="w-5 h-5 rounded-full bg-primary text-white text-[11px] font-bold flex items-center justify-center"
                                >
                                    {{ activeFiltersCount }}
                                </span>
                            </div>
                            <button
                                v-if="activeFiltersCount > 0"
                                type="button"
                                @click="clearAllFilters"
                                class="text-xs font-semibold text-primary hover:underline cursor-pointer"
                            >
                                Clear All
                            </button>
                        </div>

                        <!-- Category Filter -->
                        <div v-if="categoryFacets.length > 0" class="border-b border-gray-100 pb-4">
                            <p class="text-xs sm:text-[13px] font-bold text-gray-900 mb-2.5 uppercase tracking-wider text-gray-400">
                                Categories
                            </p>
                            <div class="space-y-2 max-h-48 overflow-y-auto no-scrollbar pr-1">
                                <label
                                    v-for="cat in categoryFacets"
                                    :key="cat.id"
                                    class="flex items-center justify-between cursor-pointer text-xs sm:text-[13px] text-gray-600 hover:text-primary transition-colors"
                                >
                                    <span class="flex items-center gap-2 min-w-0">
                                        <input
                                            type="checkbox"
                                            :value="cat.id"
                                            v-model="selectedCategories"
                                            class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300"
                                        />
                                        <span class="truncate">{{ cat.name }}</span>
                                    </span>
                                    <span class="text-[11px] text-gray-400 font-semibold shrink-0 ml-1">
                                        {{ cat.count }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="border-b border-gray-100 pb-4">
                            <p class="text-xs sm:text-[13px] font-bold text-gray-900 mb-2.5 uppercase tracking-wider text-gray-400">
                                Price Range
                            </p>
                            <div class="flex items-center gap-2 mb-2.5">
                                <div class="relative flex-1">
                                    <span class="absolute left-2 top-2 text-xs text-gray-400">৳</span>
                                    <input
                                        v-model.number="minPriceInput"
                                        type="number"
                                        placeholder="Min"
                                        min="0"
                                        class="w-full border border-gray-200 rounded-xl pl-5 pr-2 py-1.5 text-xs focus:outline-none focus:border-primary"
                                    />
                                </div>
                                <span class="text-gray-300">–</span>
                                <div class="relative flex-1">
                                    <span class="absolute left-2 top-2 text-xs text-gray-400">৳</span>
                                    <input
                                        v-model.number="maxPriceInput"
                                        type="number"
                                        placeholder="Max"
                                        min="0"
                                        class="w-full border border-gray-200 rounded-xl pl-5 pr-2 py-1.5 text-xs focus:outline-none focus:border-primary"
                                    />
                                </div>
                            </div>
                            <input
                                type="range"
                                min="0"
                                max="3000"
                                step="50"
                                :value="priceSlider"
                                @input="handlePriceSliderChange"
                                class="w-full accent-primary cursor-pointer"
                            />
                            <div class="flex justify-between text-[11px] text-gray-400 mt-1">
                                <span>৳0</span>
                                <span>৳3,000+</span>
                            </div>
                        </div>

                        <!-- Brand Filter -->
                        <div v-if="brandFacets.length > 0" class="border-b border-gray-100 pb-4">
                            <p class="text-xs sm:text-[13px] font-bold text-gray-900 mb-2.5 uppercase tracking-wider text-gray-400">
                                Brands
                            </p>
                            <div class="space-y-2 max-h-48 overflow-y-auto no-scrollbar pr-1">
                                <label
                                    v-for="b in brandFacets"
                                    :key="b.id"
                                    class="flex items-center justify-between cursor-pointer text-xs sm:text-[13px] text-gray-600 hover:text-primary transition-colors"
                                >
                                    <span class="flex items-center gap-2 min-w-0">
                                        <input
                                            type="checkbox"
                                            :value="b.id"
                                            v-model="selectedBrands"
                                            class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300"
                                        />
                                        <span class="truncate">{{ b.name }}</span>
                                    </span>
                                    <span class="text-[11px] text-gray-400 font-semibold shrink-0 ml-1">
                                        {{ b.count }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Customer Rating Filter -->
                        <div class="border-b border-gray-100 pb-4">
                            <p class="text-xs sm:text-[13px] font-bold text-gray-900 mb-2.5 uppercase tracking-wider text-gray-400">
                                Customer Rating
                            </p>
                            <div class="flex flex-col gap-1.5">
                                <button
                                    type="button"
                                    @click="minRating = minRating === 4 ? null : 4"
                                    class="flex items-center gap-1.5 border rounded-xl px-3 py-1.5 text-left text-xs transition-colors cursor-pointer"
                                    :class="minRating === 4 ? 'border-primary bg-primarylt text-primary font-bold' : 'border-gray-200 text-gray-600 hover:border-primary/50'"
                                >
                                    <span class="text-amber-500">★★★★</span><span class="text-gray-300">★</span>
                                    <span class="text-gray-500 ml-1">&amp; up</span>
                                </button>
                                <button
                                    type="button"
                                    @click="minRating = minRating === 3 ? null : 3"
                                    class="flex items-center gap-1.5 border rounded-xl px-3 py-1.5 text-left text-xs transition-colors cursor-pointer"
                                    :class="minRating === 3 ? 'border-primary bg-primarylt text-primary font-bold' : 'border-gray-200 text-gray-600 hover:border-primary/50'"
                                >
                                    <span class="text-amber-500">★★★</span><span class="text-gray-300">★★</span>
                                    <span class="text-gray-500 ml-1">&amp; up</span>
                                </button>
                            </div>
                        </div>

                        <!-- Prescription Requirement Filter -->
                        <div class="border-b border-gray-100 pb-4">
                            <p class="text-xs sm:text-[13px] font-bold text-gray-900 mb-2.5 uppercase tracking-wider text-gray-400">
                                Prescription Type
                            </p>
                            <div class="grid grid-cols-3 gap-1 text-[11px]">
                                <button
                                    type="button"
                                    @click="onlyRx = 'all'"
                                    class="py-1.5 rounded-lg border font-semibold transition-colors cursor-pointer text-center"
                                    :class="onlyRx === 'all' ? 'bg-primary text-white border-primary' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
                                >
                                    All
                                </button>
                                <button
                                    type="button"
                                    @click="onlyRx = 'otc'"
                                    class="py-1.5 rounded-lg border font-semibold transition-colors cursor-pointer text-center"
                                    :class="onlyRx === 'otc' ? 'bg-primary text-white border-primary' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
                                >
                                    OTC
                                </button>
                                <button
                                    type="button"
                                    @click="onlyRx = 'rx'"
                                    class="py-1.5 rounded-lg border font-semibold transition-colors cursor-pointer text-center"
                                    :class="onlyRx === 'rx' ? 'bg-primary text-white border-primary' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
                                >
                                    Rx Only
                                </button>
                            </div>
                        </div>

                        <!-- Special Offers / Discount Filter -->
                        <div>
                            <label class="flex items-center gap-2.5 cursor-pointer text-xs text-gray-700 font-semibold">
                                <input
                                    type="checkbox"
                                    v-model="onlyDiscounted"
                                    class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300"
                                />
                                <span>On Sale / Discounted only</span>
                            </label>
                        </div>
                    </div>
                </aside>

                <!-- ── MAIN RESULTS COLUMN ── -->
                <div class="flex-1 min-w-0">
                    <!-- Sort and count bar -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-3 sm:p-4 mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 shadow-2xs">
                        <span class="text-xs sm:text-[13.5px] text-gray-500">
                            Showing
                            <span class="font-bold text-gray-900">
                                {{ filteredProducts.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }}–{{ Math.min(currentPage * itemsPerPage, filteredProducts.length) }}
                            </span>
                            of
                            <span class="font-bold text-gray-900">{{ filteredProducts.length }}</span> results
                        </span>

                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                            <label for="sort-select" class="text-xs text-gray-400 hidden sm:inline">Sort by</label>
                            <select
                                id="sort-select"
                                v-model="sortBy"
                                class="border border-gray-200 rounded-xl px-3 py-1.5 text-xs sm:text-[13px] text-gray-700 bg-white focus:outline-none focus:border-primary transition-colors cursor-pointer"
                            >
                                <option value="relevance">Relevance</option>
                                <option value="price_low_high">Price: Low to High</option>
                                <option value="price_high_low">Price: High to Low</option>
                                <option value="newest">Newest First</option>
                                <option value="rating">Highest Rated</option>
                            </select>
                        </div>
                    </div>

                    <!-- ── PRODUCTS GRID ── -->
                    <div v-if="paginatedProducts.length > 0" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-3.5">
                        <div
                            v-for="product in paginatedProducts"
                            :key="product.id"
                            class="prod-card bg-white border border-gray-100 rounded-2xl overflow-hidden cursor-pointer flex flex-col hover:border-primary/40 hover:shadow-md transition-all group relative"
                            @click="handleCardClick(product)"
                        >
                            <!-- Thumbnail Area -->
                            <div class="relative bg-white p-3 aspect-square flex items-center justify-center">
                                <!-- Discount badge -->
                                <span
                                    v-if="product.badge"
                                    class="absolute top-2 left-2 z-10 bg-emerald-600 text-white text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-md shadow-xs"
                                >
                                    {{ product.badge }}
                                </span>

                                <!-- Prescription Badge -->
                                <span
                                    v-if="product.requires_prescription"
                                    class="absolute top-2 right-2 z-10 bg-purple-100 text-purple-800 text-[10px] font-extrabold px-1.5 py-0.2 rounded border border-purple-200"
                                >
                                    Rx
                                </span>

                                <div class="w-full h-full rounded-xl overflow-hidden bg-gray-50 flex items-center justify-center">
                                    <img
                                        v-if="product.image && !failedImages[product.id]"
                                        :src="product.image"
                                        :alt="product.name"
                                        @error="handleImgError(product.id)"
                                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                        loading="lazy"
                                    />
                                    <div v-else class="text-3xl text-primary flex items-center justify-center">
                                        💊
                                    </div>
                                </div>

                                <!-- Add to Cart Button -->
                                <button
                                    type="button"
                                    @click.stop="handleQuickAdd(product)"
                                    class="absolute bottom-2.5 right-2.5 w-8 h-8 bg-primary hover:bg-primary2 text-white rounded-full flex items-center justify-center text-xl font-bold transition-transform active:scale-90 shadow-sm cursor-pointer"
                                    :title="`Add ${product.name} to cart`"
                                >
                                    +
                                </button>
                            </div>

                            <!-- Info Area -->
                            <div class="p-3 sm:p-3.5 flex flex-col flex-1">
                                <p
                                    v-if="product.brand"
                                    class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider line-clamp-1 mb-0.5"
                                >
                                    {{ product.brand }}
                                </p>

                                <p
                                    class="text-xs sm:text-[14px] font-medium text-gray-900 leading-snug line-clamp-2 mb-1 group-hover:text-primary transition-colors"
                                    :title="product.name"
                                >
                                    {{ product.name }}
                                </p>

                                <!-- Star rating -->
                                <div class="flex items-center gap-1 mb-1">
                                    <span class="text-xs text-amber-500">
                                        {{ '★'.repeat(Math.max(1, Math.min(5, product.rating || 5))) }}
                                    </span>
                                    <span class="text-[11px] text-gray-400">
                                        ({{ product.reviews || 0 }})
                                    </span>
                                </div>

                                <!-- Prices -->
                                <div class="flex items-center gap-1.5 mt-auto pt-1">
                                    <span class="text-sm sm:text-base font-bold text-primary">
                                        {{ formatPrice(product.price) }}
                                    </span>
                                    <span
                                        v-if="product.original && product.original > product.price"
                                        class="text-xs text-gray-400 line-through"
                                    >
                                        {{ formatPrice(product.original) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── ZERO RESULTS STATE ── -->
                    <div v-else class="bg-white rounded-2xl border border-gray-100 p-8 sm:p-12 text-center shadow-2xs">
                        <div class="w-16 h-16 rounded-full bg-primarylt text-primary text-3xl flex items-center justify-center mx-auto mb-4">
                            🔍
                        </div>
                        <h2 class="text-base sm:text-lg font-extrabold text-gray-900">
                            No products found
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1.5 max-w-md mx-auto leading-relaxed">
                            We couldn't find any medicines or healthcare items matching your criteria.
                            Try clearing filters or search for another keyword.
                        </p>

                        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                            <button
                                type="button"
                                @click="clearAllFilters"
                                class="w-full sm:w-auto bg-primary hover:bg-primary2 text-white font-bold text-xs sm:text-sm px-5 py-2.5 rounded-xl transition-colors cursor-pointer shadow-xs"
                            >
                                Reset All Filters
                            </button>
                            <Link
                                href="/"
                                class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs sm:text-sm px-5 py-2.5 rounded-xl transition-colors text-center"
                            >
                                Explore Storefront
                            </Link>
                        </div>
                    </div>

                    <!-- ── PAGINATION BAR ── -->
                    <div v-if="totalPages > 1" class="flex items-center justify-center gap-1.5 mt-8 pb-4">
                        <button
                            type="button"
                            :disabled="currentPage === 1"
                            @click="setPage(currentPage - 1)"
                            class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:border-primary hover:text-primary transition-colors disabled:opacity-40 disabled:pointer-events-none cursor-pointer"
                        >
                            ‹
                        </button>

                        <button
                            v-for="p in totalPages"
                            :key="p"
                            type="button"
                            @click="setPage(p)"
                            class="w-9 h-9 flex items-center justify-center rounded-xl border text-xs sm:text-sm font-bold transition-all cursor-pointer"
                            :class="currentPage === p ? 'bg-primary text-white border-primary shadow-xs' : 'border-gray-200 text-gray-700 hover:border-primary'"
                        >
                            {{ p }}
                        </button>

                        <button
                            type="button"
                            :disabled="currentPage === totalPages"
                            @click="setPage(currentPage + 1)"
                            class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:border-primary hover:text-primary transition-colors disabled:opacity-40 disabled:pointer-events-none cursor-pointer"
                        >
                            ›
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             MOBILE FILTER BOTTOM SHEET MODAL
        ════════════════════════════════════════ -->
        <div
            v-if="mobileFilterOpen"
            class="fixed inset-0 z-50 flex flex-col justify-end bg-black/60 backdrop-blur-xs overscroll-contain lg:hidden"
            @click.self="closeFilterSheet"
        >
            <div class="bg-white rounded-t-3xl max-h-[85vh] flex flex-col shadow-2xl animate-in slide-in-from-bottom duration-250">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-extrabold text-gray-900">Filters</h3>
                        <span
                            v-if="activeFiltersCount > 0"
                            class="w-5 h-5 rounded-full bg-primary text-white text-[11px] font-bold flex items-center justify-center"
                        >
                            {{ activeFiltersCount }}
                        </span>
                    </div>
                    <button
                        type="button"
                        @click="closeFilterSheet"
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 cursor-pointer text-xs"
                    >
                        ✕
                    </button>
                </div>

                <!-- Scrollable Filter Body -->
                <div class="overflow-y-auto p-5 space-y-4 text-xs sm:text-sm">
                    <!-- Categories -->
                    <div v-if="categoryFacets.length > 0" class="border-b border-gray-100 pb-4">
                        <p class="font-bold text-gray-900 mb-2.5 uppercase tracking-wider text-[11px] text-gray-400">Categories</p>
                        <div class="space-y-2">
                            <label
                                v-for="cat in categoryFacets"
                                :key="cat.id"
                                class="flex items-center justify-between cursor-pointer text-gray-700"
                            >
                                <span class="flex items-center gap-2 min-w-0">
                                    <input
                                        type="checkbox"
                                        :value="cat.id"
                                        v-model="selectedCategories"
                                        class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300"
                                    />
                                    <span class="truncate">{{ cat.name }}</span>
                                </span>
                                <span class="text-gray-400 font-semibold">{{ cat.count }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="border-b border-gray-100 pb-4">
                        <p class="font-bold text-gray-900 mb-2.5 uppercase tracking-wider text-[11px] text-gray-400">Price Range</p>
                        <div class="flex items-center gap-2 mb-2.5">
                            <input
                                v-model.number="minPriceInput"
                                type="number"
                                placeholder="Min ৳"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs"
                            />
                            <span>–</span>
                            <input
                                v-model.number="maxPriceInput"
                                type="number"
                                placeholder="Max ৳"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs"
                            />
                        </div>
                    </div>

                    <!-- Brands -->
                    <div v-if="brandFacets.length > 0" class="border-b border-gray-100 pb-4">
                        <p class="font-bold text-gray-900 mb-2.5 uppercase tracking-wider text-[11px] text-gray-400">Brands</p>
                        <div class="space-y-2">
                            <label
                                v-for="b in brandFacets"
                                :key="b.id"
                                class="flex items-center justify-between cursor-pointer text-gray-700"
                            >
                                <span class="flex items-center gap-2 min-w-0">
                                    <input
                                        type="checkbox"
                                        :value="b.id"
                                        v-model="selectedBrands"
                                        class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300"
                                    />
                                    <span class="truncate">{{ b.name }}</span>
                                </span>
                                <span class="text-gray-400 font-semibold">{{ b.count }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Customer Rating -->
                    <div class="border-b border-gray-100 pb-4">
                        <p class="font-bold text-gray-900 mb-2.5 uppercase tracking-wider text-[11px] text-gray-400">Rating</p>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="minRating = minRating === 4 ? null : 4"
                                class="flex-1 py-2 rounded-xl border text-center text-xs font-semibold cursor-pointer"
                                :class="minRating === 4 ? 'bg-primary text-white border-primary' : 'bg-gray-50 border-gray-200 text-gray-700'"
                            >
                                ★ 4+
                            </button>
                            <button
                                type="button"
                                @click="minRating = minRating === 3 ? null : 3"
                                class="flex-1 py-2 rounded-xl border text-center text-xs font-semibold cursor-pointer"
                                :class="minRating === 3 ? 'bg-primary text-white border-primary' : 'bg-gray-50 border-gray-200 text-gray-700'"
                            >
                                ★ 3+
                            </button>
                        </div>
                    </div>

                    <!-- Prescription -->
                    <div class="border-b border-gray-100 pb-4">
                        <p class="font-bold text-gray-900 mb-2.5 uppercase tracking-wider text-[11px] text-gray-400">Prescription</p>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button
                                type="button"
                                @click="onlyRx = 'all'"
                                class="py-2 rounded-xl border text-center text-xs font-semibold"
                                :class="onlyRx === 'all' ? 'bg-primary text-white border-primary' : 'bg-gray-50 border-gray-200 text-gray-700'"
                            >
                                All
                            </button>
                            <button
                                type="button"
                                @click="onlyRx = 'otc'"
                                class="py-2 rounded-xl border text-center text-xs font-semibold"
                                :class="onlyRx === 'otc' ? 'bg-primary text-white border-primary' : 'bg-gray-50 border-gray-200 text-gray-700'"
                            >
                                OTC
                            </button>
                            <button
                                type="button"
                                @click="onlyRx = 'rx'"
                                class="py-2 rounded-xl border text-center text-xs font-semibold"
                                :class="onlyRx === 'rx' ? 'bg-primary text-white border-primary' : 'bg-gray-50 border-gray-200 text-gray-700'"
                            >
                                Rx
                            </button>
                        </div>
                    </div>

                    <!-- Discount -->
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                            <input
                                type="checkbox"
                                v-model="onlyDiscounted"
                                class="w-4 h-4 rounded text-primary focus:ring-primary"
                            />
                            <span>Discounted products only</span>
                        </label>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="p-4 border-t border-gray-100 flex gap-3 shrink-0">
                    <button
                        type="button"
                        @click="clearAllFilters"
                        class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-bold text-xs"
                    >
                        Clear All
                    </button>
                    <button
                        type="button"
                        @click="closeFilterSheet"
                        class="flex-1 py-2.5 rounded-xl bg-primary hover:bg-primary2 text-white font-bold text-xs shadow-xs"
                    >
                        Show {{ filteredProducts.length }} Results
                    </button>
                </div>
            </div>
        </div>
    </StoreLayout>
</template>
