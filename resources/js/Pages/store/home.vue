<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import CategorySidebar from '@/Components/store/CategorySidebar.vue'
import BannerSlider from '@/Components/store/BannerSlider.vue'
import ProductGrid from '@/Components/store/ProductGrid.vue'
import CategoryTiles from '@/Components/store/CategoryTiles.vue'
import BrandShowcase from '@/Components/store/BrandShowcase.vue'
import FlashSaleHeader from '@/Components/store/FlashSaleHeader.vue'
import { addToCart } from '@/composables/store/useCart'

import {
    products as fallbackProducts,
    quickActions as fallbackQuickActions,
    promos as fallbackPromos,
    stats as fallbackStats,
    trustBadges as fallbackTrustBadges,
    categories as fallbackCategories,
    categoryTiles as fallbackCategoryTiles,
    slides as fallbackSlides,
} from '@/data/store'

const props = defineProps({
    store: {
        type: Object,
        default: () => ({}),
    },
    seo: {
        type: Object,
        default: () => ({}),
    },
    categories: {
        type: Array,
        default: () => [],
    },
    categoryTiles: {
        type: Array,
        default: () => [],
    },
    slides: {
        type: Array,
        default: () => [],
    },
    promos: {
        type: Array,
        default: () => [],
    },
    flashSaleProducts: {
        type: Array,
        default: () => [],
    },
    bestPicksProducts: {
        type: Array,
        default: () => [],
    },
    quickActions: {
        type: Array,
        default: () => [],
    },
    trustBadges: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Array,
        default: () => [],
    },
    brands: {
        type: Array,
        default: () => [],
    },
})

// Store branding
const storeName = computed(() => props.store?.name || 'MediMart')
const storeLogo = computed(() => props.store?.logo_url || '')
const seoTitle = computed(() => props.seo?.meta_title || `${storeName.value} — Online Medicine, Beauty & Healthcare`)
const seoDescription = computed(() => props.seo?.meta_description || 'Order authentic medicines, wellness, and healthcare products with express doorstep delivery.')
const seoKeywords = computed(() => props.seo?.meta_keywords || '')
const seoRobots = computed(() => props.seo?.meta_robots || 'index, follow')
const canonicalUrl = computed(() => props.seo?.canonical_url || '')
const schemaMarkup = computed(() => props.seo?.schema_markup || '')

// Fallback-safe computed collections
const dynamicCategories = computed(() => {
    return props.categories?.length ? props.categories : fallbackCategories
})

const dynamicCategoryTiles = computed(() => {
    return props.categoryTiles?.length ? props.categoryTiles : fallbackCategoryTiles
})

const dynamicSlides = computed(() => {
    return props.slides?.length ? props.slides : fallbackSlides
})

const dynamicPromos = computed(() => {
    return props.promos?.length ? props.promos : fallbackPromos
})

const dynamicFlashSale = computed(() => {
    return props.flashSaleProducts?.length ? props.flashSaleProducts : fallbackProducts
})

const dynamicBestPicks = computed(() => {
    return props.bestPicksProducts?.length ? props.bestPicksProducts : fallbackProducts
})

const dynamicQuickActions = computed(() => {
    return props.quickActions?.length ? props.quickActions : fallbackQuickActions
})

const dynamicTrustBadges = computed(() => {
    return props.trustBadges?.length ? props.trustBadges : fallbackTrustBadges
})

const dynamicStats = computed(() => {
    return props.stats?.length ? props.stats : fallbackStats
})

function hasBannerText(promo) {
    if (!promo) return false
    const t = String(promo.title || '').trim()
    const s = String(promo.subtitle || '').trim()
    const e = String(promo.eyebrow || '').trim()
    return (t && t !== '#') || Boolean(s) || Boolean(e)
}
</script>

