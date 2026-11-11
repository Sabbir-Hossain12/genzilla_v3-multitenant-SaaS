<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import ProductCard from '@/Components/store/ProductCard.vue'
import { addToCart } from '@/composables/store/useCart'
import { openCartDrawer } from '@/composables/store/useStoreUi'

const props = defineProps({
    collection: {
        type: String,
        default: 'all',
    },
    hero: {
        type: Object,
        default: () => ({
            title: 'All Products',
            subtitle: 'Browse our extensive catalog of genuine medicines and healthcare essentials',
            badge: 'Verified Inventory',
            color: 'from-teal-800 to-teal-950',
        }),
    },
    products: {
        type: Array,
        default: () => [],
    },
    totalProducts: {
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
    seo: {
        type: Object,
        default: () => ({}),
    },
})

// Currency
const currencySymbol = '৳'

// Dynamic max price calculation
const maxPriceLimit = computed(() => {
    if (!props.products || props.products.length === 0) return 5000
    const highest = Math.max(...props.products.map(p => Number(p.price) || 0))
    return Math.max(1000, Math.ceil(highest / 500) * 500)
})

// ══════════════════════════════════════════
// FILTER STATES
// ══════════════════════════════════════════
const activeCollection = ref(props.collection || 'all')

const selectedCategories = ref(
    Array.isArray(props.filters?.categories) ? [...props.filters.categories] : []
)

const selectedBrands = ref(
    Array.isArray(props.filters?.brands) ? [...props.filters.brands] : []
)

const minPriceInput = ref(props.filters?.min_price ?? null)
const maxPriceInput = ref(props.filters?.max_price ?? null)
const priceSlider = ref(props.filters?.max_price ?? maxPriceLimit.value)
const minRating = ref(
    props.collection === 'best-picks' ? (props.filters?.rating ?? 4) : (props.filters?.rating ?? null)
)
const onlyDiscounted = ref(
    props.collection === 'flash-sale' ? true : (props.filters?.discount ?? false)
)
const onlyRx = ref(props.filters?.rx || 'all') // 'all' | 'rx' | 'otc'
const sortBy = ref(
    props.collection === 'best-picks' ? 'rating' : (props.collection === 'flash-sale' ? 'discount' : (props.filters?.sort || 'popularity'))
)

// Mobile bottom-sheet filter modal
const mobileFilterOpen = ref(false)

function openFilterSheet() {
    mobileFilterOpen.value = true
    if (typeof document !== 'undefined') {
        document.body.style.overflow = 'hidden'
    }
}

function closeFilterSheet() {
    mobileFilterOpen.value = false
    if (typeof document !== 'undefined') {
        document.body.style.overflow = ''
    }
}

// ══════════════════════════════════════════
// COLLECTION SWITCHER
// ══════════════════════════════════════════
function switchCollection(col) {
    activeCollection.value = col
    if (col === 'flash-sale') {
        onlyDiscounted.value = true
        sortBy.value = 'discount'
    } else if (col === 'best-picks') {
        onlyDiscounted.value = false
        minRating.value = 4
        sortBy.value = 'rating'
    } else {
        onlyDiscounted.value = false
        minRating.value = null
        sortBy.value = 'popularity'
    }
}

// ══════════════════════════════════════════
// FILTER TOGGLES & HELPERS
// ══════════════════════════════════════════
function isCategorySelected(cat) {
    if (!cat) return false
    const id = String(cat.id || '')
    const slug = String(cat.slug || '').toLowerCase()
    return selectedCategories.value.some(sel => {
        const s = String(sel).toLowerCase()
        return s === id || s === slug
    })
}

function toggleCategory(cat) {
    const slug = cat.slug || String(cat.id)
    const id = String(cat.id || '')
    const idx = selectedCategories.value.findIndex(sel => {
        const s = String(sel).toLowerCase()
        return s === slug.toLowerCase() || s === id
    })
    if (idx > -1) {
        selectedCategories.value.splice(idx, 1)
    } else {
        selectedCategories.value.push(slug)
    }
}

function selectSingleCategoryPill(cat) {
    if (!cat) {
        selectedCategories.value = []
    } else {
        const slug = cat.slug || String(cat.id)
        if (selectedCategories.value.length === 1 && isCategorySelected(cat)) {
            selectedCategories.value = []
        } else {
            selectedCategories.value = [slug]
        }
    }
}

function isBrandSelected(brand) {
    if (!brand) return false
    const id = String(brand.id || '')
    const slug = String(brand.slug || '').toLowerCase()
    const name = String(brand.name || '').toLowerCase()
    return selectedBrands.value.some(sel => {
        const s = String(sel).toLowerCase()
        return s === id || s === slug || s === name
    })
}

function toggleBrand(brand) {
    const slug = brand.slug || brand.name || String(brand.id)
    const id = String(brand.id || '')
    const name = String(brand.name || '').toLowerCase()
    const idx = selectedBrands.value.findIndex(sel => {
        const s = String(sel).toLowerCase()
        return s === slug.toLowerCase() || s === id || s === name
    })
    if (idx > -1) {
        selectedBrands.value.splice(idx, 1)
    } else {
        selectedBrands.value.push(slug)
    }
}

function toggleRating(stars) {
    if (minRating.value === stars) {
        minRating.value = null
    } else {
        minRating.value = stars
    }
}

function onSliderChange() {
    maxPriceInput.value = priceSlider.value >= maxPriceLimit.value ? null : priceSlider.value
}

function clearAllFilters() {
    activeCollection.value = 'all'
    selectedCategories.value = []
    selectedBrands.value = []
    minPriceInput.value = null
    maxPriceInput.value = null
    priceSlider.value = maxPriceLimit.value
    minRating.value = null
    onlyDiscounted.value = false
    onlyRx.value = 'all'
    sortBy.value = 'popularity'
}

const activeFiltersCount = computed(() => {
    let count = 0
    if (activeCollection.value !== 'all') count++
    if (selectedCategories.value.length > 0) count += selectedCategories.value.length
    if (selectedBrands.value.length > 0) count += selectedBrands.value.length
    if (minPriceInput.value !== null && minPriceInput.value !== '') count++
    if (maxPriceInput.value !== null && maxPriceInput.value !== '') count++
    if (minRating.value !== null && activeCollection.value !== 'best-picks') count++
    if (onlyDiscounted.value && activeCollection.value !== 'flash-sale') count++
    if (onlyRx.value !== 'all') count++
    return count
})

function removeFilter(type, value) {
    if (type === 'collection') {
        switchCollection('all')
    } else if (type === 'cat') {
        const idx = selectedCategories.value.findIndex(sel => {
            const s = String(sel).toLowerCase()
            return s === String(value).toLowerCase()
        })
        if (idx > -1) selectedCategories.value.splice(idx, 1)
    } else if (type === 'brand') {
        const idx = selectedBrands.value.findIndex(sel => {
            const s = String(sel).toLowerCase()
            return s === String(value).toLowerCase()
        })
        if (idx > -1) selectedBrands.value.splice(idx, 1)
    } else if (type === 'min_price') {
        minPriceInput.value = null
    } else if (type === 'max_price') {
        maxPriceInput.value = null
        priceSlider.value = maxPriceLimit.value
    } else if (type === 'rating') {
        minRating.value = null
    } else if (type === 'discount') {
        onlyDiscounted.value = false
    } else if (type === 'rx') {
        onlyRx.value = 'all'
    }
}

function getCategoryLabel(val) {
    const sVal = String(val).toLowerCase()
    const found = props.categoryFacets.find(c => {
        return String(c.slug || '').toLowerCase() === sVal || String(c.id || '') === sVal || String(c.name || '').toLowerCase() === sVal
    })
    return found ? found.name : val
}

function getBrandLabel(val) {
    const sVal = String(val).toLowerCase()
    const found = props.brandFacets.find(b => {
        return String(b.slug || '').toLowerCase() === sVal || String(b.id || '') === sVal || String(b.name || '').toLowerCase() === sVal
    })
    return found ? found.name : val
}

// ══════════════════════════════════════════
// FILTER COMPUTATIONS
// ══════════════════════════════════════════
const filteredProducts = computed(() => {
    let result = [...props.products]

    // 1. Collection filter
    if (activeCollection.value === 'flash-sale') {
        result = result.filter(p => {
            const price = Number(p.price) || 0
            const orig = Number(p.original_price || p.original) || price
            return Boolean(p.badge) || (orig > price)
        })
    } else if (activeCollection.value === 'best-picks') {
        result = result.filter(p => (Number(p.rating) || 5) >= 4)
    }

    // 2. Categories filter
    if (selectedCategories.value.length > 0) {
        result = result.filter(p => {
            const catId = String(p.category_id || '')
            const catSlug = String(p.category_slug || '').toLowerCase()
            const catName = String(p.category || '').toLowerCase()
            const parentCatId = String(p.parent_category_id || '')
            const parentCatSlug = String(p.parent_category_slug || '').toLowerCase()
            return selectedCategories.value.some(sel => {
                const s = String(sel).toLowerCase()
                return s === catId || s === catSlug || s === catName || s === parentCatId || s === parentCatSlug
            })
        })
    }

    // 3. Brands filter
    if (selectedBrands.value.length > 0) {
        result = result.filter(p => {
            const bId = String(p.brand_id || '')
            const bSlug = String(p.brand_slug || '').toLowerCase()
            const bName = String(p.brand || '').toLowerCase()
            return selectedBrands.value.some(sel => {
                const s = String(sel).toLowerCase()
                return s === bId || s === bSlug || s === bName
            })
        })
    }

    // 4. Price range filter
    const effectiveMin = minPriceInput.value !== null && minPriceInput.value !== '' ? Number(minPriceInput.value) : null
    const effectiveMax = maxPriceInput.value !== null && maxPriceInput.value !== '' ? Number(maxPriceInput.value) : null

    if (effectiveMin !== null) {
        result = result.filter(p => Number(p.price) >= effectiveMin)
    }
    if (effectiveMax !== null) {
        result = result.filter(p => Number(p.price) <= effectiveMax)
    }

    // 5. Rating filter
    if (minRating.value !== null) {
        result = result.filter(p => (Number(p.rating) || 5) >= minRating.value)
    }

    // 6. Discount filter
    if (onlyDiscounted.value && activeCollection.value !== 'flash-sale') {
        result = result.filter(p => {
            const price = Number(p.price) || 0
            const orig = Number(p.original_price || p.original) || price
            return Boolean(p.badge) || (orig > price)
        })
    }

    // 7. Prescription / OTC filter
    if (onlyRx.value === 'rx') {
        result = result.filter(p => Boolean(p.requires_prescription))
    } else if (onlyRx.value === 'otc') {
        result = result.filter(p => !p.requires_prescription)
    }

    // 8. Sorting
    result.sort((a, b) => {
        const priceA = Number(a.price) || 0
        const priceB = Number(b.price) || 0
        const origA = Number(a.original_price || a.original) || priceA
        const origB = Number(b.original_price || b.original) || priceB
        const discountA = origA > priceA ? (origA - priceA) / origA : 0
        const discountB = origB > priceB ? (origB - priceB) / origB : 0
        const ratingA = Number(a.rating) || 5
        const ratingB = Number(b.rating) || 5

        switch (sortBy.value) {
            case 'price_low_high':
                return priceA - priceB
            case 'price_high_low':
                return priceB - priceA
            case 'rating':
                return ratingB - ratingA
            case 'discount':
                return discountB - discountA
            case 'newest':
                return (Number(b.id) || 0) - (Number(a.id) || 0)
            default:
                return (Number(a.id) || 0) - (Number(b.id) || 0)
        }
    })

    return result
})

// Pagination
const currentPage = ref(1)
const perPage = 16

const totalPages = computed(() => Math.ceil(filteredProducts.value.length / perPage) || 1)

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * perPage
    return filteredProducts.value.slice(start, start + perPage)
})

