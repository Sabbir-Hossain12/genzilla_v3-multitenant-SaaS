<script setup>
import { formatTaka } from '@/composables/useCart'

defineProps({
    product: { type: Object, required: true },
})

defineEmits(['add'])
</script>

<template>
    <div class="prod-card bg-white border border-gray-100 rounded-xl overflow-hidden cursor-pointer flex flex-col">
        <div class="relative bg-white p-3 aspect-square flex items-center justify-center">
            <span
                v-if="product.badge"
                class="absolute top-2 left-2 bg-green-500 text-white text-[12px] font-bold px-2 py-1 rounded"
            >{{ product.badge }}</span>

            <div class="w-full h-full rounded-xl overflow-hidden shrink-0">
                <img :src="product.image" :alt="product.name" class="w-full h-full object-cover" />
            </div>

            <button
                type="button"
                class="absolute bottom-2.5 right-2.5 w-8 h-8 bg-primary hover:bg-primary2 text-white rounded-full flex items-center justify-center text-xl font-bold transition-colors shadow"
                :aria-label="`Add ${product.name} to cart`"
                @click.stop="$emit('add', product)"
            >+</button>
        </div>

        <div class="p-3.5 flex flex-col flex-1">
            <p class="text-[15px] font-medium text-gray-800 leading-snug line-clamp-2 mb-1.5">
                {{ product.name }}
            </p>

            <div class="flex items-center gap-1 mb-1">
                <span class="text-[12px] text-yellow-500">
                    {{ '★'.repeat(product.rating) }}{{ '☆'.repeat(5 - product.rating) }}
                </span>
                <span class="text-[12px] text-gray-400">({{ product.reviews }})</span>
            </div>

            <div class="flex items-center gap-1.5 mt-auto">
                <span class="text-[17px] font-bold text-primary">{{ formatTaka(product.price) }}</span>
                <span class="text-[12px] text-gray-400 line-through">{{ formatTaka(product.original) }}</span>
            </div>
        </div>
    </div>
</template>
