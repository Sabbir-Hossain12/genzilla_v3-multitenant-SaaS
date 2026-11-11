<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    brands: {
        type: Array,
        default: () => [],
    },
})

// Brand color palette helper for initials badge when no logo is uploaded
const bgGradients = [
    'from-teal-600 to-emerald-700',
    'from-blue-600 to-cyan-700',
    'from-indigo-600 to-purple-700',
    'from-rose-600 to-pink-700',
    'from-amber-600 to-orange-700',
    'from-emerald-600 to-teal-800',
]

function getBrandGradient(index) {
    return bgGradients[index % bgGradients.length]
}

function getBrandInitials(name) {
    if (!name) return 'B'
    const words = name.replace(/[^a-zA-Z0-9 ]/g, '').trim().split(/\s+/)
    if (words.length >= 2) {
        return (words[0][0] + words[1][0]).toUpperCase()
    }
    return name.substring(0, 2).toUpperCase()
}
</script>

<template>
    <section v-if="brands && brands.length" id="featured-brands" class="max-w-7xl mx-auto px-4 py-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-7 shadow-2xs">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-teal-50 text-primary text-xs font-bold">
                            🏷️
                        </span>
                        <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">
                            Top Brands & Manufacturers
                        </h2>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500">
                        100% genuine pharmaceutical, healthcare & wellness products from certified brands
                    </p>
                </div>

                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Verified Partners
                </span>
            </div>

            <!-- Brands Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5 sm:gap-4">
                <Link
                    v-for="(brand, idx) in brands"
                    :key="brand.id || idx"
                    :href="'/brand/' + (brand.slug || brand.id)"
                    class="group relative flex flex-col items-center text-center p-4 rounded-xl border border-gray-100 hover:border-primary/40 bg-white hover:bg-teal-50/20 transition-all duration-200 hover:shadow-md cursor-pointer overflow-hidden"
                >
                    <!-- Brand Logo or Monogram Card -->
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gray-50 border border-gray-100 p-2 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform duration-200 shrink-0">
                        <img
                            v-if="brand.image"
                            :src="brand.image"
                            :alt="brand.name"
                            class="max-h-full max-w-full object-contain"
                            loading="lazy"
                        />
                        <div
                            v-else
                            class="w-full h-full rounded-xl bg-gradient-to-br flex items-center justify-center text-white font-extrabold text-lg sm:text-xl shadow-xs"
                            :class="getBrandGradient(idx)"
                        >
                            {{ getBrandInitials(brand.name) }}
                        </div>
                    </div>

                    <!-- Brand Name -->
                    <h3 class="text-xs sm:text-[13px] font-bold text-gray-800 group-hover:text-primary transition-colors line-clamp-1 w-full" :title="brand.name">
                        {{ brand.name }}
                    </h3>

                    <!-- Product Count Badge -->
                    <div class="mt-1 flex items-center gap-1 text-[11px] text-gray-400 group-hover:text-teal-700 transition-colors">
                        <span v-if="brand.products_count">
                            {{ brand.products_count }} product{{ brand.products_count === 1 ? '' : 's' }}
                        </span>
                        <span v-else class="text-emerald-600 font-medium">
                            Explore →
                        </span>
                    </div>

                    <!-- Subtle corner indicator on hover -->
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-3.5 h-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>
