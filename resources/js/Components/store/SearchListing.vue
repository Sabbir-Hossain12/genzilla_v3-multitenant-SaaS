<script setup>
import { ref } from 'vue'

const props = defineProps({
    results: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    query: { type: String, default: '' },
    hasSearched: { type: Boolean, default: false },
    currencySymbol: { type: String, default: '৳' },
    activeIndex: { type: Number, default: -1 },
})

const emit = defineEmits(['select', 'add', 'close', 'hover'])

const failedImages = ref({})

function handleImgError(id) {
    failedImages.value[id] = true
}

function formatPrice(val) {
    if (!val && val !== 0) return ''
    return props.currencySymbol + Number(val).toLocaleString()
}
</script>

<template>
    <div
        class="absolute top-full left-0 right-0 mt-2 z-50 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden text-left"
    >
        <!-- Header Strip -->
        <div
            v-if="!loading && results.length > 0"
            class="px-3.5 sm:px-4 py-2.5 bg-gray-50/90 border-b border-gray-100 flex items-center justify-between text-xs text-gray-500"
        >
            <span class="truncate">
                Products matching <span class="text-gray-900 font-bold">"{{ query }}"</span>
            </span>
            <span class="text-[11px] font-bold text-primary bg-primarylt px-2 py-0.5 rounded-full shrink-0 ml-2">
                {{ results.length }} found
            </span>
        </div>

        <!-- Scrollable content area -->
        <div class="max-h-[60vh] sm:max-h-[460px] overflow-y-auto overscroll-contain divide-y divide-gray-50">
            <!-- Loading Skeleton -->
            <div v-if="loading" class="p-3 sm:p-4 space-y-3">
                <div v-for="n in 3" :key="n" class="flex items-center gap-3 animate-pulse">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-3.5 bg-gray-100 rounded w-3/4"></div>
                        <div class="h-2.5 bg-gray-50 rounded w-1/2"></div>
                    </div>
                    <div class="w-14 h-4 bg-gray-100 rounded shrink-0"></div>
                </div>
            </div>

            <!-- Zero Results State -->
            <div v-else-if="hasSearched && results.length === 0" class="p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-2 text-xl">
                    🔍
                </div>
                <p class="text-sm font-bold text-gray-800">No products found</p>
                <p class="text-xs text-gray-400 mt-1 max-w-xs mx-auto leading-relaxed">
                    No items matched "<span class="font-semibold text-gray-700">{{ query }}</span>". Try searching for generic names like <span class="text-primary font-medium">Paracetamol</span>, <span class="text-primary font-medium">Napa</span>, or <span class="text-primary font-medium">Tablet</span>.
                </p>
            </div>

            <!-- Products Listing -->
            <template v-else-if="results.length > 0">
                <div
                    v-for="(product, idx) in results"
                    :key="product.id"
                    @click="$emit('select', product)"
                    @mouseenter="$emit('hover', idx)"
                    class="p-2.5 sm:p-3 flex items-center justify-between gap-2.5 sm:gap-3 cursor-pointer transition-colors group"
                    :class="activeIndex === idx ? 'bg-primarylt/30' : 'hover:bg-gray-50'"
                >
                    <!-- Product Image + Info -->
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                            <img
                                v-if="product.image && !failedImages[product.id]"
                                :src="product.image"
                                :alt="product.name"
                                @error="handleImgError(product.id)"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                loading="lazy"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center bg-primarylt/30 text-primary text-xl">
                                💊
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <p class="text-xs sm:text-[13.5px] font-bold text-gray-900 group-hover:text-primary transition-colors truncate leading-tight">
                                    {{ product.name }}
                                </p>
                                <span
                                    v-if="product.requires_prescription"
                                    class="text-[9.5px] font-bold px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 border border-purple-200 shrink-0"
                                >
                                    Rx
                                </span>
                                <span
                                    v-if="product.badge"
                                    class="text-[9.5px] font-bold px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0"
                                >
                                    {{ product.badge }}
                                </span>
                            </div>

                            <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5 truncate flex items-center gap-1.5">
                                <span v-if="product.generic_name" class="font-medium text-gray-600">🔬 {{ product.generic_name }}</span>
                                <span v-if="product.generic_name && product.brand">·</span>
                                <span v-if="product.brand" class="font-semibold uppercase text-gray-400">{{ product.brand }}</span>
                                <span v-if="!product.generic_name && !product.brand" class="text-gray-400">{{ product.category }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Price & Quick Add Button -->
                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        <div class="text-right">
                            <p class="text-xs sm:text-[14px] font-extrabold text-primary leading-tight">
                                {{ formatPrice(product.price) }}
                            </p>
                            <p
                                v-if="product.original && product.original > product.price"
                                class="text-[10px] sm:text-[11px] text-gray-400 line-through leading-tight"
                            >
                                {{ formatPrice(product.original) }}
                            </p>
                        </div>

                        <button
                            type="button"
                            @click.stop="$emit('add', product)"
                            class="bg-primary hover:bg-primary2 active:scale-95 text-white font-bold text-xs px-2.5 sm:px-3 py-1.5 rounded-lg transition-all flex items-center gap-1 shadow-2xs cursor-pointer shrink-0"
                            :title="`Add ${product.name} to cart`"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            <span class="hidden sm:inline">Add</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer Strip -->
        <div
            v-if="!loading && results.length > 0"
            class="px-3.5 sm:px-4 py-2 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-400"
        >
            <span>💡 Click item to view cart or '+' to add</span>
            <button
                type="button"
                @click="$emit('close')"
                class="hover:text-gray-600 font-medium cursor-pointer"
            >
                Close (Esc)
            </button>
        </div>
    </div>
</template>