<template>
    <Head>
        <title>{{ seoTitle }}</title>
        <meta v-if="seoDescription" name="description" :content="seoDescription" />
        <meta v-if="seoKeywords" name="keywords" :content="seoKeywords" />
        <meta name="robots" :content="seoRobots" />
        <link v-if="canonicalUrl" rel="canonical" :href="canonicalUrl" />

        <!-- Open Graph / Social Sharing -->
        <meta property="og:title" :content="seoTitle" />
        <meta v-if="seoDescription" property="og:description" :content="seoDescription" />
        <meta property="og:type" content="website" />
        <meta v-if="canonicalUrl" property="og:url" :content="canonicalUrl" />
        <meta v-if="storeLogo" property="og:image" :content="storeLogo" />
        <meta property="og:site_name" :content="storeName" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="seoTitle" />
        <meta v-if="seoDescription" name="twitter:description" :content="seoDescription" />
        <meta v-if="storeLogo" name="twitter:image" :content="storeLogo" />
    </Head>

    <StoreLayout>
        <!-- ════════════════════════════════════════
             HERO — BANNER + CATEGORY SIDEBAR
        ════════════════════════════════════════ -->
        <section class="max-w-7xl mx-auto px-4 pt-4 pb-2">
            <div class="flex gap-3">
                <!-- Dynamic Category Sidebar -->
                <CategorySidebar :categories="dynamicCategories" />

                <!-- Banner Slider & Quick Actions -->
                <div class="flex-1 min-w-0 flex flex-col gap-3">
                    <BannerSlider :slides="dynamicSlides" />

                    <!-- Quick Action Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <a
                            v-for="action in dynamicQuickActions"
                            :key="action.title"
                            :href="action.link || '#'"
                            class="bg-white border border-gray-100 rounded-xl p-4 flex items-center gap-3.5 hover:border-primary/30 hover:shadow-md transition-all group"
                        >
                            <div
                                class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                :class="[action.bg, action.hover]"
                            >
                                <span class="text-2xl">{{ action.icon }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[14px] font-semibold text-gray-800 leading-tight">{{ action.title }}</p>
                                <p class="text-[13px] text-gray-500">{{ action.subtitle }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             TRUST BADGES / PROMO STRIP
        ════════════════════════════════════════ -->
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div
                    v-for="badge in dynamicTrustBadges"
                    :key="badge.title"
                    class="border rounded-xl px-4 py-3 flex items-center gap-3"
                    :class="badge.wrap"
                >
                    <span class="text-2xl shrink-0">{{ badge.icon }}</span>
                    <div class="min-w-0">
                        <p class="text-[14px] font-semibold truncate" :class="badge.titleColor">{{ badge.title }}</p>
                        <p class="text-[13px] text-gray-500 truncate">{{ badge.subtitle }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════
             FLASH SALE SECTION
        ════════════════════════════════════════ -->
        <section id="flash-sale" class="max-w-7xl mx-auto px-4 py-4">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50">
                    <FlashSaleHeader />
                    <Link
                        href="/products?collection=flash-sale"
                        class="text-[15px] text-primary font-semibold hover:underline flex items-center gap-1"
                    >
                        See all
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </Link>
                </div>

                <ProductGrid :products="dynamicFlashSale" @add="addToCart" />
            </div>
        </section>

        <!-- ════════════════════════════════════════
             SHOP BY CATEGORY ("ALL YOU NEED")
        ════════════════════════════════════════ -->
        <section id="all-categories" class="max-w-7xl mx-auto px-4 py-8">
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-[17px] font-bold text-gray-900">All You Need</h2>
                    <Link
                        href="/products"
                        class="text-[15px] text-primary font-semibold hover:underline flex items-center gap-1"
                    >
                        See all
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </Link>
                </div>
                <CategoryTiles :tiles="dynamicCategoryTiles" />
            </div>
        </section>

        <!-- ════════════════════════════════════════
             TOP BRANDS SHOWCASE
        ════════════════════════════════════════ -->
        <BrandShowcase :brands="brands" />

        <!-- ════════════════════════════════════════
             PROMOTIONAL BANNERS GRID (100% UNTINTED, PURE VIBRANT DESIGN FOR ALL)
        ════════════════════════════════════════ -->
        <section v-if="dynamicPromos.length" class="max-w-7xl mx-auto px-4 py-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <a
                    v-for="promo in dynamicPromos"
                    :key="promo.id || promo.title"
                    :href="promo.link || '#'"
                    class="group relative rounded-2xl overflow-hidden aspect-[2/1] sm:aspect-[2.2/1] min-h-[180px] sm:min-h-[220px] shadow-2xs hover:shadow-lg transition-all duration-300 border border-gray-100 block bg-gray-50 cursor-pointer"
                >
                    <!-- Background Image: 100% natural, crisp, zero tint for ALL banners -->
                    <img
                        v-if="promo.image"
                        :src="promo.image"
                        :alt="promo.subtitle || promo.title || 'Promotional Banner'"
                        class="w-full h-full object-cover object-center transition-transform duration-500 ease-out group-hover:scale-103"
                        loading="lazy"
                    />

                    <!-- Fallback Only When No Image Exists -->
                    <div
                        v-else
                        class="w-full h-full bg-gradient-to-r flex flex-col justify-end p-6 text-white"
                        :class="promo.gradient || 'from-teal-600 to-cyan-700'"
                    >
                        <span v-if="promo.eyebrow" class="text-xs uppercase font-bold tracking-wider mb-1 opacity-90">
                            {{ promo.eyebrow }}
                        </span>
                        <h3 v-if="promo.title && promo.title !== '#'" class="font-extrabold text-2xl mb-1">
                            {{ promo.title }}
                        </h3>
                        <p v-if="promo.subtitle" class="text-sm opacity-90 mb-3">
                            {{ promo.subtitle }}
                        </p>
                        <span v-if="promo.cta && promo.cta !== '#'" class="inline-block px-4 py-2 bg-white text-gray-900 rounded-lg text-xs font-bold self-start">
                            {{ promo.cta }}
                        </span>
                    </div>

                    <!-- Subtle Corner Arrow Badge on Hover -->
                    <div class="absolute bottom-3.5 right-3.5 z-10 opacity-0 group-hover:opacity-100 transition-all duration-200 transform translate-y-1 group-hover:translate-y-0 pointer-events-none">
                        <span class="w-8 h-8 rounded-full bg-white/95 backdrop-blur-md text-gray-800 shadow-md flex items-center justify-center font-bold text-xs">
                            →
                        </span>
                    </div>
                </a>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             BEST PICKS (FILTERABLE PRODUCT CATALOG)
        ════════════════════════════════════════ -->
        <section id="best-picks" class="max-w-7xl mx-auto px-4 py-4">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50">
                    <h2 class="text-[17px] font-bold text-gray-900">🏆 Best Picks</h2>
                    <Link
                        href="/products?collection=best-picks"
                        class="text-[15px] text-primary font-semibold hover:underline flex items-center gap-1"
                    >
                        See all
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </Link>
                </div>

                <ProductGrid
                    :products="dynamicBestPicks"
                    filterable
                    show-footer
                    footer-href="/products?collection=best-picks"
                    @add="addToCart"
                />
            </div>
        </section>

        <!-- ════════════════════════════════════════
             STATS BAR
        ════════════════════════════════════════ -->
        <section class="max-w-7xl mx-auto px-4 py-4">
            <div class="bg-primary rounded-2xl px-8 py-8 shadow-sm">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
                    <div v-for="stat in dynamicStats" :key="stat.label">
                        <p class="text-white font-extrabold text-3xl sm:text-4xl">{{ stat.value }}</p>
                        <p class="text-white/70 text-[14px] mt-0.5">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             APP DOWNLOAD BANNER
        ════════════════════════════════════════ -->
        <section class="max-w-7xl mx-auto px-4 py-4">
            <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-2xl px-8 sm:px-12 py-10 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-md">
                <div>
                    <p class="text-white/60 text-sm font-semibold uppercase tracking-widest mb-3">Download App</p>
                    <h3 class="text-white font-extrabold text-3xl sm:text-4xl mb-2">Order Medicine<br>Anytime, Anywhere</h3>
                    <p class="text-white/70 text-base mb-6">Join 1M+ customers who trust {{ storeName }}</p>
                    <div class="flex gap-3">
                        <a href="#" class="bg-white text-gray-900 font-semibold text-base px-5 py-3 rounded-xl hover:bg-gray-100 transition-colors flex items-center gap-2">
                            <span class="text-xl">🤖</span> Google Play
                        </a>
                        <a href="#" class="bg-white text-gray-900 font-semibold text-base px-5 py-3 rounded-xl hover:bg-gray-100 transition-colors flex items-center gap-2">
                            <span class="text-xl">🍎</span> App Store
                        </a>
                    </div>
                </div>
                <div class="text-9xl opacity-90 hidden sm:block select-none">📱</div>
            </div>
        </section>
    </StoreLayout>
</template>
