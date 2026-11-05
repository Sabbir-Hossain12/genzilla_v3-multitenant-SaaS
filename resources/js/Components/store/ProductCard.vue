<script setup>
import { computed } from 'vue'
import { formatTaka } from '@/composables/store/useCart'

const props = defineProps({
    product: { type: Object, required: true },
})

defineEmits(['add'])

const starRating = computed(() => {
    const r = Math.max(0, Math.min(5, Math.round(Number(props.product.rating) || 5)))
    return {
        filled: '★'.repeat(r),
        empty: '☆'.repeat(5 - r),
    }
})
</script>

<template>
    <div class="prod-card bg-white border border-gray-100 rounded-xl overflow-hidden cursor-pointer flex flex-col hover:border-primary/30 transition-all hover:shadow-md">
        <div class="relative bg-white p-3 aspect-square flex items-center justify-center">
            <span
                v-if="product.badge"
                class="absolute top-2 left-2 z-10 bg-green-500 text-white text-[12px] font-bold px-2 py-0.5 rounded shadow-sm"
            >{{ product.badge }}</span>

            <div class="w-full h-full rounded-xl overflow-hidden shrink-0 bg-gray-50 flex items-center justify-center">
                <img
                    :src="product.image || 'https://via.placeholder.com/300'"
                    :alt="product.name"
                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                    loading="lazy"
                />
            </div>

            <button
                type="button"
                class="absolute bottom-2.5 right-2.5 w-8 h-8 bg-primary hover:bg-primary2 text-white rounded-full flex items-center justify-center text-xl font-bold transition-all shadow"
                :aria-label="`Add ${product.name} to cart`"
                @click.stop="$emit('add', product)"
            >+</button>
        </div>

        <div class="p-3.5 flex flex-col flex-1">
            <p
                v-if="product.brand"
                class="text-[12px] font-semibold text-gray-400 uppercase tracking-wider line-clamp-1 mb-0.5"
            >
                {{ product.brand }}
            </p>

            <p class="text-[14px] sm:text-[15px] font-medium text-gray-800 leading-snug line-clamp-2 mb-1.5" :title="product.name">
                {{ product.name }}
            </p>

            <div class="flex items-center gap-1 mb-1">
                <span class="text-[12px] text-amber-500">
                    {{ starRating.filled }}<span class="text-gray-300">{{ starRating.empty }}</span>
                </span>
                <span class="text-[12px] text-gray-400">({{ product.reviews || 0 }})</span>
            </div>

            <div class="flex items-center gap-1.5 mt-auto pt-1">
                <span class="text-[16px] sm:text-[17px] font-bold text-primary">{{ formatTaka(product.price) }}</span>
                <span
                    v-if="product.original && product.original > product.price"
                    class="text-[12px] text-gray-400 line-through"
                >{{ formatTaka(product.original) }}</span>
            </div>
        </div>
    </div>
</template>
