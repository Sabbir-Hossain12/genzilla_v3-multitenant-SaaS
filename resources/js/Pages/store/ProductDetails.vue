<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import ProductCard from '@/Components/store/ProductCard.vue'
import { addToCart, formatTaka } from '@/composables/store/useCart'
import { openCheckoutModal } from '@/composables/store/useStoreUi'

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    relatedProducts: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    seo: {
        type: Object,
        default: () => ({}),
    },
})

const page = usePage()
const store = computed(() => page.props.store || {})
const storePhone = computed(() => store.value.phone || '09610-016778')

// Selected variant
const variants = computed(() => props.product.variants || [])
const currentVariant = ref(
    variants.value.find((v) => v.is_default) || variants.value[0] || {}
)

function selectVariant(v) {
    currentVariant.value = v
}

// Current Pricing & Math
const currentPrice = computed(() => Number(currentVariant.value?.price) || 0)
const currentOriginal = computed(() => {
    const orig = Number(currentVariant.value?.original)
    return orig > currentPrice.value ? orig : currentPrice.value
})
const hasDiscount = computed(() => currentOriginal.value > currentPrice.value)
const discountPercent = computed(() => {
    if (!hasDiscount.value || currentOriginal.value <= 0) return 0
    return Math.round(
        ((currentOriginal.value - currentPrice.value) / currentOriginal.value) * 100
    )
})
const savingsAmount = computed(() =>
    Math.max(0, currentOriginal.value - currentPrice.value)
)

// Quantity
const qty = ref(1)
function increaseQty() {
    qty.value = Math.min(10, qty.value + 1)
}
function decreaseQty() {
    qty.value = Math.max(1, qty.value - 1)
}

const totalPrice = computed(() => currentPrice.value * qty.value)

// Gallery
const gallery = computed(() => props.product.gallery || [])
const selectedImageIndex = ref(0)
const activeImage = computed(() => {
    if (gallery.value.length > 0 && gallery.value[selectedImageIndex.value]) {
        return gallery.value[selectedImageIndex.value].url
    }
    return 'https://via.placeholder.com/600'
})

// UI Interaction Flags
const isWishlisted = ref(false)
const autoReorder = ref(false)
const buyActionsRef = ref(null)
const showMobileBuyBar = ref(false)
let observer = null

// Tab switching
const activeTab = ref('details')
const tabs = [
    { id: 'details', label: 'Description' },
    { id: 'howto', label: 'How to use' },
    { id: 'ingredients', label: 'Ingredients' },
    { id: 'reviews', label: 'Ratings & Reviews' },
    { id: 'questions', label: 'FAQ' },
]

function scrollToTab(tabId) {
    activeTab.value = tabId
    const el = document.getElementById(tabId)
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
}

// Highlights & Specifications
const productSpecs = computed(() => props.product.specs || {})
const highlights = computed(() => {
    if (Array.isArray(productSpecs.value.highlights) && productSpecs.value.highlights.length) {
        return productSpecs.value.highlights
    }
    return [
        '100% Genuine and authentic formula sourced from licensed manufacturers',
        'Stored under DGDA compliant temperature-controlled conditions',
        'Doorstep express delivery with safe tamper-evident packaging',
        'Batch-tested with verified expiration date printed on package',
    ]
})

const defaultFaqs = [
    {
        q: 'How do I know this product is 100% original?',
        a: 'Every item sold on MediMart is sourced directly from verified pharmaceutical manufacturers and authorized distributors. All packages arrive factory-sealed with the manufacturer batch number and expiry date clearly printed.',
    },
    {
        q: 'How does doorstep express delivery work?',
        a: 'Orders placed before 6 PM in Dhaka are delivered on the same day. Orders outside Dhaka are dispatched via priority courier and typically delivered within 24–48 hours.',
    },
    {
        q: 'Can I return this product if I changed my mind?',
        a: 'We offer an easy 7-day return policy for unopened, sealed, and unused products in their original packaging. Simply contact our support helpline at ' + storePhone.value + '.',
    },
]

const faqs = computed(() => {
    if (Array.isArray(productSpecs.value.faqs) && productSpecs.value.faqs.length) {
        return productSpecs.value.faqs
    }
    return defaultFaqs
})

// Star ratings calculations
const starCounts = computed(() => props.product.star_counts || { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 })
const totalRatings = computed(() => props.product.reviews_count || 0)

function getBarPercent(star) {
    if (!totalRatings.value) return 0
    const count = starCounts.value[star] || 0
    return Math.min(100, Math.round((count / totalRatings.value) * 100))
}

