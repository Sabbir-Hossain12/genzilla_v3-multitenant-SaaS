<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import CategorySidebar from '@/Components/store/CategorySidebar.vue'
import BannerSlider from '@/Components/store/BannerSlider.vue'
import ProductGrid from '@/Components/store/ProductGrid.vue'
import CategoryTiles from '@/Components/store/CategoryTiles.vue'
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
                    <a href="#best-picks" class="text-[15px] text-primary font-semibold hover:underline flex items-center gap-1">
                        See all
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </a>
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
                    <a href="#all-categories" class="text-[15px] text-primary font-semibold hover:underline">See all</a>
                </div>
                <CategoryTiles :tiles="dynamicCategoryTiles" />
            </div>
        </section>

        <!-- ════════════════════════════════════════
             PROMOTIONAL BANNERS GRID
        ════════════════════════════════════════ -->
        <section v-if="dynamicPromos.length" class="max-w-7xl mx-auto px-4 py-2">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div
                    v-for="promo in dynamicPromos"
                    :key="promo.title"
                    class="bg-gradient-to-r rounded-2xl p-7 flex items-center justify-between overflow-hidden relative shadow-sm"
                    :class="promo.gradient"
                >
                    <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full -mr-8 -mt-8 pointer-events-none"></div>
                    <div class="z-10 max-w-[65%]">
                        <p class="text-white/80 text-sm font-medium mb-1.5">{{ promo.eyebrow }}</p>
                        <h3 class="text-white font-extrabold text-2xl sm:text-3xl leading-tight" v-html="promo.title"></h3>
                        <p class="text-white/85 text-sm mt-1.5 mb-4">{{ promo.subtitle }}</p>
                        <a
                            :href="promo.link || '#'"
                            class="bg-white text-sm font-bold px-5 py-2.5 rounded-lg hover:bg-gray-50 transition-colors inline-block shadow-sm"
                            :class="promo.ctaColor"
                        >{{ promo.cta }}</a>
                    </div>
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden relative z-10 ring-4 ring-white/20 shrink-0 bg-white/10">
                        <img
                            :src="promo.image"
                            :alt="promo.subtitle"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             BEST PICKS (FILTERABLE PRODUCT CATALOG)
        ════════════════════════════════════════ -->
        <section id="best-picks" class="max-w-7xl mx-auto px-4 py-4">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50">
                    <h2 class="text-[17px] font-bold text-gray-900">🏆 Best Picks</h2>
                    <a href="#best-picks" class="text-[15px] text-primary font-semibold hover:underline flex items-center gap-1">
                        See all
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </a>
                </div>

                <ProductGrid
                    :products="dynamicBestPicks"
                    filterable
                    show-footer
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
