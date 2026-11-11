<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import ProductCard from '@/Components/store/ProductCard.vue'
import { addToCart } from '@/composables/store/useCart'
import { openCartDrawer } from '@/composables/store/useStoreUi'

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
    parentCategory: {
        type: Object,
        default: null,
    },
    currentSubcategory: {
        type: Object,
        default: null,
    },
    subcategories: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
    totalProducts: {
        type: Number,
        default: 0,
    },
    allCategoryProductsCount: {
        type: Number,
        default: 0,
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

function formatPrice(val) {
    if (!val && val !== 0) return ''
    return currencySymbol + Math.round(Number(val)).toLocaleString()
}

// ══════════════════════════════════════════
// FILTER STATES
// ══════════════════════════════════════════
const selectedSubcategories = ref(
    Array.isArray(props.filters?.subcategories)
        ? [...props.filters.subcategories]
        : props.currentSubcategory
            ? [props.currentSubcategory.slug || String(props.currentSubcategory.id)]
            : []
)
const selectedBrands = ref(
    Array.isArray(props.filters?.brands) ? [...props.filters.brands] : []
)
const minPriceInput = ref(props.filters?.min_price ?? null)
const maxPriceInput = ref(props.filters?.max_price ?? null)
const priceSlider = ref(props.filters?.max_price ?? 5000)
const minRating = ref(props.filters?.rating ?? null)
const onlyDiscounted = ref(props.filters?.discount ?? false)
const onlyRx = ref(props.filters?.rx || 'all') // 'all' | 'rx' | 'otc'
const sortBy = ref(props.filters?.sort || 'popularity')

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
// FILTER COMPUTATIONS
// ══════════════════════════════════════════
const filteredProducts = computed(() => {
    let result = [...props.products]

    // Subcategory filter
    if (selectedSubcategories.value.length > 0) {
        result = result.filter(p => {
            const catId = String(p.category_id || '')
            const catSlug = p.category?.slug || ''
            return (
                selectedSubcategories.value.includes(catId) ||
                selectedSubcategories.value.includes(catSlug)
            )
        })
    }

    // Brand filter
    if (selectedBrands.value.length > 0) {
        result = result.filter(p => {
            const bId = String(p.brand_id || '')
            const bSlug = p.brand?.slug || ''
            const bName = p.brand?.name || p.brand || ''
            return (
                selectedBrands.value.includes(bId) ||
                selectedBrands.value.includes(bSlug) ||
                selectedBrands.value.includes(bName)
            )
        })
    }

    // Price range
    const effectiveMin = minPriceInput.value !== null && minPriceInput.value !== '' ? Number(minPriceInput.value) : null
    const effectiveMax = maxPriceInput.value !== null && maxPriceInput.value !== '' ? Number(maxPriceInput.value) : null

    if (effectiveMin !== null) {
        result = result.filter(p => Number(p.price) >= effectiveMin)
    }
    if (effectiveMax !== null) {
        result = result.filter(p => Number(p.price) <= effectiveMax)
    }

    // Rating
    if (minRating.value !== null) {
        result = result.filter(p => (Number(p.rating) || 5) >= minRating.value)
    }

    // Discount
    if (onlyDiscounted.value) {
        result = result.filter(p => {
            const price = Number(p.price) || 0
            const orig = Number(p.original_price) || price
            return p.badge || (orig > price && orig > 0)
        })
    }

    // Prescription / OTC
    if (onlyRx.value === 'rx') {
        result = result.filter(p => p.requires_prescription)
    } else if (onlyRx.value === 'otc') {
        result = result.filter(p => !p.requires_prescription)
    }

    // Sorting
    result.sort((a, b) => {
        const priceA = Number(a.price) || 0
        const priceB = Number(b.price) || 0
        const origA = Number(a.original_price) || priceA
        const origB = Number(b.original_price) || priceB
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

watch([selectedSubcategories, selectedBrands, minPriceInput, maxPriceInput, minRating, onlyDiscounted, onlyRx, sortBy], () => {
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

// ══════════════════════════════════════════
// FILTER TOGGLES & HELPERS
// ══════════════════════════════════════════
function toggleSubcategory(subSlugOrId) {
    const idx = selectedSubcategories.value.indexOf(subSlugOrId)
    if (idx > -1) {
        selectedSubcategories.value.splice(idx, 1)
    } else {
        selectedSubcategories.value.push(subSlugOrId)
    }
}

function isSubcategorySelected(subSlugOrId) {
    return selectedSubcategories.value.includes(subSlugOrId)
}

function selectSingleSubcategoryPill(subSlug) {
    if (!subSlug) {
        selectedSubcategories.value = []
    } else {
        if (selectedSubcategories.value.length === 1 && selectedSubcategories.value[0] === subSlug) {
            selectedSubcategories.value = []
        } else {
            selectedSubcategories.value = [subSlug]
        }
    }
}

function toggleBrand(brandSlugOrName) {
    const idx = selectedBrands.value.indexOf(brandSlugOrName)
    if (idx > -1) {
        selectedBrands.value.splice(idx, 1)
    } else {
        selectedBrands.value.push(brandSlugOrName)
    }
}

function isBrandSelected(brandSlugOrName) {
    return selectedBrands.value.includes(brandSlugOrName)
}

function toggleRating(stars) {
    if (minRating.value === stars) {
        minRating.value = null
    } else {
        minRating.value = stars
    }
}

function onSliderChange() {
    maxPriceInput.value = priceSlider.value
}

function clearAllFilters() {
    selectedSubcategories.value = []
    selectedBrands.value = []
    minPriceInput.value = null
    maxPriceInput.value = null
    priceSlider.value = 5000
    minRating.value = null
    onlyDiscounted.value = false
    onlyRx.value = 'all'
    sortBy.value = 'popularity'
}

const activeFiltersCount = computed(() => {
    let count = 0
    if (selectedSubcategories.value.length > 0) count += selectedSubcategories.value.length
    if (selectedBrands.value.length > 0) count += selectedBrands.value.length
    if (minPriceInput.value !== null && minPriceInput.value !== '') count++
    if (maxPriceInput.value !== null && maxPriceInput.value !== '') count++
    if (minRating.value !== null) count++
    if (onlyDiscounted.value) count++
    if (onlyRx.value !== 'all') count++
    return count
})

function removeFilter(type, value) {
    if (type === 'sub') {
        const i = selectedSubcategories.value.indexOf(value)
        if (i > -1) selectedSubcategories.value.splice(i, 1)
    } else if (type === 'brand') {
        const i = selectedBrands.value.indexOf(value)
        if (i > -1) selectedBrands.value.splice(i, 1)
    } else if (type === 'min_price') {
        minPriceInput.value = null
    } else if (type === 'max_price') {
        maxPriceInput.value = null
        priceSlider.value = 5000
    } else if (type === 'rating') {
        minRating.value = null
    } else if (type === 'discount') {
        onlyDiscounted.value = false
    } else if (type === 'rx') {
        onlyRx.value = 'all'
    }
}

function getSubcategoryLabel(val) {
    const found = props.subcategories.find(s => s.slug === val || s.id === val || s.name === val)
    return found ? found.name : val
}

function getBrandLabel(val) {
    const found = props.brandFacets.find(b => b.slug === val || b.id === val || b.name === val)
    return found ? found.name : val
}

// Add to cart handler
function handleAddToCart(product) {
    addToCart(product, 1)
    openCartDrawer()
}
</script>

<template>
    <StoreLayout>
        <Head>
            <title>{{ seo.meta_title || `${category.name} - Online Pharmacy` }}</title>
            <meta name="description" :content="seo.meta_description || `Shop ${category.name} products at best prices.`" />
            <meta property="og:title" :content="seo.meta_title || category.name" />
            <meta property="og:description" :content="seo.meta_description || ''" />
            <meta v-if="seo.image" property="og:image" :content="seo.image" />
        </Head>

        <!-- BREADCRUMB -->
        <div class="max-w-7xl mx-auto px-4 pt-4 sm:pt-5">
            <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-xs sm:text-[13px] text-gray-500 flex-wrap">
                <Link href="/" class="hover:text-primary transition-colors flex items-center gap-1">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Home
                </Link>
                <span class="text-gray-300">/</span>

                <template v-if="parentCategory">
                    <Link :href="`/category/${parentCategory.slug}`" class="hover:text-primary transition-colors">
                        {{ parentCategory.name }}
                    </Link>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 font-semibold truncate">{{ category.name }}</span>
                </template>
                <template v-else>
                    <span class="text-gray-800 font-semibold truncate">{{ category.name }}</span>
                </template>
            </nav>
        </div>

        <!-- CATEGORY HEADER CARD -->
        <div class="max-w-7xl mx-auto px-4 pt-3 sm:pt-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-4">
                    <div
                        v-if="category.image"
                        class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden shrink-0 bg-primarylt/30 border border-gray-100 p-1 flex items-center justify-center"
                    >
                        <img :src="category.image" :alt="category.name" class="w-full h-full object-contain" />
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-[28px] font-extrabold text-gray-900 tracking-tight leading-tight">
                            {{ category.h1_heading || category.name }}
                        </h1>
                        <p class="text-[13.5px] sm:text-[14px] text-gray-500 mt-1 line-clamp-2">
                            <span class="font-semibold text-gray-700">{{ filteredProducts.length }} products</span>
                            <span v-if="category.meta_description">&nbsp;·&nbsp;{{ category.meta_description }}</span>
                            <span v-else>&nbsp;·&nbsp;Genuine products with express doorstep delivery</span>
                        </p>
                    </div>
                </div>

                <!-- Mobile Filter Trigger -->
                <button
                    type="button"
                    class="lg:hidden flex items-center justify-center gap-2 border border-gray-200 text-gray-700 font-semibold text-[14px] px-4 py-2.5 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shrink-0 cursor-pointer"
                    @click="openFilterSheet"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M7 12h10M10 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>Filters</span>
                    <span
                        v-if="activeFiltersCount > 0"
                        class="bg-primary text-white text-[11px] font-bold w-5 h-5 rounded-full flex items-center justify-center -mr-1"
                    >{{ activeFiltersCount }}</span>
                </button>
            </div>
        </div>

        <!-- SUBCATEGORY PILL TABS -->
        <div v-if="subcategories.length > 0" class="max-w-7xl mx-auto px-4 pt-3">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                <!-- All Pill -->
                <button
                    type="button"
                    class="px-4 py-2 rounded-xl text-[13.5px] font-semibold whitespace-nowrap transition-all flex items-center gap-2 cursor-pointer border"
                    :class="selectedSubcategories.length === 0
                        ? 'bg-primary text-white border-primary shadow-xs'
                        : 'bg-white text-gray-700 border-gray-200 hover:border-primary/40 hover:text-primary'"
                    @click="selectSingleSubcategoryPill(null)"
                >
                    <span>All {{ parentCategory ? parentCategory.name : category.name }}</span>
                    <span
                        class="text-[11px] px-1.5 py-0.5 rounded-full"
                        :class="selectedSubcategories.length === 0 ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'"
                    >{{ allCategoryProductsCount }}</span>
                </button>

                <!-- Subcategory Pills -->
                <button
                    v-for="sub in subcategories"
                    :key="sub.id"
                    type="button"
                    class="px-4 py-2 rounded-xl text-[13.5px] font-semibold whitespace-nowrap transition-all flex items-center gap-2 cursor-pointer border"
                    :class="isSubcategorySelected(sub.slug) || isSubcategorySelected(String(sub.id))
                        ? 'bg-primary text-white border-primary shadow-xs'
                        : 'bg-white text-gray-700 border-gray-200 hover:border-primary/40 hover:text-primary'"
                    @click="selectSingleSubcategoryPill(sub.slug)"
                >
                    <img v-if="sub.image" :src="sub.image" :alt="sub.name" class="w-4 h-4 object-contain rounded" />
                    <span>{{ sub.name }}</span>
                    <span
                        class="text-[11px] px-1.5 py-0.5 rounded-full"
                        :class="isSubcategorySelected(sub.slug) || isSubcategorySelected(String(sub.id))
                            ? 'bg-white/20 text-white'
                            : 'bg-gray-100 text-gray-500'"
                    >{{ sub.count }}</span>
                </button>
            </div>
        </div>

        <!-- MAIN: SIDEBAR + PRODUCT GRID -->
        <section class="max-w-7xl mx-auto px-4 py-5">
            <div class="flex gap-5 items-start">

                <!-- DESKTOP FILTER SIDEBAR -->
                <aside class="hidden lg:block w-64 shrink-0">
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 sticky top-24 max-h-[calc(100vh-7rem)] overflow-y-auto no-scrollbar shadow-xs">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                            <h3 class="text-[16px] font-bold text-gray-900 flex items-center gap-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M7 12h10M10 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                Filters
                            </h3>
                            <button
                                v-if="activeFiltersCount > 0"
                                type="button"
                                class="text-[13px] font-semibold text-primary hover:underline cursor-pointer"
                                @click="clearAllFilters"
                            >
                                Clear All ({{ activeFiltersCount }})
                            </button>
                        </div>

                        <!-- Subcategories Facet -->
                        <div v-if="subcategories.length > 0" class="border-b border-gray-100 pb-4 mb-4">
                            <p class="text-[14px] font-bold text-gray-900 mb-3 flex items-center justify-between">
                                <span>Subcategory</span>
                                <span class="text-[12px] text-gray-400 font-normal">{{ subcategories.length }}</span>
                            </p>
                            <div class="space-y-2 max-h-48 overflow-y-auto no-scrollbar pr-1">
                                <label
                                    v-for="sub in subcategories"
                                    :key="sub.id"
                                    class="flex items-center justify-between gap-2 cursor-pointer text-[13.5px] text-gray-600 hover:text-gray-900 py-0.5"
                                >
                                    <span class="flex items-center gap-2.5 truncate">
                                        <input
                                            type="checkbox"
                                            :checked="isSubcategorySelected(sub.slug) || isSubcategorySelected(String(sub.id))"
                                            class="w-4 h-4 rounded accent-primary text-primary focus:ring-primary/20 border-gray-300"
                                            @change="toggleSubcategory(sub.slug)"
                                        />
                                        <span class="truncate">{{ sub.name }}</span>
                                    </span>
                                    <span class="text-[12px] text-gray-400 font-medium shrink-0">({{ sub.count }})</span>
                                </label>
                            </div>
                        </div>

                        <!-- Price Range Facet -->
                        <div class="border-b border-gray-100 pb-4 mb-4">
                            <p class="text-[14px] font-bold text-gray-900 mb-3">Price Range</p>
                            <div class="flex items-center gap-2 mb-3">
                                <div class="relative flex-1">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">৳</span>
                                    <input
                                        v-model.number="minPriceInput"
                                        type="number"
                                        placeholder="Min"
                                        min="0"
                                        class="w-full pl-6 pr-2 py-1.5 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:border-primary transition-colors"
                                    />
                                </div>
                                <span class="text-gray-300">–</span>
                                <div class="relative flex-1">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">৳</span>
                                    <input
                                        v-model.number="maxPriceInput"
                                        type="number"
                                        placeholder="Max"
                                        min="0"
                                        class="w-full pl-6 pr-2 py-1.5 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:border-primary transition-colors"
                                    />
                                </div>
                            </div>
                            <input
                                v-model.number="priceSlider"
                                type="range"
                                min="0"
                                max="5000"
                                step="50"
                                class="w-full accent-primary h-1.5 bg-gray-200 rounded-lg cursor-pointer"
                                @input="onSliderChange"
                            />
                            <div class="flex justify-between text-[11.5px] text-gray-400 mt-1.5">
                                <span>৳0</span>
                                <span>৳{{ Number(priceSlider).toLocaleString() }}+</span>
                            </div>
                        </div>

                        <!-- Brand Facet -->
                        <div v-if="brandFacets.length > 0" class="border-b border-gray-100 pb-4 mb-4">
                            <p class="text-[14px] font-bold text-gray-900 mb-3 flex items-center justify-between">
                                <span>Brand</span>
                                <span class="text-[12px] text-gray-400 font-normal">{{ brandFacets.length }}</span>
                            </p>
                            <div class="space-y-2 max-h-44 overflow-y-auto no-scrollbar pr-1">
                                <label
                                    v-for="brand in brandFacets"
                                    :key="brand.id || brand.name"
                                    class="flex items-center justify-between gap-2 cursor-pointer text-[13.5px] text-gray-600 hover:text-gray-900 py-0.5"
                                >
                                    <span class="flex items-center gap-2.5 truncate">
                                        <input
                                            type="checkbox"
                                            :checked="isBrandSelected(brand.slug) || isBrandSelected(brand.name) || isBrandSelected(String(brand.id))"
                                            class="w-4 h-4 rounded accent-primary text-primary focus:ring-primary/20 border-gray-300"
                                            @change="toggleBrand(brand.slug || brand.name)"
                                        />
                                        <span class="truncate">{{ brand.name }}</span>
                                    </span>
                                    <span class="text-[12px] text-gray-400 font-medium shrink-0">({{ brand.count }})</span>
                                </label>
                            </div>
                        </div>

                        <!-- Customer Rating -->
                        <div class="border-b border-gray-100 pb-4 mb-4">
                            <p class="text-[14px] font-bold text-gray-900 mb-3">Customer Rating</p>
                            <div class="flex flex-col gap-2">
                                <button
                                    type="button"
                                    class="flex items-center justify-between border rounded-lg px-3 py-1.5 text-left transition-all cursor-pointer"
                                    :class="minRating === 4 ? 'border-primary bg-primarylt/30 text-primary font-semibold' : 'border-gray-200 text-gray-700 hover:border-gray-300'"
                                    @click="toggleRating(4)"
                                >
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-amber-400 text-[13px]">★★★★</span><span class="text-gray-300 text-[13px]">★</span>
                                        <span class="text-[12.5px] text-gray-600">& up</span>
                                    </div>
                                    <span v-if="minRating === 4" class="text-xs text-primary font-bold">✓</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex items-center justify-between border rounded-lg px-3 py-1.5 text-left transition-all cursor-pointer"
                                    :class="minRating === 3 ? 'border-primary bg-primarylt/30 text-primary font-semibold' : 'border-gray-200 text-gray-700 hover:border-gray-300'"
                                    @click="toggleRating(3)"
                                >
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-amber-400 text-[13px]">★★★</span><span class="text-gray-300 text-[13px]">★★</span>
                                        <span class="text-[12.5px] text-gray-600">& up</span>
                                    </div>
                                    <span v-if="minRating === 3" class="text-xs text-primary font-bold">✓</span>
                                </button>
                            </div>
                        </div>

                        <!-- Discount -->
                        <div class="border-b border-gray-100 pb-4 mb-4">
                            <p class="text-[14px] font-bold text-gray-900 mb-3">Special Offers</p>
                            <label class="flex items-center gap-2.5 cursor-pointer text-[13.5px] text-gray-600 hover:text-gray-900">
                                <input
                                    v-model="onlyDiscounted"
                                    type="checkbox"
                                    class="w-4 h-4 rounded accent-primary text-primary focus:ring-primary/20 border-gray-300"
                                />
                                <span>Discounted Items Only</span>
                            </label>
                        </div>

                        <!-- Prescription (Rx) Filter -->
                        <div>
                            <p class="text-[14px] font-bold text-gray-900 mb-3">Medicine Type</p>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2.5 cursor-pointer text-[13px] text-gray-600">
                                    <input
                                        v-model="onlyRx"
                                        type="radio"
                                        value="all"
                                        name="rx_desktop"
                                        class="accent-primary"
                                    />
                                    <span>All Products</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer text-[13px] text-gray-600">
                                    <input
                                        v-model="onlyRx"
                                        type="radio"
                                        value="otc"
                                        name="rx_desktop"
                                        class="accent-primary"
                                    />
                                    <span>OTC (Over The Counter)</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer text-[13px] text-gray-600">
                                    <input
                                        v-model="onlyRx"
                                        type="radio"
                                        value="rx"
                                        name="rx_desktop"
                                        class="accent-primary"
                                    />
                                    <span>Prescription Required (Rx)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- RIGHT: PRODUCT LISTING AREA -->
                <div class="flex-1 min-w-0">

                    <!-- Active filter chips + sort bar -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-xs">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[13.5px] text-gray-500">
                                Showing <span class="font-semibold text-gray-800">{{ filteredProducts.length === 0 ? 0 : (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filteredProducts.length) }}</span>
                                of <span class="font-semibold text-gray-800">{{ filteredProducts.length }}</span> results
                            </span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                            <label class="text-[13px] text-gray-400 hidden sm:block">Sort by</label>
                            <select
                                v-model="sortBy"
                                class="border border-gray-200 rounded-lg pl-3 pr-8 py-2 text-[13.5px] text-gray-700 bg-white focus:outline-none focus:border-primary transition-colors cursor-pointer"
                            >
                                <option value="popularity">Popularity</option>
                                <option value="price_low_high">Price: Low to High</option>
                                <option value="price_high_low">Price: High to Low</option>
                                <option value="rating">Customer Rating</option>
                                <option value="discount">Biggest Discount</option>
                                <option value="newest">Newest First</option>
                            </select>
                        </div>
                    </div>

                    <!-- Active Filter Chips -->
                    <div v-if="activeFiltersCount > 0" class="flex flex-wrap items-center gap-2 mb-4">
                        <!-- Subcategory chips -->
                        <span
                            v-for="subVal in selectedSubcategories"
                            :key="subVal"
                            class="inline-flex items-center gap-1.5 bg-primarylt text-primary text-[12.5px] font-semibold pl-3 pr-2 py-1 rounded-full border border-primary/20"
                        >
                            {{ getSubcategoryLabel(subVal) }}
                            <button
                                type="button"
                                class="hover:bg-primary/20 rounded-full p-0.5 cursor-pointer"
                                @click="removeFilter('sub', subVal)"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            </button>
                        </span>

                        <!-- Brand chips -->
                        <span
                            v-for="bVal in selectedBrands"
                            :key="bVal"
                            class="inline-flex items-center gap-1.5 bg-primarylt text-primary text-[12.5px] font-semibold pl-3 pr-2 py-1 rounded-full border border-primary/20"
                        >
                            Brand: {{ getBrandLabel(bVal) }}
                            <button
                                type="button"
                                class="hover:bg-primary/20 rounded-full p-0.5 cursor-pointer"
                                @click="removeFilter('brand', bVal)"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            </button>
                        </span>

                        <!-- Price chips -->
                        <span
                            v-if="minPriceInput !== null && minPriceInput !== ''"
                            class="inline-flex items-center gap-1.5 bg-primarylt text-primary text-[12.5px] font-semibold pl-3 pr-2 py-1 rounded-full border border-primary/20"
                        >
                            Min: ৳{{ minPriceInput }}
                            <button
                                type="button"
                                class="hover:bg-primary/20 rounded-full p-0.5 cursor-pointer"
                                @click="removeFilter('min_price')"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            </button>
                        </span>
                        <span
                            v-if="maxPriceInput !== null && maxPriceInput !== ''"
                            class="inline-flex items-center gap-1.5 bg-primarylt text-primary text-[12.5px] font-semibold pl-3 pr-2 py-1 rounded-full border border-primary/20"
                        >
                            Max: ৳{{ maxPriceInput }}
                            <button
                                type="button"
                                class="hover:bg-primary/20 rounded-full p-0.5 cursor-pointer"
                                @click="removeFilter('max_price')"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            </button>
                        </span>

                        <!-- Rating chip -->
                        <span
                            v-if="minRating !== null"
                            class="inline-flex items-center gap-1.5 bg-primarylt text-primary text-[12.5px] font-semibold pl-3 pr-2 py-1 rounded-full border border-primary/20"
                        >
                            {{ minRating }}★ & up
                            <button
                                type="button"
                                class="hover:bg-primary/20 rounded-full p-0.5 cursor-pointer"
                                @click="removeFilter('rating')"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            </button>
                        </span>

                        <!-- Discount chip -->
                        <span
                            v-if="onlyDiscounted"
                            class="inline-flex items-center gap-1.5 bg-primarylt text-primary text-[12.5px] font-semibold pl-3 pr-2 py-1 rounded-full border border-primary/20"
                        >
                            On Sale
                            <button
                                type="button"
                                class="hover:bg-primary/20 rounded-full p-0.5 cursor-pointer"
                                @click="removeFilter('discount')"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            </button>
                        </span>

                        <!-- Rx chip -->
                        <span
                            v-if="onlyRx !== 'all'"
                            class="inline-flex items-center gap-1.5 bg-primarylt text-primary text-[12.5px] font-semibold pl-3 pr-2 py-1 rounded-full border border-primary/20"
                        >
                            {{ onlyRx === 'rx' ? 'Rx Prescription' : 'OTC Only' }}
                            <button
                                type="button"
                                class="hover:bg-primary/20 rounded-full p-0.5 cursor-pointer"
                                @click="removeFilter('rx')"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            </button>
                        </span>

                        <button
                            type="button"
                            class="text-[12.5px] font-semibold text-gray-400 hover:text-red-500 transition-colors ml-1 cursor-pointer"
                            @click="clearAllFilters"
                        >
                            Clear all
                        </button>
                    </div>

                    <!-- Products Grid -->
                    <div
                        v-if="paginatedProducts.length > 0"
                        class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4"
                    >
                        <ProductCard
                            v-for="product in paginatedProducts"
                            :key="product.id"
                            :product="product"
                            @add="handleAddToCart"
                        />
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="bg-white rounded-2xl border border-gray-100 p-12 text-center shadow-xs my-4"
                    >
                        <div class="w-16 h-16 rounded-full bg-primarylt text-primary mx-auto flex items-center justify-center text-2xl mb-4">
                            🔍
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">No products match your filters</h3>
                        <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">
                            Try adjusting your price range, clearing brand or subcategory filters to see available products.
                        </p>
                        <button
                            type="button"
                            class="bg-primary hover:bg-primary2 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors cursor-pointer inline-flex items-center gap-2"
                            @click="clearAllFilters"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M1 4v6h6M23 20v-6h-6M20.49 9A9 9 0 005.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 013.51 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Reset all filters
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="totalPages > 1"
                        class="flex items-center justify-center gap-1.5 mt-8 pb-4"
                    >
                        <button
                            type="button"
                            :disabled="currentPage === 1"
                            class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 disabled:opacity-40 disabled:cursor-not-allowed hover:border-primary hover:text-primary transition-colors cursor-pointer"
                            @click="setPage(currentPage - 1)"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>

                        <button
                            v-for="p in totalPages"
                            :key="p"
                            type="button"
                            class="w-9 h-9 flex items-center justify-center rounded-lg border text-[14px] font-semibold transition-colors cursor-pointer"
                            :class="currentPage === p
                                ? 'bg-primary text-white border-primary shadow-xs'
                                : 'bg-white border-gray-200 text-gray-600 hover:border-primary hover:text-primary'"
                            @click="setPage(p)"
                        >
                            {{ p }}
                        </button>

                        <button
                            type="button"
                            :disabled="currentPage === totalPages"
                            class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 disabled:opacity-40 disabled:cursor-not-allowed hover:border-primary hover:text-primary transition-colors cursor-pointer"
                            @click="setPage(currentPage + 1)"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>

                </div>
            </div>
        </section>

        <!-- MOBILE FILTER BOTTOM-SHEET MODAL -->
        <Teleport to="body">
            <div
                v-if="mobileFilterOpen"
                class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex flex-col justify-end transition-opacity"
                @click.self="closeFilterSheet"
            >
                <div class="bg-white rounded-t-3xl w-full max-h-[85vh] flex flex-col shadow-2xl">
                    <!-- Sheet Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-[17px] font-bold text-gray-900">Filters</h3>
                            <span v-if="activeFiltersCount > 0" class="text-xs bg-primarylt text-primary font-bold px-2 py-0.5 rounded-full">
                                {{ activeFiltersCount }} active
                            </span>
                        </div>
                        <button
                            type="button"
                            class="w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-colors cursor-pointer"
                            @click="closeFilterSheet"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </button>
                    </div>

                    <!-- Sheet Body -->
                    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5">
                        <!-- Subcategory -->
                        <div v-if="subcategories.length > 0">
                            <p class="text-[14px] font-bold text-gray-900 mb-3">Subcategory</p>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="sub in subcategories"
                                    :key="sub.id"
                                    type="button"
                                    class="text-left px-3 py-2 rounded-xl border text-[13px] flex items-center justify-between transition-colors cursor-pointer"
                                    :class="isSubcategorySelected(sub.slug) || isSubcategorySelected(String(sub.id))
                                        ? 'border-primary bg-primarylt text-primary font-bold'
                                        : 'border-gray-200 text-gray-700 bg-gray-50/50'"
                                    @click="toggleSubcategory(sub.slug)"
                                >
                                    <span class="truncate">{{ sub.name }}</span>
                                    <span class="text-[11px] opacity-70 shrink-0 ml-1">({{ sub.count }})</span>
                                </button>
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div>
                            <p class="text-[14px] font-bold text-gray-900 mb-3">Price Range</p>
                            <div class="flex items-center gap-2 mb-3">
                                <div class="relative flex-1">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">৳</span>
                                    <input
                                        v-model.number="minPriceInput"
                                        type="number"
                                        placeholder="Min"
                                        min="0"
                                        class="w-full pl-6 pr-2 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:border-primary"
                                    />
                                </div>
                                <span class="text-gray-300">–</span>
                                <div class="relative flex-1">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">৳</span>
                                    <input
                                        v-model.number="maxPriceInput"
                                        type="number"
                                        placeholder="Max"
                                        min="0"
                                        class="w-full pl-6 pr-2 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:border-primary"
                                    />
                                </div>
                            </div>
                            <input
                                v-model.number="priceSlider"
                                type="range"
                                min="0"
                                max="5000"
                                step="50"
                                class="w-full accent-primary h-1.5 bg-gray-200 rounded-lg"
                                @input="onSliderChange"
                            />
                            <div class="flex justify-between text-[11px] text-gray-400 mt-1">
                                <span>৳0</span>
                                <span>৳{{ Number(priceSlider).toLocaleString() }}+</span>
                            </div>
                        </div>

                        <!-- Brands -->
                        <div v-if="brandFacets.length > 0">
                            <p class="text-[14px] font-bold text-gray-900 mb-3">Brand</p>
                            <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto no-scrollbar">
                                <button
                                    v-for="brand in brandFacets"
                                    :key="brand.id || brand.name"
                                    type="button"
                                    class="text-left px-3 py-2 rounded-xl border text-[13px] flex items-center justify-between transition-colors cursor-pointer"
                                    :class="isBrandSelected(brand.slug) || isBrandSelected(brand.name) || isBrandSelected(String(brand.id))
                                        ? 'border-primary bg-primarylt text-primary font-bold'
                                        : 'border-gray-200 text-gray-700 bg-gray-50/50'"
                                    @click="toggleBrand(brand.slug || brand.name)"
                                >
                                    <span class="truncate">{{ brand.name }}</span>
                                    <span class="text-[11px] opacity-70 shrink-0 ml-1">({{ brand.count }})</span>
                                </button>
                            </div>
                        </div>

                        <!-- Rating -->
                        <div>
                            <p class="text-[14px] font-bold text-gray-900 mb-3">Customer Rating</p>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    class="border rounded-xl px-3 py-2 text-center text-sm font-semibold transition-colors cursor-pointer"
                                    :class="minRating === 4 ? 'border-primary bg-primarylt text-primary' : 'border-gray-200 text-gray-700'"
                                    @click="toggleRating(4)"
                                >
                                    ★ 4 & up
                                </button>
                                <button
                                    type="button"
                                    class="border rounded-xl px-3 py-2 text-center text-sm font-semibold transition-colors cursor-pointer"
                                    :class="minRating === 3 ? 'border-primary bg-primarylt text-primary' : 'border-gray-200 text-gray-700'"
                                    @click="toggleRating(3)"
                                >
                                    ★ 3 & up
                                </button>
                            </div>
                        </div>

                        <!-- Discount & Rx -->
                        <div class="space-y-3 pt-2">
                            <label class="flex items-center gap-3 cursor-pointer text-[14px] text-gray-700">
                                <input
                                    v-model="onlyDiscounted"
                                    type="checkbox"
                                    class="w-4 h-4 rounded accent-primary text-primary"
                                />
                                <span>Discounted Items Only</span>
                            </label>

                            <div class="pt-2">
                                <p class="text-[14px] font-bold text-gray-900 mb-2">Medicine Type</p>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="flex-1 py-2 text-xs font-semibold rounded-xl border transition-colors cursor-pointer"
                                        :class="onlyRx === 'all' ? 'border-primary bg-primarylt text-primary' : 'border-gray-200 text-gray-700'"
                                        @click="onlyRx = 'all'"
                                    >All</button>
                                    <button
                                        type="button"
                                        class="flex-1 py-2 text-xs font-semibold rounded-xl border transition-colors cursor-pointer"
                                        :class="onlyRx === 'otc' ? 'border-primary bg-primarylt text-primary' : 'border-gray-200 text-gray-700'"
                                        @click="onlyRx = 'otc'"
                                    >OTC</button>
                                    <button
                                        type="button"
                                        class="flex-1 py-2 text-xs font-semibold rounded-xl border transition-colors cursor-pointer"
                                        :class="onlyRx === 'rx' ? 'border-primary bg-primarylt text-primary' : 'border-gray-200 text-gray-700'"
                                        @click="onlyRx = 'rx'"
                                    >Rx Only</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sheet Footer -->
                    <div class="p-4 border-t border-gray-100 flex items-center gap-3 shrink-0 bg-gray-50/50">
                        <button
                            type="button"
                            class="flex-1 py-3 border border-gray-200 text-gray-700 font-semibold text-sm rounded-xl hover:bg-gray-100 transition-colors cursor-pointer"
                            @click="clearAllFilters"
                        >
                            Clear All
                        </button>
                        <button
                            type="button"
                            class="flex-1 py-3 bg-primary hover:bg-primary2 text-white font-semibold text-sm rounded-xl transition-colors cursor-pointer shadow-sm"
                            @click="closeFilterSheet"
                        >
                            Show Results ({{ filteredProducts.length }})
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </StoreLayout>
</template>