// Toast helper
function dispatchToast(msg) {
    window.dispatchEvent(
        new CustomEvent('medimart:toast', {
            detail: msg,
        })
    )
}

// Cart & Buy Actions
function handleAddToCart() {
    const itemToAdd = {
        id: props.product.id,
        name: props.product.name + (currentVariant.value?.title ? ` (${currentVariant.value.title})` : ''),
        price: currentPrice.value,
        original: currentOriginal.value,
        image: activeImage.value,
    }
    addToCart(itemToAdd, qty.value)
    dispatchToast(`🛒 Added ${qty.value} × ${props.product.name} to cart!`)
}

function handleBuyNow() {
    const itemToAdd = {
        id: props.product.id,
        name: props.product.name + (currentVariant.value?.title ? ` (${currentVariant.value.title})` : ''),
        price: currentPrice.value,
        original: currentOriginal.value,
        image: activeImage.value,
    }
    addToCart(itemToAdd, qty.value)
    openCheckoutModal()
}

function toggleWishlist() {
    isWishlisted.value = !isWishlisted.value
    dispatchToast(isWishlisted.value ? '❤️ Saved to wishlist!' : 'Removed from wishlist')
}

function shareProduct() {
    if (navigator.clipboard) {
        navigator.clipboard
            .writeText(window.location.href)
            .then(() => dispatchToast('🔗 Product link copied to clipboard!'))
            .catch(() => dispatchToast('🔗 URL: ' + window.location.href))
    } else {
        dispatchToast('🔗 URL: ' + window.location.href)
    }
}

function toggleAuto() {
    autoReorder.value = !autoReorder.value
    dispatchToast(
        autoReorder.value ? '🔄 Monthly auto reorder is ON' : '🔄 Monthly auto reorder is OFF'
    )
}

function handleReviewHelpful(review) {
    review.helpful_count = (review.helpful_count || 0) + 1
    dispatchToast('👍 Thank you for your feedback!')
}

// Lifecycle for Mobile Buy Bar
onMounted(() => {
    if ('IntersectionObserver' in window && buyActionsRef.value) {
        observer = new IntersectionObserver(
            ([entry]) => {
                showMobileBuyBar.value = !entry.isIntersecting && entry.boundingClientRect.top < 0
            },
            { threshold: 0 }
        )
        observer.observe(buyActionsRef.value)
    }
})

onBeforeUnmount(() => {
    if (observer) {
        observer.disconnect()
    }
})
</script>