watch([activeCollection, selectedCategories, selectedBrands, minPriceInput, maxPriceInput, minRating, onlyDiscounted, onlyRx, sortBy], () => {
    currentPage.value = 1
}, { deep: true })

function setPage(p) {
    if (p >= 1 && p <= totalPages.value) {
        currentPage.value = p
        if (typeof window !== 'undefined') {
            window.scrollTo({ top: 220, behavior: 'smooth' })
        }
    }
}

// Add to cart handler
function handleAddToCart(product) {
    addToCart(product, 1)
    openCartDrawer()
}
</script>

<template>
    <StoreLayout :categories="categories">
        <Head>
            <title>{{ seo?.meta_title || 'All Products — MediMart' }}</title>
            <meta v-if="seo?.meta_description" name="description" :content="seo.meta_description" />
        </Head>

        <!-- Container -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <!-- 1. Breadcrumb navigation -->
            <nav class="flex items-center flex-wrap gap-1.5 text-xs sm:text-sm text-gray-400 mb-4" aria-label="Breadcrumb">
                <Link href="/" class="hover:text-primary transition-colors">Home</Link>
                <span>/</span>
                <span class="text-gray-900 font-semibold">
                    {{ activeCollection === 'flash-sale' ? 'Flash Sale' : (activeCollection === 'best-picks' ? 'Best Picks' : 'All Products') }}
                </span>
            </nav>

            <!-- 2. Hero Banner with Dynamic Gradient -->
            <div
                class="relative rounded-2xl p-5 sm:p-7 text-white shadow-sm overflow-hidden mb-6 bg-gradient-to-r"
                :class="activeCollection === 'flash-sale' ? 'from-amber-600 to-rose-700' : 'from-teal-800 to-teal-950'"
            >
                <!-- Decorative Blur Circle -->
                <div class="absolute -right-12 -bottom-12 w-56 h-56 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                    <div class="space-y-2">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-xs bg-white/15 text-white border border-white/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-300"></span>
                            {{ activeCollection === 'flash-sale' ? '⚡ Limited Time Deals' : (activeCollection === 'best-picks' ? '⭐ Customer Favorites' : '📦 100% Genuine Pharmacy') }}
                        </div>

                        <!-- Title -->
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                            {{ activeCollection === 'flash-sale' ? 'Flash Sale & Special Offers' : (activeCollection === 'best-picks' ? 'Best Picks & Top Rated Products' : 'All Store Products') }}
                        </h1>

                        <!-- Description -->
                        <p class="text-xs sm:text-sm text-white/90 max-w-2xl line-clamp-2">
                            {{ activeCollection === 'flash-sale' ? 'Grab verified medicines, healthcare essentials, and personal care products at exclusive discounted prices.' : (activeCollection === 'best-picks' ? 'Discover our highest-rated customer favorites and doctor-recommended selections.' : 'Browse our comprehensive online catalog with express doorstep delivery and authentic medicine guarantee.') }}
                        </p>
                    </div>

                    <!-- Right count pill & Mobile filter button -->
                    <div class="flex items-center gap-3 shrink-0 self-end md:self-auto">
                        <div class="bg-white/15 backdrop-blur-xs border border-white/20 rounded-xl px-4 py-2.5 text-center">
                            <span class="block text-xl font-bold text-white">{{ filteredProducts.length }}</span>
                            <span class="text-[11px] uppercase tracking-wider text-teal-100">Products</span>
                        </div>

                        <!-- Mobile Filter Button Trigger -->
                        <button
                            type="button"
                            @click="openFilterSheet"
                            class="lg:hidden flex items-center gap-2 bg-white text-gray-900 hover:bg-gray-50 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow transition-colors cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>Filters</span>
                            <span
                                v-if="activeFiltersCount > 0"
                                class="w-5 h-5 rounded-full bg-primary text-white text-[11px] font-bold flex items-center justify-center ml-0.5"
                            >
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Collection Switcher Tabs -->
            <div class="mb-5 flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
                <button
                    type="button"
                    @click="switchCollection('all')"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold shrink-0 transition-all cursor-pointer border"
                    :class="activeCollection === 'all'
                        ? 'bg-primary text-white border-primary shadow-xs'
                        : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                >
                    📦 All Products
                    <span class="ml-1 opacity-80 text-xs font-normal">({{ totalProducts }})</span>
                </button>

                <button
                    type="button"
                    @click="switchCollection('flash-sale')"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold shrink-0 transition-all cursor-pointer border flex items-center gap-1.5"
                    :class="activeCollection === 'flash-sale'
                        ? 'bg-amber-600 text-white border-amber-600 shadow-xs'
                        : 'bg-white text-gray-700 border-gray-200 hover:border-amber-300 hover:bg-amber-50/50'"
                >
                    <span>⚡ Flash Sale / Offers</span>
                </button>

                <button
                    type="button"
                    @click="switchCollection('best-picks')"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold shrink-0 transition-all cursor-pointer border flex items-center gap-1.5"
                    :class="activeCollection === 'best-picks'
                        ? 'bg-primary text-white border-primary shadow-xs'
                        : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                >
                    <span>🏆 Best Picks</span>
                </button>
            </div>

            <!-- 4. Quick Category Filter Pills -->
            <div v-if="categoryFacets && categoryFacets.length > 0" class="mb-6 bg-white border border-gray-100 rounded-xl p-3 sm:p-4 shadow-2xs">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                        Filter by Category
                    </span>
                    <span v-if="selectedCategories.length > 0" class="text-xs text-primary font-medium">
                        {{ selectedCategories.length }} selected
                    </span>
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
                    <button
                        type="button"
                        @click="selectSingleCategoryPill(null)"
                        class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition-all cursor-pointer border"
                        :class="selectedCategories.length === 0
                            ? 'bg-primary text-white border-primary shadow-xs'
                            : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-gray-300 hover:bg-gray-100'"
                    >
                        All Categories
                    </button>

                    <button
                        v-for="cat in categoryFacets"
                        :key="cat.id"
                        type="button"
                        @click="selectSingleCategoryPill(cat)"
                        class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition-all cursor-pointer border flex items-center gap-1.5"
                        :class="isCategorySelected(cat)
                            ? 'bg-primary text-white border-primary shadow-xs'
                            : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-gray-300 hover:bg-gray-100'"
                    >
                        <span>{{ cat.name }}</span>
                        <span v-if="cat.count !== undefined" class="text-[11px] opacity-75">({{ cat.count }})</span>
                    </button>
                </div>
            </div>

            <!-- 5. Active Filters Bar & Sorter Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                <!-- Count and active tags -->
                <div class="flex items-center flex-wrap gap-2 text-xs text-gray-600">
                    <span class="font-medium text-gray-900">
                        Showing <strong class="text-primary">{{ paginatedProducts.length }}</strong> of {{ filteredProducts.length }} products
                    </span>

                    <span v-if="activeFiltersCount > 0" class="text-gray-300">|</span>

                    <!-- Collection tag -->
                    <span v-if="activeCollection !== 'all'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-amber-50 text-amber-800 border border-amber-200 font-semibold">
                        <span>{{ activeCollection === 'flash-sale' ? '⚡ Flash Sale' : '🏆 Best Picks' }}</span>
                        <button type="button" @click="removeFilter('collection')" class="hover:text-red-600 cursor-pointer text-xs ml-0.5">✕</button>
                    </span>

                    <!-- Category tags -->
                    <template v-for="catVal in selectedCategories" :key="'tag-cat-' + catVal">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-teal-50 text-teal-800 border border-teal-200 font-medium">
                            <span>{{ getCategoryLabel(catVal) }}</span>
                            <button type="button" @click="removeFilter('cat', catVal)" class="hover:text-red-600 cursor-pointer text-xs ml-0.5">✕</button>
                        </span>
                    </template>

                    <!-- Brand tags -->
                    <template v-for="bVal in selectedBrands" :key="'tag-b-' + bVal">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-teal-50 text-teal-800 border border-teal-200 font-medium">
                            <span>{{ getBrandLabel(bVal) }}</span>
                            <button type="button" @click="removeFilter('brand', bVal)" class="hover:text-red-600 cursor-pointer text-xs ml-0.5">✕</button>
                        </span>
                    </template>

                    <!-- Price tag -->
                    <span v-if="minPriceInput !== null || (maxPriceInput !== null && maxPriceInput < maxPriceLimit)" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-teal-50 text-teal-800 border border-teal-200 font-medium">
                        <span>
                            {{ currencySymbol }}{{ minPriceInput || 0 }} - {{ currencySymbol }}{{ maxPriceInput || maxPriceLimit }}
                        </span>
                        <button type="button" @click="removeFilter('max_price'); removeFilter('min_price')" class="hover:text-red-600 cursor-pointer text-xs ml-0.5">✕</button>
                    </span>

                    <!-- Rating tag -->
                    <span v-if="minRating !== null && activeCollection !== 'best-picks'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-teal-50 text-teal-800 border border-teal-200 font-medium">
                        <span>{{ minRating }}★ & above</span>
                        <button type="button" @click="removeFilter('rating')" class="hover:text-red-600 cursor-pointer text-xs ml-0.5">✕</button>
                    </span>

                    <!-- Discount tag -->
                    <span v-if="onlyDiscounted && activeCollection !== 'flash-sale'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-teal-50 text-teal-800 border border-teal-200 font-medium">
                        <span>On Sale Only</span>
                        <button type="button" @click="removeFilter('discount')" class="hover:text-red-600 cursor-pointer text-xs ml-0.5">✕</button>
                    </span>

                    <!-- Rx tag -->
                    <span v-if="onlyRx !== 'all'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-teal-50 text-teal-800 border border-teal-200 font-medium">
                        <span>{{ onlyRx === 'rx' ? 'Prescription Only' : 'Over The Counter (OTC)' }}</span>
                        <button type="button" @click="removeFilter('rx')" class="hover:text-red-600 cursor-pointer text-xs ml-0.5">✕</button>
                    </span>

                    <!-- Clear All Button -->
                    <button
                        v-if="activeFiltersCount > 0"
                        type="button"
                        @click="clearAllFilters"
                        class="text-xs font-bold text-red-600 hover:text-red-700 underline cursor-pointer ml-1"
                    >
                        Clear all
                    </button>
                </div>

                <!-- Sort Control -->
                <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                    <label for="sort-select" class="text-xs text-gray-500 font-medium">Sort by:</label>
                    <select
                        id="sort-select"
                        v-model="sortBy"
                        class="text-xs sm:text-sm bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 font-medium text-gray-700 focus:outline-hidden focus:border-primary cursor-pointer shadow-2xs"
                    >
                        <option value="popularity">Popularity</option>
                        <option value="price_low_high">Price: Low to High</option>
                        <option value="price_high_low">Price: High to Low</option>
                        <option value="rating">Customer Rating</option>
                        <option value="discount">Biggest Discount</option>
                        <option value="newest">Newest Additions</option>
                    </select>
                </div>
            </div>

            <!-- 6. Main Grid (Sidebar Filters + Products Grid) -->
            <div class="flex items-start gap-6">
                <!-- Desktop Sidebar Filters -->
                <aside class="hidden lg:block w-64 xl:w-72 shrink-0 sticky top-24 bg-white border border-gray-100 rounded-2xl p-5 shadow-2xs space-y-6">
                    <!-- Filter Top Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <h3 class="font-bold text-gray-900 text-sm">Filters</h3>
                            <span v-if="activeFiltersCount > 0" class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary font-bold">
                                {{ activeFiltersCount }}
                            </span>
                        </div>
                        <button
                            v-if="activeFiltersCount > 0"
                            type="button"
                            @click="clearAllFilters"
                            class="text-xs text-red-600 hover:text-red-700 font-semibold cursor-pointer"
                        >
                            Reset all
                        </button>
                    </div>

                    <!-- Filter 1: Categories -->
                    <div v-if="categoryFacets && categoryFacets.length > 0" class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Categories</h4>
                        <div class="space-y-1.5 max-h-52 overflow-y-auto pr-1 text-sm scrollbar-thin">
                            <label
                                v-for="cat in categoryFacets"
                                :key="'side-cat-' + cat.id"
                                class="flex items-center justify-between p-1.5 rounded-lg hover:bg-gray-50 cursor-pointer group text-xs sm:text-[13px]"
                            >
                                <div class="flex items-center gap-2.5">
                                    <input
                                        type="checkbox"
                                        :checked="isCategorySelected(cat)"
                                        @change="toggleCategory(cat)"
                                        class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer"
                                    />
                                    <span class="text-gray-700 group-hover:text-primary transition-colors font-medium">
                                        {{ cat.name }}
                                    </span>
                                </div>
                                <span v-if="cat.count !== undefined" class="text-[11px] text-gray-400">
                                    {{ cat.count }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Filter 2: Brands -->
                    <div v-if="brandFacets && brandFacets.length > 0" class="space-y-3 pt-3 border-t border-gray-100">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Brands</h4>
                        <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1 text-sm scrollbar-thin">
                            <label
                                v-for="brand in brandFacets"
                                :key="'side-brand-' + brand.id"
                                class="flex items-center justify-between p-1.5 rounded-lg hover:bg-gray-50 cursor-pointer group text-xs sm:text-[13px]"
                            >
                                <div class="flex items-center gap-2.5">
                                    <input
                                        type="checkbox"
                                        :checked="isBrandSelected(brand)"
                                        @change="toggleBrand(brand)"
                                        class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer"
                                    />
                                    <span class="text-gray-700 group-hover:text-primary transition-colors font-medium">
                                        {{ brand.name }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-gray-400">{{ brand.count }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Filter 3: Price Range -->
                    <div class="space-y-3 pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Price Range</h4>
                            <span class="text-[11px] font-semibold text-primary">
                                Up to {{ currencySymbol }}{{ maxPriceInput || maxPriceLimit }}
                            </span>
                        </div>
                        <input
                            type="range"
                            min="0"
                            :max="maxPriceLimit"
                            step="50"
                            v-model.number="priceSlider"
                            @input="onSliderChange"
                            class="w-full accent-primary cursor-pointer"
                        />
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <label class="text-[10px] text-gray-400 block mb-0.5">Min ({{ currencySymbol }})</label>
                                <input
                                    type="number"
                                    v-model.number="minPriceInput"
                                    placeholder="0"
                                    min="0"
                                    class="w-full text-xs px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-hidden focus:border-primary"
                                />
                            </div>
                            <span class="text-gray-400 text-xs mt-3">-</span>
                            <div class="flex-1">
                                <label class="text-[10px] text-gray-400 block mb-0.5">Max ({{ currencySymbol }})</label>
                                <input
                                    type="number"
                                    v-model.number="maxPriceInput"
                                    :placeholder="String(maxPriceLimit)"
                                    min="0"
                                    class="w-full text-xs px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-hidden focus:border-primary"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Filter 4: Minimum Customer Rating -->
                    <div class="space-y-2.5 pt-3 border-t border-gray-100">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Rating</h4>
                        <div class="space-y-1">
                            <button
                                v-for="stars in [4, 3, 2]"
                                :key="'star-' + stars"
                                type="button"
                                @click="toggleRating(stars)"
                                class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors cursor-pointer"
                                :class="minRating === stars ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-gray-50 text-gray-600'"
                            >
                                <span class="flex items-center gap-1 text-amber-400">
                                    <span v-for="s in stars" :key="'s' + s">★</span>
                                    <span class="text-gray-700 font-medium ml-1">& up</span>
                                </span>
                                <span v-if="minRating === stars" class="text-primary text-xs">✓</span>
                            </button>
                        </div>
                    </div>

                    <!-- Filter 5: Prescription / OTC Filter -->
                    <div class="space-y-2.5 pt-3 border-t border-gray-100">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Prescription Status</h4>
                        <div class="grid grid-cols-3 gap-1 bg-gray-100 p-1 rounded-lg text-[11px] font-semibold text-center">
                            <button
                                type="button"
                                @click="onlyRx = 'all'"
                                class="py-1 rounded-md transition-all cursor-pointer"
                                :class="onlyRx === 'all' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                            >
                                All
                            </button>
                            <button
                                type="button"
                                @click="onlyRx = 'rx'"
                                class="py-1 rounded-md transition-all cursor-pointer"
                                :class="onlyRx === 'rx' ? 'bg-primary text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                            >
                                Rx Only
                            </button>
                            <button
                                type="button"
                                @click="onlyRx = 'otc'"
                                class="py-1 rounded-md transition-all cursor-pointer"
                                :class="onlyRx === 'otc' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                            >
                                OTC
                            </button>
                        </div>
                    </div>

                    <!-- Filter 6: Offers / Discounts -->
                    <div class="pt-3 border-t border-gray-100">
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input
                                type="checkbox"
                                v-model="onlyDiscounted"
                                class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer"
                            />
                            <span class="text-xs font-semibold text-gray-700 group-hover:text-primary transition-colors">
                                On Sale / Discounted only
                            </span>
                        </label>
                    </div>
                </aside>

                <!-- Products Grid Column -->
                <div class="flex-1 min-w-0">
                    <!-- Products Found -->
                    <div v-if="filteredProducts.length > 0">
                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                            <ProductCard
                                v-for="product in paginatedProducts"
                                :key="product.id"
                                :product="product"
                                @add="handleAddToCart(product)"
                            />
                        </div>

                        <!-- Pagination -->
                        <div v-if="totalPages > 1" class="mt-8 flex items-center justify-center gap-1.5 sm:gap-2">
                            <button
                                type="button"
                                @click="setPage(currentPage - 1)"
                                :disabled="currentPage === 1"
                                class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                :class="currentPage === 1 ? 'border-gray-200 text-gray-400' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                            >
                                ← Prev
                            </button>

                            <template v-for="p in totalPages" :key="'p-' + p">
                                <button
                                    v-if="p === 1 || p === totalPages || (p >= currentPage - 1 && p <= currentPage + 1)"
                                    type="button"
                                    @click="setPage(p)"
                                    class="w-8 h-8 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                    :class="currentPage === p ? 'bg-primary text-white shadow-xs' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'"
                                >
                                    {{ p }}
                                </button>
                                <span
                                    v-else-if="p === currentPage - 2 || p === currentPage + 2"
                                    class="px-1 text-gray-400 text-xs"
                                >...</span>
                            </template>

                            <button
                                type="button"
                                @click="setPage(currentPage + 1)"
                                :disabled="currentPage === totalPages"
                                class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                :class="currentPage === totalPages ? 'border-gray-200 text-gray-400' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                            >
                                Next →
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="bg-white border border-gray-100 rounded-2xl p-8 sm:p-12 text-center shadow-2xs">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-teal-50 flex items-center justify-center text-2xl text-teal-600">
                            🔍
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-1">
                            No products match your selected filters
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto mb-5">
                            We couldn't find any products matching your specific combination of filters. Try clearing or relaxing some criteria.
                        </p>
                        <button
                            type="button"
                            @click="clearAllFilters"
                            class="px-5 py-2.5 rounded-xl bg-primary hover:bg-teal-800 text-white font-semibold text-xs sm:text-sm shadow transition-colors cursor-pointer inline-flex items-center gap-2"
                        >
                            <span>Clear All Filters</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Mobile Bottom Sheet Filter Modal (Teleport) -->
        <Teleport to="body">
            <div
                v-if="mobileFilterOpen"
                class="fixed inset-0 z-50 flex flex-col justify-end lg:hidden"
                aria-modal="true"
                role="dialog"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity"
                    @click="closeFilterSheet"
                ></div>

                <!-- Drawer Content -->
                <div class="relative bg-white rounded-t-3xl max-h-[85vh] flex flex-col shadow-2xl z-10 animate-in slide-in-from-bottom duration-200">
                    <!-- Drawer Handle & Header -->
                    <div class="px-5 pt-3 pb-3 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-primary"></div>
                            <h3 class="font-bold text-gray-900 text-base">Filter & Refine</h3>
                            <span v-if="activeFiltersCount > 0" class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary font-bold">
                                {{ activeFiltersCount }}
                            </span>
                        </div>
                        <button
                            type="button"
                            @click="closeFilterSheet"
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center font-bold text-sm cursor-pointer"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Scrollable Filter Body -->
                    <div class="p-5 overflow-y-auto space-y-6 flex-1">
                        <!-- Categories -->
                        <div v-if="categoryFacets && categoryFacets.length > 0" class="space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Categories</h4>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <label
                                    v-for="cat in categoryFacets"
                                    :key="'mob-cat-' + cat.id"
                                    class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 hover:border-primary cursor-pointer"
                                    :class="isCategorySelected(cat) ? 'bg-teal-50 border-primary text-primary font-semibold' : 'bg-gray-50 text-gray-700'"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="isCategorySelected(cat)"
                                        @change="toggleCategory(cat)"
                                        class="w-3.5 h-3.5 text-primary rounded border-gray-300 focus:ring-primary"
                                    />
                                    <span class="truncate">{{ cat.name }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Brands -->
                        <div v-if="brandFacets && brandFacets.length > 0" class="space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Brands</h4>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <label
                                    v-for="brand in brandFacets"
                                    :key="'mob-brand-' + brand.id"
                                    class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 hover:border-primary cursor-pointer"
                                    :class="isBrandSelected(brand) ? 'bg-teal-50 border-primary text-primary font-semibold' : 'bg-gray-50 text-gray-700'"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="isBrandSelected(brand)"
                                        @change="toggleBrand(brand)"
                                        class="w-3.5 h-3.5 text-primary rounded border-gray-300 focus:ring-primary"
                                    />
                                    <span class="truncate">{{ brand.name }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Max Price</h4>
                                <span class="text-xs font-bold text-primary">{{ currencySymbol }}{{ maxPriceInput || maxPriceLimit }}</span>
                            </div>
                            <input
                                type="range"
                                min="0"
                                :max="maxPriceLimit"
                                step="50"
                                v-model.number="priceSlider"
                                @input="onSliderChange"
                                class="w-full accent-primary"
                            />
                        </div>

                        <!-- Prescription Status -->
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Type</h4>
                            <div class="grid grid-cols-3 gap-2 text-xs font-semibold text-center">
                                <button
                                    type="button"
                                    @click="onlyRx = 'all'"
                                    class="py-2 rounded-lg border cursor-pointer"
                                    :class="onlyRx === 'all' ? 'bg-primary text-white border-primary' : 'bg-gray-50 border-gray-200 text-gray-700'"
                                >
                                    All
                                </button>
                                <button
                                    type="button"
                                    @click="onlyRx = 'rx'"
                                    class="py-2 rounded-lg border cursor-pointer"
                                    :class="onlyRx === 'rx' ? 'bg-primary text-white border-primary' : 'bg-gray-50 border-gray-200 text-gray-700'"
                                >
                                    Rx Only
                                </button>
                                <button
                                    type="button"
                                    @click="onlyRx = 'otc'"
                                    class="py-2 rounded-lg border cursor-pointer"
                                    :class="onlyRx === 'otc' ? 'bg-primary text-white border-primary' : 'bg-gray-50 border-gray-200 text-gray-700'"
                                >
                                    OTC
                                </button>
                            </div>
                        </div>

                        <!-- Special Offers -->
                        <div>
                            <label class="flex items-center gap-2 text-xs font-semibold text-gray-800 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="onlyDiscounted"
                                    class="w-4 h-4 text-primary rounded border-gray-300"
                                />
                                <span>Show On Sale / Discounted Products Only</span>
                            </label>
                        </div>
                    </div>

                    <!-- Sticky Drawer Footer -->
                    <div class="p-4 border-t border-gray-100 flex items-center gap-3 bg-gray-50 rounded-b-3xl shrink-0">
                        <button
                            type="button"
                            @click="clearAllFilters"
                            class="flex-1 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold text-xs hover:bg-gray-100 transition-colors cursor-pointer"
                        >
                            Reset All
                        </button>
                        <button
                            type="button"
                            @click="closeFilterSheet"
                            class="flex-1 py-2.5 rounded-xl bg-primary text-white font-bold text-xs hover:bg-teal-800 shadow transition-colors cursor-pointer"
                        >
                            Show {{ filteredProducts.length }} Products
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </StoreLayout>
</template>