<template>
    <StoreLayout>
        <Head>
            <title>{{ seo.meta_title || `${product.name} — Buy Online | ${store.name || 'MediMart'}` }}</title>
            <meta name="description" :content="seo.meta_description || product.description || 'Authentic medicine, beauty & healthcare product online.'" />
        </Head>

        <main class="max-w-7xl mx-auto px-3 sm:px-4 pt-3 sm:pt-4 pb-16 lg:pb-12">
            <!-- ════════════════════════════════════════
                 BREADCRUMBS
            ════════════════════════════════════════ -->
            <nav aria-label="Breadcrumb" class="text-xs sm:text-[13px] text-gray-500 flex items-center gap-1.5 flex-wrap mb-3 sm:mb-4">
                <Link href="/" class="hover:text-primary transition-colors flex items-center gap-1">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Home
                </Link>
                <span class="text-gray-300">/</span>

                <template v-if="product.category">
                    <template v-if="product.category.parent">
                        <Link :href="'/category/' + (product.category.parent.slug || product.category.parent.id)" class="hover:text-primary transition-colors">
                            {{ product.category.parent.name }}
                        </Link>
                        <span class="text-gray-300">/</span>
                    </template>
                    <Link :href="'/category/' + (product.category.slug || product.category.id)" class="hover:text-primary transition-colors">
                        {{ product.category.name }}
                    </Link>
                    <span class="text-gray-300">/</span>
                </template>

                <span class="text-gray-800 font-semibold line-clamp-1 max-w-[280px] sm:max-w-md">
                    {{ product.name }}
                </span>
            </nav>

            <!-- ════════════════════════════════════════
                 PRODUCT HERO (3-COL LAYOUT)
            ════════════════════════════════════════ -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                <!-- ── 1. GALLERY (5 cols) ── -->
                <div class="lg:col-span-5">
                    <div class="bg-white border border-gray-100 rounded-2xl p-3 sm:p-4 shadow-2xs">
                        <div class="relative bg-white rounded-xl overflow-hidden aspect-square flex items-center justify-center border border-gray-50">
                            <!-- Discount Badge -->
                            <span
                                v-if="hasDiscount"
                                class="absolute top-3 left-3 z-10 bg-green-500 text-white text-xs sm:text-[13px] font-bold px-2.5 py-1 rounded shadow-xs"
                            >
                                {{ discountPercent }}% OFF
                            </span>

                            <!-- Prescription Required (Rx) Badge -->
                            <span
                                v-if="product.requires_prescription"
                                class="absolute bottom-3 left-3 z-10 bg-purple-600 text-white text-[11px] font-bold px-2.5 py-1 rounded shadow-xs flex items-center gap-1"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M12 2v20M2 12h20" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                Rx Prescription Required
                            </span>

                            <!-- Action Buttons: Wishlist & Share -->
                            <div class="absolute top-3 right-3 flex flex-col gap-2 z-10">
                                <button
                                    type="button"
                                    @click="toggleWishlist"
                                    :aria-pressed="isWishlisted"
                                    class="w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm border border-gray-200 flex items-center justify-center transition-all shadow-sm hover:scale-105 cursor-pointer"
                                    :class="isWishlisted ? 'text-red-500' : 'text-gray-500 hover:text-red-500'"
                                    aria-label="Wishlist"
                                >
                                    <svg width="19" height="19" viewBox="0 0 24 24" :fill="isWishlisted ? 'currentColor' : 'none'">
                                        <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    @click="shareProduct"
                                    class="w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm border border-gray-200 text-gray-500 hover:text-primary flex items-center justify-center transition-all shadow-sm hover:scale-105 cursor-pointer"
                                    aria-label="Share product"
                                >
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/>
                                        <circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                        <circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/>
                                        <path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Stage Image -->
                            <img
                                :src="activeImage"
                                :alt="product.name"
                                class="w-full h-full object-contain p-2 transition-transform duration-300 hover:scale-105"
                            />
                        </div>

                        <!-- Gallery Thumbnails -->
                        <div v-if="gallery.length > 1" class="grid grid-cols-4 sm:grid-cols-5 gap-2 mt-3">
                            <button
                                v-for="(img, idx) in gallery"
                                :key="img.id || idx"
                                type="button"
                                @click="selectedImageIndex = idx"
                                class="aspect-square rounded-xl overflow-hidden border-2 bg-white transition-all cursor-pointer p-1"
                                :class="selectedImageIndex === idx ? 'border-primary ring-2 ring-primary/20' : 'border-gray-100 hover:border-gray-300'"
                            >
                                <img :src="img.url" :alt="img.alt || product.name" class="w-full h-full object-contain" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── 2. PRODUCT INFO (4 cols) ── -->
                <div class="lg:col-span-4">
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-6 shadow-2xs">
                        <!-- Brand -->
                        <Link
                            v-if="product.brand"
                            :href="'/search?brands=' + product.brand.id"
                            class="text-xs sm:text-[13px] font-bold text-primary hover:underline uppercase tracking-wider inline-block"
                        >
                            {{ product.brand.name }}
                        </Link>
                        <span v-else class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                            Generic Brand
                        </span>

                        <!-- Title -->
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 leading-snug mt-1">
                            {{ product.name }}
                            <span v-if="currentVariant?.title && currentVariant.title !== 'Standard'" class="text-gray-500 font-medium text-lg">
                                — {{ currentVariant.title }}
                            </span>
                        </h1>

                        <!-- Ratings bar -->
                        <div class="flex items-center flex-wrap gap-y-1 mt-2.5 text-sm">
                            <button
                                type="button"
                                @click="scrollToTab('reviews')"
                                class="flex items-center gap-1.5 pr-3 text-left hover:opacity-80 transition-opacity cursor-pointer"
                            >
                                <span class="text-amber-500 tracking-tight text-base">★★★★★</span>
                                <span class="font-bold text-gray-900">{{ product.avg_rating || 5.0 }}</span>
                                <span class="text-gray-400 text-xs">({{ product.reviews_count || 0 }} reviews)</span>
                            </button>
                            <span class="pl-3 border-l border-gray-200 text-xs sm:text-sm text-gray-500 flex items-center gap-1">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="text-emerald-500"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                100% Genuine
                            </span>
                        </div>

                        <!-- Price & Discounts -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-baseline flex-wrap gap-x-3 gap-y-1">
                                <span class="text-2xl sm:text-3xl font-extrabold text-primary">
                                    {{ formatTaka(currentPrice) }}
                                </span>
                                <span
                                    v-if="hasDiscount"
                                    class="text-base text-gray-400 line-through"
                                >
                                    {{ formatTaka(currentOriginal) }}
                                </span>
                                <span
                                    v-if="hasDiscount"
                                    class="text-xs font-bold text-accent bg-accentlt px-2 py-0.5 rounded-md"
                                >
                                    Save {{ formatTaka(savingsAmount) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1.5">
                                Inclusive of all applicable taxes. Delivery calculated at checkout.
                            </p>
                        </div>

                        <!-- Variants selection -->
                        <div v-if="variants.length > 1" class="mt-5">
                            <p class="text-xs sm:text-[13px] font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Select Variant / Pack Size
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="v in variants"
                                    :key="v.id"
                                    type="button"
                                    @click="selectVariant(v)"
                                    class="px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all border cursor-pointer"
                                    :class="currentVariant?.id === v.id
                                        ? 'border-primary bg-primarylt text-primary ring-1 ring-primary'
                                        : 'border-gray-200 bg-white text-gray-700 hover:border-primary/50'"
                                >
                                    {{ v.title }}
                                    <span v-if="v.price" class="text-[11px] opacity-75 font-normal ml-1">
                                        ({{ formatTaka(v.price) }})
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Highlights Box -->
                        <div class="mt-5 bg-primarylt/60 border border-primary/10 rounded-xl p-3.5 sm:p-4">
                            <p class="text-xs sm:text-[13px] font-bold text-primary2 uppercase tracking-wider mb-2.5">
                                Product Highlights
                            </p>
                            <ul class="space-y-2 text-xs sm:text-sm text-gray-700">
                                <li v-for="(hl, i) in highlights" :key="i" class="flex gap-2.5 items-start">
                                    <svg class="shrink-0 mt-0.5 text-primary" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span>{{ hl }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Quick facts -->
                        <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-2.5 text-xs sm:text-sm">
                            <div v-if="product.brand">
                                <dt class="text-gray-400">Brand</dt>
                                <dd class="font-medium text-gray-800">{{ product.brand.name }}</dd>
                            </div>
                            <div v-if="product.generic_name">
                                <dt class="text-gray-400">Generic Name</dt>
                                <dd class="font-medium text-gray-800">{{ product.generic_name }}</dd>
                            </div>
                            <div v-if="product.category">
                                <dt class="text-gray-400">Category</dt>
                                <dd class="font-medium text-gray-800">{{ product.category.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-400">Product SKU</dt>
                                <dd class="font-medium text-gray-800 font-mono">{{ currentVariant?.sku || `SKU-${product.id}` }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- ── 3. BUY BOX (3 cols, sticky) ── -->
                <aside class="lg:col-span-3">
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-5 lg:sticky top-24 shadow-2xs space-y-4">
                        <!-- Stock Status -->
                        <div class="flex items-center gap-2 text-xs sm:text-sm font-semibold">
                            <span
                                class="w-2.5 h-2.5 rounded-full"
                                :class="currentVariant?.is_in_stock !== false ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"
                            ></span>
                            <span :class="currentVariant?.is_in_stock !== false ? 'text-emerald-700' : 'text-red-600'">
                                {{ currentVariant?.is_in_stock !== false ? 'In Stock (Ready to dispatch)' : 'Currently Out of Stock' }}
                            </span>
                        </div>

                        <!-- Subtotal Line -->
                        <div class="flex items-baseline justify-between pt-1">
                            <span class="text-xs sm:text-sm text-gray-500">Order Total</span>
                            <span class="text-2xl font-bold text-gray-900">
                                {{ formatTaka(totalPrice) }}
                            </span>
                        </div>

                        <!-- Quantity Selector -->
                        <div class="flex items-center justify-between">
                            <span class="text-xs sm:text-sm font-bold text-gray-700">Quantity</span>
                            <div class="flex items-center border border-gray-200 rounded-xl bg-gray-50/50">
                                <button
                                    type="button"
                                    @click="decreaseQty"
                                    class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center text-gray-600 hover:text-primary hover:bg-gray-100 rounded-l-xl text-lg font-bold transition-colors cursor-pointer"
                                    aria-label="Decrease quantity"
                                >−</button>
                                <span class="w-10 text-center font-bold text-sm text-gray-900">{{ qty }}</span>
                                <button
                                    type="button"
                                    @click="increaseQty"
                                    class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center text-gray-600 hover:text-primary hover:bg-gray-100 rounded-r-xl text-lg font-bold transition-colors cursor-pointer"
                                    aria-label="Increase quantity"
                                >+</button>
                            </div>
                        </div>

                        <!-- CTAs -->
                        <div ref="buyActionsRef" class="space-y-2.5 pt-1">
                            <button
                                type="button"
                                @click="handleAddToCart"
                                :disabled="currentVariant?.is_in_stock === false"
                                class="w-full bg-primary hover:bg-primary2 disabled:bg-gray-300 text-white font-bold text-sm sm:text-base py-3.5 rounded-xl transition-all shadow-sm hover:shadow flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Add to Cart
                            </button>

                            <button
                                type="button"
                                @click="handleBuyNow"
                                :disabled="currentVariant?.is_in_stock === false"
                                class="w-full bg-accent hover:bg-orange-600 disabled:bg-gray-300 text-white font-bold text-sm sm:text-base py-3.5 rounded-xl transition-all shadow-sm hover:shadow cursor-pointer"
                            >
                                Buy Now
                            </button>
                        </div>

                        <!-- Monthly Auto-Reorder Toggle -->
                        <div class="flex items-start justify-between gap-3 border border-gray-100 rounded-xl p-3 bg-gray-50/50">
                            <div>
                                <p class="text-xs sm:text-[13px] font-bold text-gray-800">Monthly auto reorder</p>
                                <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Delivered monthly. Skip or cancel anytime.</p>
                            </div>
                            <button
                                type="button"
                                @click="toggleAuto"
                                role="switch"
                                :aria-checked="autoReorder"
                                class="w-11 h-6 rounded-full relative transition-colors shrink-0 mt-0.5 cursor-pointer"
                                :class="autoReorder ? 'bg-primary' : 'bg-gray-300'"
                            >
                                <span
                                    class="w-5 h-5 rounded-full bg-white absolute top-0.5 left-0.5 shadow-sm transition-transform"
                                    :class="autoReorder ? 'translate-x-5' : 'translate-x-0'"
                                ></span>
                            </button>
                        </div>

                        <!-- Pharmacy Value Guarantees -->
                        <ul class="pt-3 border-t border-gray-100 space-y-2.5 text-xs text-gray-600">
                            <li class="flex gap-2.5 items-start">
                                <svg class="shrink-0 text-primary mt-0.5" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M1 3h15v13H1zM16 8h4l3 3v5h-7z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="5.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/><circle cx="18.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/></svg>
                                <span><b class="text-gray-800 font-semibold">Same-Day Express in Dhaka</b><br>Order before 6 PM. Free above ৳500.</span>
                            </li>
                            <li class="flex gap-2.5 items-start">
                                <svg class="shrink-0 text-primary mt-0.5" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8M3 3v5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span><b class="text-gray-800 font-semibold">7-Day Easy Return</b><br>For sealed, unused authentic packs.</span>
                            </li>
                            <li class="flex gap-2.5 items-start">
                                <svg class="shrink-0 text-primary mt-0.5" width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/><path d="M2 10h20" stroke="currentColor" stroke-width="2"/></svg>
                                <span><b class="text-gray-800 font-semibold">bKash, Nagad, Cards &amp; COD</b><br>Safe and flexible payment methods.</span>
                            </li>
                        </ul>
                    </div>
                </aside>
            </section>

            <!-- ════════════════════════════════════════
                 TABS & DETAILED SPECIFICATIONS
            ════════════════════════════════════════ -->
            <div class="mt-6 sm:mt-8">
                <!-- Tab Navigation Bar -->
                <div class="sticky top-[68px] z-30 bg-white border border-gray-100 rounded-2xl shadow-2xs mb-4">
                    <div class="flex overflow-x-auto no-scrollbar px-2 sm:px-4 text-xs sm:text-sm">
                        <button
                            v-for="t in tabs"
                            :key="t.id"
                            type="button"
                            @click="scrollToTab(t.id)"
                            class="px-4 py-3.5 whitespace-nowrap font-semibold border-b-2 transition-all cursor-pointer"
                            :class="activeTab === t.id
                                ? 'text-primary border-primary'
                                : 'text-gray-500 border-transparent hover:text-primary'"
                        >
                            {{ t.label }}
                            <span v-if="t.id === 'reviews' && product.reviews_count">
                                ({{ product.reviews_count }})
                            </span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <!-- Left: Tab Contents (8 cols) -->
                    <div class="lg:col-span-8 space-y-4">

                        <!-- Tab 1: Description -->
                        <section id="details" class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-6 shadow-2xs scroll-mt-28">
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-3">Product Description</h2>
                            <div class="text-sm sm:text-[15px] text-gray-600 leading-relaxed space-y-3">
                                <p v-if="product.description" class="whitespace-pre-line">{{ product.description }}</p>
                                <p v-else>
                                    {{ product.name }} is a genuine quality product sourced directly from verified manufacturers. Designed for reliable daily health and wellness needs.
                                </p>
                            </div>

                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mt-6 mb-3">
                                Specifications &amp; Overview
                            </h3>
                            <dl class="border border-gray-100 rounded-xl overflow-hidden text-xs sm:text-sm divide-y divide-gray-100">
                                <div class="grid grid-cols-3 bg-gray-50/70 p-3">
                                    <dt class="text-gray-500 font-medium">Brand</dt>
                                    <dd class="col-span-2 text-gray-900 font-semibold">{{ product.brand?.name || 'Generic' }}</dd>
                                </div>
                                <div class="grid grid-cols-3 p-3">
                                    <dt class="text-gray-500 font-medium">Category</dt>
                                    <dd class="col-span-2 text-gray-900 font-semibold">{{ product.category?.name || 'General' }}</dd>
                                </div>
                                <div v-if="product.generic_name" class="grid grid-cols-3 bg-gray-50/70 p-3">
                                    <dt class="text-gray-500 font-medium">Generic Name</dt>
                                    <dd class="col-span-2 text-gray-900 font-semibold">{{ product.generic_name }}</dd>
                                </div>
                                <div class="grid grid-cols-3 p-3">
                                    <dt class="text-gray-500 font-medium">Country of Origin</dt>
                                    <dd class="col-span-2 text-gray-900 font-semibold">{{ productSpecs.origin || productSpecs.country_of_origin || 'Bangladesh' }}</dd>
                                </div>
                                <div class="grid grid-cols-3 bg-gray-50/70 p-3">
                                    <dt class="text-gray-500 font-medium">Prescription Needed</dt>
                                    <dd class="col-span-2 text-gray-900 font-semibold">
                                        {{ product.requires_prescription ? 'Yes (Valid Rx required at checkout)' : 'No (Over-The-Counter)' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3 p-3">
                                    <dt class="text-gray-500 font-medium">Storage Instructions</dt>
                                    <dd class="col-span-2 text-gray-900 font-semibold">{{ productSpecs.storage || 'Store below 30°C in a dry place away from direct sunlight.' }}</dd>
                                </div>
                            </dl>
                        </section>

                        <!-- Tab 2: How to use -->
                        <section id="howto" class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-6 shadow-2xs scroll-mt-28">
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">How to Use &amp; Dosage</h2>
                            <ol class="space-y-3.5 text-xs sm:text-sm text-gray-700">
                                <li class="flex gap-3">
                                    <span class="shrink-0 w-7 h-7 rounded-full bg-primarylt text-primary font-bold text-xs flex items-center justify-center">1</span>
                                    <p class="pt-0.5">Read product packaging and dosage label instructions carefully prior to usage.</p>
                                </li>
                                <li class="flex gap-3">
                                    <span class="shrink-0 w-7 h-7 rounded-full bg-primarylt text-primary font-bold text-xs flex items-center justify-center">2</span>
                                    <p class="pt-0.5">Take or apply strictly as directed by your physician or as indicated on the pack.</p>
                                </li>
                                <li class="flex gap-3">
                                    <span class="shrink-0 w-7 h-7 rounded-full bg-primarylt text-primary font-bold text-xs flex items-center justify-center">3</span>
                                    <p class="pt-0.5">Do not exceed the recommended daily dose without consulting a healthcare professional.</p>
                                </li>
                            </ol>

                            <div class="mt-5 bg-amber-50 border border-amber-200/60 rounded-xl p-3.5 text-xs sm:text-sm text-amber-900">
                                <p class="font-bold mb-1 flex items-center gap-1.5 text-amber-950">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Precautions &amp; Advisory
                                </p>
                                <ul class="list-disc pl-5 space-y-1 text-amber-800">
                                    <li>Keep out of reach of children and domestic pets.</li>
                                    <li>If you experience any adverse reaction or allergic symptom, discontinue use immediately and seek medical attention.</li>
                                    <li>Store in cool, dry conditions away from moisture.</li>
                                </ul>
                            </div>
                        </section>

                        <!-- Tab 3: Ingredients -->
                        <section id="ingredients" class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-6 shadow-2xs scroll-mt-28">
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">Active Ingredients &amp; Composition</h2>
                            <div class="flex gap-3.5 items-start bg-primarylt/60 rounded-xl p-3.5 sm:p-4 border border-primary/10">
                                <div class="shrink-0 w-10 h-10 rounded-lg bg-white flex items-center justify-center text-primary shadow-xs">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 22c0-6 2-10 8-12-1 6-3 10-8 12zM12 22C12 16 10 12 4 10c1 6 3 10 8 12zM12 14V4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm sm:text-base">
                                        {{ product.generic_name || product.name }}
                                    </p>
                                    <p class="text-xs sm:text-sm text-gray-600 mt-1">
                                        {{ productSpecs.ingredients || 'Pharmaceutical formulation with certified active active constituents conforming to pharmacopoeial standards.' }}
                                    </p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-3">
                                Always inspect full ingredient details printed on the product packaging before use.
                            </p>
                        </section>

                        <!-- Tab 4: Reviews -->
                        <section id="reviews" class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-6 shadow-2xs scroll-mt-28">
                            <div class="flex items-center justify-between gap-3 mb-5">
                                <div>
                                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">Ratings &amp; Reviews</h2>
                                    <p class="text-xs text-gray-500">Verified customer feedback and experiences</p>
                                </div>
                                <button
                                    type="button"
                                    @click="dispatchToast('Please log in from your account to write a verified review.')"
                                    class="border border-primary text-primary text-xs sm:text-sm font-semibold px-3.5 py-1.5 sm:py-2 rounded-xl hover:bg-primarylt transition-colors cursor-pointer"
                                >
                                    Write a review
                                </button>
                            </div>

                            <!-- Rating summary header -->
                            <div class="grid sm:grid-cols-[160px_1fr] gap-6 items-center pb-6 border-b border-gray-100">
                                <div class="text-center sm:text-left">
                                    <p class="text-4xl sm:text-5xl font-extrabold text-gray-900">{{ product.avg_rating || 5.0 }}</p>
                                    <p class="text-amber-500 text-base sm:text-lg mt-1">★★★★★</p>
                                    <p class="text-xs text-gray-400 mt-1">Based on {{ product.reviews_count || 0 }} ratings</p>
                                </div>

                                <!-- Bars -->
                                <div class="space-y-1.5 text-xs sm:text-[13px]">
                                    <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="flex items-center gap-3">
                                        <span class="w-8 text-gray-600 font-medium shrink-0">{{ star }} ★</span>
                                        <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                                            <div
                                                class="h-full bg-amber-400 rounded-full transition-all duration-500"
                                                :style="{ width: getBarPercent(star) + '%' }"
                                            ></div>
                                        </div>
                                        <span class="w-7 text-right text-gray-400 shrink-0 font-mono text-[11px]">
                                            {{ starCounts[star] || 0 }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Reviews List -->
                            <div v-if="product.reviews && product.reviews.length" class="divide-y divide-gray-100 mt-2">
                                <article v-for="r in product.reviews" :key="r.id" class="py-4 sm:py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-primarylt text-primary font-bold flex items-center justify-center text-xs">
                                            {{ r.initials || 'VC' }}
                                        </div>
                                        <div>
                                            <p class="text-xs sm:text-sm font-bold text-gray-900">{{ r.reviewer_name }}</p>
                                            <p class="text-[11px] text-gray-400 flex items-center gap-1">
                                                <svg v-if="r.is_verified_purchase" width="12" height="12" viewBox="0 0 24 24" fill="none" class="text-emerald-500"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                {{ r.is_verified_purchase ? 'Verified purchase' : 'Customer review' }} &bull; {{ r.date }}
                                            </p>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-xs text-amber-500">
                                        {{ '★'.repeat(r.rating) }}<span class="text-gray-300">{{ '★'.repeat(Math.max(0, 5 - r.rating)) }}</span>
                                    </p>
                                    <p v-if="r.title" class="mt-1 text-sm font-bold text-gray-900">{{ r.title }}</p>
                                    <p class="mt-1 text-xs sm:text-sm text-gray-600 leading-relaxed">{{ r.comment }}</p>
                                    <button
                                        type="button"
                                        @click="handleReviewHelpful(r)"
                                        class="mt-2.5 text-xs text-gray-500 border border-gray-200 rounded-lg px-2.5 py-1 hover:border-primary hover:text-primary transition-colors cursor-pointer"
                                    >
                                        Helpful (<span>{{ r.helpful_count || 0 }}</span>)
                                    </button>
                                </article>
                            </div>
                            <div v-else class="py-8 text-center text-xs sm:text-sm text-gray-400">
                                No customer reviews yet. Be the first to share your thoughts on this product!
                            </div>
                        </section>

                        <!-- Tab 5: FAQ -->
                        <section id="questions" class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-6 shadow-2xs scroll-mt-28">
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
                            <div class="space-y-2.5">
                                <details v-for="(f, idx) in faqs" :key="idx" class="border border-gray-100 rounded-xl group" :open="idx === 0">
                                    <summary class="flex items-center justify-between gap-3 px-4 py-3.5 text-xs sm:text-sm font-bold text-gray-800 cursor-pointer list-none">
                                        <span>{{ f.q }}</span>
                                        <svg class="shrink-0 text-gray-400 group-open:rotate-180 transition-transform" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </summary>
                                    <div class="px-4 pb-4 text-xs sm:text-sm text-gray-600 leading-relaxed border-t border-gray-50 pt-2">
                                        <p>{{ f.a }}</p>
                                        <span class="block text-[11px] text-primary font-medium mt-1.5">
                                            Answered by MediMart Licensed Pharmacist
                                        </span>
                                    </div>
                                </details>
                            </div>
                        </section>
                    </div>

                    <!-- Right: Pharmacist Advice Sidebar (4 cols) -->
                    <aside class="lg:col-span-4">
                        <div class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-5 lg:sticky top-24 shadow-2xs space-y-3">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900">Need advice before you buy?</h3>
                            <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                                Our certified pharmacists can help you confirm medicine interactions and proper usage.
                            </p>
                            <a
                                :href="`tel:${storePhone}`"
                                class="flex items-center justify-center gap-2 border border-primary text-primary font-bold text-xs sm:text-sm py-2.5 rounded-xl hover:bg-primarylt transition-colors"
                            >
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Call {{ storePhone }}
                            </a>
                            <a
                                href="https://wa.me/8801810117100"
                                target="_blank"
                                rel="noopener"
                                class="flex items-center justify-center gap-2 bg-emerald-50 text-emerald-800 font-bold text-xs sm:text-sm py-2.5 rounded-xl hover:bg-emerald-100 transition-colors"
                            >
                                💬 WhatsApp Chat with Pharmacist
                            </a>
                        </div>
                    </aside>
                </div>
            </div>

            <!-- ════════════════════════════════════════
                 YOU MAY ALSO LIKE (RELATED PRODUCTS)
            ════════════════════════════════════════ -->
            <section v-if="relatedProducts.length > 0" class="mt-8 bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-2xs">
                <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-50">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900">You May Also Like</h2>
                    <Link
                        v-if="product.category"
                        :href="'/category/' + (product.category.slug || product.category.id)"
                        class="text-xs sm:text-sm text-primary font-bold hover:underline flex items-center gap-1"
                    >
                        See all
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 sm:gap-3 p-3 sm:p-4">
                    <ProductCard
                        v-for="p in relatedProducts"
                        :key="p.id"
                        :product="p"
                        @add="addToCart"
                    />
                </div>
            </section>
        </main>

        <!-- ════════════════════════════════════════
             MOBILE STICKY BOTTOM BUY BAR
        ════════════════════════════════════════ -->
        <transition
            enter-active-class="transition-transform duration-200 ease-out"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-150 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div
                v-if="showMobileBuyBar"
                class="lg:hidden fixed left-0 right-0 bottom-0 z-40 bg-white/95 backdrop-blur-md border-t border-gray-200 px-4 py-2.5 shadow-[0_-4px_16px_rgba(0,0,0,.08)]"
                style="padding-bottom: max(0.65rem, env(safe-area-inset-bottom))"
            >
                <div class="flex items-center gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] text-gray-400 font-medium leading-tight">Total</p>
                        <p class="text-lg font-extrabold text-primary leading-tight">
                            {{ formatTaka(totalPrice) }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="handleAddToCart"
                        :disabled="currentVariant?.is_in_stock === false"
                        class="ml-auto bg-primary hover:bg-primary2 disabled:bg-gray-300 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl transition-colors cursor-pointer"
                    >
                        Add to Cart
                    </button>
                    <button
                        type="button"
                        @click="handleBuyNow"
                        :disabled="currentVariant?.is_in_stock === false"
                        class="bg-accent hover:bg-orange-600 disabled:bg-gray-300 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl transition-colors cursor-pointer"
                    >
                        Buy Now
                    </button>
                </div>
            </div>
        </transition>
    </StoreLayout>
</template>
